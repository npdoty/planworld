<script language="JavaScript" type="text/javascript">
<!--
var perm=0;
var sel=0;
function selectAll (form) {
  if (form == "perm") {
    // permissions
    for (var i=3; i < document.archive.elements.length - 1; i=i+3) {
      document.archive.elements[i].checked = (perm % 2 == 0);
    }
    perm++;
  } else {
    // selection
    for (var i=2; i < document.archive.elements.length - 1; i=i+3) {
      document.archive.elements[i].checked = (sel % 2 == 0);
    }
    sel++;
  }
  return false;
}
// -->
</script>
<div class="subtitle">Archives</div>
<p><?php if (isset($archive_text)) echo $archive_text; ?></p>
<?php if (isset($entries) && is_array($entries)) { ?>

<?php
if ($uid == $_user->getUsername()) {
?>
<p><a href="<?php echo PW_URL_INDEX; ?>?id=archiving;u=<?php echo $uid; ?>;repost=y">Re-post your plan</a><br />
<a href="#export">Export your archives</a><br />
<a href="<?php echo PW_URL_INDEX; ?>?id=archiving;u=<?php echo $uid; ?>;r=y" onclick="return confirm('Are you sure you wish to clear your archives?');">Clear your archives</a></p>
<?php
} // if ($uid == $_user->getUsername()) {
?>

<table cellpadding="0" cellspacing="0">
<tr>
<td class="border">
<table cellspacing="1" cellpadding="3">
<?php
if ($uid == $_user->getUsername()) {
?>
<form name="archive" method="post" name="archiveEntryList" action="<?php echo PW_URL_INDEX; ?>?id=archiving;u=<?php echo $uid; ?>">
<input type="hidden" name="u" value="<?php echo $uid; ?>" />
<input type="hidden" name="offset" value="<?php echo $offset; ?>" />
<?php
} // if ($uid == $_user->getUsername()) {
?>
<tr>
<td class="description" colspan="<?php echo ($uid == $_user->getUsername()) ? 6 : 2; ?>" align="center">
<?php
if ($offset > 0) {
?>
<a class="description" href="?id=archiving;u=<?php echo $uid; ?>;s=<?php echo $sort; ?>;d=<?php echo $cdir; ?>;os=0" title="first 50">|&lt;&lt;</a>&nbsp;
<a class="description" href="?id=archiving;u=<?php echo $uid; ?>;s=<?php echo $sort; ?>;d=<?php echo $cdir; ?>;os=<?php echo (($offset-50) >= 0) ? $offset-50 : 0; ?>" title="previous 50">&lt;&lt;</a>&nbsp;
<?php
}

echo '(' . ($offset + 1) . '-' . ((sizeof($entries) < $offset + 50) ? sizeof($entries) : $offset + 50) . ' of ' . sizeof($entries) . ')';

if (($offset + 50) < sizeof($entries)) {
?>
&nbsp;<a class="description" href="?id=archiving;u=<?php echo $uid; ?>;s=<?php echo $sort; ?>;d=<?php echo $cdir; ?>;os=<?php echo $offset+50; ?>" title="next 50">&gt;&gt;</a>
&nbsp;<a class="description" href="?id=archiving;u=<?php echo $uid; ?>;s=<?php echo $sort; ?>;d=<?php echo $cdir; ?>;os=<?php echo sizeof($entries)-50; ?>" title="last 50">&gt;&gt;|</a>
<?php
}
?>
</td>
</tr>
<tr>
<?php
if ($uid == $_user->getUsername()) {
?>
<td align="center" class="columnheader"><a title="Select All" class="columnheader" href="" onclick="return selectAll('sel');">(all)</a></td>
<?php
} // if ($uid == $_user->getUsername()) {
?>
<td align="center" class="columnheader"><a class="columnheader" href="?id=archiving;u=<?php echo $uid; ?>;s=ts;d=<?php echo $dir; ?>;os=<?php echo $offset; ?>" title="Sort by date">date</a></td>
<td align="center" class="columnheader"><a class="columnheader" href="?id=archiving;u=<?php echo $uid; ?>;s=name;d=<?php echo $dir; ?>;os=<?php echo $offset; ?>" title="Sort by name">name</a></td>
<?php
if ($uid == $_user->getUsername()) {
?>
<td align="center" class="columnheader"><a class="columnheader" href="?id=archiving;u=<?php echo $uid; ?>;s=views;d=<?php echo $dir; ?>;os=<?php echo $offset; ?>" title="Sort by views">views</a></td>
<td align="center" class="columnheader"><a class="columnheader" href="?id=archiving;u=<?php echo $uid; ?>;s=public;d=<?php echo $dir; ?>;os=<?php echo $offset; ?>" title="Sort by status">public <a title="Select All" class="columnheader" href="" onclick="return selectAll('perm');">(all)</a></td>
<td align="center" class="columnheader">&nbsp;</td>
<?php
} // if ($uid == $_user->getUsername()) {
?>
</tr>
<?php
for ($i=$offset;$i<((sizeof($entries) < $offset + 50) ? sizeof($entries) : $offset + 50);$i++) {
  // foreach($entries as $entry) {
?>
<tr class="entry">
<?php
if ($uid == $_user->getUsername()) {
?>
<td align="center"><input class="inputCheckBox" type="checkbox" name="entries[]" value="<?php echo $entries[$i][0]; ?>" /></td>
<?php
} // if ($uid == $_user->getUsername()) {
?>
<td align="center"><a href="<?php echo PW_URL_INDEX; ?>?id=<?php echo $uid; ?>;d=<?php echo $entries[$i][0]; ?>"><?php echo Planworld::getDisplayDate($entries[$i][0]); ?></a></td>
<td align="left"><a href="<?php echo PW_URL_INDEX; ?>?id=<?php echo $uid; ?>;d=<?php echo $entries[$i][0]; ?>"><?php echo $entries[$i][1]; ?></a></td>
<?php
if ($uid == $_user->getUsername()) {
?>
<td align="center"><?php echo $entries[$i][2]; ?></td>
<td align="center"><input onclick="document.archive.elements[<?php echo 2 + ($i * 3); ?>].checked=true;" class="inputCheckBox" type="checkbox" name="public[]" value="<?php echo $entries[$i][0]; ?>"<?php if ($entries[$i][3]) echo ' checked="checked"'; ?> /></td>
<td align="center"><input onfocus="document.archive.action.selectedIndex=1;document.archive.elements[<?php echo 2 + ($i * 3); ?>].checked=true;" class="inputTextBox" type="text" name="<?php echo $entries[$i][0]; ?>" value="<?php echo $entries[$i][1]; ?>" maxlength="64" length="15" /></td>
<?php
} // if ($uid == $_user->getUsername()) {
?>
</tr>
<?php
} // foreach($entries as $entry)
?>
<?php
if ($uid == $_user->getUsername()) {
?>

<tr>
<td class="entry" colspan="6" align="right">
<select name="action">
<option value="permissions">change permissions</option>
<option value="rename">rename selected</option>
<option value="delete">delete selected</option>
</select>
<input class="inputButton" type="submit" name="go" value="go" />
</td>
</tr>
</form>
<?php
} // if ($uid == $_user->getUsername()) {
?>
</table>
</td>
</tr>
</table>
<br />

<?php
if ($uid == $_user->getUsername()) {
?>
<!-- export table -->
<a name="export"></a>
<form method="get" action="<?php echo PW_URL_BASE; ?>export.php">
<table cellpadding="0" cellspacing="0">
<tr>
<td class="columnheader" width="50%">&nbsp;:: export&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr>
<td colspan="2" class="border">
<table cellspacing="1" cellpadding="3">
<tr>
<td class="description" align="right">Start:</td>
<td class="entry"><select name="s">
<?php foreach ($list as $entry) { ?>
<option value="<?php echo $entry[0]; ?>"><?php echo Planworld::getDisplayDate($entry[0]); if (!empty($entry[1])) echo " - " . Planworld::teaser($entry[1]); ?></option>
<?php
}
?>
</select></td>
</tr>
<tr>
<td class="description" align="right">End:</td>
<td class="entry"><select name="e">
<?php foreach ($list as $entry) { ?>
<option value="<?php echo $entry[0]; ?>"><?php echo Planworld::getDisplayDate($entry[0]); if (!empty($entry[1])) echo " - " . Planworld::teaser($entry[1]); ?></option>
<?php
}
?>
</select></td>
</tr>
<tr>
<td class="description" align="right">Type:</td>
<td class="entry"><select name="t">
<option value="text">text</option>
<option value="html">html</option>
</select></td>
</tr>
<tr>
<td colspan="2" class="entry" align="right">
<input class="inputButton" type="submit" value="export" />
</td>
</tr>
</table>
</td>
</tr>
</table>
</form>
<!-- end export table -->
<?php
} // if ($_user->getUserID() == $_target->getUserID()) 
?>
<?php } // if(isarray($entries)) ?>
