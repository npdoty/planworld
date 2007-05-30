<?php
/**
 * $Id: prefs.inc,v 1.24.2.6 2003/08/24 20:53:40 seth Exp $
 * Preferences page.
 */

if (isset($_GET['err'])) {
  switch($_GET['err']) {
  case '0':
    $error_message = "<p class=\"error\">Your preferences have been changed.</p>\n";
    break;
  case '1':
    $error_message = "<p class=\"error\"><strong>NOTE</strong>: Journal-style plans require archiving to be enabled, so your archive setting has been set to <em>private</em>.</p>\n";
    break;
  case '2':
    $error_message = "<p class=\"error\"><strong>You must first turn off journal-style plans before you can disable archiving.</strong></p>\n";
  default:
    break;
  }
}

$themes = Skin::getThemeList($skin['id']);
$timezones = Planworld::getTimezones();

?>
<span class="subtitle">Preferences</span><br />
<?php if (isset($error_message)) echo $error_message; ?>
<p>To change your preferences, choose your desired settings and select update.  All changes can be made with only one update.</p>
<form method="post" action="prefs.php">
<div align="center"><input class="inputButton" type="submit" value="update" /></div><br />
<table cellspacing="0" cellpadding="0" border="0">
<tr>
<td class="border">
<table width="100%" cellpadding="3" cellspacing="1" border="0">

<!-- Snitch -->
<tr>
<td colspan="3" align="left" class="columnheader">Snitch</td>
</tr>
<tr>
<td align="right" class="entry">
Enable:
</td>
<td align="left" class="entry">
<input class="inputRadio" type="radio" name="snitch" value="Y"<?php if ($_user->getSnitch()) echo ' checked="checked"'; ?> />
</td>
<td rowspan="2" valign="top" class="description">
<p><strong>Snitch</strong> enables you to see who has viewed your plan (though you can only see others who have snitch on as well). Disabling Snitch will also clear all past Snitch data.</p>
</td>
</tr>
<tr>
<td align="right" class="entry">
Disable:
</td>
<td align="left" class="entry">
<input class="inputRadio" type="radio" name="snitch" value="N"<?php if (!$_user->getSnitch()) echo ' checked="checked"'; ?>>
</td>
</tr>

<tr>
<td align="right" class="entry">Display:</td>
<td align="left" class="entry">
<select name="snitchviews">
<option value="10"<?php if ($_user->getSnitchDisplayNum() == 10) echo ' selected="selected"'; ?>>10</option>
<option value="25"<?php if ($_user->getSnitchDisplayNum() == 25) echo ' selected="selected"'; ?>>25</option>
<option value="50"<?php if ($_user->getSnitchDisplayNum() == 50) echo ' selected="selected"'; ?>>50</option>
<option value="100"<?php if ($_user->getSnitchDisplayNum() == 100) echo ' selected="selected"'; ?>>100</option>
<option value="250"<?php if ($_user->getSnitchDisplayNum() == 250) echo ' selected="selected"'; ?>>250</option>
<option value="0"<?php if ($_user->getSnitchDisplayNum() == 0) echo ' selected="selected"'; ?>>all</option>
</select>
</td>
<td valign="top" class="description">
<p>This determines how many snitch entries appear when you check to see who has viewed your plan.</p>
</td>
</tr>

<?php
if ($_user->getSnitch()) {
?>
<!-- Snitch Tracker -->
<tr>
<td colspan="3" align="left" class="columnheader">Snitch Tracker</td>
</tr>
<tr>
<td align="right" class="entry">
Enable:
</td>
<td align="left" class="entry">
<input class="inputRadio" type="radio" name="snitchtracker" value="Y"<?php if ($_user->getSnitchTracker()) echo ' checked="checked"'; ?> />
</td>
<td rowspan="3" valign="top" class="description">
<p><strong>Snitch Tracker</strong> provides the ability to download snitch stats in a format that can be loaded into Excel (or some other spreadsheet program).</p>
</td>
</tr>
<tr>
<td align="right" class="entry">
Disable:
</td>
<td align="left" class="entry">
<input class="inputRadio" type="radio" name="snitchtracker" value="N"<?php if (!$_user->getSnitchTracker()) echo ' checked="checked"'; ?> />
</td>
</tr>
<tr>
<td align="right" class="entry">
Clear:
</td>
<td align="left" class="entry">
<input class="inputCheckbox" type="checkbox" name="st_clear" value="Y" />
</td>
</tr>
<?php
} // if ($_user->getSnitch())
?>

<!-- Archiving -->
<tr>
<td colspan="3" align="left" class="columnheader">Archiving</td>
</tr>
<tr>
<td align="right" class="entry">
Private:
</td>
<td align="left" class="entry">
<input class="inputRadio" type="radio" name="archive" value="P"<?php if ($_user->getArchive() == 'P') echo ' checked="checked"'; ?> />
</td>
<td rowspan="3" valign="top" class="description">
<p>If <strong>Archiving</strong> is set to either <i>Public</i> or <i>Private</i>, old plans will be archived. <i>Public</i> archiving means that archived plans will be public by default (you can change make individual entries private through the <a href="<?php echo PW_URL_INDEX; ?>?id=archiving;u=<?php echo $_user->getUsername(); ?>">archiving interface</a> later) while <i>Private</i> archiving means that they will be private by default.</p>
</td>
</tr>
<tr>
<td align="right" class="entry">
Public:
</td>
<td align="left" class="entry">
<input class="inputRadio" type="radio" name="archive" value="Y"<?php if ($_user->getArchive() == 'Y') echo ' checked="checked"'; ?> />
</td>
</tr>
<tr>
<td align="right" class="entry">
Disabled:
</td>
<td align="left" class="entry">
<input class="inputRadio" type="radio" name="archive" value="N"<?php if ($_user->getArchive() == 'N') echo ' checked="checked"'; ?> />
</td>
</tr>

<!-- Plan style -->
<?php
if (!$divider = $_user->getPreference('journal_divider')) {
  $divider = PW_DIVIDER;
}
$divider_display = Planworld::getDisplayDivider($divider);
if (Planworld::isText($divider)) {
  $divider_edit = htmlentities(Planworld::unwrap($divider));
  $divider_type = "text";
} else {
  $divider_edit = htmlentities($divider);
  $divider_type = "html";
}
?>
<tr>
<td colspan="3" align="left" class="columnheader">Plan Style</td>
</tr>
<tr>
<td align="right" class="entry">
Shared:
</td>
<td align="left" class="entry">
<input class="inputCheckbox" type="checkbox" name="shared" value="Y"<?php if ($_user->getPreference('shared')) echo ' checked="checked"'; ?> />
</td>
<td rowspan="<?php echo ($_user->getPreference('journal')) ? '5' : '3'; ?>" valign="top" class="description">
<p><i>Shared</i> plans allow other users to edit your plan without needing to re-login.  This is generally used for organizations or other groups.</p>
<p>The <i>traditional</i> style is the standard plan format: freeform.  For a more organized format, choose <i>journal</i>; entries will be displayed chronologically.  Changes will show up on your next plan update.</p>
<?php
if ($_user->getPreference('shared')) {
?>
<p>List other users who will be allowed to edit this plan:<br />
<textarea name="shared_list" class="inputTextArea" rows="4" cols="60"><?php echo $_user->showSharedUsers(); ?></textarea>
</p>
<?php
} // if ($_user->getPreference('shared'))
?>
<?php
if ($_user->getPreference('journal')) {
?>
<p>Customize your journal divider (<a href="" onclick="alert('To change the date format, fill in date[ ] with a combination of the following:\na - \'am\' or \'pm\'\nA - \'AM\' or \'PM\'\nB - Swatch Internet time\nd - day of the month, 2 digits with leading zeros; i.e. \'01\' to \'31\'\nD - day of the week, textual, 3 letters; i.e. \'Fri\'\nF - month, textual, long; i.e. \'January\'\ng - hour, 12-hour format without leading zeros; i.e. \'1\' to \'12\'\nG - hour, 24-hour format without leading zeros; i.e. \'0\' to \'23\'\nh - hour, 12-hour format; i.e. \'01\' to \'12\'\nH - hour, 24-hour format; i.e. \'00\' to \'23\'\ni - minutes; i.e. \'00\' to \'59\'\nj - day of the month without leading zeros; i.e. \'1\' to \'31\'\nl (lowercase \'L\') - day of the week, textual, long; i.e. \'Friday\'\nm - month; i.e. \'01\' to \'12\'\nM - month, textual, 3 letters; i.e. \'Jan\'\nn - month without leading zeros; i.e. \'1\' to \'12\'\ns - seconds; i.e. \'00\' to \'59\'\nS - English ordinal suffix, textual, 2 characters; i.e. \'th\', \'nd\'\nY - year, 4 digits; i.e. \'1999\'\ny - year, 2 digits; i.e. \'99\'\nz - day of the year; i.e. \'0\' to \'365\''); return false;">help</a>):<br />
<textarea name="journal_divider" class="inputTextArea" rows="4" cols="60"><?php echo $divider_edit; ?></textarea><br />
Content type: <select name="journal_type">
<option value="text"<?php if ($divider_type == 'text') echo " selected=\"selected\""; ?>>text</option>
<option value="html"<?php if ($divider_type == 'html') echo " selected=\"selected\""; ?>>html</option>
</select>
<br /><br />This will display as:<br />
<?php echo $divider_display; ?>
</p>
<p><a href="<?php echo PW_URL_INDEX; ?>?id=archiving;u=<?php echo $_user->getUsername(); ?>;repost=y">Click here to re-post your plan with updated divider and entry settings</a><br /></p>
<?php
} // if ($_user->getPreference('journal'))
?>
</td>
</tr>
<tr>
<td align="right" class="entry">
Traditional:
</td>
<td align="left" class="entry">
<input class="inputRadio" type="radio" name="journal" value="N"<?php if (!$_user->getPreference('journal')) echo ' checked="checked"'; ?> />
</td>
</tr>
<tr valign="top">
<td align="right" class="entry">
Journal:
</td>
<td align="left" class="entry">
<input class="inputRadio" type="radio" name="journal" value="Y"<?php if ($_user->getPreference('journal')) echo ' checked="checked"'; ?> />
</td>
</tr>
<?php
if ($_user->getPreference('journal')) {
?>
<tr valign="top">
<td align="right" class="entry">
Display:
</td>
<td align="left" class="entry">
<select name="journal_entries">
   <option value="1"<?php if ($_user->getPreference('journal_entries') == 1) echo 'selected="selected"'; ?>>1 entry</option>
<option value="2"<?php if ($_user->getPreference('journal_entries') == 2) echo 'selected="selected"'; ?>>2 entries</option>
<option value="3"<?php if ($_user->getPreference('journal_entries') == 3) echo 'selected="selected"'; ?>>3 entries</option>
<option value="4"<?php if ($_user->getPreference('journal_entries') == 4) echo 'selected="selected"'; ?>>4 entries</option>
<option value="5"<?php if ($_user->getPreference('journal_entries') == 5) echo 'selected="selected"'; ?>>5 entries</option>
<option value="6"<?php if ($_user->getPreference('journal_entries') == 6) echo 'selected="selected"'; ?>>6 entries</option>
<option value="7"<?php if ($_user->getPreference('journal_entries') == 7) echo 'selected="selected"'; ?>>7 entries</option>
<option value="8"<?php if ($_user->getPreference('journal_entries') == 8) echo 'selected="selected"'; ?>>8 entries</option>
<option value="9"<?php if ($_user->getPreference('journal_entries') == 9) echo 'selected="selected"'; ?>>9 entries</option>
<option value="10"<?php if ($_user->getPreference('journal_entries') == 10) echo 'selected="selected"'; ?>>10 entries</option>
<option value="11"<?php if ($_user->getPreference('journal_entries') == 11) echo 'selected="selected"'; ?>>11 entries</option>
<option value="12"<?php if ($_user->getPreference('journal_entries') == 12) echo 'selected="selected"'; ?>>12 entries</option>
<option value="13"<?php if ($_user->getPreference('journal_entries') == 13) echo 'selected="selected"'; ?>>13 entries</option>
<option value="14"<?php if ($_user->getPreference('journal_entries') == 14) echo 'selected="selected"'; ?>>14 entries</option>
<option value="15"<?php if ($_user->getPreference('journal_entries') == 15) echo 'selected="selected"'; ?>>15 entries</option>
<option value="16"<?php if ($_user->getPreference('journal_entries') == 16) echo 'selected="selected"'; ?>>16 entries</option>
<option value="17"<?php if ($_user->getPreference('journal_entries') == 17) echo 'selected="selected"'; ?>>17 entries</option>
<option value="18"<?php if ($_user->getPreference('journal_entries') == 18) echo 'selected="selected"'; ?>>18 entries</option>
<option value="19"<?php if ($_user->getPreference('journal_entries') == 19) echo 'selected="selected"'; ?>>19 entries</option>
<option value="20"<?php if ($_user->getPreference('journal_entries') == 20) echo 'selected="selected"'; ?>>20 entries</option>
</select>
</td>
</tr>
<tr valign="top">
<td align="right" class="entry">
Order:
</td>
<td align="left" class="entry">
<select name="journal_order">
<option value="new"<?php if ($_user->getPreference('journal_order') == 'new') echo " selected=\"selected\""; ?>>newest first</option>
<option value="old"<?php if ($_user->getPreference('journal_order') == 'old') echo " selected=\"selected\""; ?>>oldest first</option>
</select>
</td>
</tr>
<?php
} // if ($_user->getPreference('journal'))
?>

<!-- Look and Feel -->
<tr>
<td colspan="3" align="left" class="columnheader">Look and Feel</td>
</tr>
<tr>
<td align="left" class="entry" colspan="2">
Theme:
</td>
<td valign="top" class="description" rowspan="2">
<p><strong>Themes</strong> determine how planworld looks for <i>you</i>.  Themes generally change the color scheme, although other elements may be affected.</p>
</td>
</tr>
<tr>
<td class="entry" colspan="2">
<select name="theme">
<?php
if (is_array($themes)) {
  foreach ($themes as $t) {
?>
<option value="<?php echo $t['ID']; ?>"<?php if ($_user->getTheme() == $t['ID']) echo ' selected'; ?>><?php echo $t['Name']; ?></option>
<?php
  } // foreach ($themes as $t)
} // if (is_array($themes))
?>
</select>
</td>
</tr>

<!-- Timezones -->
<tr>
<td colspan="3" align="left" class="columnheader">Timezone</td>
</tr>
<tr>
<td align="left" class="entry" colspan="2">
Timezone:
</td>
<td valign="top" class="description" rowspan="2">
<p>Your <strong>timezone</strong> should reflect your current location.  Planworld uses it to display update and login times relative to your local time.</p>
</td>
</tr>
<tr>
<td class="entry" colspan="2">
<select name="timezone">
<?php
if (is_array($timezones)) {
  foreach ($timezones as $t) {
?>
<option value="<?php echo $t; ?>"<?php if ($_user->getTimezone() == $t) echo ' selected="selected"'; ?>><?php echo str_replace('_', ' ', $t); ?></option>
<?php
  } // foreach ($timezones as $t)
} // if (is_array($timezones))
?>
</select>
</td>
</tr>

<!-- Watch Order -->
<tr>
<td colspan="3" align="left" class="columnheader">
Watch Order
</td>
</tr>
<tr>
<td align="right" class="entry">
Alph.:
</td>
<td align="left" class="entry">
<input class="inputRadio" type="radio" name="watchorder" value="alph"<?php if ($_user->getWatchOrder() == 'alph') echo ' checked="checked"'; ?> />
</td>
<td rowspan="3" class="description" valign="top">
<p><strong>Watch Order</strong> determines the order in which your planwatch will be displayed. <i>Alph.</i> orders entries alphabetically, by username.  <i>Recent</i> orders entries with the most recently updated on the top.  <i>Old</i> orders entries with the least recently updated on the top.</p>
</td>
</tr>
<tr>
<td align="right" class="entry">
Recent:
</td>
<td align="left" class="entry">
<input class="inputRadio" type="radio" name="watchorder" value="newest"<?php if ($_user->getWatchOrder() == 'newest') echo ' checked="checked"'; ?> />
</td>
</tr>
<tr>
<td align="right" class="entry">
Old:
</td>
<td align="left" class="entry">
<input class="inputRadio" type="radio" name="watchorder" value="old"<?php if ($_user->getWatchOrder() == 'old') echo ' checked="checked"'; ?> />
</td>
</tr>

<!-- World Viewable -->
<tr>
<td colspan="3" align="left" class="columnheader">World Viewable</td>
</tr>
<tr>
<td align="right" class="entry">
Enable:
</td>
<td align="left" class="entry">
<input class="inputRadio" type="radio" name="world" value="Y"<?php if ($_user->getWorld()) echo ' checked="checked"'; ?> />
</td>
<td rowspan=2 valign=top class="description">
<?php /* Amherst-specific text */ ?>
<p>If your plan is <strong>World Viewable</strong>, non-Amherst users will be able to view your plan using a finger gateway. E.g., <a href="http://www.amherst.edu/cgi-bin/finger?marx@note.amherst.edu" title="Finger marx" target="_blank">marx's plan</a>.</p>
</td>
</tr>
<tr>
<td align="right" class="entry">
Disable:
</td>
<td align="left" class="entry">
<input class="inputRadio" type="radio" name="world" value="N"<?php if (!$_user->getWorld()) echo ' checked="checked"'; ?> />
</td>
</tr>

</table>
</td>
</tr>
</table>
<br />
<div align="center"><input class="inputButton" type="submit" value="update" /></div>
</form>
