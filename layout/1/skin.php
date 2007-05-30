<?php
/**
 * $Id: skin.php,v 1.5 2002/02/08 01:22:04 seth Exp $
 * Skin functions / configuration.
 */

/* includes */
require_once($_base . 'lib/Skin/' . $_user->getSkin() . '.php');

/* local configuration */
$skin['id'] = 1;
$skin['name'] = "Basic";
$skin['author'] = "Seth Fitzsimmons, Peter Kupfer, and Baker Franke";

/* delete plan if desired */
if (isset($_POST['delete']) && $_POST['delete'] == $_user->getUsername() && $_POST['clear']) {
  $_user->clearPlan();
}

/* initialization */
$pagetitle = 'planworld :: ' . Skin::getTitleString($_target);
$theme = Skin::getThemeDir($_user->getTheme());

/* prepare to display an alternate title image if one exists for that theme */
if (file_exists($_base . "layout/{$skin['id']}/themes/{$theme}/planworld.gif")) {
  $headerImage = "layout/{$skin['id']}/themes/{$theme}/planworld.gif";
} else {
  $headerImage = "layout/{$skin['id']}/themes/default/planworld.gif";
}

/* display the framework */
require($_base . "layout/{$skin['id']}/outline.tpl");

?>