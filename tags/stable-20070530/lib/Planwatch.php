<?php
/* $Id: Planwatch.php,v 1.9.2.7 2003/11/02 16:12:35 seth Exp $ */

/* includes */
require_once($_base . 'lib/Planworld.php');

/**
 * Planwatch
 */
class Planwatch {
  var $changed;
  var $dbh;
  var $planwatch;
  var $groupData;
  var $groups;
  var $numNew = 0;
  var $user;

  /**
   * void Planwatch ($user)
   * Creates a planwatch for $user
   */
  function Planwatch (&$user) {
    $this->user = $user;
    $this->dbh = &Planworld::_connect();
    if ($this->user->isUser()) {
      $this->load();
    }
  }

  /**
   * void load ()
   * Loads planwatch data into this object.
   */
  function load ($sort = null) {
    /* assemble the query */
    $query = "SELECT u.username, u.id, p.last_view, u.last_update, g.name AS name, g.gid AS gid, g.uid AS owner, m.seen AS hasmessage FROM pw_groups AS g, planwatch AS p, users AS u LEFT JOIN message AS m ON (m.uid=p.w_uid AND m.to_uid=p.uid) WHERE p.uid=" . $this->user->getUserID() . " AND p.w_uid=u.id AND g.gid=p.gid ORDER BY g.pos, g.name,";

    if (!isset($sort)) {
      $sort = $this->user->getWatchOrder();
    }

    switch ($sort) {
    case 'alph':
      $query .= "u.username";
      break;
    case 'newest':
      $query .= "u.last_update DESC, u.username";
      break;
    case 'old':
      $query .= "u.last_update, u.username";
      break;
    default:
      $query .= "u.username";
    }

    /* execute the query */
    $result = $this->dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $this->planwatch = array();
      $this->groupData = array();
      // initalize send group to display first
      $this->groupData['Stalkernet'] = array();
      $this->groupData['Send'] = array();
      // reset this; groups may have changed ?!
      $this->groups = array();
      while ($row = $result->fetchRow()) {
	/* assemble the array */
	$user = addslashes($row['username']);
	$group = &$row['name'];
	/* create the (non-group) entry */
	$this->planwatch[$user] = array((int) $row['id'],
					(int) $row['last_update'],
					(int) $row['last_view'],
					false,
					((isset($row['hasmessage']) && $row['hasmessage'] == 0) ? true : false));
	/* create a pointer to this entry within the appropriate group */
	$this->groupData[$group][$user] = &$this->planwatch[$user];

	/* if it's new, increment the number of new plans */
	if (($row['last_update']) > ($row['last_view'])) {
	  $this->numNew++;
	}
      }
    }

    /* get snoop group */
    foreach (Snoop::getReferences($this->user) as $u) {
      $username = $u['userName'];
      if (!isset($this->planwatch[$username])) {
	/* create a new entry if one doesn't already exist */
	$this->planwatch[$username] = array($u['userID'], $u['lastUpdate'], 9999999999);
      } else {
        $this->planwatch[$username]['count'] = 2;
      }
      $this->groupData['Snoop'][$username] = &$this->planwatch[$username];
    }

    /* get send group */
    $query = "SELECT u.username, u.id, u.last_update FROM users AS u INNER JOIN message ON u.id=message.uid LEFT JOIN online ON message.uid=online.uid WHERE message.to_uid=" . $this->user->getUserID() . " AND message.seen=0 ORDER BY username";

    /* execute the query */
    $result = $this->dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      while ($row = $result->fetchrow()) {
	$username = $row['username'];
	if (!isset($this->planwatch[$username])) {
	  $this->planwatch[$username] = array($row['id'], $row['last_update'], 9999999999, false, true);
	} else if (isset($this->groupData['Snoop'][$username])) {
	  $this->planwatch[$username]['count'] = 3;
	} else {
	  $this->planwatch[$username]['count'] = 2;
	}
	$this->groupData['Stalkernet'][$username] = &$this->planwatch[$username];
      }
    }

    /* get secondary send group */
    $query = "SELECT username, id, last_update, sent, seen FROM send, users WHERE send.uid=users.id AND to_uid=" . $this->user->getUserID() . " AND seen=0 ORDER BY username";

    $result = $this->dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      while ($row = $result->fetchrow()) {
        $username = $row['username'];
        if (!isset($this->planwatch[$username])) {
          $this->planwatch[$username] = array($row['id'], $row['last_update'], 9999999999, false, true);
        } else if (isset($this->groupData['Snoop'][$username])) {
          $this->planwatch[$username]['count'] = 3;
        } else {
          $this->planwatch[$username]['count'] = 2;
        }
        $this->groupData['Send'][$username] = &$this->planwatch[$username];
      }
    }

    if (empty($this->groupData['Stalkernet'])) unset($this->groupData['Stalkernet']);
    if (empty($this->groupData['Send'])) unset($this->groupData['Send']);

    $this->changed = false;
  }

  /**
   * void save ()
   * Saves planwatch data.
   */
  function save () {
    if ($this->changed) {
      $this->dbh = Planworld::_connect();
      foreach ($this->planwatch as $u=>$entry) {
	if (isset($entry[3]) && $entry[3]) {
	  /* entry was changed; let's save it */
	  $query = "UPDATE planwatch SET last_view=" . $entry[1] . " WHERE uid=" . $this->user->getUserID() . " AND w_uid=" . Planworld::nameToID($u);
	  $this->dbh->query($query);
	}
      }
    }
  }

  /**
   * bool inPlanwatch ($uid)
   * Returns whether $uid is in this user's planwatch or not.
   */
  function inPlanwatch ($uid) {
    if (is_object($uid)) {
      $username = $uid->getUsername();
    } else if (is_string($uid)) {
      $username = $uid;
    } else {
      return false;
    }

    if (isset($this->planwatch[$username]) && $this->planwatch[$username][2] != 9999999999) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * void markSeen ($uid)
   * Marks $uid as having had his/her plan read.
   */
  function markSeen ($uid) {
    if (is_object($uid)) {
      if ($this->planwatch[$uid->getUsername()][1] > $this->planwatch[$uid->getUsername()][2]) {
	$this->numNew--;
      }

      /* update in-place and mark as changed */
      /* set view time to now */
      $this->planwatch[$uid->getUsername()][2] = mktime();
      /* set changed flag to true */
      $this->planwatch[$uid->getUsername()][3] = true;

      $this->changed = true;
    }
  }

  /**
   * int getNumNew ()
   * Returns the number of users that are marked as new.
   */
  function getNumNew () {
    return $this->numNew;
  }

  /**
   * int getNum ()
   * Returns the number of users on said planwatch.
   */
  function getNum () {
    return sizeof($this->planwatch);
  }

  /**
   * array getList ()
   * Returns an array containing this user's planwatch.
   */
  function &getList () {
    return $this->groupData;
  }

  /**
   * array getGroups ()
   * Returns an array with all of the groups.
   */
  function getGroups () {
    $query = "SELECT gid, uid AS owner, name, pos FROM pw_groups WHERE uid=0 OR uid IS NULL OR uid=" . $this->user->getUserID() . " ORDER BY pos, name";
    
    /* execute the query */
    $result = $this->dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $this->groups = array();
      while ($row = $result->fetchRow()) {
	$group = $row['name'];
	$this->groups[$group] = array((int) $row['gid'],
				      ($row['owner'] == $this->user->getUserID()) ? true : false,
				      (int) $row['pos']);
      }
      return $this->groups;
    } else {
      return PLANWORLD_ERROR;
    }
  }

  /**
   * void move ($uid, $gid)
   * Moves $uid into group $gid.
   */
  function move ($uid, $gid) {
    if (is_int($uid)) {
      $query = "UPDATE planwatch SET gid={$gid} WHERE w_uid={$uid} AND uid=" . $this->user->getUserID();
    } else {
      $query = "UPDATE planwatch SET gid={$gid} WHERE w_uid=" . Planworld::nameToID($uid) . " AND uid=" . $this->user->getUserID();
    }

    $this->dbh->query($query);
  }

  /**
   * int addGroup ($name)
   * Create a group named $name
   */
  function addGroup ($name) {
    $id = (int) $this->dbh->nextId('groupid');
    $query = "INSERT INTO pw_groups (gid, uid, name) VALUES ({$id}, " . $this->user->getUserID() . ", '{$name}')";

    $this->dbh->query($query);
  }

  /**
   * void removeGroup ($gid)
   * Remove group with id $gid.
   */
  function removeGroup ($gid) {
    /** TODO: accept arrays of gids */

    /* delete the group */
    $query = "DELETE FROM pw_groups WHERE gid={$gid} AND uid=" . $this->user->getUserID();
    $this->dbh->query($query);

    /* move entries from that group into the unsorted category */
    $query = "UPDATE planwatch SET gid=1 WHERE gid={$gid} AND uid=" . $this->user->getUserID();
    $this->dbh->query($query);
  }

  function renameGroup ($gid, $name) {
    $query = "UPDATE pw_groups SET name='{$name}' WHERE gid={$gid} AND uid=" . $this->user->getUserID();
    $this->dbh->query($query);
  }

  /**
   * void remove ($uid)
   * Removes $uid from this user's planwatch.
   */
  function remove ($uid) {
    unset($this->planwatch[$uid]);

    if (is_int($uid)) {
      $query = "DELETE FROM planwatch WHERE w_uid={$uid} AND uid=" . $this->user->getUserID();
    } else {
      $query = "DELETE FROM planwatch WHERE w_uid=" . Planworld::nameToID($uid) . " AND uid=" . $this->user->getUserID();
    }
    $this->dbh->query($query);
  }

  /**
   * void add ($uid)
   * Adds $uid to this user's planwatch.
   */
  function add ($uid) {
    /* no need to fill this entry, as the planwatch will probably be reloaded before it's used */

    if (is_int($uid)) {
      $query = "INSERT INTO planwatch (w_uid, uid) VALUES ({$uid}," . $this->user->getUserID() . ")";
    } else {
      $query = "INSERT INTO planwatch (w_uid, uid) VALUES (" . Planworld::nameToID($uid) . "," . $this->user->getUserID() . ")";
    }

    $this->dbh->query($query);
  }
}

?>
