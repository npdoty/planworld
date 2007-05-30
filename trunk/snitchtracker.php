<?php

/**
 * $Id: snitchtracker.php,v 1.1 2002/02/16 04:02:15 seth Exp $
 * SnitchTracker exporting.
 */

/* framework for additional export types */
if (isset($_GET['t']) && $_GET['t'] == 'tab') {
  $type = 'tab';
  $delim = "\t";
  $filename = 'snitchtracker.tab';
  header('Content-Type: text/tab-separated-values');
} else {
  $type = 'csv';
  $delim = ',';
  $filename = 'snitchtracker.csv';
  /* the following is either text/x-csv, text/x-comma-separated-values, application/csv */
//  header('Content-Type: text/comma-separated-values');
  header('Content-Type: application/csv');
//  header('Content-Type: text/x-csv');
}

/* suggest a filename */
header('Content-Disposition: attachment; filename=' . $filename);

/* fetch entries */
$entries = $_user->getSnitchTrackerEntries();

/* print them out */
foreach ($entries as $entry) {
  // $entry[0] = username, $entry[1] = viewed ts
  echo $entry[0] . $delim . date('n/j/Y h:i:s A', $entry[1]) . "\r\n";
}

?>
