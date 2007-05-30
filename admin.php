<?php
/**
 * $Id: admin.php,v 1.11 2002/01/21 18:24:34 seth Exp $
 * Administration page.
 */

$_base = dirname(__FILE__) . '/';

require_once($_base . 'lib/Planworld.php');

switch($_GET['e']) {
 case 1:
   $smsg = "<font color=\"red\">Your news item was successfully added.</font><br>\n";
   break;
 case 2:
   $smsg = "<font color=\"red\">There was a problem adding your news item.</font><br>\n";
   break;
 case 3:
   $smsg = "<font color=\"red\">Your cookie was successfully added.</font><br>\n";
   break;
 case 4:
   $smsg = "<font color=\"red\">There was a problem adding your cookie.</font><br>\n";
   break;
 case 5:
   $smsg = "<font color=\"red\">The selected news item(s) were successfully deleted.</font><br>\n";
   break;
 case 6:
   $smsg = "<font color=\"red\">There was a problem deleting selected news item(s).</font><br>\n";
   break;
}

require_once('functions.php');
list($planworld_user, $planworld_id, $planworld_target, $planworld_target_id) = initialize($note_user);

if (!Planworld::isAdmin($planworld_id)) {
  echo "Access prohibited.";
  exit();
}
?>
<html>
<head>
<title>planWorld :: Administrative Interface</title>
</head>
<body>
<a href="index.php" title="Back to planWorld">Return To planWorld</a><br>
<?php echo $smsg; ?>
Hello <?php echo $planworld_user; ?>.  This is the planWorld Web-interface<br><br>
<form method="post" action="cookieadd.php">
Enter Cookie Text :<br>
<textarea cols=25 rows=6 name="cookie"></textarea><br><br>
Enter Author	  :<br>
<input type="text" name="author" maxlength=255><br><br>
Enter SubmittedBy :<br>
<input type="text" name="submitted" maxlength=32><br><br>
<input type="submit" value="Submit Cookie">
</form>

<form method="post" action="newsadd.php">
Enter News To Add:<br>
<textarea cols=50 rows=6 name="newstoadd">Enter news here</textarea><br>
<input type="submit" value="Submit News">
</form>

<?php
// list all news entries, for deletion options
$SQL = "SELECT * FROM news ORDER BY date DESC";
$result = mysql_query ($SQL, $dbh);
?>
<TABLE bgcolor="lightblue">
<form method="post" action="deletenews.php">
<?
for ($i=0;$i < mysql_num_rows($result); $i++) {
  echo "<tr><td>";
  $row = mysql_fetch_row($result);
  echo "<b>";
  print(date("l, F jS, Y, g:i a", $row[2]));
  echo "</b><br>";
  echo "$row[1]<br>";
?>
<input type="checkbox" name="deletes[]" value="<?php echo "$row[0]"; ?> ">
<?
  echo "Remove<br><br></td></tr>";
}
?>
<input type=submit value="REMOVE">
</form>
</TABLE>

<?php echo ("The following people are online (");
$SQL = "SELECT UserID, What FROM Online";
$result = mysql_query($SQL, $dbh);
$num = mysql_num_rows($result);
echo ($num . ")<br>");
for ($j=0; $j<$num; $j++) {

$row = mysql_fetch_row($result);
  $name = Planworld::idToName($row[0]);
  echo ($name . "&nbsp;&nbsp;" . $row[1] . "<br>");
}

?>
</body>
</html>
