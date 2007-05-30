<?php
/**
 * $Id: edit.inc,v 1.29.2.1 2003/12/07 16:44:24 seth Exp $
 * Edit page.
 */

if (isset($_GET['u']) && !empty($_GET['u']) && Planworld::isUser($_GET['u'])) {
  $to_edit = User::factory($_GET['u']);
  if ($to_edit->isSharedFor($_user)) {
    $to_edit->setShared();
    $to_edit->setEditingUser($_user);
    $_user = &$to_edit;
  } else if ($to_edit->getUsername() != $_user->getUsername()) {
    $error = "<p class=\"error\">You are not authorized to edit " . $to_edit->getUsername() . "'s plan.</p>\n";
  }
}

$plan_edit = $_user->getPlan($_user);
if (empty($plan_edit)) {
  $type = 'text';
} else if (preg_match('/^\<pre\>(.*)\<\/pre\>$/misD', $plan_edit, $matches)) {
  $plan_edit = $matches[1];
  $type = 'text';
} else {
  $type = 'html';
}

if ($_user->getPreference('journal')) {
  $plan_edit = '';
}

$archive = $_user->getArchive();

echo "<form action=\"parser.php\" method=\"post\">\n";
require($_base . "layout/{$skin['id']}/edit.tpl");
?>
