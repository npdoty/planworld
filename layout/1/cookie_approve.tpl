<?php
/**
 * $Id: cookie_approve.tpl,v 1.1.2.1 2003/08/27 19:09:37 seth Exp $
 * List and approve cookies (for admins only).
 */

if (!$_user->isAdmin()) {
?>
<p class="error">You are not authorized to use this resource.</p>
<?php
} else {
  require_once($_base . 'lib/Cookie.php');

  if (isset($_POST['action']) && isset($_POST['approve']) && !empty($_POST['approve'])) {
    if ($_POST['action'] == 'approve') {
      /* process incoming approvals */
      Cookie::approve($_POST['approve']);
      $msg = "<p class=\"error\">The cookie(s) you selected have been approved.</p>\n";
    } else if ($_POST['action'] == 'remove') {
      /* process incoming deletions */
      Cookie::remove($_POST['approve']);
      $msg = "<p class=\"error\">The cookie(s) you selected have been removed.</p>\n";
    }
  }

  $cookies = Cookie::getPendingCookies();
?>
<span class="subtitle">Fortune Cookie Approval Queue</span><br />
<?php if (isset($msg)) echo $msg; ?>
<form action="<?php echo PW_URL_INDEX; ?>?id=stuff;s=cookie;a=approve" method="POST">
<table cellpadding="0" cellspacing="0" align="center" border="0">
<tr>
<td class="border">
<table cellspacing="1" cellpadding="3" border="0">
<tr>
<td align="center" class="columnheader">&nbsp;</td>
<td align="center" class="columnheader">content</td>
<td align="center" class="columnheader">submitted by</td>
<td align="center" class="columnheader">&nbsp;</td>
</tr>
<?php
if (isset($cookies) && is_array($cookies) && !empty($cookies)) {
  foreach ($cookies as $cookie) {
?>
<tr class="entry">
<td align="center" valign="center"><input type="checkbox" name="approve[]" value="<?php echo $cookie['id']; ?>" /></td>
<td align="left"><?php echo $cookie['quote']; ?><br />
<?php if (!empty($cookie['author'])) echo "-- " . $cookie['author'] . "<br />\n"; ?>
</td>
<td align="center"><?php echo $cookie['credit']; ?></td>
<td align="center"><a href="<?php echo PW_URL_INDEX; ?>?id=stuff;s=cookie;a=edit;cid=<?php echo $cookie['id']; ?>" title="edit this cookie">edit</a></td>
</tr>
<?php
  } // foreach($cookies as $cookie)
?>
<tr class="entry">
<td colspan="4" align="right">
<select name="action">
<option value="approve">approve selected</option>
<option value="remove">remove selected</option>
</select>
&nbsp;<input type="submit" value="go" />
</td>
</tr>
<?php
} else {
?>
<tr class="entry">
<td colspan="4"><p class="error">The approval queue is empty.</p></td>
</tr>
<?php
} // if (isset($cookies) && is_array($cookies) && !empty($cookies))
?>
</table>
</td>
</tr>
</table>
</form>
<?php
} // if (!$_user->isAdmin())
?>
