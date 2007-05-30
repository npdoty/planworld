<?php
/**
 * $Id: planwatchedit.inc,v 1.23.2.2 2003/10/04 01:08:28 seth Exp $
 * Planwatch editing.
 */

/* includes */
require_once($_base . 'lib/Planwatch.php');

/* reload the user's planwatch (to get different ordering) */
$_user->planwatch->load('alph');
$pw = $_user->planwatch->getList();
$groups = $_user->planwatch->getGroups();

?>
<span class="subtitle">Edit Your Planwatch</span><br />
<table cellspacing="0" cellpadding="0" width="100%" border="0">
<tr>
<td class="border">
<table cellspacing="1" cellpadding="5" border="0" width="100%">
<tr>
<td width="60%" valign="top" class="entry">
<!-- edit panel -->
<?php if (isset($error_message)) echo $error_message; ?>
<form method="get" action="<?php echo PW_URL_BASE; ?>add.php">
<p><strong>To add someone, enter their username:</strong><br />
<input class="inputTextBox" type="text" size="14" name="add" />
&nbsp;<input class="inputButton" type="submit" value="add" />
</p>
</form>
<?php /* Amherst-specific text */ ?>
<p>You must know someone's username to add them (usually first initial, middle initial, last name, class year).</p>
<br />
<form method="post" action="<?php echo PW_URL_BASE; ?>add.php">
<p>You may also add people en masse by listing their usernames below (one per line).<br />
<textarea class="inputTextArea" rows="5" cols="20" name="list"> </textarea><br />
<input class="inputButton" type="submit" value="add" /> <input class="inputButton" type="reset" value="clear" />
</p>
</form>
<br />
<p><a href="<?php echo PW_URL_BASE; ?>pw_export.php">Export your planwatch</a></p>
<hr noshade="true" size="1" />
<strong>Planwatch Groups</strong>
<form method="post" action="groups.php">
<input class="inputTextBox" type="text" name="name" length="20" maxlength="64" />
<input class="inputButton" type="submit" name="add" value="add" /><br /><br />
<?php
if (is_array($groups) && !empty($groups)) {
  foreach ($groups as $name => $group) {
?>
<input class="inputCheckBox" type="checkbox" name="group[]" value="<?php echo $group[0]; ?>"<?php if (!$group[1]) echo " disabled=\"true\""; ?> />
<input class="inputTextBox" type="text" name="name_<?php echo $group[0]; ?>" value="<?php echo $name; ?>" length="20" maxlength="64"<?php if (!$group[1]) echo " disabled=\"true\""; ?> /><br />
<?php
  }  // foreach ($groups as $name => $group)
}  // if (is_array($groups) && !empty($groups))
?>
<br />
<input class="inputButton" type="submit" name="rename" value="rename selected" /><input class="inputButton" type="submit" name="delete" value="delete selected" />
</form>
<!-- end edit panel -->
</td>
<td width="40%" class="entry" valign="top">
<!-- list panel -->
<form method="get" action="<?php echo PW_URL_BASE; ?>add.php">
<?php
if (is_array($pw) && !empty($pw)) {
  foreach ($pw as $name => $group) {
    if ($name == 'Snoop' || $name == 'Send' || $name == 'Stalkernet') continue;
?>
<strong><u><?php echo $name; ?></u></strong><br />
<?php
    foreach ($group as $u => $entry) {
?>
<input class="inputCheckbox" type="checkbox" name="add[]" value="<?php echo $entry[0]; ?>" /><?php echo stripslashes($u); ?><br />
<?php
    }
?>
<br />
<?php
  }
}
?>
<br />
<input class="inputButton" type="submit" name="remove" value="remove" />
<input class="inputButton" type="submit" name="move" value="move to:" />
<select name="group">
<?php
if (is_array($groups) && !empty($groups)) {
  foreach ($groups as $name => $group) {
?>
<option value="<?php echo $group[0]; ?>"><?php echo $name; ?></option>
<?php
  }  // foreach ($groups as $name => $group)
}  // if (is_array($groups) && !empty($groups))
?>
</select>
</form>
</td>
</tr>
</table>
</td>
</tr>
</table>
