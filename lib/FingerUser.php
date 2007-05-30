<?php
/* $Id: FingerUser.php,v 1.4.2.1 2003/10/12 15:50:43 seth Exp $ */

/* includes */
require_once($_base . 'lib/Planworld.php');
require_once($_base . 'lib/User.php');

/**
 * Contains additional logic for remote finger-able users.
 */
class FingerUser extends User {
  var $localname;
  var $host;
  var $port = 79;
  var $plan;

  /**
   * Constructor.
   * @param uid User to initialize.
   * @public
   * @returns bool
   */
  function FingerUser ($uid) {
    $this->type = 'finger';

    $this->username = $uid;
    
    /* check if this user exists */
    if (!$this->isUser()) {
      $this->valid = true;
      $this->userID = Planworld::addUser($this->username);
    }

    $this->lastUpdate = 0;

    list($this->localname, $this->host) = split('@', $this->username);

    $this->load();
  }

  /**
   * Alternate load method using finger.
   */
  function load () {
    $this->plan = $this->finger();
  }

  /**
   * Finger gateway.
   * @param $host Host to query.
   * @param $user Username to query (optional).
   * @param $port Port number to use (optional).
   * @returns Complete contents of plan.
   */
  function finger () {
    $rc = fsockopen($this->host, $this->port, $errno, $errstr, 2);
    if (!$rc)
      return false;
    fputs($rc, $this->localname . "\r\n");
    socket_set_timeout($rc, 4);
    $str = fread($rc, 1000000);
    fclose($rc);
    if ($str)
      return $str;
    else
      return false;
  }

    /**
     * Return formatted plan contents for display.
     * @param user User viewing plan.
     * @public
     * @returns Plan
     */
    function displayPlan ($user) {
      $out = '<pre>';
      if (!$user->planwatch->inPlanwatch($this)) {
	$out .= "<a href=\"" . PW_URL_BASE . "add.php?add=" . $this->username . ";trans=t\" title=\"Add " . $this->username . " to my planwatch\">(Add to my planwatch)</a>\n";
      } else {
	$out .= "<a href=\"" . PW_URL_BASE . "add.php?add=" . $this->username . ";trans=t;remove=t\" title=\"Remove " . $this->username . " from my planwatch\">(Remove from my planwatch)</a>\n";
      }

      $plan = $this->getPlan($user);
      if (!empty($plan))
	$out .= Planworld::addLinks($this->getPlan($user), $user->getUsername(), $this->host) . "</pre>\n";
      else {
	$out .= "Login name: " . $this->username . "\n";
	$out .= "Last login: ???\n";
	$out .= "Last update: ???\n";
	$out .= "Plan:\n";
	$out .= "[Sorry, could not find \"" . $this->username . "\"]</pre>\n";
      }

      return $out;
    }

  /**
   * Returns this user's plan.
   * @public
   * @param user Requesting user (object)
   * @returns string
   */
  function getPlan (&$user) {
    return $this->plan;
  }

  /**
   * Fetches the hostname that this user exists on.
   * @public
   * @returns string
   */
  function getHost () {
    return $this->host;
  }
}

?>
