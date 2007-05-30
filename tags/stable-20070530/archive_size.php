<?php
/**
 * $Id: snoop.php,v 1.5 2002/04/23 20:49:32 seth Exp $
 * Takes pre-snoop plans and grabs references from them.
 */


set_time_limit(0);

$dbh = mysql_connect("localhost", "planworld", "community");
@mysql_select_db("planworld", $dbh);
$dbh2 = mysql_connect("localhost", "planworld", "community");
@mysql_select_db("planworld", $dbh2);

$SQL = "SELECT id, username FROM users WHERE remote='N' AND last_login > 0 ORDER BY username";
$res = mysql_query($SQL, $dbh2);
while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
    echo "{$row['username']}...";
    $uid = $row['id'];
    flush();
    $SQL = "SELECT COUNT(*) as size FROM archive WHERE uid={$uid}";
    $result = mysql_query($SQL, $dbh);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    echo $row['size'];
    flush();
    $SQL = "UPDATE users SET archive_size={$row['size']} WHERE id={$uid}";
    mysql_query($SQL, $dbh);
    echo "a..";
    flush();
    $SQL = "SELECT COUNT(*) as size FROM archive WHERE uid={$uid} AND pub='Y'";
    $result = mysql_query($SQL, $dbh);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    echo $row['size'];
    flush();
    $SQL = "UPDATE users SET archive_size_pub={$row['size']} WHERE id={$uid}";
    mysql_query($SQL, $dbh);
    echo "p\n";
}
mysql_free_result($result);

?>
