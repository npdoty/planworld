<?php
/**
 * $Id: alumni.inc,v 1.24.2.6 2003/08/24 20:53:40 seth Exp $
 * Alumni movement page (ndoty: copy of prefs.inc).
 */

if (isset($_GET['err'])) {
  switch($_GET['err']) {
  case '0':
    $error_message = "<p class=\"error\">Your account has been moved.</p>\n";
    break;
  case '1':
    $error_message = "<p class=\"error\"><strong>There was an unexpected error.</strong></p>\n";
    break;
  default:
    break;
  }
}
else {
	$year = $_user->getYear();
	$alumniName = $_user->getUsername();
	
	if ($_user->hasOldUsername())
	{
		$nameGuess = $_user->oldUsername();
	}
	else if ($year == "UNKNOWN" || $year > 6) {
		$error_message = "<p class=\"error\">You can't move your old account to your alumni account until you've logged in with your alumni account.</p>";
	}
	else {
		$nameGuess = preg_replace("/[0-9]{2}$/","",$alumniName);		

		if ($_user->getUserID() < Planworld::nameToID($nameGuess)) {
			$error_message = "<p class=\"error\">It appears that this user's account may have already been moved, or some other error occurred.</p>";
		}
	}

	if (!Planworld::isUser($nameGuess, true)) {
		$error_message = "<p class=\"error\">Sorry, we guessed that your old username was $nameGuess, but we can't find that user.  But don't lose all hope, we might still be able to help you move your account manually: please email <strong>note@amherst.edu</strong> with both your alumni username and your old username.</p>";
	}
}
?>
<span class="subtitle">Alumni Movement</span><br />
<?php if (isset($error_message)) echo $error_message;
else { ?>
<p>There are two different things you can do to move yourself more fully to your new alumni identity.  You'll probably only want to do one, though it is possible to do the second and then later come back and do the first.  Even if you are able to do this twice, <i>don't</i>.  (Also, don't refresh this or the following page once you've clicked the submit button.)</p>
<form method="post" action="alumni.php">

<input type="radio" name="alumniMove" value="full">Move your User ID. <em>This changes your username on everyone's planWatch (everyone who has <? echo $nameGuess; ?> on their planwatch will suddenly have <? echo $alumniName; ?> <i>and</i> everyone who has <? echo $alumniName; ?> will suddenly have <? echo $nameGuess; ?>), adds your old archives to your new archives (so that all archives are accessible using <? echo $alumniName; ?>) and shares your old plan (<? echo $nameGuess; ?>) so that you can put up a goodbye message.  This is the full move option.  Think carefully about this.  It cannot (easily) be undone.</em><br />
	<input type="radio" name="alumniMove" value="archive">Move your Archives only. <em>This just moves your old archives to your new archives (so that all archives are accessible using <? echo $alumniName; ?>).  It will also let you edit your old plan (<? echo $nameGuess; ?>) so that you can put up a goodbye message.  It cannot be undone.</em><br />
<input class="inputButton" type="submit" value="Move" /><br />
</form>
<?
}	//end else (what to do if we want them to be able to take an alumni movement action)
?>