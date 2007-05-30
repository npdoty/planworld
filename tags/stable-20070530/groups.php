<?php
/** 
 * $Id: groups.php,v 1.1 2002/02/25 20:50:14 seth Exp $
 * Planwatch group modification.
 */

/* includes */
require_once($_base . 'lib/User.php');
require_once($_base . 'lib/Planwatch.php');

if (isset($_POST) && !empty($_POST)) {
  /* incoming!  probably a group addition or change */

  /* initialize this user's planwatch */
  $_user->loadPlanwatch();

  if (isset($_POST['delete']) && is_array($_POST['group']) && !empty($_POST['group'])) {
    foreach ($_POST['group'] as $gid) {
      $_user->planwatch->removeGroup($gid);
    }
  } else if (isset($_POST['rename'])) {
    foreach ($_POST['group'] as $gid) {
      $_user->planwatch->renameGroup($gid, addslashes($_POST['name_' . $gid]));
    }
  } else if (isset($_POST['name']) && !empty($_POST['name'])) {
    /* we can assume that they wanted to add a group (carriage return submits the form without a button value */
    $_user->planwatch->addGroup(addslashes($_POST['name']));
  }
}

header("Location: " . PW_URL_INDEX . "?id=edit_pw\n");

?>