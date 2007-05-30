<?php
/**
 * $Id: deletenews.php,v 1.4 2001/12/12 21:47:23 seth Exp $
 * Delete news items from the database.
 */


require_once('functions.php');
list($planworld_user, $planworld_id, $planworld_target, $planworld_target_id) = initialize($note_user);

if (!isAdmin($planworld_id)) {
  echo "Access prohibited.";
  exit();
}

$deletes = &$_POST['deletes'];
$SQL = "DELETE FROM news WHERE newsId='{$deletes[0]}'";
for ($i=1;$i<sizeof($deletes);$i++) {
  $SQL .= " OR newsId ='$deletes[$i]'";
}
mysql_query($SQL);

header("Location: admin.php?e=5\n");
?>