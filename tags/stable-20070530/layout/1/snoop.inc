<?php
/**
 * $Id: snoop.inc,v 1.12 2002/02/07 05:19:17 seth Exp $
 * Snoop: see who referenced you on their plan.
 */

$snoop = Snoop::getReferences($_user, (isset($_GET['o'])) ? $_GET['o'] : null, (isset($_GET['d'])) ? $_GET['d'] : null);
if (empty($snoop)) {
  $text = "<p>Sorry, no one has referenced your plan.</p>";
} else {
  $text = "<p>Your plan has been referenced " . sizeof($snoop) . " time(s):</p>";
}
if (isset($_GET['d']) && $_GET['d'] == 'a') {
  $dir = 'd';
} else {
  $dir = 'a';
}
?>
<span class="subtitle">Snoop</span><br />
<p><?php echo $text; ?></p>
<?php if (is_array($snoop) && !empty($snoop)) { ?>
<table cellpadding="0" cellspacing="0" align="center" border="0">
<tr>
<td class="border">
<table cellspacing="1" cellpadding="3" border="0">
<tr>
<td align="center" class="columnheader"><a class="columnheader" href="?id=snoop;o=u;d=<?php echo $dir; ?>" title="Sort by username">username</a></td>
<td align="center" class="columnheader"><a class="columnheader" href="?id=snoop;o=d;d=<?php echo $dir; ?>" title="Sort by date">date</a></td>
<td align="center" class="columnheader"><a class="columnheader" href="?id=snoop;o=l;d=<?php echo $dir; ?>" title="Sort by last update">last update</a></td>
</tr>
<?php
foreach($snoop as $entry) {
?>
<tr class="entry">
<td align="left"><a href="<?php echo PW_URL_INDEX; ?>?id=<?php echo $entry['userName']; ?>" title="Finger <?php echo $entry['userName']; ?>"><?php echo $entry['userName']; ?></a></td>
<td align="center"><?php echo Planworld::getDisplayDate($entry['date']); ?></td>
<td align="center"><?php echo Planworld::getDisplayDate($entry['lastUpdate']); ?></td>
</tr>
<?php
} // foreach($snoop as $entry)
?>
</table>
</td>
</tr>
</table>
<?php } // if(isarray($snoop)) ?>
