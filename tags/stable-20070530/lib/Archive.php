<?php
/**
 * $Id: Archive.php,v 1.8.2.8 2003/11/02 16:12:35 seth Exp $
 * Planworld archiving functions.
 */

/* includes */
require_once('DB.php');
require_once($_base . 'config.php');
require_once($_base . 'lib/Planworld.php');
/** TEMPORARY */
require_once($_base . 'backend/epi-utils.php');

// Return codes
/** @constant ARCHIVE_OK Operation succeeded. */
define('ARCHIVE_OK', 0);

/** @constant ARCHIVE_ERROR Operation failed. */
define('ARCHIVE_ERROR', -1);

/** @constant ARCHIVE_ERROR_RETRIEVE Retrieval failure. */
define('ARCHIVE_ERROR_RETRIEVE', -2);

/** @constant ARCHIVE_ERROR_EMPTY Empty retrieval result. */
define('ARCHIVE_ERROR_EMPTY', -3);

/**
 * Utility class for handling archives (SQL-backed).
 */
class Archive {
  /**
   * Calls a remote method via xml-rpc.
   * @param method Method to call.
   * @param params Parameters to use.
   * @private
   */
  function _call ($nodeinfo, $method, $params=null) {
     return xu_rpc_http_concise(array('method' => $method,
                                      'args'   => $params, 
                                      'host'   => $nodeinfo['Hostname'], 
                                      'uri'    => $nodeinfo['Path'], 
                                      'port'   => $nodeinfo['Port'], 
                                      'debug'  => 0)); // 0=none, 1=some, 2=more
  }

  /**
   * string|int Archive::_get ($col, $uid, $ts)
   * return the value for $col for $uid's entry @ $ts
   */
  function _get ($col, &$uid, $ts=null, $idx=0) {
    $dbh = Planworld::_connect();

    /* construct the query */
    $query = "SELECT DISTINCT ";

    if (is_array($col) && !empty($col)) {
      for ($i=0;$i<sizeof($col)-1;$i++) {
	$query .= "archive.{$col[$i]}, ";
      }
      $query .= "archive.{$col[$i]}";
    } else {
      $query .= "archive.{$col}";
    }

    $query .= " FROM archive";

    if (is_string($uid)) {
      $query .= ", users WHERE archive.uid=users.id AND users.username='{$uid}'";
    } else if (is_int($uid)) {
      $query .= " WHERE archive.uid=$uid";
    } else if (is_object($uid)) {
      $query .= " WHERE archive.uid=" . $uid->getUserID();
    }

    if (isset($ts)) {
      $query .= " AND archive.posted={$ts}";
    }

    $query .= " ORDER BY archive.posted DESC";

    /* execute the query */
    $result = $dbh->limitQuery($query, $idx, 1);
    $dbh->limit_from = $dbh->limit_count = null;
    if (isset($result) && !DB::isError($result)) {
      if ($result->numRows($result) < 1) return ARCHIVE_ERROR_EMPTY;
      $row = $result->fetchRow();
      if (DB::isError($row)) return ARCHIVE_ERROR_RETRIEVE;
      if (is_array($col) && !empty($col)) {
	$ret = array();
	foreach ($col as $c) {
	  $ret[] = $row[$c];
	}
	return $ret;
      } else {
	return $row[$col];
      }
    } else {
      return ARCHIVE_ERROR;
    }
  }

  /**
   * void Archive::_set ($col, $val, $uid, $ts, $low_priority)
   * set $col to $val for $uid @ $ts
   */
  function _set ($col, $val, $uid, $ts, $low_priority=false) {
    $dbh = Planworld::_connect();

    /* construct the query */
    if (is_string($uid)) {
      $uid = Planworld::nameToID($uid);
    } else if (is_object($uid)) {
      $uid = $uid->getUserID();
    }

    $query = "UPDATE ";
    if ($low_priority)
      $query .= "LOW_PRIORITY ";
    $query .= "archive SET {$col}={$val} WHERE uid={$uid} AND posted={$ts}";

    /* execute the query */
    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      if ($dbh->affectedRows() != 1) {
	return ARCHIVE_ERROR_EMPTY;
      } else {
	return ARCHIVE_OK;
      }
    } else {
      return ARCHIVE_ERROR;
    }
  }

  /**
   * bool Archive::isError ($result)
   * return whether a result (code) is an error
   */
  function isError ($res) {
    if ($res < 0)
      return true;
    else
      return false;
  }

  /**
   * string Archive::getEntry ($uid, $ts)
   * fetch an entry from $uid's archives with timestamp $ts
   */
  function getEntry ($uid, $ts) {
    if (Planworld::isRemoteUser($uid)) {
      list($username, $host) = split('@', Planworld::idToName($uid));
      $nodeinfo = Planworld::getNodeInfo($host);
      if ($nodeinfo['Version'] >= 2) {
        return Archive::_call($nodeinfo, 'planworld.archive.get', array($username, $ts));
      }
    } else {
      return Archive::_get('content', $uid, $ts);
    }
  }

  /**
   * string Archive::getEntryByIndex ($uid, $ts)
   * fetch an entry from $uid's archives with the index $idx
   */
  function getEntryByIndex ($uid, $idx) {
    return Archive::_get(array('posted', 'content'), $uid, null, $idx);
  }

  /**
   * void Archive::saveEntry ($uid, $ts, $content, $name, $public)
   * save $content with $name as $uid's entry @ $ts ($public)
   */
  function saveEntry ($uid, $ts, $content, $name='', $public=false) {
    $dbh = &Planworld::_connect();

    /* construct the query */
    if (is_string($uid)) {
      $uid = Planworld::nameToID($uid);
    } else if (is_object($uid)) {
      $uid = $uid->getUserID();
    }
      
    $perm = ($public) ? 'Y' : 'N';

    $query = "INSERT INTO archive (uid, posted, name, pub, content) VALUES ({$uid}, {$ts}, '{$name}', '{$perm}', '{$content}')";

    /* execute the query */
    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      if ($dbh->affectedRows() < 1) return ARCHIVE_ERROR;
      $query = "UPDATE users SET archive_size=archive_size+1 WHERE id={$uid}";
      Planworld::query($query);
      return ARCHIVE_OK;
    } else {
      return ARCHIVE_ERROR;
    }
  }
  
  /**
   * array($timestamp, $name, $views, $perm) Archive::listEntries ($uid, $public)
   * return a list of entries in $uid's archives (all if
   * $public=false, public only if $public=true)
   */
  function listEntries ($uid, $public=true, $start=null, $end=null) {
    $dbh = Planworld::_connect();

    /* construct the query */
    if (is_string($uid)) {
      $query = "SELECT archive.name, archive.posted, archive.views, archive.pub FROM archive, users WHERE archive.uid=users.id AND users.username='{$uid}'";
    } else if (is_int($uid)) {
      $query = "SELECT archive.name, archive.posted, archive.views, archive.pub FROM archive WHERE archive.uid={$uid}";
    } else if (is_object($uid)) {
      $query = "SELECT archive.name, archive.posted, archive.views, archive.pub FROM archive WHERE archive.uid=" . $uid->getUserID();
    }

    if ($public) {
      $query .= " AND archive.pub='Y'";
    }

    if (isset($start)) {
      $query .= " AND archive.posted >= {$start}";
    }

    if (isset($end)) {
      $query .= " AND archive.posted <= {$end}";
    }

    $query .= " ORDER BY archive.posted DESC";

    /* execute the query */
    $result = $dbh->query($query);
    $return = array();
    if (isset($result) && !DB::isError($result)) {
      if ($result->numRows($result) < 1) return ARCHIVE_ERROR_EMPTY;

      while ($row = $result->fetchRow()) {
	$return[] = array($row['posted'], $row['name'], $row['views'], ($row['pub'] == 'Y') ? true : false);
      }
      return $return;
    } else {
      return ARCHIVE_ERROR;
    }
  }

  /**
   * void Archive::clear ($uid)
   * clear $uid's archives
   */
  function clear ($uid) {
    $dbh = &Planworld::_connect();

    /* construct the query */
    if (is_string($uid)) {
      $uid = Planworld::nameToID($uid);
    } else if (is_object($uid)) {
      $uid = $uid->getUserID();
    }

    $query = "DELETE FROM archive WHERE uid={$uid}";

    /* execute the query */
    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $query = "UPDATE users SET archive_size=0 where id={$uid}";
      Planworld::query($query);
      return ARCHIVE_OK;
    } else {
      return ARCHIVE_ERROR;
    }
  }

  /**
   * bool Archive::delete ($uid, $ts)
   * delete entry with timestamp $ts from $uid's archives
   */
  function delete ($uid, $ts) {
    $dbh = &Planworld::_connect();

    /* construct the query */
    if (is_string($uid)) {
      $uid = Planworld::nameToID($uid);
    } else if (is_object($uid)) {
      $uid = $uid->getUserID();
    }


    if (is_array($ts) && sizeof($ts) > 0) {
      $query = "DELETE FROM archive WHERE uid={$uid} AND (posted={$ts[0]}";
      for ($i=1;$i<sizeof($ts);$i++) {
	$query .= " OR posted={$ts[$i]}";
      }
      $query .= ")";
    } else {
      $query = "DELETE FROM archive WHERE uid={$uid} AND posted={$ts}";
    }

    /* execute the query */
    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      if ($dbh->affectedRows() < 1) {
	return ARCHIVE_EMPTY;
      } else {
        $query = "UPDATE users SET archive_size=0 WHERE id={$uid}";
        Planworld::query($query);
	return ARCHIVE_OK;
      }
    } else {
      return ARCHIVE_ERROR;
    }
  }

  /**
   * void Archive::setPublic ($uid, $ts)
   * make $uid's entry with timestamp $ts public
   */
  function setPublic ($uid, $ts) {
    if (is_array($ts) && sizeof($ts) > 0) {
      $dbh = &Planworld::_connect();
      
      /* construct the query */
      if (is_string($uid)) {
	$uid = Planworld::nameToID($uid);
      } else if (is_object($uid)) {
	$uid = $uid->getUserID();
      }

      $query = "UPDATE archive SET pub='Y' WHERE uid={$uid} AND (posted={$ts[0]}";

      for ($i=1;$i<sizeof($ts);$i++) {
	$query .= " OR posted={$ts[$i]}";
      }

      $query .= ")";
      
      /* execute the query */
      $result = $dbh->query($query);
      if (isset($result) && !DB::isError($result)) {
	if ($dbh->affectedRows() < 1) {
	  return ARCHIVE_EMPTY;
	} else {
          $query = "UPDATE users SET archive_size_pub=archive_size_pub + 1 WHERE id={$uid}";
          Planworld::query($query);
	  return ARCHIVE_OK;
	}
      } else {
	return ARCHIVE_ERROR;
      } 
    } else {
      return Archive::_set('pub', "'Y'", $uid, $ts);
    }
  }

  /**
   * void Archive::setPrivate ($uid, $ts)
   * make $uid's entry with timestamp $ts private
   */
  function setPrivate ($uid, $ts) {
    if (is_array($ts) && sizeof($ts) > 0) {
      $dbh = &Planworld::_connect();
      
      /* construct the query */
      if (is_string($uid)) {
	$uid = Planworld::nameToID($uid);
      } else if (is_object($uid)) {
	$uid = $uid->getUserID();
      }

      $query = "UPDATE archive SET pub='N' WHERE uid={$uid} AND (posted={$ts[0]}";

      for ($i=1;$i<sizeof($ts);$i++) {
	$query .= " OR posted={$ts[$i]}";
      }

      $query .= ")";
      
      /* execute the query */
      $result = $dbh->query($query);
      if (isset($result) && !DB::isError($result)) {
	if ($dbh->affectedRows() < 1) {
	  return ARCHIVE_EMPTY;
	} else {
          $query = "UPDATE users SET archive_size_pub=archive_size_pub - 1 WHERE id={$uid}";
          Planworld::query($query);
	  return ARCHIVE_OK;
	}
      } else {
	return ARCHIVE_ERROR;
      } 
    } else {
      return Archive::_set('pub', "'N'", $uid, $ts);
    }
  }

  /**
   * bool Archive::isPublic ($uid, $ts)
   * returns whether $uid's entry at $ts is public
   */
  function isPublic ($uid, $ts) {
    $perm = Archive::_get('pub', $uid, $ts);
    if (!Archive::isError($perm)) {
      if ($perm == 'Y')
	return true;
      else
	return false;
    } else {
      return $perm;
    }
  }

  /**
   * bool Archive::isPrivate ($uid, $ts)
   * returns whether $uid's entry at $ts is private
   */
  function isPrivate ($uid, $ts) {
    $perm = Archive::isPublic($uid, $ts);
    if (!Archive::isError($perm))
      return !$perm;
    else
      return $perm;
  }

  /**
   * string Archive::getName ($uid, $ts)
   * returns name associated with $uid's entry at $ts
   */
  function getName ($uid, $ts) {
    return Archive::_get('name', $uid, $ts);
  }

  /**
   * string Archive::setName ($uid, $ts, $name)
   * sets name associated with $uid's entry at $ts to $name
   */
  function setName ($uid, $ts, $name) {
    return Archive::_set('name', "'{$name}'", $uid, $ts);
  }

  /**
   * int Archive::getSize ($uid)
   * returns the number of archive entries owned by $uid
   */
  function getSize ($uid) {
    $dbh = &Planworld::_connect();

    /* construct the query */
    if (is_string($uid)) {
      // $query = "SELECT COUNT(*) as size FROM archive, users WHERE archive.uid=users.id AND users.username='{$uid}'";
      $query = "SELECT archive_size AS size FROM users WHERE users.username='{$uid}'";
    } else if (is_int($uid)) {
      // $query = "SELECT COUNT(*) as size FROM archive WHERE archive.uid={$uid}";
      $query = "SELECT archive_size AS size FROM users WHERE users.id={$uid}";
    } else if (is_object($uid)) {
      // $query = "SELECT COUNT(*) as size FROM archive WHERE archive.uid=" . $uid->getUserID();
      $query = "SELECT archive_size AS size FROM users WHERE users.id=" . $uid->getUserID();
    }

    /* execute the query */
    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      if ($result->numRows($result) < 1) return ARCHIVE_EMPTY;
      $row = $result->fetchRow();
      if (DB::isError($row)) return ARCHIVE_ERROR;
      return $row['size'];
    } else {
      return ARCHIVE_ERROR;
    }
  }

  /**
   * int Archive::getViews ($uid, $ts)
   * returns the number of views for $uid's archive entry @ $ts
   */
  function getViews ($uid, $ts) {
    return Archive::_get('views', $uid, $ts);
  }

  /**
   * void Archive::addView ($uid, $ts)
   * add a view to the number of views of $uid's archive entry @ $ts
   */
  function addView ($uid, $ts) {
    return Archive::_set('views', "views + 1", (int) $uid, $ts, true);
  }

  /**
   * bool Archive::hasPublicEntries ($uid)
   * returns whether $uid has any public entries
   */
  function hasPublicEntries ($uid) {
    $dbh = &Planworld::_connect();

    /* construct the query */
    if (is_string($uid)) {
      // $query = "SELECT COUNT(*) AS size FROM archive, users WHERE archive.uid=users.id AND users.username='{$uid}' AND archive.pub='Y'";
      $query = "SELECT archive_size_pub AS size FROM users WHERE username='{$uid}'";
    } else if (is_int($uid)) {
      // $query = "SELECT COUNT(*) AS size FROM archive WHERE archive.uid={$uid} AND archive.pub='Y'";
      $query = "SELECT archive_size_pub AS size FROM users WHERE id={$uid}";
    } else if (is_object($uid)) {
      // $query = "SELECT COUNT(*) AS size FROM archive WHERE archive.uid=" . $uid->getUserID() . " AND archive.pub='Y'";
      $query = "SELECT archive_size_pub AS size FROM users WHERE username=" . $uid->getUserID();
    }

    /* execute the query */
    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      if ($result->numRows($result) < 1) return ARCHIVE_EMPTY;
      $row = $result->fetchRow();
      if (DB::isError($row)) return ARCHIVE_ERROR;
      if ($row['size'] > 0)
	return true;
      else
	return false;
    } else {
      return ARCHIVE_ERROR;
    }
  }

  /**
   * array Archive::sortList ($list, $sortby)
   * returns new list, sorted by $sortby
   */
  function sortList (&$list, $sortby, $reverse=false) {
    switch ($sortby) {
    case 'ts':
      usort($list, '_sortByTS');
      break;
    case 'name':
      usort($list, '_sortByName');
      break;
    case 'views':
      usort($list, '_sortByViews');
      break;
    case 'public':
      usort($list, '_sortByPublic');
      break;
    }
    if ($reverse) {
      $list = array_reverse($list);
    }
  }
}

/**
 * _sortByTS ($a, $b)
 * user-defined sorting function by timestamps
 */
function _sortByTS ($a, $b) {
  if ($a[0] == $b[0]) return 0;
  return ($a[0] < $b[0]) ? -1 : 1;
}

/**
 * _sortByName ($a, $b)
 * user-defined sorting function by name
 */
function _sortByName ($a, $b) {
  return strcmp($a[1], $b[1]);
}

/**
 * _sortByViews ($a, $b)
 * user-defined sorting function by views
 */
function _sortByViews ($a, $b) {
  if ($a[2] == $b[2]) return 0;
  return ($a[2] < $b[2]) ? -1 : 1;
}

/**
 * _sortByPublic ($a, $b)
 * user-defined sorting function by status
 */
function _sortByPublic ($a, $b) {
  if ($a[3] && $b[3]) return 0;
  return (!$a[3] && $b[3]) ? -1 : 1;
}

?>
