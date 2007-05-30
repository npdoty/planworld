<?php
/**
 * $Id: add.php,v 1.8 2002/02/25 20:50:14 seth Exp $
 * Add a user to your planwatch.
 */

/* includes */
require_once($_base . 'lib/Planworld.php');
require_once($_base . 'lib/Planwatch.php');

/*
incoming variables:
add    : user to add (name) (string / array)
list   : list of users to add (names) (multi-line string) (POST ONLY)
remove : remove this user (t/f)
trans  : do this transparently (t/f)
*/

/* initialize planwatch */
$_user->loadPlanwatch();

if ($_GET) {
  $add = &$_GET['add'];
  if (isset($_GET['remove']) && !empty($_GET['remove'])) {
    /* removing users; remove button was selected */
    if (is_array($add)) {
      /* removing a list of users */
      foreach ($add as $u) {
	$_user->planwatch->remove((int) $u);
      }
    } else {
      /* removing an individual user (from finger page) */
      $_user->planwatch->remove($add);

      if ($_GET['trans'] == 't') {
	/* transparent removal */
	$url = PW_URL_INDEX . "?id=" . $add;
      }
    }
  } else if (isset($_GET['move']) && !empty($_GET['move'])) {
    /* moving users between groups */
    foreach ($add as $u) {
      /* $u is alpha, $group is numeric */
      $_user->planwatch->move((int) $u, $_GET['group']);
    }
  } else {
    /* adding users */
    if (Planworld::isUser($add)) {
      $_user->planwatch->add($add);
    } else {
      $_user->planwatch->add(Planworld::addUser($add));
    }

    if (isset($_GET['trans']) && $_GET['trans'] == 't') {
      $url = PW_URL_INDEX . "?id=" . $add;
    }
  }
} else if ($_POST) {
  $list = &$_POST['list'];
  $users = explode ("\r\n", $list);
  
  foreach ($users as $u) {
    $u = trim($u);
    if ($u == '')
      continue;
    if (Planworld::isUser($u)) {
      $_user->planwatch->add($u);
    } else {
      $_user->planwatch->add(Planworld::addUser($u));
    }
  }
}

/* save the new planwatch */
$_user->save();

if (!isset($url)) {
  $url = PW_URL_INDEX . "?id=edit_pw";
}

header("Location: {$url}\n");
exit();
?>