<?php
/**
 * $Id: functions.php,v 1.116 2002/02/06 14:42:56 seth Exp $
 * General planworld functions.
 */

/* includes */
require_once($_base . 'lib/Planworld.php');

/*
 * Deprecated functions are marked with "MOVED: " and their new
 * function name
 */

//------------------------------------------------------------
/** MOVED: Planworld::_connect() */
function connectToDatabase() {
  //connect to and select database
  global $dbh;

  if (!$dbh) {
	$dbh = mysql_pconnect(PW_DB_HOST, PW_DB_USER, PW_DB_PASS);
	mysql_select_db(PW_DB_NAME, $dbh);
  }
}
//------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: RemoteUser->forceUpdateLastUpdate(), User->getLastUpdate(), Planworld::getLastUpdate() */
function getLastUpdated ($uid, $host='', $force='N') {
  //given a user's id, returns the lastUpdated
  global $dbh;

  if (is_array($uid) && empty($host)) {

    /* local fetch-by-array */

    /* query construction */
    if (is_int($uid[0])) {
      /* fetch by userid */
      $SQL = "SELECT id, lastUpdate FROM users WHERE id='{$uid[0]}'";
      for ($i=1;$i<sizeof($uid);$i++) {
        $SQL .= " OR id='{$uid[$i]}'";
      }
    } else {
      /* fetch by username */
      $SQL = "SELECT username as id, lastUpdate FROM users WHERE username='{$uid[0]}'";
      for ($i=1;$i<sizeof($uid);$i++) {
        $SQL .= " OR username='{$uid[$i]}'";
      }
    }

    $result = mysql_query($SQL, $dbh);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $return["{$row['id']}"] = (int) $row['lastUpdate'];
    }
    mysql_free_result($result);

    return $return;

  } else if (is_int($uid) && empty($host)) {

    /* local fetch-by-userid */

    $SQL = "SELECT lastUpdate FROM users WHERE id={$uid}";
    $result = mysql_query($SQL, $dbh);
    $row = mysql_fetch_row($result);
    mysql_free_result($result);

    return (int) $row[0];

  } else if (is_string($uid) && empty($host)) {

    /* local fetch-by-username */

    $SQL = "SELECT lastUpdate FROM users WHERE username='{$uid}'";
    $result = mysql_query($SQL, $dbh);
    $row = mysql_fetch_row($result);
    mysql_free_result($result);

    return (int) $row[0];

  } else if (is_string($uid) && !empty($host) && $force == 'N') {

    /* remote fetch-by-username from cache */

    $SQL = "SELECT lastUpdate FROM users WHERE username='{$uid}@{$host}'";
    $result = mysql_query($SQL, $dbh);
    $row = mysql_fetch_row($result);
    mysql_free_result($result);

    return (int) $row[0];

  } else if ($node = getNodeInfo($host)) {

    /* remote fetch-by-username (forced) */

    /* initialize the RPC client */
    include_once('XML/RPC.php');
    $client = new XML_RPC_Client($node['Path'], $node['Hostname'], $node['Port']);
    $client->setDebug(false);

    /* send the request */
    if (is_string($uid)) {
      /* fetching an individual */
      $result = $client->send(new XML_RPC_Message('users.getLastUpdate', array(new XML_RPC_Value($uid, 'string'))));
    } else if (is_array($uid)) {
      /* fetching an array */
      $result = $client->send(new XML_RPC_Message('users.getLastUpdate', array(XML_RPC_encode($uid))));
    }

    if (is_object($result)) {
      $val = $result->value();
      if (!$result->faultCode()) {
	/* call was successful */
	if ($val->kindOf() == 'struct' || $val->kindOf() == 'array') {
	  /* decode the received array */
	  $times = XML_RPC_decode($val);

	  /* freshen the cache */
	  foreach ($times as $u=>$t) {
	    mysql_query("UPDATE users SET lastUpdate='{$t}' WHERE username='{$u}@{$host}'", $dbh); 
	  }

	  return $times;
	} else {
	  /* received a single value */

	  /* freshen the cache */
	  $SQL = "UPDATE users SET lastUpdate=" . $val->scalarval() . " WHERE username='{$uid}@{$host}'";
	  mysql_query($SQL, $dbh);

	  return $val->scalarval();
	}
      } else {
	/* call failed */
	/* available debugging information: $result->faultCode(), $result->faultString() */

	//	print "Fault in getLastUpdate: ";
	//	print "Code: " . $result->faultCode() . " Reason '" .$result->faultString()."'<br />";
	return false;
      }
    }
  
  } else {
    /* remote attempt for a node not listed in the nodelist */
    return false;
  }
}
//------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: RemoteUser->forceUpdateLastLogin(), User->getLastLogin(), Planworld::getLastLogin() */
function getLastLogin ($uid, $host='', $force='N') {
  // get the time a user was last logged in
  global $dbh;

  if (is_array($uid) && empty($host)) {

    /* local fetch-by-array */

    /* query construction */
    if (is_int($uid[0])) {
      /* fetch by userid */
      $SQL = "SELECT id, lastOn FROM users WHERE id='{$uid[0]}'";
      for ($i=1;$i<sizeof($uid);$i++) {
        $SQL .= " OR id='{$uid[$i]}'";
      }
    } else {
      /* fetch by username */
      $SQL = "SELECT username as id, lastOn FROM users WHERE username='{$uid[0]}'";
      for ($i=1;$i<sizeof($uid);$i++) {
        $SQL .= " OR username='{$uid[$i]}'";
      }
    }

    $result = mysql_query($SQL, $dbh);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $return["{$row['id']}"] = (int) $row['lastOn'];
    }
    mysql_free_result($result);

    return $return;

  } else if (is_int($uid) && empty($host)) {

    /* local fetch-by-userid */

    $SQL = "SELECT lastOn FROM users WHERE id={$uid}";
    $result = mysql_query($SQL, $dbh);
    $row = mysql_fetch_row($result);
    mysql_free_result($result);

    return (int) $row[0];

  } else if (is_string($uid) && empty($host)) {

    /* local fetch-by-username */

    $SQL = "SELECT lastOn FROM users WHERE username='{$uid}'";
    $result = mysql_query($SQL, $dbh);
    $row = mysql_fetch_row($result);
    mysql_free_result($result);

    return (int) $row[0];

  } else if (is_string($uid) && !empty($host) && $force == 'N') {

    /* remote fetch-by-username from cache */

    $SQL = "SELECT lastOn FROM users WHERE username='{$uid}@{$host}'";
    $result = mysql_query($SQL, $dbh);
    $row = mysql_fetch_row($result);
    mysql_free_result($result);

    return (int) $row[0];

  } else if ($node = getNodeInfo($host)) {

    /* remote fetch-by-username (forced) */

    /* initialize the RPC client */
    include_once('XML/RPC.php');
    $client = new XML_RPC_Client($node['Path'], $node['Hostname'], $node['Port']);
    $client->setDebug(false);

    /* send the request */
    if (is_string($uid)) {
      /* fetching an individual */
      $result = $client->send(new XML_RPC_Message('users.getLastLogin', array(new XML_RPC_Value($uid, 'string'))));
    } else if (is_array($uid)) {
      /* fetching an array */
      $result = $client->send(new XML_RPC_Message('users.getLastLogin', array(XML_RPC_encode($uid))));
    }

    if (is_object($result)) {
      $val = $result->value();
      if (!$result->faultCode()) {
	/* call was successful */
	if ($val->kindOf() == 'struct' || $val->kindOf() == 'array') {
	  /* decode the received array */
	  $times = XML_RPC_decode($val);

	  /* freshen the cache */
	  foreach ($times as $u=>$t) {
	    mysql_query("UPDATE users SET lastOn='{$t}' WHERE username='{$u}@{$host}'", $dbh);
	  }

	  return $times;
	} else {
	  /* received a single value */

	  /* freshen the cache */
	  $SQL = "UPDATE users SET lastOn=" . $val->scalarval() . " WHERE username='{$uid}@{$host}'";
	  mysql_query($SQL, $dbh);

	  return $val->scalarval();
	}
      } else {
	/* call failed */
	/* available debugging information: $result->faultCode(), $result->faultString() */

	//	print "Fault in getLastLogin: ";
	//	print "Code: " . $result->faultCode() . " Reason '" .$result->faultString()."'<br />";
	return false;
      }
    }

  } else {
    /* remote attempt for a node not listed in the nodelist */
    return false;
  }
}
//------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: User->addView() */
function addView ($uid) {
  global $dbh;

  if (is_string($uid)) {
	$SQL = "UPDATE users SET totalViews=totalViews + 1 WHERE username='{$uid}'";
  } else if (is_int($uid)) {
	$SQL = "UPDATE users SET totalViews=totalViews + 1 WHERE id='{$uid}'";
  }
  mysql_query($SQL, $dbh);
}
//------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: Planworld::addUser() */
function addUser ($username) {
  global $dbh;

  if (strstr($username, '@')) {
	$remote = 'Y';
  } else {
	$remote = 'N';
  }
  $SQL = "INSERT INTO users (username, remote) VALUES ('{$username}', '{$remote}')";
  mysql_query($SQL, $dbh);
  return mysql_insert_id($dbh);
}
//------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: Planworld::nameToID() */
function getIdFromName ($userName, $host='') {
  // given a userName, returns the userID
  global $dbh, $planworld_user, $planworld_id, $planworld_target, $planworld_target_id;
  $userName = addslashes($userName);

  // don't execute the query if it's not necessary
  if ($userName == $planworld_user)
	return $planworld_id;
  if ($userName == $planworld_target)
	return $planworld_target_id;

  if (empty($host)) {
    $SQL = "SELECT id FROM users WHERE username='{$userName}'";
    $result = mysql_query($SQL, $dbh);
    if ((mysql_num_rows($result) == 0) && strstr($userName, '@')) {
      mysql_free_result($result);
	  return Planworld::addUser($userName);
    } else if ($row = mysql_fetch_row($result)) {
      $uid = (int) $row[0];
      mysql_free_result($result);
	  return $uid;
    }
  } else if ($node = getNodeInfo($host)) {
    // XML-RPC
    include_once('XML/RPC.php');
    $node = getNodeInfo($host);
    $c = new XML_RPC_Client($node['Path'], $node['Hostname'], $node['Port']);
    $c->setDebug(false);
    $r = $c->send(new XML_RPC_Message('users.getID', array(new XML_RPC_Value($userName, 'string'))));
    $v = $r->value();
    if (!$r->faultCode()) {
      return $v->scalarval();
    } else {
      print "Fault in getIdFromName: ";
      print "Code: " . $r->faultCode() . " Reason '" .$r->faultString()."'<BR>";
    }
  }

  return false;
}
//------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: Planworld::idToName() */
function getNameFromID($id) {
  // given a userID, returns the userName
  global $dbh, $planworld_user, $planworld_id, $planworld_target, $planworld_target_id;
  $id = addslashes($id);

  // don't execute the query if it's not necessary
  if ($id == $planworld_id)
	return $planworld_user;
  if ($id == $planworld_target_id)
	return $planworld_target;

  $SQL = "SELECT username FROM users WHERE id='{$id}'";
  $result = mysql_query($SQL, $dbh);
  $row = mysql_fetch_row($result);
  $name = $row[0];
  mysql_free_result($result);
  return $name;
}
//------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: Planworld::isAdmin(), $user->isAdmin() */
function isAdmin ($uid) {
  // is the user identified by $uid an administrator?
  
  if ((is_int($uid) && $uid == 6) || (is_string($uid) && Planworld::nameToID($uid) == 6)) {
      return true;
  }
  
  return false;
}
//------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: Planworld::isRemoteUser(), $user->isRemoteUser() */
function isRemoteUser ($uid) {
  global $dbh;

  if (is_int($uid)) {
      $SQL = "SELECT remote FROM users WHERE id='{$uid}'";
  } else if (is_string($uid)) {
      $SQL = "SELECT remote FROM users WHERE username='{$uid}'";
  }
  $result = mysql_query($SQL, $dbh);
  $row = mysql_fetch_row($result);
  if ($row[0] == 'Y') {
    mysql_free_result($result);
	return true;
  } else {
    mysql_free_result($result);
	return false;
  }
}
//------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: Planworld::isUser() */
function isUser ($uid) {
  global $dbh;

  if (is_string($uid)) {
	$SQL = "SELECT id FROM users WHERE username='{$uid}'";
  } else if (is_int($uid)) {
	$SQL = "SELECT id FROM users WHERE id='{$uid}'";
  }
  $result = mysql_query($SQL, $dbh);
  if (mysql_num_rows($result) > 0) {
	return true;
  } else {
	return false;
  }
}
//------------------------------------------------------------

//------------------------------------------------------------
/**
 * array($plan, $host, $method) = getPlanText ($uid, $ts)
 * returns plan text, remote host, and method used to fetch $uid's
 * plan (@ $ts if archived)
 */
/** MOVED: User->getPlan() */
function getPlanText ($uid, $ts=null) {
  // XXX accept user as string
  global $dbh, $planworld_user, $planworld_id;
  
  if (isRemoteUser($uid)) {
    list($user, $host) = explode('@', Planworld::idToName($uid));
    if ($node = getNodeInfo($host)) {
      // XML-RPC test
      include_once('XML/RPC.php');
      $c = new XML_RPC_Client($node['Path'], $node['Hostname'], $node['Port']);
      $c->setDebug(false);
      // second param is username, third is whether snitch is enabled
      $r = $c->send(new XML_RPC_Message('plan.getText', array(new XML_RPC_Value($user, 'string'), new XML_RPC_Value($planworld_user . '@' . PW_NAME, 'string'), new XML_RPC_Value(isSnitchRegistered($planworld_id), 'boolean'))));
      $v = $r->value();
      if (!$r->faultCode() && $v->scalarval()) {
        return array($v->scalarval(), $host, 'planworld');
      } else if (!$r->faultCode()) {
	return array('[No Plan]', $host, 'planworld');
      } else {
	// Fault
	// 100: No such user
	return array(false, $host, $r->faultString());
	//        print "Fault in getPlanText: ";
	//        print "Code: " . $r->faultCode() . " Reason '" .$r->faultString()."'<BR>";
      }
    } else {
      return array(finger($host, $user), $host, 'finger');
    }
  } else {
    if ($uid != $planworld_id) {
      addView($uid);
      snitchCheck($planworld_id, $uid);
    }
    if (!isset($ts)) {
      // current plan is requested
      $SQL = "SELECT planText FROM plan WHERE userId='{$uid}'";
      $result = mysql_query($SQL, $dbh);
      $row = mysql_fetch_row($result);
      $plan = $row[0];
      mysql_free_result($result);
      return array($plan, null, null);
    } else {
      // get it from the archive
      require_once('./lib/Archive.php');
      if (Archive::isPublic($uid, $ts) || $uid == $planworld_id) {
	$plan = Archive::getEntry($uid, $ts);
	if (Archive::isError($plan)) {
	  /* TODO: not handled correctly by finger.inc Should display
          error messages in better form.  A correct solution probably
          involves changing the interface that plans are transferred
          in.  (probably a Plan class) */
	  return array('[The entry you requested could not be found.]', null, null);
	} else {
	  return array($plan, null, null);
	}
      } else {
	return array('[You are unable to view this archive entry.]');
      }
    }
  }
}
//------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: User->clearPlan() */
function clearPlan ($uid) {
    global $dbh;
    
    if (is_string($uid)) {
    	$uid = Planworld::nameToID($uid);
    }

    /* delete one's plan */
    $query = "DELETE FROM plan WHERE userId={$uid}";
    mysql_query($query, $dbh);
    
    /* update the last update time */
    $query = "UPDATE users SET lastUpdate=UNIX_TIMESTAMP() WHERE id={$uid}";
    mysql_query($query, $dbh);
    
    /* clear snoop references */
    $query = "DELETE FROM snoop WHERE snoopedBy={$uid}";
    mysql_query($query, $dbh);
}
//------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: Planworld::addLinks() */
function addLinks ($plan, $host='') {
  global $planworld_user;

  // auto-link links
  $plan = preg_replace("/(^|[[:space:]])((https?|ftp|telnet|mailto):\/?\/?[^[:space:]]+)([[:space:]]|$)/", "\\1<a href=\"\\2\">\\2</a>\\4", $plan);

// Josh's regexps:
// ! link:([[:alnum:]?/+-_~&%]+):([[:alnum:][:space:]?'"&~%/+-_]+) ! 
// ! email:([[:alnum:].-_]+):([[:alnum:][:space:]?/+-_]+) ! 

  $plan = preg_replace("/!link:(.+):(.+)!/i", "<a href=\"\\1\">\\2</a>", $plan);
  $plan = preg_replace("/!email:([^!:]+):([^!:]+)!/i", "<a href=\"mailto:\\1\">\\2</a>", $plan);
  $plan = preg_replace("/!((https?|ftp|telnet|mailto):.+):([^:]+)!/i", "<a href=\"\\1\">\\3</a>", $plan);

  // macros
  $plan = str_replace('%user%', $planworld_user, $plan);
  $plan = str_replace('%date%', date('n-j-Y'), $plan);
  $plan = str_replace('%time%', date('g:ia'), $plan);
  $plan = str_replace('%version%', PW_VERSION, $plan);

  // !user! (and !user:description!) notation (with logic for external plans)
  if (empty($host)) {
	$plan = preg_replace('/!([a-z0-9\-\.]+):([^!]+)!/i', "<a href=\"" . PW_URL_INDEX . "?id=\\1\">\\2</a>", $plan);
	$plan = preg_replace('/!([a-z0-9\-\.]+)!/i', "<a href=\"" . PW_URL_INDEX . "?id=\\1\">\\1</a>", $plan);
  } else {
	$plan = preg_replace('/!([a-z0-9\-\.]+):([^!]+)!/i', "<a href=\"" . PW_URL_INDEX . "?id=\\1@{$host}\">\\2</a>", $plan);
	$plan = preg_replace('/!([a-z0-9\-\.]+)!/i', "<a href=\"" . PW_URL_INDEX . "?id=\\1@{$host}\">\\1</a>", $plan);
  }
  $plan = preg_replace('/!([a-z0-9\-\.]+)@' . PW_NAME . ':([^!]+)!/i', "<a href=\"" . PW_URL_INDEX . "?id=\\1\">\\2</a>", $plan);
  $plan = preg_replace('/!(([a-z0-9\-\.]+)@' . PW_NAME . ')!/i', "<a href=\"" . PW_URL_INDEX . "?id=\\2\">\\1</a>", $plan);
  $plan = preg_replace('/!([a-z0-9\-\.]+@[a-z0-9\-\.]+):([^!]+)!/i', "<a href=\"" . PW_URL_INDEX . "?id=\\1\">\\2</a>", $plan);
  $plan = preg_replace('/!([a-z0-9\-\.]+@[a-z0-9\-\.]+)!/i', "<a href=\"" . PW_URL_INDEX . "?id=\\1\">\\1</a>", $plan);

  return $plan;
}
//------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: Snoop::process() */
function snoop ($userid, $text, $old_text='', $date='') {
    // XXX accept numeric userids

  global $dbh, $planworld_user;

  // clear snoop entries for this user

  // get references from previous plan
  preg_match_all("/!([a-z0-9\-\.@]+)(!|:[^!]+!)/i", $old_text, $remote_users, PREG_PATTERN_ORDER);

  // get references from new plan
  preg_match_all("/!([a-z0-9\-\.@]+)(!|:[^!]+!)/i", $text, $matches, PREG_PATTERN_ORDER);

  // find differences
  $users_to_add = array_diff($matches[1], $remote_users[1]);
  $users_to_del = array_diff($remote_users[1], $matches[1]);

  foreach ($users_to_add as $user) {
    if (strstr($user, '@')) {
      list($user, $host) = explode('@', $user);
    }
      if (empty($host) && $sid = Planworld::nameToID($user)) {  // check for valid user
          if (empty($date)) {
              $SQL = "INSERT INTO snoop (userID, snoopedBy, snoopDate) VALUES ('{$sid}', '{$userid}', UNIX_TIMESTAMP(NOW()))";
          } else {
              $SQL = "INSERT INTO snoop (userID, snoopedBy, snoopDate) VALUES ('{$sid}', '{$userid}', '{$date}')";
          }
          mysql_query($SQL, $dbh);
      } else if (isset($host) && $node = getNodeInfo($host)) {
          include_once('XML/RPC.php');
          $c = new XML_RPC_Client($node['Path'], $node['Hostname'], $node['Port']);
          $c->setDebug(false);
          $r = $c->send(new XML_RPC_Message('snoop.addReference', array(new XML_RPC_Value($user, 'string'), new XML_RPC_Value($planworld_user . '@' . PW_NAME, 'string'))));
	if (is_object($r)) {
          $v = $r->value();
          if ($r->faultCode()) {
              // XXX more elegant error handling needed
              print "Fault in snoop: ";
              print "Code: " . $r->faultCode() . " Reason '" .$r->faultString()."'<BR>";
          }
	}
      }
  }
  foreach ($users_to_del as $user) {
    if (strstr($user, '@')) {
      list($user, $host) = explode('@', $user);
    }
      if (empty($host) && $sid = Planworld::nameToID($user)) {
          $SQL = "DELETE FROM snoop WHERE userID='{$sid}' AND snoopedBy='{$userid}'";
          mysql_query($SQL, $dbh);
      } else if (isset($host) && $node = getNodeInfo($host)) {
          include_once('XML/RPC.php');
          $c = new XML_RPC_Client($node['Path'], $node['Hostname'], $node['Port']);
          $c->setDebug(false);
          $r = $c->send(new XML_RPC_Message('snoop.removeReference', array(new XML_RPC_Value($user, 'string'), new XML_RPC_Value($planworld_user . '@' . PW_NAME, 'string'))));
	if (is_object($r)) {
          $v = $r->value();
          if ($r->faultCode()) {
              // XXX more elegant error handling needed
              print "Fault in snoop: ";
              print "Code: " . $r->faultCode() . " Reason '" .$r->faultString()."'<BR>";
          }
	}
      }
  }
}
//------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: Snoop::getReferences() */
function getSnoopRefs ($uid, $order='d', $dir='d') {
  global $dbh;
  

  /* direction to sort */
  if ($dir == 'a')
    $dir = 'ASC';
  else
    $dir = 'DESC';

  /* attribute to sort by */
  switch ($order) {
  case 'l':
    $order = 'lastUpdate';
    break;
  case 'u':
    $order = 'username';
    break;
  default:
    $order = 'snoopDate';
  }

  if (is_int($uid)) {
    $SQL = "SELECT snoopedBy, snoopDate, username, lastUpdate FROM snoop,users WHERE userID='{$uid}' AND users.id=snoopedBy ORDER BY {$order} {$dir}";
  } else if (is_string($uid)) {
    $SQL = "SELECT snoopedBy, snoopDate, users.username, users.lastUpdate FROM snoop,users,users as u2 WHERE userID=u2.id AND u2.username='{$uid}' AND users.id=snoopedBy ORDER BY {$order} {$dir}";
  }
  $result = mysql_query($SQL, $dbh);

  $return = array();
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $return[] = array("userID" => $row['snoopedBy'],
		      "userName" => $row['username'],
		      "date" => $row['snoopDate'],
		      "lastUpdate" => $row['lastUpdate']);
  }
  mysql_free_result($result);
  return $return;
}
//------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: Online::getOnlineUsers() */
function getOnlineUsers () {
  // returns an associative array of online users, last access time, login 
  // time, and what they are currently accessing.

  global $dbh;

  $SQL = "SELECT users.username, Online.LastAccess, Online.Login, Online.What FROM users, Online WHERE users.id = Online.userID ORDER BY LastAccess DESC";
  $result = mysql_query($SQL);
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $return[] = array("name" => $row['username'],
                        "lastAccess" => $row['LastAccess'],
                        "login" => $row['Login'],
                        "what" => $row['What']);
  }   // while $row = fetch_array
  mysql_free_result($result);
  return $return;
}  // function getOnlineUsers

//------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: Planwatch->inPlanwatch() */
function inPlanwatch ($owner, $user) {
    global $dbh;

    if (is_int($owner) && is_int($user)) {
        $SQL = "SELECT watchId FROM planwatch WHERE userId={$owner} AND watchId={$user}";
    } else if (is_string($owner) && is_int($user)) {
        $SQL = "SELECT watchId FROM planwatch, users WHERE userId=users.id AND users.username='{$owner}' AND watchId={$user}";
    } else if (is_int($owner) && is_string($user)) {
        $SQL = "SELECT watchId FROM planwatch, users WHERE userId={$owner} AND watchId=users.id AND users.username='{$user}'";
    } else if (is_string($owner) && is_string($user)) {
        $SQL = "SELECT watchId FROM planwatch, users, users as u2 WHERE userId=users.id AND users.username='{$owner}' AND watchId=u2.id AND u2.username='{$user}'";
    } else {
	return false;
    }

    $result = mysql_query($SQL, $dbh);
    if (mysql_fetch_row($result)) {
        mysql_free_result($result);
        return true;
    } else {
        mysql_free_result($result);
        return false;
    }
}
//------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: prepend.php */
function initialize ($user, $section='') {
  // When starting, retrieve user's last Updated, and their plan
  // $section is the requested section or user, change to $numId (make numeric)
  global $dbh;

  // initiate database connection
  connectToDatabase();

  // check to see if user record exists; create one if not
  if (!isUser($user)) {
	$idNumber = Planworld::addUser($user);
  }

  $section = str_replace(' ', '', $section);
  
  if (strtolower($section) == 'random') {
	$planworld_target_id = getRandomUser();
	$section = Planworld::idToName($planworld_target_id);
  } else {
	$planworld_target_id = Planworld::nameToID($section);
  }
  
  if (!isset($idNumber)) {
	$idNumber = Planworld::nameToID($user);
  }

  // online users code
  // XXX convert this to a function
  /** MOVED: Online::updateUser/addUser() */
  $SQL = "UPDATE Online SET LastAccess=UNIX_TIMESTAMP(NOW()), What='{$section}' WHERE UserID='{$idNumber}'";
  mysql_query($SQL, $dbh);
  if (mysql_affected_rows($dbh) < 1) {
	$SQL = "INSERT INTO Online (UserID, Login, LastAccess, What) VALUES ('{$idNumber}', UNIX_TIMESTAMP(NOW()), UNIX_TIMESTAMP(NOW()), '{$section}')";
	mysql_query($SQL, $dbh);
  }

  clearOnlineUsers();

  return array(strtolower($user), $idNumber, $section, $planworld_target_id);
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: Online::clearIdle() */
function clearOnlineUsers () {
  global $dbh;

  // remove users who have been idle longer than 10 minutes
  $SQL = "DELETE FROM Online WHERE LastAccess < UNIX_TIMESTAMP(NOW()) - 600";
  mysql_query($SQL, $dbh);
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: Planwatch->markSeen() */
function updatePlanwatchView ($id, $dest) {
    // XXX accept strings and ints
  global $dbh;

  $SQL = "UPDATE planwatch SET lastView=UNIX_TIMESTAMP(NOW()) WHERE userId='{$id}' AND watchId='{$dest}'";
  mysql_query($SQL, $dbh);
  return true;
}
//-------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: Planwatch->getNum() */
function getWatchNumber($userId) {
  // returns the number of people in $userName's planWatch
    // XXX accept strings and ints
  global $dbh;
  $userId = addslashes($userId);

  $SQL = "SELECT COUNT(*) FROM planwatch WHERE userId='{$userId}'";
  $result = mysql_query($SQL, $dbh);
  $row = mysql_fetch_row($result);
  $num = (int) $row[0];
  mysql_free_result($result);
  return $num;
}
//------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: Planworld::getAllUsers(), Planworld::getAllUsersWithPlans() */
function getAllUsers ($type='') {
  global $dbh;
  if ($type == 'plans') {
	$SQL = "SELECT username FROM users, plan WHERE users.id=plan.userId ORDER BY username";
  } else if ($type == 'login') {
	$SQL = "SELECT username FROM users WHERE lastOn!=0 ORDER BY username";
  } else {
	$SQL = "SELECT username FROM users ORDER BY username";
  }
  $result = mysql_query($SQL, $dbh);
  $count = mysql_num_rows($result);
  while ($row = mysql_fetch_row($result)) {
	$return[] = $row[0];
  }
  mysql_free_result($result);
  return array($return, $count);
}
//-------------------------------------------------------------

//------------------------------------------------------------
/** MOVED: Planworld::getRandomUser () */
function getRandomUser() {
  global $dbh;

  $SQL = "SELECT userId FROM plan ORDER BY RAND() LIMIT 1";
  $result = mysql_query($SQL, $dbh);
  $row = mysql_fetch_row($result);
  $uid = (int) $row[0];
  mysql_free_result($result);
  return $uid;

}
//------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: Planwatch->add() */
function addToPW ($wid, $uid, $redirect = true) {
    global $dbh;
    
    if (!inPlanwatch($uid, $wid)) {
        if (is_string($wid)) {
            $SQL = "INSERT INTO planwatch (userId, watchId) VALUES ('{$uid}','" . Planworld::nameToID($wid) . "')";
        } else if (is_int($wid)) {
            $SQL = "INSERT INTO planwatch (userId, watchId) VALUES ('{$uid}','{$wid}')";
        }
        mysql_query($SQL, $dbh);
    }
    
    if ($redirect) {
        header("Location: " . PW_URL_INDEX . "?id=edit_pw\n");
        exit();
    }
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: Planwatch->remove() */
function removeFromPW ($wid, $uid, $redirect = true) {
  global $dbh;

  if (is_string($wid)) {
  	$wid = Planworld::nameToID($wid);
  }
  
  $SQL = "DELETE FROM planwatch WHERE userId='{$uid}' AND watchId='{$wid}'";
  mysql_query($SQL, $dbh);

  if ($redirect) {
	header("Location: " . PW_URL_INDEX . "?id=edit_pw\n");
	exit();
  }
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: Planworld::getRandomCookie() */
// fetches a cookie.  If id is empty, fetch a random one.  returns an
// associative array (cookie, author, credit)
function getCookie ($id='') {
  global $dbh;
  $id = addslashes($id);

  $SQL = "SELECT quote, author, submittedBy FROM cookies";
  if (empty($id)) {  // select random cookie
	$SQL .= " ORDER BY RAND() LIMIT 1";
  } else {
	$SQL .= " WHERE cookieId='{$id}' LIMIT 1";
  }
  $result = mysql_query($SQL, $dbh);
  $row = mysql_fetch_array($result, MYSQL_ASSOC);

  $return = array('quote'  => $row['quote'],
				  'author' => $row['author'],
				  'credit' => $row['submittedBy']);
  mysql_free_result($result);
  return $return;
}
//-------------------------------------------------------------

//-------------------------------------------------------------
// fetches news items.  if dates are given, return news items
// from between those dates
// XXX todo: implement selective news
/** MOVED: Planworld::getCurrentNewsItems() */
function getNews ($begin='', $end='') {
  global $dbh;

  $SQL = "SELECT news, date FROM news ORDER BY date DESC";
  $result = mysql_query($SQL, $dbh);
  $return = array();
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $return[] = array('date' => $row['date'],
		      'news' => $row['news']);
  }
  mysql_free_result($result);
  return $return;
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: Skin::getTitleString() */
// returns the title string for a given section
function getTitleString ($section) {
  switch (strtolower($section)) {
  case 'about':
	$title = 'About';
    break;
  case 'prefs':
	$title = 'Edit Your Preferences';
    break;
  case 'stats':
	$title = 'Statistics';
    break;
  case 'news':
	$title = 'Home';
    break;
  case 'snitch':
	$title = 'Snitch';
    break;
  case 'snoop':
	$title = 'Snoop';
	break;
  case 'edit_pw':
	$title = 'Edit Your planWatch';
    break;
  case 'log_out':
	$title = 'Log Out';
    break;
  case 'edit_plan':
	$title = 'Edit Your Plan';
    break;
  case 'help':
	$title = 'Help';
    break;
  case 'del_plan':
	$title = 'Edit Your Plan';
    break;
  case 'whois':
	$title = 'Whois';
    break;
  case 'preview':
	$title = 'Preview Your Plan';
    break;
  case 'archiving':
	$title = 'View the Archives';
	break;
  case 'who':
	$title = 'Online Users';
	break;
  default:
	if ($section)
	  $title = "Finger {$section}";
	else
	  $title = "Home";
	break;
  } // switch

  return $title;
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: Skin::getIncludeFile() */
// returns the include file for a given section
function getIncludeFile ($section) {

  switch (strtolower($section)) {
  case 'about':
	$file = 'about.inc';
    break;
  case 'prefs':
	$file = 'prefs.inc';
    break;
  case 'stats':
	$file = 'stats.inc';
	break;
  case 'news':
	$file = 'home.inc';
	break;
  case 'snitch':
	$file = 'snitch.inc';
	break;
  case 'snoop':
	$file = 'snoop.inc';
	break;
  case 'edit_pw':
	$file = 'planwatchedit.inc';
	break;
  case 'log_out':
	$file = 'home.inc';
	break;
  case 'edit_plan':
	$file = 'edit.inc';
	break;
  case 'help':
	$file = 'faq.inc';
	break;
  case 'del_plan':
	$file = 'delete.inc';
	break;
  case 'whois':
	$file = 'whois.inc';
        break;
  case 'who':
	$file = 'who.inc';
        break;
  case 'preview':
	$file = 'preview.inc';
	break;
  case 'archiving':
	$file = 'archive.inc';
    break;
  default:
	if ($section)
	  $file = "finger.inc";
	else
	  $file = 'home.inc';
	break;
  } // switch

  return $file;
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: User->getWatchOrder() */
// user must be a numeric userid
// $order_type will be one of:
//  alph
//  newest
//  old
function getWatchOrder ($user) {
    // XXX accept strings and ints
  global $dbh;

  $SQL = "SELECT watchOrder FROM users WHERE id='{$user}'";
  $result = mysql_query($SQL);
  $row = mysql_fetch_array($result, MYSQL_ASSOC);
  $order_type = $row['watchOrder'];
  mysql_free_result($result);

  return $order_type;
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: User->setWatchOrder() */
function setWatchOrder ($userid, $val) {
    // XXX accept strings and ints
  global $dbh;
  
  $SQL = "UPDATE users SET watchOrder='{$val}' WHERE id='{$userid}'";
  mysql_query($SQL, $dbh);
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: User->getSnitch() */
function getSnitch ($user) {
    // XXX accept strings and ints
  global $dbh;
  $user = addslashes($user);

  $SQL = "SELECT snitch FROM users WHERE id='{$user}'";
  $result = mysql_query($SQL, $dbh);
  $row = mysql_fetch_array($result, MYSQL_ASSOC);
  $snitch = $row['snitch'];
  mysql_free_result($result);

  return $snitch;
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: User->getSnitch() */
function isSnitchRegistered ($uid) {
    // XXX accept strings and ints
  if (is_string($uid)) {
	$uid = Planworld::nameToID($uid);
  }

  if (getSnitch($uid) == 'Y') {
	return true;
  } else {
	return false;
  }
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: User->setSnitch() */
function setSnitch ($userid, $val) {
    // XXX accept strings and ints
  global $dbh;

  $SQL = "UPDATE users SET snitch='{$val}' WHERE id='{$userid}'";
  mysql_query($SQL, $dbh);
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: User->clearSnitch() */
function clearSnitch ($userid) {
    // XXX accept strings and ints
  global $dbh;

  $SQL = "DELETE FROM snitch WHERE userId='{$userid}'";
  mysql_query($SQL, $dbh);
}
//-------------------------------------------------------------

//-------------------------------------------------------------
function startSnitch ($userid) {
    // XXX accept strings and ints
  global $dbh;

  $SQL = "UPDATE users SET snitch='Y', snitchOn=UNIX_TIMESTAMP(NOW()) WHERE id='{$userid}'";
  mysql_query($SQL, $dbh);
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: User->getSnitchNum() */
function getSnitchNum ($user) {
    // XXX accept strings and ints
  global $dbh;
  $user = addslashes($user);

  $SQL = "SELECT snitchViews from users where id='{$user}'";
  $result = mysql_query($SQL, $dbh);
  $row = mysql_fetch_row($result);
  $num = (int) $row[0];
  mysql_free_result($result);
  return $num;
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: User->setSnitchNum() */
function setSnitchNum ($userid, $val) {
    // XXX accept strings and ints
  global $dbh;

  $SQL = "UPDATE users SET snitchViews='{$val}' WHERE id='{$userid}'";
  mysql_query($SQL, $dbh);
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: User->getWorld() */
function getWorld ($user) {
    // XXX accept strings and ints
  global $dbh;
  $user = addslashes($user);

  $SQL = "SELECT world FROM users WHERE id='{$user}'";
  $result = mysql_query($SQL, $dbh);
  $row = mysql_fetch_row($result);
  $world = $row[0];
  mysql_free_result($result);
  return $world;
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: User->setWorld() */
function setWorld ($userid, $val) {
    // XXX accept strings and ints
  global $dbh;

  $SQL = "UPDATE users SET world='{$val}' WHERE id='{$userid}'";
  mysql_query($SQL, $dbh);
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: User->getArchive() */
function getArchive ($uid) {
  global $dbh;

  if (is_string($uid)) {
    $SQL = "SELECT archive FROM users WHERE username='{$uid}'";
  } else {
    $SQL = "SELECT archive FROM users WHERE id='{$uid}'";
  }
  $result = mysql_query($SQL, $dbh);
  $row = mysql_fetch_row($result);
  $archive = $row[0];
  mysql_free_result($result);
  return $archive;
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: User->setArchive() */
function setArchive ($userid, $val) {
    // XXX accept strings and ints
  global $dbh;

  $SQL = "UPDATE users SET archive='{$val}' WHERE id='{$userid}'";
  mysql_query($SQL, $dbh);
}
//-------------------------------------------------------------

//------- NO LONGER USING THIS---------------------------------
function getThemeTag ($tid) {
  global $dbh;

  $SQL = "SELECT Tag FROM Themes WHERE ID='{$tid}'";
  $result = mysql_query($SQL, $dbh);
  $row = mysql_fetch_row($result);
  $tag = $row[0];
  mysql_free_result($result);
  return $tag;
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: Skin::getThemeDir() */
function getThemeDir($tid){
  global $dbh;

  $SQL = "SELECT Dir FROM Themes WHERE ID='{$tid}'";
  $result = mysql_query($SQL, $dbh);
  $row = mysql_fetch_row($result);
  $dir = $row[0];
  mysql_free_result($result);
  return $dir;

}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: User->getTheme() */
function getTheme ($uid) {
    // XXX accept strings and ints
  global $dbh;

  $SQL = "SELECT themeID FROM users WHERE id='{$uid}'";
  $result = mysql_query($SQL, $dbh);
  $row = mysql_fetch_row($result);
  $tid = (int) $row[0];
  mysql_free_result($result);
  return $tid;
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: User->setTheme() */
function setTheme ($uid, $sid) {
    // XXX accept strings and ints
  global $dbh;

  $SQL = "UPDATE users set themeID='{$sid}' WHERE id='{$uid}'";
  mysql_query($SQL, $dbh);
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: Skin::getThemeList() */
function getThemeList ($sid) {
  global $dbh;

  $SQL = "SELECT * FROM Themes WHERE SkinID='{$sid}' ORDER BY Name";
  $result = mysql_query($SQL, $dbh);
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$return[] = array('ID' => $row['ID'],
			  'Name' => $row['Name']);
  }
  mysql_free_result($result);
  return $return;
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** This is Amherst-specific */
// connect to an LDAP server and get information on a given user
function getUserInfo ($username) {
  // XXX should the ldap connection be shared?

  if (!defined('PW_LDAP_ENABLE'))
	return false;

  $username = addslashes($username);
  $ds = ldap_connect(PW_LDAP_HOST);

  if (!$ds)
	return false;

  $r = ldap_bind($ds);  // bind anonymously
  $sr = ldap_search($ds, PW_LDAP_BASE, "uid={$username}");
  if (ldap_count_entries($ds, $sr) == 0)
	return false;
  
  $info = ldap_get_entries($ds, $sr);
  $return['Name']= $info[0]["cn"][0];
  $return['GradYear'] = $info[0]["gradyear"][0];

  ldap_unbind($ds);

  return $return;
}
//-------------------------------------------------------------

//-------------------------------------------------------------
// HACK: $bool is probably not the best way to handle this (used only
// in layout/1/finger.inc
// XXX add timezone
/** MOVED: Planworld::getDisplayDate() */
function getDisplayDate ($date, $bool=true) {
  if (!$date && $bool) {
    return false;
  } else if (!$date && !$bool) {
    return "Never";
  }
  if (date('n-j-y') == date('n-j-y', $date)) {
    return 'Today, ' . date('g:i a', $date);
  } else if (date('n-j-y') == date('n-j-y', $date + 86400)) {
    return 'Yesterday, ' . date('g:i a', $date);
  } else {
    return date('n-j-y, g:i a', $date);
  }
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: Planworld::getNodeInfo() */
function getNodeInfo ($node) {
  global $dbh;

  $SQL = "SELECT * FROM Nodes WHERE Name='{$node}' LIMIT 1";

  $result = mysql_query($SQL, $dbh);
  if ($row = mysql_fetch_array($result)) {
    $return = array('Name' => $row['Name'],
		    'Hostname' => $row['Hostname'],
		    'Path' => $row['Path'],
		    'Port' => (int) $row['Port']);
    mysql_free_result($result);
    return $return;
  } else {
    mysql_free_result($result);
    return false;
  }
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: User->setPreference() */
function setPreference ($uid, $name, $val) {
    // XXX accept strings and ints
  global $dbh;
  $SQL = "REPLACE INTO Preferences (UserID, Name, Value) VALUES ('{$uid}', '{$name}', '{$val}')";
  mysql_query($SQL, $dbh);
}
//-------------------------------------------------------------

//-------------------------------------------------------------
/** MOVED: User->getPreference() */
function getPreference ($uid, $name) {
    // XXX accept strings and ints
  global $dbh;
  $SQL = "SELECT Value FROM Preferences WHERE UserID='{$uid}' AND Name='{$name}'";
  $result = mysql_query($SQL, $dbh);
  while ($row = mysql_fetch_row($result)) {
    $ret[] = $row[0];
  }
  mysql_free_result($result);
  return $ret;
}
//-------------------------------------------------------------

/**
 * Finger gateway.
 * @param $host Host to query.
 * @param $user Username to query (optional).
 * @param $port Port number to use (optional).
 * @returns Complete contents of plan.
 */
/** MOVED: FingerUser */
function finger ($host, $user='', $port=79) {
  $rc = fsockopen($host, $port, $errno, $errstr, 2);
  if (!$rc)
	return false;
  fputs($rc, "$user\n");
  socket_set_timeout($rc, 4);
  $str = fread($rc, 1000000);
  fclose($rc);
  if ($str)
	return $str;
  else
	return false;
}
?>
