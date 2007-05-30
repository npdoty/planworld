<?php
/**
 * $Id: home.inc,v 1.22.2.1 2003/03/17 15:44:46 seth Exp $
 * Home.
 */

require_once($_base . 'lib/Cookie.php');
require_once($_base . 'lib/News.php');


$news = News::getCurrentNewsItems();
if (is_array($news) && !empty($news)) {
  $news_text = "<hr size=\"1\" noshade=\"noshade\" />\n<p><strong>News:</strong><br />\n";
  foreach ($news as $news_item) {
    $news_text .= "<p><strong>" . date("n/j/y, g:ia", $news_item['date']) . "</strong>";
    if ($_user->isAdmin()) {
      $news_text .= " [<a href=\"" . PW_URL_INDEX . "?id=stuff;s=news;a=edit;nid={$news_item['id']}\">edit</a>]";
    }
    $news_text .= "<br />\n";
    $news_text .= "{$news_item['news']}</p>\n";
  }
  $news_text .= "</p>\n";
}

$cookie = Cookie::getRandomCookie();
$cookie_text = "{$cookie['quote']}<br />\n";
if (!empty($cookie['author'])) {
  $cookie_text .= "-- {$cookie['author']}<br />\n";
}
if (!empty($cookie['credit'])) {
  $cookie_text .= "<br />\nSubmitted by <a href=\"" . PW_URL_INDEX . "?id={$cookie['credit']}\">{$cookie['credit']}</a>\n";
}
if ($_user->isAdmin()) {
  $cookie_text .= "[<a href=\"" . PW_URL_INDEX . "?id=stuff;s=cookie;a=edit;cid={$cookie['id']}\">edit</a>]<br />\n";
}

if ($_user->hasOldUsername())
{
	$oldUsername = $_user->oldUsername();
	if (Planworld::isUser($oldUsername))
	{
		$moveUsernames = "<p><strong>Important Logistical Notice:</strong><br />Hey!  It looks like there's an old version of your username on planWorld ($oldUsername).  If you haven't already done so, you should probably <a href=\"".PW_URL_INDEX."?id=alumni\">move</a> your account.  (If you have already done so, don't do it again!)</p>";
	}
}

?>
<table width="100%" cellspacing="0" cellpadding="0">
<tr>
<td align="left">
<span class="subtitle">Home</span>
</td>
<td align="right">
<?php echo date("l, F jS, Y  g:ia"); ?>
</td>
</tr>
</table>
<?php if (isset($error_message)) echo $error_message; ?>
<p><strong>Cookie:</strong><br />
<?php echo $cookie_text; ?>
</p>
<p>To submit cookies, <a href="<?php echo PW_URL_INDEX; ?>?id=stuff;s=cookie;a=add" title="Submit a cookie">click here</a>.</p>
<?php if (isset($news_text)) echo $news_text; ?>
<?php if (isset($moveUsernames)) echo $moveUsernames; ?>
