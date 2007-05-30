#!/usr/local/bin/php -q
<?php
/**
 * $Id: upgrade.php,v 1.5 2002/08/23 21:27:31 seth Exp $
 * Upgrade script.
 */

$_base = dirname(__FILE__) . '/';

require_once($_base . 'config.php');
require_once($_base . 'archive.php');
require_once($_base . 'lib/Archive.php');
require_once($_base . 'lib/Planworld.php');

$users = Planworld::getAllUsersWithPlans('plans');
/* everything happens in archives */
chdir($_base . 'archives/');

set_time_limit(0);

/* move archives from RCS to a database */
foreach ($users as $uid) {
  /* use old code to do this */
  $entries = getArchiveEntries($uid);
  if (is_array($entries) && !empty($entries)) {
    echo sizeof($entries) . " archive entries for {$uid}.\n";
    foreach($entries as $entry) {
      echo "Processing {$entry}...";
      flush();
      $content = '<pre>' . addslashes(getArchiveEntry($uid, $entry)) . '</pre>';
      Archive::saveEntry($uid, $entry, $content);
      echo "done.\n";
      flush();
    }
  }
}
?>
