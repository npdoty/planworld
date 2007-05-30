<?php
/**
 * New: 25 Newest Users
 */

/* includes */
require_once($_base . "lib/Planworld.php");

$users = Planworld::getNewUsers(25);

?>
<span class="subtitle">25 Newest Users</span><br /><br />
       
<?php if(is_array($users) && !empty($users)) { ?>
  
<table cellpadding="0" cellspacing="0" align="left" border="0">
<tr>
<td class="border">
<table cellspacing="1" cellpadding="3" border="0" width="100%">
<tr>
<td align="center" class="columnheader">username</td>
<td align="center" class="columnheader">first login</td>
<td align="center" class="columnheader">last update</td>
</tr>
<?php
foreach($users as $entry) {
?>
<tr class="entry">
<td align="left"><a href="<?php echo PW_URL_INDEX; ?>?id=<?php echo $entry['username']; ?>" title="Finger <?php echo $entry['username']; ?>"><?php echo $entry['username']; ?></a></td>
<td align="center"><?php echo Planworld::getDisplayDate($entry['first_login']); ?></td>
<td align="center"><?php echo Planworld::getDisplayDate($entry['last_update']); ?></td>
</tr>
<?php } ?>
</table>
</td>
</tr>
</table>
<?php
} // if (is_array($users) && !empty($users)) 
?>
