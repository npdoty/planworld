<?php
/* $Id: Snoop.php,v 1.10.2.2 2003/11/02 16:12:35 seth Exp $ */

/* includes */
require_once($_base . 'lib/Planworld.php');
/** TEMPORARY */
require_once($_base . 'backend/epi-utils.php');

/**
 * Snoop functions
 */
class Snoop {
  /**
   * Calls a remote method via xml-rpc.
   * @param method Method to call.
   * @param params Parameters to use.
   * @private
   */
  function _call ($server, $method, $params=null) {
    return xu_rpc_http_concise(array('method' => $method,
				     'args'   => $params, 
				     'host'   => $server['Hostname'], 
				     'uri'    => $server['Path'], 
				     'port'   => $server['Port'], 
				     'debug'  => 0)); // 0=none, 1=some, 2=more
  }

  /**
   * Pulls references from $content.
   * @param content Content to search.
   * @returns matches Array of references.
   * @private
   */
  function _getReferences ($content) {
    /* find references in plan */
    preg_match_all("/!([a-z0-9\-\.@]+)(!|:[^!]+!)/i", $content, $matches, PREG_PATTERN_ORDER);
    return $matches;
  }

  /**
   * void Snoop::addReference ($from, $to)
   * Add a snoop reference by $from for $to
   */
  function addReference ($from, $to, $date=null) {
    $dbh = Planworld::_connect();

    if (!isset($date)) {
      $date = mktime();
    }

    if ($from == 0 || $from == '' || $to == 0 || $to == '')
      return false;

    $query = "INSERT INTO snoop (uid, s_uid, referenced) VALUES ({$to}, {$from}, {$date})";

    $dbh->query($query);
  }

  /**
   * void Snoop::removeReference ($from, $to)
   * Removes a snoop reference by $from for $to
   */
  function removeReference ($from, $to) {
    $dbh = Planworld::_connect();

    $query = "DELETE FROM snoop WHERE uid={$to} AND s_uid={$from}";

    $dbh->query($query);
  }

  /**
   * void Snoop::clearReferences ($uid)
   * Clear all snoop references by $uid
   */
  function clearReferences ($uid) {
    $dbh = Planworld::_connect();

    $query = "DELETE FROM snoop WHERE s_uid={$uid}";

    $dbh->query($query);
  }

  /**
   * void Snoop::clearRemoteReferences ($uid)
   * Clear all remote snoop references by $uid
   */
  function clearRemoteReferences ($node, $uid) {
    Snoop::_call($node, 'planworld.snoop.clear', $uid . '@' . PW_NAME);
  }

  /**
   * void Snoop::process ($user, $new, $old)
   * Find new / removed snoop references in $user's plan.
   */
  function process (&$user, $new, $old) {

    /* find references in old plan */
    $old_matches = Snoop::_getReferences($old);

    /* find references in new plan */
    $new_matches = Snoop::_getReferences($new);

    /* find differences */
    $users_to_add = array_values(array_diff($new_matches[1], $old_matches[1]));
    $users_to_del = array_values(array_diff($old_matches[1], $new_matches[1]));

    foreach ($users_to_add as $u) {
      if (strstr($u, '@')) {
	list($username, $host) = explode('@', $u);
      }

      $sid = Planworld::nameToID($u);
      if (!isset($host) && $sid > 0) {
	/* valid local user */

	Snoop::addReference($user->getUserID(), $sid);
      } else if (isset($host) && $node = Planworld::getNodeInfo($host)) {
	/* remote planworld user */

	if ($node['Version'] < 2) {
	  Snoop::_call($node, 'snoop.addReference', array($username, $user->getUsername() . '@' . PW_NAME));
	} else {
	  Snoop::_call($node, 'planworld.snoop.add', array($username, $user->getUsername() . '@' . PW_NAME));
        }
      }
    }

    foreach ($users_to_del as $u) {
      if (strstr($u, '@')) {
	list($username, $host) = explode('@', $u);
      }

      $sid = Planworld::nameToID($u);
      if (!isset($host) && $sid > 0) {
	/* valid local user */

	Snoop::removeReference($user->getUserID(), $sid);
      } else if (isset($host) && $node = Planworld::getNodeInfo($host)) {
	/* remote planworld user */

	if ($node['Version'] < 2) {
	  Snoop::_call($node, 'snoop.removeReference', array($username, $user->getUsername() . '@' . PW_NAME));
	} else {
	  Snoop::_call($node, 'planworld.snoop.remove', array($username, $user->getUsername() . '@' . PW_NAME));
	}
      }
    }
  }

  function getReferences (&$user, $order='d', $dir='d') {
    $dbh = Planworld::_connect();
  

    /* direction to sort */
    if ($dir == 'a')
      $dir = 'ASC';
    else
      $dir = 'DESC';

    /* attribute to sort by */
    switch ($order) {
    case 'l':
      $order = 'last_update';
      break;
    case 'u':
      $order = 'username';
      break;
    default:
      $order = 'referenced';
    }

    if (is_int($user)) {
      $query = "SELECT s_uid, referenced, username, last_update FROM snoop,users WHERE uid={$user} AND users.id=s_uid ORDER BY {$order} {$dir}";
    } else if (is_string($user)) {
      $query = "SELECT s_uid, referenced, users.username, users.last_update FROM snoop,users,users as u2 WHERE uid=u2.id AND u2.username='{$user}' AND users.id=s_uid ORDER BY {$order} {$dir}";
    } else if (is_object($user)) {
      $query = "SELECT s_uid, referenced, username, last_update FROM snoop,users WHERE uid=" . $user->getUserID() . " AND users.id=s_uid ORDER BY {$order} {$dir}";
    }

    /* execute the query */
    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $return = array();
      if (date('n-j') == '4-1') {
	/* April fool's easter egg */
	$uid = Planworld::getRandomUser();
	$return[] = array("userID" => $uid,
			  "userName" => Planworld::idToName($uid),
			  "date" => mktime(0,0,0,4,1,date('Y')),
			  "lastUpdate" => Planworld::getLastUpdate($uid));
      }
      while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
	$return[] = array("userID" => (int) $row['s_uid'],
			  "userName" => $row['username'],
			  "date" => (int) $row['referenced'],
			  "lastUpdate" => (int) $row['last_update']);
      }
      return $return;
    } else {
      return PLANWORLD_ERROR;
    }
  }

}

?>
