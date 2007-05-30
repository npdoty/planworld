<?php
/**
 * Whois.
 */

require_once($_base . 'lib/Stats.php');

$start = (isset($_GET['u']) ? $_GET['u'][0] : 'a');
$users = Planworld::getAllUsersWithPlans($start);
$num_users = Stats::getNumPlans();

/* assemble a 4 column listing of people (specify as $cols) */
$cols = 4;
$userdata = '';
$i = 0;
while ($i < sizeof($users)) {
  $userdata .= "<tr class=\"entry\">\n";
  for ($j=0; $j<$cols; $j++) {
    if (!isset($users[$i])) {
      /* pad out the remainder with blank cells */
      $userdata .= "<td align=\"center\" width=\"25%\">&nbsp;</td>\n";
    } else {
      $userdata .= "<td align=\"center\" width=\"25%\"><a href=\"" . PW_URL_INDEX . "?id={$users[$i]}\" title=\"Finger {$users[$i]}\">{$users[$i++]}</a></td>\n";
    }
  }
  $userdata .= "</tr>\n";
}

?>
<span class="subtitle">Whois (<?php echo number_format($num_users); ?> users with plans)</span><br /><br />
<div align="center">
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=a">A</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=b">B</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=c">C</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=d">D</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=e">E</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=f">F</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=g">G</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=h">H</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=i">I</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=j">J</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=k">K</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=l">L</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=m">M</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=n">N</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=o">O</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=p">P</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=q">Q</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=r">R</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=s">S</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=t">T</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=u">U</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=v">V</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=w">W</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=x">X</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=y">Y</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=z">Z</a> |
<a href="<?php echo PW_URL_INDEX; ?>?id=whois;u=%23">#</a>
</div>
<hr width="60%" noshade="noshade" align="center" />
<div style="float: left; margin-right: 10px;">
<?php
$i = 0;
$total = sizeof($users);
foreach ($users as $u) {
  if ($i > ($total / 5)) { ?>
</div>
<div style="float: left; margin-right: 10px; margin-left: 10px;">
<?php  $i = 0;
  } // if ($i % ($total / 5) == 0) ?>
<a href="<?php echo PW_URL_INDEX; ?>?id=<?php echo $u; ?>" title="Finger <?php echo $u; ?>"><?php echo $u; ?></a><br />
<?php $i++;
} // foreach ($users as $u) ?>
</div>
