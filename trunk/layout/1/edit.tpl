<span class="subtitle">Edit <?php if ($_user->isShared()) echo $_user->getUsername() . "'s"; else echo 'Your'; ?> Plan</span>
<?php
if (isset($_user->editor)) {
  $shared = $_user->editor->getPermittedPlans();
} else {
  $shared = $_user->getPermittedPlans();
}
if ($shared) {
  echo " (others: ";
  for ($i=0; $i < sizeof($shared); $i++) {
    echo "<a href=\"" . PW_URL_INDEX . "?id=edit_plan;u={$shared[$i]}\" title=\"edit {$shared[$i]}'s plan\">{$shared[$i]}</a>";
    if ($i < sizeof($shared) - 1)
      echo ", ";
  }
  echo ")";
}
?>
<br /><br />
<?php if (isset($error)) echo $error; ?>
<?php if ($_user->isShared()) { ?>
<input type="hidden" name="shared" value="<?php echo $_user->getUsername(); ?>" />
<?php } ?>
<textarea class="inputTextArea" cols="76" rows="20" name="ptext" wrap="virtual"><?php echo htmlspecialchars($plan_edit); ?></textarea><br />
<p><strong>NOTE:</strong> To automatically link to a user's plan, put their name in exclamation points.
For example, <i>!marx!</i> will appear as <a href="<?php echo PW_URL_INDEX; ?>?id=marx" title="Finger marx">marx</a>.
You can also use !username:description! as shorthand for linking to others by nickname.</p>

<!-- archive settings -->
<table border="0" cellpadding="0" cellspacing="0">
<tr>
<td class="columnheader" width="50%">&nbsp;:: archiving&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr>
<td class="border" colspan="2">
<table border="0" width="100%" cellpadding="3" cellspacing="1">
<tr>
<td align="right" class="description">Content type:</td>
<td class="entry"><select name="type">
<option value="text"<?php if (isset($type) && $type == 'text') echo ' selected="selected"'; ?>>text</option>
<option value="html"<?php if (isset($type) && $type == 'html') echo ' selected="selected"'; ?>>html</option>
</select></td>
</tr>
<tr>
<td align="right" class="description">Archive:</td>
<td class="entry"><select name="archive">
<option value="Y"<?php if ($archive == 'Y') echo ' selected="selected"'; ?>>public</option>
<option value="P"<?php if ($archive == 'P') echo ' selected="selected"'; ?>>private</option>
<option value="N"<?php if ($archive == 'N') echo ' selected="selected"'; ?>>no</option>
</select></td>
</tr>
<tr>
<td align="right" class="description">Entry name:</td>
<td class="entry"><input type="text" name="name" maxlength="64" length="20" value="<?php if (isset($_POST['name'])) echo $_POST['name']; ?>" /></td>
</tr>
<tr>
<td align="right" colspan="2" class="entry">
<input class="inputButton" type="submit" name="preview" value="preview" />&nbsp;<input class="inputButton" type=submit name="post" value="fixplan" />&nbsp;<input class="inputButton" type="reset" value="undo" />
</td>
</tr>
</table>
</td>
</tr>
</table>
<!-- end archive settings -->
</form>
