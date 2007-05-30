<?php
/**
 * $Id: stats.inc,v 1.24 2002/02/10 22:19:02 seth Exp $
 * Statistics page.
 */

require_once($_base . 'lib/Stats.php');

$total_views = Stats::getTotalPlanViews();
$personal_views = Stats::getNumViews($_user);
$num_users = Stats::getNumLoggedIn();
$num_plans = Stats::getNumPlans();
$num_snitch = Stats::getNumSnitchRegistered();
$day_updates = Stats::getNumPlans(86400);
$week_updates = Stats::getNumPlans(604800);
$day_logins = Stats::getNumLoggedIn(86400);
$week_logins = Stats::getNumLoggedIn(604800);

/* display the page fragment */
require_once($_base . 'layout/1/stats.tpl');
?>