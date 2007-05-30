#!/usr/local/bin/php -q
<?php
/**
 * $Id: getnodes.php,v 1.3.4.1 2002/09/18 18:46:50 seth Exp $
 * Creates INSERT statements for additional planworld nodes based on the list hosted by NOTE.
 */

require_once('XML/RPC.php');
$f = new XML_RPC_Message('nodes.getNodes');
$c = new XML_RPC_Client('/planworld/backend/', 'note.amherst.edu', 80);
$c->setDebug(0);
$r = $c->send($f);
$v = $r->value();
if (!$r->faultCode()) {
  foreach (XML_RPC_decode($v) as $node) {
	echo "REPLACE INTO nodes (name, hostname, path, port, version) VALUES ('{$node['Name']}', '{$node['Hostname']}', '{$node['Path']}', '{$node['Port']}', $node['Version']);\n";
  }
} else {
  print "Fault: ";
  print "Code: " . $r->faultCode() . " Reason '" . $r->faultString() . "'<BR>";
}
?>