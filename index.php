<?php
/**
 * $Id: index.php,v 1.38.2.5 2003/03/17 15:44:45 seth Exp $
 * PLANWORLD
 * http://planworld.net
 */

/* includes */
$_base = dirname(__FILE__) . '/';
require_once($_base . 'lib/Planworld.php');
require_once($_base . 'lib/Stats.php');
require_once($_base . 'lib/User.php');

// debugging
// $_user->dump();

/* record this view as a hit */
Stats::addHit();

/* set the timezone to the user's local timezone */
putenv('TZ=' . $_user->getTimezone());

/* shift control to skin */
require($_base . 'layout/' . $_user->getSkin() . '/skin.php');

/* save any user settings that may have changed during this session */
$_user->save(true);

?>
