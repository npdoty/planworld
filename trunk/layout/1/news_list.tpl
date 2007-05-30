<?php
/**
 * $Id: news_list.tpl,v 1.1.2.3 2002/09/13 03:13:04 seth Exp $
 * List and enliven news entries (for admins only).
 */

if (!$_user->isAdmin()) {
?>
<p class="error">You are not authorized to use this resource.</p>
<?php
} else {
  require_once($_base . 'lib/News.php');
  if (isset($_POST['action'])) {
    if ($_POST['action'] == 'live') {
      /* process incoming live requests */
      News::enliven((isset($_POST['live']) ? $_POST['live'] : null));
      $msg = "<p class=\"error\">The news item(s) you selected are now live.</p>\n";
    } else if ($_POST['action'] == 'remove' && isset($_POST['live'])) {
      /* process incoming deletions */
      News::remove($_POST['live']);
      $msg = "<p class=\"error\">The news item(s) you selected have been removed.</p>\n";
    }
  }

  $news = News::getAllNews();
?>
<span class="subtitle">News Item Management</span><br />
<?php if (isset($msg)) echo $msg; ?>
<p><a href="<?php echo PW_URL_INDEX; ?>?id=stuff;s=news;a=add">add a news entry</a></p>
<table cellpadding="0" cellspacing="0" align="center" border="0">
<tr>
<td class="border">
<form action="<?php echo PW_URL_INDEX; ?>?id=stuff;s=news;a=list" method="POST">
<table cellspacing="1" cellpadding="3" border="0">
<tr>
<td align="center" class="columnheader">live</td>
<td align="center" class="columnheader">date</td>
<td align="center" class="columnheader">content</td>
<td align="center" class="columnheader">&nbsp;</td>
</tr>
<?php
if (isset($news) && is_array($news) && !empty($news)) {
  foreach ($news as $news_item) {
?>
<tr class="entry">
<td align="center" valign="center"<?php if ($news_item['live']) echo ' class="description"'; ?>><input type="checkbox" name="live[]" value="<?php echo $news_item['id']; ?>"<?php if ($news_item['live']) echo ' checked="true"'; ?> /></td>
<td align="left"><?php echo Planworld::getDisplayDate($news_item['date']); ?></td>
<td align="left"><?php echo $news_item['news']; ?></td>
<td align="center"><a href="<?php echo PW_URL_INDEX; ?>?id=stuff;s=news;a=edit;nid=<?php echo $news_item['id']; ?>" title="edit this news item">edit</a></td>
</tr>
<?php
  } // foreach($news as $new_item)
?>
<tr class="entry">
<td colspan="4" align="right">
<select name="action">
<option value="live">enliven selected</option>
<option value="remove">remove selected</option>
</select>
&nbsp;<input type="submit" value="go" />
</td>
</tr>
<?php
} else {
?>
<tr class="entry">
<td colspan="4"><p class="error">There is no news.</p></td>
</tr>
<?php
} // if (isset($news) && is_array($news) && !empty($news))
?>
</table>
</form>
</td>
</tr>
</table>
<?php
} // if (!$_user->isAdmin())
?>
