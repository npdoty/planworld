<?php
/**
 * $Id: prefs.php,v 1.5.2.5 2003/03/17 15:44:45 seth Exp $
 */

/* Initialization */
$_base = dirname(__FILE__) . '/';

/* send them elsewhere if no POST data */
if (!isset($_POST)) {
  header("Location: " . PW_URL_INDEX);
  exit();
}


// standard response (overridden by certain preference changes)
$location = PW_URL_INDEX . "?id=prefs;err=0";

/* set the default archiving policy */
if ($_POST['archive'] == 'N' && ($_user->getPreference('journal') || $_POST['journal'] == 'Y')) {
  $location = PW_URL_INDEX . "?id=prefs;err=2";
} else {
  $_user->setArchive($_POST['archive']);
}

/* turn snitch on or off (User class will handle starting / clearing */
$_user->setSnitch(($_POST['snitch'] == 'Y') ? true : false);

/* set the number of users to display when checking snitch views */
$_user->setSnitchDisplayNum($_POST['snitchviews']);

/* make this user's plan world viewable or not */
$_user->setWorld(($_POST['world'] == 'Y') ? true : false);

/* set the type of planwatch ordering that this user desires */
$_user->setWatchOrder($_POST['watchorder']);

/* set the theme that the user wishes */
$_user->setTheme($_POST['theme']);

/* set the user's local timezone */
if ($_POST['timezone'] != $_user->getTimezone() && $_POST['timezone'] != PW_TIMEZONE) {
  $_user->setTimezone($_POST['timezone']);
} else if ($_POST['timezone'] == PW_TIMEZONE) {
  $_user->clearPreference('timezone');
}

/* set the user's snitch tracker preference */
if (isset($_POST['snitchtracker']) && $_user->getSnitch()) {
  $_user->setSnitchTracker(($_POST['snitchtracker'] == 'Y') ? true : false);
}
if (isset($_POST['st_clear']) && $_POST['st_clear'] == 'Y') {
  $_user->clearSnitchTracker();
}

/* set the user's plan style preference */
if (!$_user->getPreference('journal') && isset($_POST['journal']) && $_POST['journal'] == 'Y') {
  // enable journaling
  $_user->setPreference('journal', 'true');
  // force archiving to private if off
  if ($_user->getArchive() == 'N') {
    $_user->setArchive('P');
    $location = PW_URL_INDEX . "?id=prefs;err=1";
  }
} else if (isset($_POST['journal']) && $_POST['journal'] == 'N' && $_user->getPreference('journal')) {
  $_user->setPreference('journal', 'false');
}
if (isset($_POST['journal_entries']) && isset($_POST['journal_order']) && $_user->getPreference('journal')) {
  $_user->setPreference('journal_entries', $_POST['journal_entries']);
  $_user->setPreference('journal_order', $_POST['journal_order']);
}
if (isset($_POST['journal_divider'])) {
  if (isset($_POST['journal_type']) && $_POST['journal_type'] == 'text') {
    $divider = "<pre>" . addslashes($_POST['journal_divider']) . "</pre>";
  } else {
    $divider = addslashes($_POST['journal_divider']);
  }
  if ($divider != $_user->getPreference('journal_divider') && $divider != PW_DIVIDER) {
    $_user->setPreference('journal_divider', $divider);
  } else if ($divider == PW_DIVIDER) {
    $_user->clearPreference('journal_divider');
  }
}

/* set the user's shared plan preference */
if (!$_user->getPreference('shared') && isset($_POST['shared']) && $_POST['shared'] == 'Y') {
  // enable shared plans
  $_user->setPreference('shared', 'true');
} else if ($_user->getPreference('shared') && ((isset($_POST['shared']) && $_POST['shared'] != 'Y') || !isset($_POST['shared']))) {
  // disable shared plans
  $_user->setPreference('shared', 'false');
}
if ($_user->getPreference('shared')) {
  /* clear the list of users allowed to edit this plan */
  $_user->clearPreference('shared_%');

  if (isset($_POST['shared_list'])) {
    /* recreate the list */
    $shared_list = explode("\r\n", $_POST['shared_list']);
    foreach ($shared_list as $u) {
      $u = trim($u);
      if (empty($u))
	continue;
      if (Planworld::isUser($u))
	$_user->setPreference('shared_' . $u, 'true');
    }
  }
}

/* save the user's settings */
$_user->save();

header("Location: " . $location);
exit();
?>
