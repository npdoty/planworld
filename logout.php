<?php
/**
 * $Id: logout.php,v 1.11.4.2 2003/03/17 15:44:45 seth Exp $
 * Logout page.
 */

/* includes */
require_once($_base . 'lib/Online.php');

/* destroy the current session */
session_destroy();

/* remove this user from the list of those online */
Online::removeUser($_user);

/* redirect this user's browser to an appropriate page */
header("Location: " . PW_LOGOUT_URL . "\n");
exit();

?>
