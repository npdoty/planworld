<?php
/**
 * $Id: navbar.inc,v 1.25.2.4 2003/10/13 02:12:38 seth Exp $
 * Navigation Bar for planWorld.
 */

/* includes */
require_once($_base . 'lib/Planworld.php');

?>
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td align="left" class="navbar">
&nbsp;<a href="<?php echo PW_URL_INDEX; ?>" class="navbar" title="Home">Home</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=stats" class="navbar" title="Statistics">Stats</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=snitch" class="navbar" title="Snitch">Snitch</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=snoop" class="navbar" title="Snoop">Snoop</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=prefs" class="navbar" title="Preferences">Preferences</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=help" class="navbar" title="Frequently Asked Questions">FAQ</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=about" class="navbar" title="About Planworld">About</a>
<?php if ($_user->isAdmin()) { ?>
| <a href="<?php echo PW_URL_INDEX; ?>?id=stuff;s=admin" class="navbar" title="Planworld Administrator Functions">Admin</a>
<?php } // if ($_user->isAdmin()) ?>
</td>
<td align="right" class="navbar">
<?php echo $_user->getUsername(); ?>: <a href="<?php echo PW_URL_INDEX . "?id=" . $_user->getUsername(); ?>" class="navbar" title="View your plan">View</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=edit_plan" class="navbar" title="Edit your plan">Edit</a> | 
<a href="<?php echo PW_URL_INDEX; ?>?id=archiving;u=<?php echo $_user->getUsername(); ?>" class="navbar" title="View your archives">Archives</a> | 
<a href="<?php echo PW_URL_INDEX; ?>?id=del_plan" class="navbar" title="Clear your plan" onclick="return confirmDelete();">Delete</a> | 
<a href="/login/logout.php" title="Log out of planworld" onclick="return confirmLogout();">Logout</a>&nbsp;
</td>
</tr>
</table>
