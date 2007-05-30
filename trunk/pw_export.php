<?php
/**
 * Exports a user's planwatch.
 */

header("Content-type: text/plain\r\n");

$_user->setWatchOrder("alpha");
$_user->loadPlanwatch();
foreach ( $_user->planwatch->getList() as $groupName => $group ) {
  if ( $groupName == "Send" || $groupName == "Snoop" )
    continue;
  foreach ( $group as $user => $data ) {
    echo $user . "\n";
  }
}
?>
