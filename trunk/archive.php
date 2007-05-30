<?php
/**
 * $Id: archive.php,v 1.7 2002/02/28 04:25:26 seth Exp $
 * Planworld archiving functions.
 */

function getArchiveEntries ($user) {
  $user = addslashes($user);

  // run rlog to get version information
  //  $dir = getcwd();
  // chdir('./archives');
  exec("rlog -zLT {$user}", $log, $retcode);
  //  chdir($dir);

  // if it failed, no version information available
  if ($retcode != 0) {
    return false;
  }

  // loop through output and parse
  foreach ($log as $entry) {
    if (ereg('date', $entry)) {
      list($date, $null) = explode(';', $entry, 2);
      list($null, $date, $time) = explode(' ', $date);
      list($y, $m, $d) = explode('-', $date);
      list($h, $i, $s) = explode(':', $time);
      $info[] = mktime($h, $i, $s, $m, $d, $y);
    }
  }

  // returns an array of timestamps
  return $info;
}

function clearArchives ($username) {
  $dir = getcwd();
  chdir('./archives/RCS');
  unlink("./{$username},v");
  chdir($dir);
}

function getArchiveEntry ($uid, $ts) {
  $dir = getcwd();
  // chdir('./archives');
  exec("co -zLT -d'" . date('Y/m/d H:i:s', $ts) . "' {$uid} 2> /dev/null", $null, $retcode);
  $fp = fopen ("./{$uid}", "r");
  $plan = fread($fp, filesize("./{$uid}"));
  fclose($fp);
  unlink("./{$uid}");
  //  chdir($dir);
  return $plan;
}

?>
