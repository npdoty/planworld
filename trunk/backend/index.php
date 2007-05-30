<?php
/**
 * $Id: index.php,v 1.21.4.1 2003/10/12 14:08:55 seth Exp $
 * XML-RPC back-end.
 * This consists of xml-rpc wrappers for functions to provide remote
 * apps with the ability to make calls on the local system.  See
 * xmlrpc.org for more information.
 */

$_base = dirname(__FILE__) . '/../';

require_once($_base . 'config.php');
require_once($_base . 'lib/Online.php');
require_once($_base . 'lib/Planworld.php');
require_once($_base . 'lib/Send.php');
require_once($_base . 'lib/Snoop.php');
require_once($_base . 'lib/Stats.php');
require_once($_base . 'lib/User.php');

function xmlrpc_getVersion ($method_name, $params) {
  return 2;
}

function xmlrpc_getText ($method_name, $params) {

  $uid = &$params[0];
  /* remote user (for snitch) */
  $r_uid = addslashes($params[1]);
  /* is snitch enabled for the remote user? */
  $isSnitch = $params[2];

#  error_log(date("[m/d/Y h:i:s] ") . "planworld.plan.getText: L:${uid}, R:${r_uid}, S:${isSnitch} (${_SERVER['REMOTE_ADDR']})\n", 3, "/tmp/planworld.log");

  $r_user = User::factory($r_uid);
  // error_log(date("[m/d/Y h:i:s] ") . "planworld.plan.getText: ${r_user->getUsername()}\n", 3, "/tmp/planworld.log");

  if (is_string($uid) && Planworld::isUser(addslashes($uid))) {
    $user = User::factory($uid);
  } else {
#    error_log(date("[m/d/Y h:i:s] ") . "planworld.plan.getText: No such user: ${uid}\n", 3, "/tmp/planworld.log");
    return array('faultCode' => 800,
		 'faultString' => 'No such user');
  }

  if (!$user->getWorld()) {
    $err = "[This user's plan is not available]";
#    error_log(date("[m/d/Y h:i:s] ") . "planworld.plan.getText: Plan not available: ${uid}\n", 3, "/tmp/planworld.log");
    xmlrpc_set_type(&$err, 'base64');
    return $err;
  }

  if ($isSnitch) {
    $user->addSnitchView($r_user);
  }

  $text = $user->getPlan($r_user);
  xmlrpc_set_type($text, 'base64');
  return $text;
}

function xmlrpc_getLastLogin ($method_name, $params) {

  $uid = array_pop($params);

  if (is_array($uid)) {
    return Planworld::getLastLogin($uid);
  } else if (is_string($uid) && Planworld::isUser($uid)) {
    return Planworld::getLastLogin($uid);
  } else {
    return array('faultCode' => 800,
		 'faultString' => 'No such user');
  }
}

function xmlrpc_getLastUpdate ($method_name, $params) {

  $uid = array_pop($params);

  if (is_array($uid)) {
    return Planworld::getLastUpdate($uid);
  } else if (is_string($uid) && Planworld::isUser($uid)) {
    return Planworld::getLastUpdate($uid);
  } else {
    return array('faultCode' => 800,
		 'faultString' => 'No such user');
  }
}

function xmlrpc_getNodes ($method_name, $params) {
  return Planworld::getNodes();
}

function xmlrpc_getNumUsers ($method_name, $params) {
  $type = &$params[0];
  $since = &$params[1];

  if ($type == 'all') {
    return Stats::getNumUsers();
  } else if ($type == 'login') {
    if (!empty($since)) {	
      return Stats::getNumLoggedIn($since);
    } else {
      return Stats::getNumLoggedIn();
    }
  } else {
    return array('faultCode' => 801,
		 'faultString' => 'Method not supported');
  }
}

function xmlrpc_getNumPlans ($method_name, $params) {
  $since = &$params[0];

  if (!empty($since)) {	
    return Stats::getNumPlans($since);
  } else {
    return Stats::getNumPlans();
  }
}

function xmlrpc_getNumViews ($method_name, $params) {
  return Stats::getTotalPlanViews();
}

function xmlrpc_getNumHits ($method_name, $params) {
  return Stats::getNumHits();
}

function xmlrpc_getNumSnitchRegistered ($method_name, $params) {
  return Stats::getNumSnitchRegistered();
}

function xmlrpc_addSnoopReference ($method_name, $params) {
  $uid = &$params[0];
  $sbid = &$params[1];

  if (!$uid = Planworld::nameToID($uid)) {
    return false;
  }
  if (!$sbid_id = Planworld::nameToID($sbid)) {
    $sbid_id = Planworld::addUser($sbid);
  }

  Snoop::addReference($sbid_id, $uid);

  return true;
}

function xmlrpc_removeSnoopReference ($method_name, $params) {
    $uid = &$params[0];
    $sbid = &$params[1];

    if (!$uid = Planworld::nameToID($uid)) {
      return false;
    }
    $sbid = Planworld::nameToID($sbid);

    Snoop::removeReference($sbid, $uid);

    return true;
}

function xmlrpc_clearSnoop ($method_name, $params) {
  $uid = &$params[0];

  echo $uid;

  Snoop::clearReferences(Planworld::nameToID($uid));

  return true;
}

function xmlrpc_whois ($method_name, $params) {

  $type = $params[0];
  
  if (isset($type) && $type == 'plans') {
    $who = Planworld::getAllUsersWithPlans();
  } else {
    $who = Planworld::getAllUsers();
  }
  return $who;
}

function xmlrpc_online ($method_name, $params) {
  return Online::getOnlineUsers();
}

function xmlrpc_sendMessage ($method_name, $params) {
  list($from, $to, $message) = $params;

  Send::sendMessage(Planworld::nameToId($from), Planworld::nameToId($to), $message); 
}

$request_xml = $HTTP_RAW_POST_DATA;
if(!$request_xml) {
  $request_xml = $_POST['xml'];
}

// create server
$xmlrpc_server = xmlrpc_server_create();

if($xmlrpc_server) {
  // register methods
  /* Version 2 */
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.api.version', 'xmlrpc_getVersion');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.plan.getContent', 'xmlrpc_getText');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.user.getPlan', 'xmlrpc_getText');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.user.getLastLogin', 'xmlrpc_getLastLogin');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.user.getLastUpdate', 'xmlrpc_getLastUpdate');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.user.list', 'xmlrpc_whois');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.user.online', 'xmlrpc_online');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.nodes.list', 'xmlrpc_getNodes');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.send.sendMessage', 'xmlrpc_sendMessage');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.snoop.add', 'xmlrpc_addSnoopReference');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.snoop.remove', 'xmlrpc_removeSnoopReference');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.snoop.clear', 'xmlrpc_clearSnoop');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.stats.getNumUsers', 'xmlrpc_getNumUsers');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.stats.getNumPlans', 'xmlrpc_getNumPlans');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.stats.getNumSnitchRegistered', 'xmlrpc_getNumSnitchRegistered');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.stats.getNumViews', 'xmlrpc_getNumViews');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.stats.getNumHits', 'xmlrpc_getNumHits');

  /* Version 1 */
  xmlrpc_server_register_method($xmlrpc_server, 'users.getLastLogin', 'xmlrpc_getLastLogin');
  xmlrpc_server_register_method($xmlrpc_server, 'users.getLastUpdate', 'xmlrpc_getLastUpdate');
  xmlrpc_server_register_method($xmlrpc_server, 'plan.getText', 'xmlrpc_getText');
  xmlrpc_server_register_method($xmlrpc_server, 'nodes.getNodes', 'xmlrpc_getNodes');
  xmlrpc_server_register_method($xmlrpc_server, 'stats.getNumUsers', 'xmlrpc_getNumUsers');
  xmlrpc_server_register_method($xmlrpc_server, 'stats.getNumPlans', 'xmlrpc_getNumPlans');
  xmlrpc_server_register_method($xmlrpc_server, 'stats.getNumSnitchRegistered', 'xmlrpc_getNumSnitchRegistered');
  xmlrpc_server_register_method($xmlrpc_server, 'stats.getNumViews', 'xmlrpc_getNumViews');
  xmlrpc_server_register_method($xmlrpc_server, 'stats.getNumHits', 'xmlrpc_getNumHits');
  xmlrpc_server_register_method($xmlrpc_server, 'snoop.addReference', 'xmlrpc_addSnoopReference');
  xmlrpc_server_register_method($xmlrpc_server, 'snoop.removeReference', 'xmlrpc_removeSnoopReference');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.online', 'xmlrpc_online');
  xmlrpc_server_register_method($xmlrpc_server, 'planworld.whois', 'xmlrpc_whois');
  
  header("Content-Type: text/xml");
  
  echo xmlrpc_server_call_method($xmlrpc_server, $request_xml, $response='', array('output_type' => "xml", 'version' => "auto"));
  
  // free server resources
  xmlrpc_server_destroy($xmlrpc_server);
  
}
?>
