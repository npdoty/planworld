<?php
/**
 * $Id: cookie_edit.tpl,v 1.1 2002/09/10 16:15:52 seth Exp $
 * Cookie addition.
 */

/** includes */
require_once($_base . 'lib/Cookie.php');

if (!$_user->isAdmin()) {
?>
<p class="error">You are not authorized to use this resource.</p>
<?php
} else if (!isset($_GET['cid'])) {
  $msg = "<p class=\"error\">You did not specify a cookie.</p>\n";
  require($_base . "layout/{$skin['id']}/cookie_approve.tpl");
} else if (!($cookie = Cookie::get($_GET['cid']))) {
  $msg = "<p class=\"error\">Could not find the cookie you are attempting to edit.</p>\n";
  require($_base . "layout/{$skin['id']}/cookie_approve.tpl");
} else {
  /** process any changes that may have been made */
  if (isset($_POST) && !empty($_POST)) {
    if (isset($_POST['submittor']) && !empty($_POST['submittor'])) {
      $error_msg = "<p class=\"error\">Your edits have been acknowledged.</p>\n";
      if (!Planworld::isUser($_POST['submittor'])) {
	/* user to attribute to doesn't exist */
	$error_msg = "<p class=\"error\">Could not find the user you wished to attribute that quote to; please check the name and re-submit it.</p>\n";
      }
      $submittor = addslashes($_POST['submittor']);
    } else {
      $submittor = '';
    }
    
    if (isset($_POST['approved']) && $_POST['approved'] == 'Y') {
      $approved = true;
    } else {
      $approved = false;
    }
    
    Cookie::edit($_GET['cid'], strip_tags($_POST['cookie'], PW_ALLOWED_TAGS), $_POST['author'], $submittor, $approved);
    $cookie = Cookie::get($_GET['cid']);
  }
?>
<form method="post" action="<?php echo PW_URL_INDEX; ?>?id=stuff;s=cookie;a=edit;cid=<?php echo $_GET['cid']; ?>" name="cookie">
<p class="subtitle">Edit a Cookie</p>
<p><a href="<?php echo PW_URL_INDEX; ?>?id=stuff;s=cookie;a=approve">return to the approval queue</a></p>
<?php if (isset($error_msg)) echo $error_msg; ?>
<table border="0" cellpadding="0" cellspacing="0">
  <tr>
	<td width="50%" class="columnheader">&nbsp;:: edit a 
	  cookie&nbsp;</td>
  <td>&nbsp;</td>
  </tr>
  <tr> 
	<td class="border" colspan="2"> 
	  <table border="0" width="100%" cellpadding="3" cellspacing="1">
		<tr> 
		  <td align="right" class="description">Quotation:</td>
		  <td class="entry"> 
			<textarea name="cookie" wrap="virtual" rows="4" cols="40" class="inputTextarea"><?php echo $cookie['quote']; ?></textarea>
		  </td>
		</tr>
		<tr> 
		  <td align="right" class="description">Author:</td>
		  <td class="entry"> 
			<input type="text" name="author" class="inputTextBox" maxlength="255" size="40" value="<?php echo $cookie['author']; ?>" />
		  </td>
		</tr>
<tr>
<td align="right" class="description">Attribute to:</td>
<td class="entry">
<input type="text" name="submittor" class="inputTextBox" maxlength="128" size="40" value="<?php echo $cookie['credit']; ?>" />
</td>
</tr>
<tr>
<td align="right" class="description">Approved:</td>
<td class="entry">
<select name="approved">
<option value="Y"<?php if ($cookie['approved']) echo " selected=\"true\""; ?>>yes</option>
<option value="N"<?php if (!$cookie['approved']) echo " selected=\"true\""; ?>>no</option>
</select>
</td>
</tr>
		<tr>
<td class="entry" align="left">
<?php if ($_GET['cid'] > 1) { ?><a href="<?php echo PW_URL_INDEX; ?>?id=stuff;s=cookie;a=edit;cid=<?php echo $_GET['cid'] - 1; ?>" name="previous">&lt;&lt;</a><?php } ?> <a href="<?php echo PW_URL_INDEX; ?>?id=stuff;s=cookie;a=edit;cid=<?php echo $_GET['cid'] + 1; ?>" name="next">&gt;&gt;</a></td>
		  <td class="entry" align="right">
			<input class="inputButton" type="submit" name="submit" value="edit" />
		  </td>
		</tr>
	  </table>
	</td>
  </tr>
</table>
</form>
<?php
}
?>