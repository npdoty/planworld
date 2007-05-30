<?php
/**
 * $Id: prepend.php,v 1.19.2.3 2003/03/17 19:32:35 seth Exp $
 * Initialization routines.
 */

/* error level set to ALL (inc. warnings) for debugging */
error_reporting(E_ALL);

/* turn off magic quotes (they're evil) */
set_magic_quotes_runtime(0);

/* start a session to keep localized variables */
session_start();

/* set some site-dependent constants */
$planworld_url_base = eregi_replace("[A-z.]+$", "", $_SERVER['PHP_SELF']);
define('PW_URL_BASE', $planworld_url_base);
define('PW_URL_INDEX', $planworld_url_base);
unset($planworld_url_base);

/* includes */
$_base = dirname(__FILE__) . '/';

/* if the authentication system uses $PHP_AUTH_USER (HTTP auth), the
 order of the next 2 files matters; if the auth scripts unset
 $PHP_AUTH_USER, auth.php (or its substitute) must come first in order
 for users to show up in the logs */

require_once($_base . 'config.php');
require_once($_base . 'lib/Planworld.php');

/* Amherst specific alumni conversion for new users */
/*** MUST COME BEFORE auth.php IS INCLUDED ***/
if (isset($_SESSION['note_user'])) {
  if (is_numeric(substr($_SESSION['note_user'], -2)) && !Planworld::isUser($_SESSION['note_user'])) {
    // change the user's name
    $query = "UPDATE users SET username='" . $_SESSION['note_user'] . "' WHERE username='" . substr($_SESSION['note_user'], 0, -2) . "'";
    Planworld::query($query);
  }
}
/* end Amherst specific code */

require_once('auth.php');
require_once($_base . 'lib/Online.php');
require_once($_base . 'lib/User.php');

PEAR::setErrorHandling(PEAR_ERROR_PRINT);

/* set the random function to use (varies by database) */
if (PW_DB_TYPE == 'pgsql') {
  define('PW_RANDOM_FN', 'RANDOM()');
} else if (PW_DB_TYPE == 'mysql') {
  define('PW_RANDOM_FN', 'RAND()');
}

/* allow pass-thru even if user hasn't logged in (auth system will take care of this */
if (isset($_SESSION['note_user'])) {
  /* create an object representing the browsing user */
  /* this assumes that note_user has been set as a session variable
 somewhere in the auth system */
  $_user = User::factory($_SESSION['note_user']);

  /* update this user's last login */
  $_user->setLastLogin(mktime());

  /* update this user's last known ip address */
  $_user->setLastIP($_SERVER['REMOTE_ADDR']);
  
  /* save it to prevent planwatch weirdness */
  $_user->save();
  
  /* create an object representing the target user (or a string representing the page) */
  if (isset($_GET['id'])) {
    $section = str_replace(' ', '', $_GET['id']);
    
    if ($section == $_user->getUsername()) {
      $_target = &$_user;
    } else if (Planworld::isValidUser($section)) {
      $_target = User::factory($section);
    } else if ($section == 'random') {
      $_target = User::factory(Planworld::getRandomUser());
    } else {
      $_target = $section;
    }
    
    /* force fetching of update / login times if the target user is remote */
    if (is_object($_target) && $_target->getType() == 'planworld') {
      $_target->forceUpdate();
    }
    
  }
  
  /* update the current status of online users (including this one) */
  Online::clearIdle();
  Online::updateUser($_user, $_target);
}
?>
