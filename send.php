<?php
$_base = dirname(__FILE__) . "/";
require_once($_base . "lib/Planworld.php");
require_once($_base . "lib/Send.php");

if (isset($_SERVER['PATH_INFO'])) {
  $params = explode("/", $_SERVER['PATH_INFO']);
  array_shift($params);
  if (isset($params[0])) {
    $_REQUEST['id'] = $params[0];
  }
}

if (isset($_POST) && !empty($_POST)) {
  $uid = Planworld::nameToId($_SESSION['note_user']);
  $to_uid = Planworld::nameToId($_REQUEST['id']);
  Send::sendMessage($uid, $to_uid, $_POST['message']);
}
if ((isset($_POST) && !empty($_POST)) || !isset($_REQUEST['id'])) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script language="javascript" type="text/javascript">
alert('Your message was sent.');
window.close();
</script>
</head>
<body>
</body>
</html>
<?php
  exit();
}

$uid = Planworld::nameToId($_SESSION['note_user']);
$to_uid = Planworld::nameToId($_REQUEST['id']);

$messages = Send::getMessages($uid, $to_uid);
?>
<?php echo "<" . "?xml version=\"1.0\" encoding=\"iso-8859-1\"?" . ">\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>send :: <?php echo $_REQUEST['id']; ?></title>
<style type="text/css">
@import url(/planworld/styles/send.css);
</style>
</head>
<body onload="document.send.message.focus();">
<div align="center">
<img src="/planworld/images/send.gif" alt="send" align="center" width="119" height="34" />
<blockquote>send with <?php echo $_REQUEST['id']; ?> (<?php echo Planworld::getDisplayDate(mktime(), true); ?>)</blockquote>
</div>
<?php
if ($to_uid == PLANWORLD_ERROR) {
?>
<h2 class="error"><?php echo $_REQUEST['id']; ?> is not a valid user.</h2>
<?php
} else {
?>
<?php
  if (isset($messages) && !empty($messages)) {
    foreach ($messages as $msg) {
?>
<div class="<?php echo ($msg['uid'] == $uid) ? 'me' : 'them'; ?>"><span class="name"><?php echo Planworld::idToName($msg['uid']); ?> (<?php echo Planworld::getDisplayDate($msg['sent'], true); ?>):</span> <?php echo nl2br(Planworld::addLinks($msg['message'],$_SESSION['note_user'])); ?></div>
<?php
    } // foreach ($messages as $msg)
?>
<hr />
<?php
  } // if (isset($messages) && is_array($messages)
?>
<a name="form"></a>
<form name="send" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
<input type="hidden" name="id" value="<?php echo $_REQUEST['id']; ?>" />
<div class="form">
<textarea name="message" rows="6" cols="40"></textarea><br />
<input type="submit" value="send" /> <input type="submit" value="cancel" onclick="window.close(); return false;" />
</div>
</form>
<?php
} // if ($to_uid == PLANWORLD_ERROR)
?>
</body>
</html>
