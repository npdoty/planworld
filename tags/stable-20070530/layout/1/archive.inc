<?php
/**
 * $Id: archive.inc,v 1.20.2.1 2002/09/16 03:23:44 seth Exp $
 * Archive page.
 */

require_once($_base . 'lib/Archive.php');

/* delete / rename selected entries if the deleting user is the owner */
if (isset($_POST['entries']) && is_array($_POST['entries']) && isset($_POST['action']) && $_POST['u'] == $_user->getUsername()) {
  $offset = $_POST['offset'];

  if ($_POST['action'] == 'delete') {
    Archive::delete($_POST['u'], $_POST['entries']);
  } else if ($_POST['action'] == 'rename') {
    foreach ($_POST['entries'] as $entry) {
      Archive::setName($_POST['u'], $entry, addslashes(htmlspecialchars($_POST[$entry])));
    }
  } else if ($_POST['action'] == 'permissions') {
    /* grab a list of all of the archive entries that are currently
       public so that we can mask out the ones that need to be made
       private */
    $entries = Archive::listEntries($_POST['u']);
    /* munge array so we end up with a new one containing just
       timestamps */
    if (is_array($entries)) {
      for ($i=$offset; $i < min(sizeof($entries), $offset + 50); $i++) {
        $all[] = $entries[$i][0];
      }
    }
    if (isset($all)) {
      Archive::setPrivate($_POST['u'], $all);
    }
    if (isset($_POST['public'])) {
      Archive::setPublic($_POST['u'], $_POST['public']);
    }
  }
}

/* clear all entries */
if (isset($_GET['r']) && $_GET['r'] == 'y' && isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], 'archiving')) {
  Archive::clear($_user);
  $archive_text = "Your archives have been cleared.";
}

if (isset($_GET['repost']) && isset($_SERVER['HTTP_REFERER']) && ((strstr($_SERVER['HTTP_REFERER'], 'archiving')) || (strstr($_SERVER['HTTP_REFERER'], 'prefs')))) {
  $_user->repostPlan();
  $archive_text = "Your plan has been re-posted with the current (relevant) parts of your archives and your divider settings.";
}

/* fetch the list */
$uid = &$_GET['u'];

if (isset($uid)) {
  // array(array($timestamp, $name, $views)) Archive::listEntries ($uid)
  $entries = Archive::listEntries($uid, ($uid != $_user->getUsername()) ? true : false);
  $list = $entries;
  if (Archive::isError($entries)) {
    if ($uid == $_user->getUsername() && $_user->getArchive() == 'N') {
      $archive_text = "Archiving is currently disabled. Please visit your <a href=\"" . PW_URL_INDEX . "?id=prefs\" title=\"Preferences\">preferences</a> to enable it.";
    } else if ($uid == $_user->getUsername()) {
      $archive_text = "Your archives are currently empty.";
    } else {
      $archive_text = "Sorry, {$uid}'s public archives are currently empty.";
    }
  } else {
    /* sort entries */
    if (isset($_GET['s']) && isset($_GET['d']) && isset($_GET['os'])) {
      Archive::sortList($entries, $_GET['s'], ($_GET['d'] == 'a') ? false : true);
      $dir = ($_GET['d'] == 'd') ? 'a' : 'd';
      $cdir = ($_GET['d'] == 'd') ? 'd' : 'a';
      if (!isset($offset))
        $offset = $_GET['os'];
      $sort = $_GET['s'];
    } else {
      $cdir = 'd';
      $dir = 'd';
      if (!isset($offset))
        $offset = 0;
      $sort = 'ts';
    }
    /* sort the list of dates */
    Archive::sortList($list, 'ts', false);
  }
} else {
  $archive_text = "You did not select a set of archives to view.";
}
    
require_once($_base . 'layout/1/archive.tpl');
?>
