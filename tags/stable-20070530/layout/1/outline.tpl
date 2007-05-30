<?php echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?".">"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!--

$Id: outline.tpl,v 1.5.2.8 2003/11/11 15:55:29 seth Exp $
Skin <?php echo $skin['id']; ?> (<?php echo $skin['name']; ?>) by <?php echo $skin['author']; ?>

-->
<title><?php echo $pagetitle; ?></title>
<?php // require($_base . "layout/{$skin['id']}/themes/{$theme}/styles.css"); ?>
<link rel="stylesheet" type="text/css" media="all" title="<?php echo $theme; ?>" href="layout/<?php echo $skin['id']; ?>/themes/<?php echo $theme; ?>/styles.css" />
<?php
foreach (Skin::getThemeList() as $theme) {
?>
<link rel="alternative stylesheet" type="text/css" media="all" title="<?php echo $theme['Name']; ?>" href="layout/<?php echo $skin['id']; ?>/themes/<?php echo Skin::getThemeDir($theme['ID']); ?>/styles.css" />
<?php
}
?>
<script language="JavaScript" type="text/javascript">
<!--
function confirmDelete() {
  return confirm("Are you sure you wish to clear your plan?");
}
function confirmLogout() {
  return confirm("Are you sure you wish to leave planworld?");
}
function send (u) {
  window.open('<?php echo PW_URL_BASE; ?>send/' + u + '#form', 'send', 'width=450,height=400,location=no,menubar=no,toolbar=no,status=no,scrollbars=yes');
  return false;
}
//-->
</script>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-66841-3";
urchinTracker();
</script>
</head>

<body>
<?php
/* layer treatments to the original skin (uses absolute positioning) */
if (file_exists($_base . "layout/{$skin['id']}/themes/{$theme}/extras.inc")) {
  include($_base . "layout/{$skin['id']}/themes/{$theme}/extras.inc");
}
?>
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td valign="top" class="border">
<!-- border table -->
<table width="100%" cellspacing="1" cellpadding="0" border="0">
<tr>
<form method="get" action="<?php echo PW_URL_INDEX; ?>">
<td colspan="2" height="50" valign="top" class="header">
<table width="100%" cellpadding="2" cellspacing="0">
<tr>
<td align="left" valign="bottom">
<br />
<a href="<?php echo PW_URL_INDEX; ?>" title="planworld"><img src="<? echo $headerImage; ?>" alt="planworld" border="0" /></a>
</td>
<td valign="bottom" align="right">
<input class="inputTextBox" type="text" size="12" name="id" />&nbsp;<input class="inputButton" type="submit" value="Finger" />
</td>
</tr>
</table>
</td>
</form>
</tr>
<tr>
<td colspan="2" height="20" align="left" class="navbar">
<!-- nav bar -->
<?php require($_base . "layout/{$skin['id']}/navbar.inc"); ?>
<!-- / nav bar -->
</td>
</tr>
<tr>
<td width="160" valign="top" align="left" class="planwatch">
<img src="images/ghost.gif" width="160" height="1" alt="" /><br />
<!-- planwatch -->
<?php require($_base . "layout/{$skin['id']}/planwatch.inc"); ?>
<!-- / planwatch -->
</td>
<td width="100%" valign="top" class="content">
<table width="100%" cellpadding="5" cellspacing="0">
<tr>
<td valign="top">
<!-- BEGIN CONTENT -->
<?php
require($_base . "layout/{$skin['id']}/" . Skin::getIncludeFile($_target));
?>
<!-- END CONTENT -->
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td colspan="2" align="center" class="trailer">
<?php require($_base . "layout/{$skin['id']}/trailer.inc"); ?>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>
