<?php
/**
 * $Id: Online.php,v 1.6.2.2 2003/11/02 16:12:35 seth Exp $
 * Utility class for determining online users.
 */

require_once($_base . 'lib/User.php');
require_once($_base . 'lib/Planworld.php');

class Online {
  /**
   * void Online::clearIdle ()
   * Removes users who have been idle too long
   */
  function clearIdle () {
    $dbh = Planworld::_connect();

    $query = "DELETE FROM online WHERE last_access < " . (mktime() - PW_IDLE_TIMEOUT);
    $dbh->query($query);
  }

  /**
   * void Online::updateUser (&$user, &$target)
   * Update's $user's status to $target
   */
  function updateUser (&$user, &$target) {
    $dbh = Planworld::_connect();

    $query = "UPDATE online SET last_access=" . mktime() . ", what='";
    if (is_object($target)) {
      $query .= $target->getUsername();
    } else {
      $query .= addslashes($target);
    }
    $query .= "' WHERE uid=" . $user->getUserID();

    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      if ($dbh->affectedRows() < 1) {
	return Online::addUser($user, $target);
      } else {
	return true;
      }
    } else {
      return PLANWORLD_ERROR;
    }
  }

  /**
   * void Online::addUser($user, $target)
   * Adds $user to the list of online users (with status $target)
   */
  function addUser (&$user, $target) {
    $dbh = Planworld::_connect();

    $query = "INSERT INTO online (uid, login, last_access, what) VALUES (" . $user->getUserID() . ", " . mktime() . ", " . mktime() . ", '";
    
    if (is_object($target)) {
      $query .= $target->getUserName();
    } else {
      $query .= $target;
    }

    $query .= "')";

    $dbh->query($query);
  }

  /**
   * void Online::removeUser ($user)
   * Removes $user from the list of online users
   */
  function removeUser (&$user) {
    $dbh = Planworld::_connect();

    $query = "DELETE FROM online WHERE uid=" . $user->getUserID();
    $dbh->query($query);
  }

  /**
   * array Online::getOnlineUsers ()
   * Returns a list of all users who are currently online.
   */
  function getOnlineUsers () {
    $dbh = Planworld::_connect();

    $query = "SELECT users.username, online.last_access, online.login, online.what FROM users, online WHERE users.id = online.uid ORDER BY last_access DESC";

    /* execute the query */
    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $return = array();
      while ($row = $result->fetchRow()) {
	$return[] = array('name' => $row['username'],
			  'lastAccess' => (int) $row['last_access'],
			  'login' => (int) $row['login'],
			  'what' => $row['what']);
      }
      return $return;
    } else {
      return PLANWORLD_ERROR;
    }
  }
}
?>
