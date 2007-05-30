#!/usr/local/bin/php
<?php
/**
 * $Id: snoop.php,v 1.5.4.1 2003/11/02 16:09:35 seth Exp $
 * Takes pre-snoop plans and grabs references from them.
 */

global $dbh;

set_time_limit(0);

require_once('config.php');
require_once('functions.php');
require_once('lib/Snoop.php');
require_once('lib/User.php');
connectToDatabase();

$SQL = "SELECT users.id, users.username, users.last_update, plans.content FROM users, plans WHERE users.id=plans.uid";
$result = mysql_query($SQL, $dbh);
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    echo "Parsing {$row['username']}'s plan...\n";
    $SQL = "DELETE FROM snoop WHERE s_uid='{$row['id']}'";
    mysql_query($SQL, $dbh);
    $user = User::factory($row['username']);
    Snoop::process($user, $row['content'], '', $row['last_update']);
}
mysql_free_result($result);

?>
