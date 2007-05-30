<?php
if (isset($_POST) && !empty($_POST)) {
  $_user->setPreference('send_forward', addslashes($_POST['send_forward']));
}
?>

Hello world.


<form method="post">

<div>
Forward sends to: <input type="text" name="send_forward" value="<?php echo $_user->getPreference('send_forward'); ?>" /><br />
Explanation: copies and forwards messages on to a user of your choice.
</div>

<input type="submit" value="apply" />

</form>
