<?php
/**
 * $Id: snitch.inc,v 1.31.4.2 2003/10/04 01:07:26 seth Exp $
 * Snitch page.
 */

if (!$_user->getSnitch()) {
  $snitch_text = "<p>You must enable snitch to use this function.  Please <a href=\"" . PW_URL_INDEX . "?id=prefs\" title=\"Edit your preferences\">edit your preferences</a>.</p>";
} else {
  /* reset snitch views */
  if (isset($_GET['r']) && $_GET['r'] == 'y' && isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], 'snitch')) {
    $_user->resetSnitchViews();
  }

  $views = $_user->getSnitchViews((isset($_GET['o'])) ? $_GET['o'] : null, (isset($_GET['d'])) ? $_GET['d'] : null);
  $lastupdate = $_user->getLastUpdate();

  if (empty($views)) {
	$snitch_text = "<p>No one has viewed your plan since you last enabled snitch.</p>";
  }
  if (isset($_GET['d']) && $_GET['d'] == 'a') {
	$dir = 'd';
  } else {
	$dir = 'a';
  }
}
?>
<span class="subtitle">Snitch</span><br />
<p><?php if (isset($snitch_text)) echo $snitch_text; ?></p>
<?php if (isset($views) && !empty($views)) { ?>
<p>
<?php if ($_user->getSnitchTracker()) { ?>
<a href="<?php echo PW_URL_BASE; ?>snitchtracker.php">Export Snitch Tracker data</a><br />
<? } // if ($_user->getSnitchTracker()) ?>
<a href="?id=snitch;o=<?php (isset($_GET['o']) ? $_GET['o'] : 'd'); ?>;d=<?php (isset($_GET['d']) ? $_GET['d'] : 'a'); ?>;r=y" onclick="return confirm('The number of times each user has viewed your plan will be reset to zero.  Are you sure that you want to do this?');">Reset view counters</a>
</p>
<p>
Your plan has had the following views by <strong>
<?php echo $_user->getNumSnitchViews(); ?>
</strong> different people since you last enabled snitch (users with <strong>*</strong>'s are on your planwatch)<?php if ($_user->getSnitchDisplayNum() > 0) echo "; displaying the most recent <strong>" . $_user->getSnitchDisplayNum() . "</strong>"; ?>:
</p>
<table cellpadding="0" cellspacing="0" align="center" border="0">
<tr>
<td class="border">
<table cellspacing="1" cellpadding="3" border="0">
<tr>
<td align="center" class="columnheader"><a class="columnheader" href="?id=snitch;o=u;d=<?php echo $dir; ?>" title="Sort by username">username</a></td>
<td align="center" class="columnheader"><a class="columnheader" href="?id=snitch;o=d;d=<?php echo $dir; ?>" title="Sort by most recently viewed">most recently viewed</a></td>
<td align="center" class="columnheader"><a class="columnheader" href="?id=snitch;o=v;d=<?php echo $dir; ?>" title="Sort by total views">total views</a></td>
<td align="center" class="columnheader">last update</td>
</tr>
<?php
foreach($views as $snitch) {
  if (((!isset($_GET['o']) || (isset($_GET['o']) && $_GET['o'] == 'd' && $_GET['d'] == 'd')) && $lastupdate > $snitch['Date'] && ((isset($last_view) && $lastupdate <= $last_view['Date']) || !isset($last_view))) || (isset($_GET['o']) && $_GET['o'] == 'd' && $_GET['d'] == 'a' && $lastupdate < $snitch['Date'] && $lastupdate >= $last_view['Date'])) {
?>
<tr class="entry">
<td align="center" colspan="4"><strong>your last update (<?php echo Planworld::getDisplayDate($lastupdate); ?>)</strong></td>
</tr>
<?php
	$lastupdate = 0;
  }  // if ($lastupdate > $views['lastUpdate']) 
?>
<tr class="entry">
<td align="left">
<?php if ($snitch['InPlanwatch']) echo "* "; ?>
<a href="<?php echo PW_URL_INDEX; ?>?id=<?php echo $snitch['Name']; ?>" title="Finger <?php echo $snitch['Name']; ?>"><?php echo $snitch['Name']; ?></a>
</td>
<td align="center"><?php echo Planworld::getDisplayDate($snitch['Date']); ?></td>
<td align="center"><?php echo $snitch['Views']; ?></td>
<td align="center"><?php echo Planworld::getDisplayDate($snitch['LastUpdate'], false); ?></td>
</tr>
<?php
  $last_view = $snitch;
} // foreach($views as $snitch)
?>
</table>
</td>
</tr>
</table>
<?php } // if(isarray($views)) ?>
