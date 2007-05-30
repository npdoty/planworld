<?php
/**
 * $Id: planwatch.inc,v 1.18.2.3 2003/10/07 12:22:59 seth Exp $
 * Planwatch panel.
 */

/* includes */
require_once($_base . 'lib/Planwatch.php');

/* load the user's planwatch */
$_user->loadPlanwatch();

if ($_user->planwatch->inPlanwatch($_target)) {
  $_user->planwatch->markSeen($_target);
}

$pw = $_user->planwatch->getList();

?>
<table width="100%" cellpadding="5" cellspacing="0">
<tr>
<td class="planwatch">
<table width="100%" cellspacing="0" cellpadding="0">
<tr>
<td align="left">
<a href='<?php echo PW_URL_INDEX; ?>?id=edit_pw' title="Edit your planwatch" class="subtitle">planwatch</a>
</td>
<td align="right" valign="top">
<a href="<?php echo PW_URL_INDEX; ?>?id=edit_pw" title="Edit your planwatch"><span class="small">EDIT</span></a>
</td>
</tr>
</table>
(<?php echo $_user->planwatch->getNum(); ?> people, <?php echo $_user->planwatch->getNumNew(); ?> new)
<hr size="1" noshade="noshade" />
</td>
</tr>
<tr>
<td class="planwatch">
<?php
if (isset($pw) && !empty($pw)) {
  /* group display */
  foreach ($pw as $name => $group) {
    echo "<strong><u>{$name}</u></strong><br />\n";
    foreach ($group as $u => $entry) {
      if ($name == 'Stalkernet' && isset($entry[4]) && $entry[4])
      	echo "<a href=\"/message.jsp?id=" . stripslashes($u) . "\" title=\"pick up message(s)\"><img src=\"/images/mail.gif\" alt=\"message\" border=\"0\" /></a> ";
      if ($name == 'Send')
      	echo "<a href=\"#\" onclick=\"return send('" . stripslashes($u) . "');\" title=\"pick up message(s)\"><img src=\"/images/mail.gif\" alt=\"message\" border=\"0\" /></a> ";
      if ($entry[1] > $entry[2])
	echo "<span class='new'>NEW</span> ";
      echo "<a class=\"planwatch\" href=\"" . PW_URL_INDEX . "?id=" . stripslashes($u) . "\" title=\"Finger " . stripslashes($u) . "\">" . stripslashes($u) . "</a> ";
      if ($entry[1] == 0)
	echo "(Never)<br />\n";
      else if (date('n-j-y') == date('n-j-y', $entry[1]))
	echo "(" . date('g:ia', $entry[1]) . ")<br />\n";
      else
	echo "(" . date('n/j/y', $entry[1]) . ")<br />\n";
    }
    echo "<br />\n";
  }
} else {
  echo "You are currently watching no plans.<br />\n";
  echo "<a href=\"" . PW_URL_INDEX . "?id=edit_pw\" title=\"Edit your planwatch\">Edit your planwatch</a>";
}
?>
</td>
</tr>
</table>
