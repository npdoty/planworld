<?php
/**
 * $Id: cookieadd.php,v 1.6 2002/08/23 21:27:31 seth Exp $
 * Insert new cookies into the database.
 */

// quote comes in as $cookie
// author comes in as $author
// submittor comes in as $submittor, but only if true submittor is an admin

/* send them elsewhere if no POST data */
if (!isset($_POST)) {
  header("Location: " . PW_URL_INDEX);
  exit();
}

/* includes */
require_once($_base . 'lib/Cookie.php');
require_once($_base . 'lib/Planworld.php');

if ($_user->isAdmin()) {
  if (isset($_POST['submittor']) && !empty($_POST['submittor'])) {
    if (!Planworld::isUser($_POST['submittor'])) {
      /* user to attribute to doesn't exist */
      header("Location: " . PW_URL_INDEX . "?id=stuff;resp=2\n");
      exit();
    }
    $submittor = addslashes($_POST['submittor']);
  } else {
    $submittor = &$_user;
  }

  if (isset($_POST['approved']) && $_POST['approved']) {
    $approved = true;
  } else {
    $approved = false;
  }

  Cookie::addCookie(strip_tags($_POST['cookie'], PW_ALLOWED_TAGS), $_POST['author'], $submittor, $approved);
} else {
  Cookie::addCookie(strip_tags($_POST['cookie'], PW_ALLOWED_TAGS), $_POST['author'], $_user);
}

header("Location: " . PW_URL_INDEX . "?id=stuff;resp=1\n");
exit();
?>