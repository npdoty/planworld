<?php
/**
 * $Id: news_add.tpl,v 1.1 2002/09/10 16:16:44 seth Exp $
 * News management (addition).
 */

/** includes */
require_once($_base . 'lib/News.php');

if (!$_user->isAdmin()) {
?>
<p class="error">You are not authorized to use this resource.</p>
<?php
} else if (isset($_POST) && !empty($_POST)) {
  /** process any changes that may have been made */
  
  if (isset($_POST['live']) && $_POST['live'] == 'Y') {
    $live = true;
  } else {
    $live = false;
  }
  
  $nid = News::add($_POST['content'], mktime(), $live);
  $news = News::get($nid);
  $passthru = true;

  require_once($_base . "layout/{$skin['id']}/news_edit.tpl");
} else {
?>
<form method="post" action="<?php echo PW_URL_INDEX; ?>?id=stuff;s=news;a=add">
<p class="subtitle">Add a News Entry</p>
<p><a href="<?php echo PW_URL_INDEX; ?>?id=stuff;s=news;a=list">return to the list of news entries</a></p>
<?php if (isset($error_msg)) echo $error_msg; ?>
<table border="0" cellpadding="0" cellspacing="0">
  <tr>
	<td width="50%" class="columnheader">&nbsp;:: add a news entry&nbsp;</td>
  <td>&nbsp;</td>
  </tr>
  <tr> 
	<td class="border" colspan="2"> 
	  <table border="0" width="100%" cellpadding="3" cellspacing="1">
		<tr> 
		  <td align="right" class="description">News:</td>
		  <td class="entry"> 
			<textarea name="content" wrap="virtual" rows="4" cols="40" class="inputTextarea"> </textarea>
		  </td>
		</tr>
<tr>
<td align="right" class="description">Live:</td>
<td class="entry">
<select name="live">
<option value="Y">yes</option>
<option value="N">no</option>
</select>
</td>
</tr>
		<tr>
		  <td colspan="2" class="entry" align="right">
			<input class="inputButton" type="submit" name="submit" value="add" />
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