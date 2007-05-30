<?php
/**
 * $Id: preview.inc,v 1.15.2.1 2002/09/14 04:09:14 seth Exp $
 * Preview page.
 */

$type = &$_POST['type'];
$archive = &$_POST['archive'];

/* preview the correct plan (in the case that it's shared) */
if ($to_edit->isShared()) {
  /* switch planwatches so that the logged-in user's is active */
  $to_edit->planwatch = $_user->planwatch;
  /* do the switch */
  $_user = $to_edit;
}

?>
<span class="subtitle">Preview</span><br />
<form action="parser.php" method="post">
<p>You can either <a href="#edit" title="Edit your plan">edit your plan</a> or post it.</p>
<input class="inputButton" type="submit" name="post" value="fixplan" />&nbsp;<input class="inputButton" type="submit" name="cancel" value="cancel" /><br /><br />
<hr />
<?php echo $_user->previewPlan($_user, $plan_display); ?>
<br />
<hr />
<br />
<?php require($_base . "layout/{$skin['id']}/edit.tpl"); ?>
