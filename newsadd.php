<?php
/**
 * $Id: newsadd.php,v 1.6 2002/01/21 18:24:35 seth Exp $
 * Insert new news into the database.
 */

$_base = dirname(__FILE__) . '/';
require_once($_base . 'functions.php');
require_once($_base . 'lib/Planworld.php');

global $dbh;
list($planworld_user, $planworld_id, $planworld_target, $planworld_target_id) = initialize($note_user);

if (!Planworld::isAdmin($planworld_id)) {
  echo "Access prohibited.";
  exit();
}

//news comes in as $newstoadd;

$SQL = "INSERT into news (news, date) VALUES ('" . addslashes($_POST['newstoadd']) . "', UNIX_TIMESTAMP(NOW()))";
mysql_query($SQL, $dbh);

header("Location: admin.php?e=1\n");
?>