<?php
/**
 * $Id: parser.php,v 1.43.2.5 2003/09/02 22:14:55 seth Exp $
 * Plan parser.
 * Inserts changed plans into the database after filtering.
 */

/* includes */
$_base = dirname(__FILE__) . '/';
require_once($_base . 'lib/Archive.php');

if (!isset($_POST) || empty($_POST)) {
  /* return to this user's plan */
  header("Location: " . PW_URL_INDEX . "?id=" . $_user->getUsername() . "\r\n");
  exit();
}

$plan_edit = $_POST['ptext'];

/* Apply magic to the incoming plan. */

/** TODO: fix this to work with javascript */
/* allows lone <'s and <'s (i.e., not part of an html tag) */
/* this is supposedly fixed in PHP, so it may not be necessary */
/* test case: 8 < 9, 9 > 8, this > that */
$plan_edit = preg_replace("/<([^a-z\/\"'])/is", "&lt;\\1", $plan_edit);
$plan_edit = preg_replace("/([^a-z0-9\"'%\/])>/is", "\\1&gt;", $plan_edit);
/* strip dis-allowed tags */
$plan_edit = strip_tags($plan_edit, PW_ALLOWED_TAGS);

/* if type is text, add appropriate pre tags for the displayable version (but just once) */
if (isset($_POST['type']) && $_POST['type'] == 'text' && !preg_match('/^\<pre\>(.*)\<\/pre\>\s*$/misAD', $plan_edit)) {
  $plan_display = '<pre>' . $plan_edit . '</pre>';
} else {
  $plan_display = $plan_edit;
}


/* necessary magic for shared plans */
if (isset($_POST['shared']) && !empty($_POST['shared'])) {
  $to_edit = User::factory($_POST['shared']);
  if ($to_edit->isSharedFor($_user)) {
    /* mark this as a shared user */
    $to_edit->setShared();
    /* prepend a marker to show who last edited this plan */
    $plan_display = "<!-- Shared plan modified by " . $_user->getUsername() . " -->\n" . $plan_display;
  } else {
    $to_edit = &$_user;
  }
} else {
  $to_edit = &$_user;
}

if (isset($_POST['preview'])) {
  /* preview said plan */
  $_target = 'preview';
  include('./layout/' . PW_LAYOUT . '/skin.php');
} else if (isset($_POST['cancel'])) {
  /* return to this user's plan */
  header("Location: " . PW_URL_INDEX . "?id=" . $to_edit->getUsername() . "\n");
  exit();
} else {
  /* start saving */
  $now = mktime();
  $to_edit->setPlan($plan_display, $_POST['archive'], (isset($_POST['name']) ? $_POST['name'] : ''), $now);
  $to_edit->setLastUpdate($now);
  $to_edit->save();
  /* display the saving.. status message */
  include ('./layout/' . PW_LAYOUT . '/saving.inc');
}
?>
