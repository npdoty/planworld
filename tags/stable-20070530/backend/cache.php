#!/usr/local/bin/php -q
<?php
/**
 * $Id: cache.php,v 1.7 2002/03/03 22:19:51 seth Exp $
 * XML-RPC cache: updates LastUpdate and LastLogin for all remote
 * users on planwatches.
 */

$_base = dirname(__FILE__) . '/../';
require_once($_base . 'config.php');
require_once($_base . 'lib/Planworld.php');

// display errors in a readable format (since this is run from cron)
ini_set('html_errors','off');

$dbh = Planworld::_connect();

$query = "SELECT DISTINCT users.username FROM planwatch, users WHERE planwatch.w_uid=users.id AND users.remote='Y'";
$result = $dbh->query($query);

$hosts = array();
while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
  list($user, $host) = split('@', $row['username']);
  if (!in_array($host, $hosts)) $hosts[] = $host;
  if (!is_array($$host)) $$host = array();
  array_push($$host, $user);
}

foreach($hosts as $host) {
  Planworld::getLastUpdate($$host, $host);
  Planworld::getLastLogin($$host, $host);
}

?>
