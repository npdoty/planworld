<?php
/**
 * Send functions.
 */

require_once($_base . 'lib/Planworld.php');

class Send {

  /**
   * Return all messages between $uid and $to_uid
   */
  function getMessages ($uid, $to_uid) {
    $dbh = Planworld::_connect();

    $query = "UPDATE send SET seen=" . mktime() . " WHERE uid={$to_uid} AND to_uid={$uid} AND seen=0";
    $dbh->query($query);

    $query = "SELECT uid, to_uid, sent, message FROM send WHERE (uid={$uid} AND to_uid={$to_uid}) OR (uid={$to_uid} AND to_uid={$uid}) ORDER BY sent ASC";
    $result = $dbh->query($query);

    if (isset($result) && !DB::isError($result)) {
      $return = array();
      while ($row = $result->fetchRow()) {
        if (preg_match("/^(\[fwd:.+\])(.*)$/", $row['message'], $matches)) {
          $message = "<span class=\"forward\">{$matches[1]}</span>{$matches[2]}";
        } else {
          $message = $row['message'];
        }
        $return[] = array("uid" => (int) $row['uid'], "to_uid" => (int) $row['to_uid'], "sent" => (int) $row['sent'], "message" => $message);
      }
      return $return;
    } else {
      return PLANWORLD_ERROR;
    }
  }

  /**
   * Send a message from $uid to $to_uid
   */
  function sendMessage ($uid, $to_uid, $message) {
    if (Planworld::isRemoteUser($to_uid)) {
      list($to_user, $host) = split("@", Planworld::idToName($to_uid));
      $from_user = Planworld::idToName($uid) . "@" . PW_NAME;
      $nodeinfo = Planworld::getNodeInfo($host);
      // make xml-rpc call
      xu_rpc_http_concise(array('method' => 'planworld.send.sendMessage',
                                'args'   => array($from_user, $to_user, $message), 
                                'host'   => $nodeinfo['Hostname'], 
                                'uri'    => $nodeinfo['Path'], 
                                'port'   => $nodeinfo['Port'], 
                                'debug'  => 0));
      $query = "INSERT INTO send (uid, to_uid, sent, seen, message) VALUES ({$uid}, {$to_uid}, " . mktime() . ", " . mktime() . ", '" . htmlentities(strip_tags(addslashes($message))) . "')";
    } else {
      $fwd = Planworld::getPreference($to_uid, 'send_forward');
      if ($fwd) {
        // forward the message if necessary
        $fwd_uid = Planworld::nameToId($fwd);
	error_log("forwarding to ${fwd_uid} ({$fwd})");
        if (Planworld::isRemoteUser($fwd_uid)) {
          $fwd_message = "[fwd:" . Planworld::idToName($to_uid) . "@" . PW_NAME . "] " . $message;
          list($to_user, $host) = split("@", $fwd);
	  if (!Planworld::isRemoteUser($uid)) {
            $from_user = Planworld::idToName($uid) . "@" . PW_NAME;
	  } else {
            $from_user = Planworld::idToName($uid);
	    list($f_user, $f_host) = split('@', $from_user);
	    if ($f_host == $host) {
              $from_user = $f_user;
	    }
	  }
          $nodeinfo = Planworld::getNodeInfo($host);
          // make xml-rpc call
          xu_rpc_http_concise(array('method' => 'planworld.send.sendMessage',
                                    'args'   => array($from_user, $to_user, $fwd_message),
                                    'host'   => $nodeinfo['Hostname'],
                                    'uri'    => $nodeinfo['Path'],
                                    'port'   => $nodeinfo['Port'],
                                    'debug'  => 0));
        } else {
          $fwd_message = "[fwd:" . Planworld::idToName($to_uid) . "] " . $message;
          Planworld::query("INSERT INTO send (uid, to_uid, sent, message) VALUES ({$uid}, {$fwd_uid}, " . mktime() . ", '" . htmlentities(strip_tags(addslashes($fwd_message))) . "')");
        }
      }
      $query = "INSERT INTO send (uid, to_uid, sent, message) VALUES ({$uid}, {$to_uid}, " . mktime() . ", '" . htmlentities(strip_tags(addslashes($message))) . "')";
    }
    Planworld::query($query);
  }

}
?>
