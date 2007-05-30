<?php
/**
 * $Id: delete.inc,v 1.6 2002/02/07 05:19:16 seth Exp $
 * Delete page.
 */

?>
<span class="error">You are about to clear your plan.  Are you <strong>absolutely</strong> sure you want to?</span>
<form action="<?php echo PW_URL_INDEX; ?>" method="post">
<input type="hidden" name="delete" value="<?php echo $_user->getUsername(); ?>" />
<input type="hidden" name="id" value="<?php echo $_user->getUsername(); ?>" />
<input class="inputButton" type="submit" name="clear" value="Yes" />
<input class="inputButton" type="submit" value="No!" />
</form>
