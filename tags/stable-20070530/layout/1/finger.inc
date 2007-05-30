<?php
/**
 * $Id: finger.inc,v 1.41.2.1 2002/09/12 01:05:03 seth Exp $
 * Finger page.
 */

/* includes */
require_once($_base . 'lib/Planworld.php');
require_once($_base . 'lib/Archive.php');

/* Amherst-specific code */
//if (!$remote && $userinfo) {
//  $login_info = "<a href=\"http://note.amherst.edu/directory/results.php?t=a;uid={$planworld_target}\" target=\"_blank\" title=\"User Information\">{$planworld_target}</a>";
//  if ($userinfo['Name']) {
//    $login_info .= " (" . $userinfo['Name'];
//    if ($userinfo['GradYear'])
//      $login_info .= " '" . $userinfo['GradYear'];
//    $login_info .= ")";
//  }
//}
/* end Amherst-specific code */

/* display the plan */
echo $_target->displayPlan($_user, null, (isset($_GET['d']) ? $_GET['d'] : null));

?>
