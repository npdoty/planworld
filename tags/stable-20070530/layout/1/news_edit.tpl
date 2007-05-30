<?php
/**
 * $Id: news_edit.tpl,v 1.1 2002/09/10 16:16:44 seth Exp $
 * News management.
 */

/** includes */
require_once($_base . 'lib/News.php');

if (!$_user->isAdmin()) {
?>
<p class="error">You are not authorized to use this resource.</p>
<?php
} else if (!isset($news) && !isset($_GET['nid'])) {
  $msg = "<p class=\"error\">You did not specify a news entry.</p>\n";
  require($_base . "layout/{$skin['id']}/news_list.tpl");
} else if (!isset($news) && !($news = News::get($_GET['nid']))) {
  $msg = "<p class=\"error\">Could not find the news entry you are attempting to edit.</p>\n";
  require($_base . "layout/{$skin['id']}/news_list.tpl");
} else {
  /** process any changes that may have been made */
  if (isset($_POST) && !empty($_POST) && !isset($passthru)) {

    if (isset($_POST['live']) && $_POST['live'] == 'Y') {
      $live = true;
    } else {
      $live = false;
    }
    
    News::edit($_GET['nid'], $_POST['content'], $_POST['date'], $live);
    $news = News::get($_GET['nid']);
  }
?>
<form method="post" action="<?php echo PW_URL_INDEX; ?>?id=stuff;s=news;a=edit;nid=<?php echo $_GET['nid']; ?>">
<input type="hidden" name="date" value="<?php echo $news['date']; ?>" />
<p class="subtitle">Edit a News Entry</p>
<p><a href="<?php echo PW_URL_INDEX; ?>?id=stuff;s=news;a=list">return to the list of news entries</a></p>
<?php if (isset($error_msg)) echo $error_msg; ?>
<table border="0" cellpadding="0" cellspacing="0">
  <tr>
	<td width="50%" class="columnheader">&nbsp;:: edit a news entry&nbsp;</td>
  <td>&nbsp;</td>
  </tr>
  <tr> 
	<td class="border" colspan="2"> 
	  <table border="0" width="100%" cellpadding="3" cellspacing="1">
		<tr> 
		  <td align="right" class="description">News:</td>
		  <td class="entry"> 
			<textarea name="content" wrap="virtual" rows="4" cols="40" class="inputTextarea"><?php echo $news['news']; ?></textarea>
		  </td>
		</tr>
<tr>
<td align="right" class="description">Live:</td>
<td class="entry">
<select name="live">
<option value="Y"<?php if ($news['live']) echo " selected=\"true\""; ?>>yes</option>
<option value="N"<?php if (!$news['live']) echo " selected=\"true\""; ?>>no</option>
</select>
</td>
</tr>
		<tr>
		  <td colspan="2" class="entry" align="right">
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