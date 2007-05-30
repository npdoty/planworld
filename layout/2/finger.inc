<?php
/**
 * $Id: finger.inc,v 1.2 2001/12/12 21:47:23 seth Exp $
 * Finger page.
 */

require_once('snitch.php');
global $planworld_user, $planworld_id, $planworld_target, $planworld_target_id;

$userinfo = getUserInfo($planworld_target);
$remote = isRemoteUser($planworld_target_id);

// if (!strstr($planworld_target, '@')) {
if (!$remote && $userinfo) {
  $login_info = "<a href=\"http://note.amherst.edu/directory/results.php?t=a&uid={$planworld_target}\" target=\"_blank\" title=\"User Information\">{$planworld_target}</a>";
  if ($userinfo['Name']) {
	$login_info .= " (" . $userinfo['Name'];
	if ($userinfo['GradYear'])
	  $login_info .= " '" . $userinfo['GradYear'];
	$login_info .= ")";
  }
  $login_info .= "\n";
} else {
  $login_info = "{$planworld_target}\n";
}

if (!$remote) {
  $last_login = getLastLogin($planworld_target_id);

  if (!$planworld_target_id && !$userinfo) {
	$last_login = $last_update = "???\n";
	$plan = "[Sorry, could not find \"{$planworld_target}\"]\n";
	$planwatch_add = '';
  } else if ((!$planworld_target_id || !$last_login)) {
	$last_login = $last_update = "Never\n";
	$plan = "[No Plan]\n";
  } else if (!getLastUpdated($planworld_target_id)) {
	$last_login = getDisplayDate($last_login) . "\n";
	$last_update = "Never\n";
	$plan = "[No Plan]\n";
  } else {
//	if ($planworld_id != $planworld_target_id) {
//	  addView($planworld_target_id);
//	  snitchCheck($planworld_id, $planworld_target_id);
//	}
	$last_login = getDisplayDate($last_login) . "\n";
	$last_update = getDisplayDate(getLastUpdated($planworld_target_id));
//	$archive = getArchive($planworld_target_id);
//	if ($archive == 'Y' || ($archive == 'P' && $planworld_id == $planworld_target_id)) {
//	  $last_update .= " (<a href=\"" . PW_URL_INDEX . "?id=archiving&u={$planworld_target}\" title=\"Archives\">archives</a>)";
//	}
	$last_update .= "\n";
	if ($_GET['d']) {
	  $plan_date = "Date posted: " . getDisplayDate($_GET['d']) . "\n";
	}
  }
} else {
  $login_info = "{$planworld_target}\n";
}

if (empty($plan)) {
  list($plan,$host) = getPlanText($planworld_target_id, $_GET['d'], $_GET['r']);
  if (!$plan && $host) {
	$plan = "\n<strong>Error</strong>: Remote host ({$host}) could not be contacted. Please try again later.";
  } else if (!$plan) {
	$plan = "[No Plan]\n";
  }
  $plan = nl2br(addLinks($plan, $host));
}

?>
<center><h1>View <?php echo $planworld_target; ?>'s Plan</h1></center>
<b>Name:</b>  <?php echo $login_info; ?><br>
<b>Last Login:</b> <?php echo $last_login; ?><br><br><br>
<?php echo $plan; ?>
