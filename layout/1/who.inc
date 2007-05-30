<?php
/**
 * $Id: who.inc,v 1.9.2.2 2003/03/17 15:44:46 seth Exp $
 * Who: see who else is online right NOW
 */

/* includes */
require_once($_base . 'lib/Online.php');

$who = Online::getOnlineUsers();
?>
<span class="subtitle">Who</span><br />
<p>There are currently <?php echo sizeof($who); ?> user(s) online.<br />
The time is currently <?php echo date("g:i a"); ?>.</p>
       
<?php if(is_array($who) && !empty($who)) { ?>
  
<table cellpadding="0" cellspacing="0" align="left" border="0">
<tr>
<td class="border">
<table cellspacing="1" cellpadding="3" border="0" width="100%">
<tr>
<td align="center" class="columnheader">username</td>
<td align="center" class="columnheader">last active</td>
<td align="center" class="columnheader">logged in</td>
<?php
   if ($_user->isAdmin()) {
?>
<td align="center" class="columnheader">what</td>
<?php
   }
?>
</tr>
<?php
foreach($who as $entry) {
?>
<tr class="entry">
<td align="left"><a href="<?php echo PW_URL_INDEX; ?>?id=<?php echo $entry['name']; ?>" title="Finger <?php echo $entry['name']; ?>"><?php echo $entry['name']; ?></a></td>
<td align="center"><?php echo Planworld::getDisplayDate($entry['lastAccess']); ?></td>
<td align="center"><?php echo Planworld::getDisplayDate($entry['login']); ?></td>
<?php
   if ($_user->isAdmin()) {
?>
<td align="center"><?php echo $entry['what']; ?></td>
<?php
   }
?>
</tr>
<?php } ?>
<tr>
<td colspan="<?php echo ($_user->isAdmin()) ? '4' : '3'; ?>" class="entry">Users idle for more than <?php echo PW_IDLE_TIMEOUT / 60; ?> minutes are considered inactive.</td>
</tr>
</table>
</td>
</tr>
</table>
<?php
} // if (is_array($who) && !empty($who)) 
?>
