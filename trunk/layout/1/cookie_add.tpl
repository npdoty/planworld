<?php
/**
 * $Id: cookie_add.tpl,v 1.1 2002/09/10 16:15:52 seth Exp $
 * Cookie addition.
 */

if (isset($_GET['resp'])) {
  switch($_GET['resp']) {
  case 1:
    $error_msg = "<p class=\"error\">Your submission has been acknowledged.</p>\n";
    break;
  case 2:
    $error_msg = "<p class=\"error\">Could not find the user you wished to attribute that quote to; please check the name and re-submit it.</p>\n";
    break;
  }
}

?>
<form method="post" action="<?php echo PW_URL_BASE . "cookieadd.php"; ?>" name="cookie">
<p class="subtitle">Submit a Cookie</p>
<?php if (isset($error_msg)) echo $error_msg; ?>
<table border="0" cellpadding="0" cellspacing="0">
  <tr>
	<td width="50%" class="columnheader">&nbsp;:: submit a 
	  cookie&nbsp;</td>
  <td>&nbsp;</td>
  </tr>
  <tr> 
	<td class="border" colspan="2"> 
	  <table border="0" width="100%" cellpadding="3" cellspacing="1">
		<tr> 
		  <td align="right" class="description">Quotation:</td>
		  <td class="entry"> 
			<textarea name="cookie" wrap="virtual" rows="4" cols="40" class="inputTextarea"></textarea>
		  </td>
		</tr>
		<tr> 
		  <td align="right" class="description">Author:</td>
		  <td class="entry"> 
			<input type="text" name="author" class="inputTextBox" maxlength="255" size="40" />
		  </td>
		</tr>
<?php
  if ($_user->isAdmin()) {
?>
<tr>
<td align="right" class="description">Attribute to:</td>
<td class="entry">
<input type="text" name="submittor" class="inputTextBox" maxlength="128" size="40" />
</td>
</tr>
<tr>
<td align="right" class="description">Approved:</td>
<td class="entry">
<select name="approved">
<option value="Y">yes</option>
<option value="N">no</option>
</select>
</td>
</tr>
<?php
  } // if ($_user->isAdmin())
?>
		<tr align="right"> 
		  <td colspan="2" class="entry"> 
			<input class="inputButton" type="submit" name="submit" value="submit" />
		  </td>
		</tr>
	  </table>
	</td>
  </tr>
</table>
</form>