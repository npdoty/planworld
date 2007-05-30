<span class="subtitle">Statistics</span><br />
<p>There have been a total of <strong>
<?php echo number_format(Stats::getNumHits()); ?></strong> hits since <?php echo PW_INCEPTION; ?>.<br />
<strong><?php echo number_format($num_users); ?></strong> users have logged in to planworld;
<strong><?php echo number_format($num_plans); ?></strong> of these users have plans (<?php echo round(($num_plans / $num_users) * 100, 2); ?>%).<br />
<strong><?php echo number_format($num_snitch); ?></strong> are snitch registered (<?php echo round(($num_snitch / $num_users) * 100, 2); ?>%).<br />
<strong><?php echo number_format($day_updates); ?></strong> plans have been updated in the past 24 hours (<?php echo round(($day_updates / $num_users) * 100, 2); ?>%),
<strong><?php echo number_format($week_updates); ?></strong> in the past week (<?php echo round(($week_updates / $num_users) * 100, 2); ?>%).<br />
<strong><?php echo number_format($day_logins); ?></strong> people have logged in in the past 24 hours (<?php echo round(($day_logins / $num_users) * 100, 2); ?>%),
<strong><?php echo number_format($week_logins); ?></strong> in the past week (<?php echo round(($week_logins / $num_users) * 100, 2); ?>%).</p>

<p>Throughout planworld, there have been <strong><?php echo number_format($total_views); ?></strong> plan views. There are a total of <strong><?php echo number_format(Stats::getNumArchiveEntries()); ?></strong> archive entries (<strong><?php echo number_format(Archive::getSize($_user->getUserID())); ?></strong> of which are yours).</p>
<?php
if ($total_views == 0)
  $total_views = 1;
?>
<p>Your plan has been viewed <strong><?php echo number_format($personal_views); ?></strong> times and accounts for <strong><?php echo round(($personal_views / $total_views) * 100, 2); ?>%</strong> of the total.</p>

<p>There are a total of <strong><?php echo number_format(Stats::getNumCookies()); ?></strong> cookies, <strong><?php echo number_format(Stats::getNumCookies(true)); ?></strong> of which were submitted by planworld users.</p>
<p><img src="http://halogen.note.amherst.edu/~seth/images/pw_stats.png" align="left" alt="Usage Graph" /><img src="http://halogen.note.amherst.edu/~seth/images/pw_stats_week.png" align="left" alt="Daily Usage Graph" /><br />
<img src="http://halogen.note.amherst.edu/~seth/images/pw_stats_month.png" align="left" alt="Weekly Usage" /></p>
