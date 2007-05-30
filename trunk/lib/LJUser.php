<?php
/* $Id: LJUser.php,v 1.1.2.3 2002/10/09 02:33:10 seth Exp $ */

/* includes */
require_once($_base . 'lib/Planworld.php');
require_once($_base . 'lib/User.php');

/**
 * Contains additional logic for remote finger-able users.
 */
class LJUser extends User {
  var $localname;
  var $plan;

  /**
   * Constructor.
   * @param uid User to initialize.
   * @public
   * @returns bool
   */
  function LJUser ($uid) {
    $this->type = 'livejournal';

    $this->username = $uid;
    
    /* check if this user exists */
    if (!$this->isUser()) {
      $this->valid = true;
      $this->userID = Planworld::addUser($this->username);
    }

    $this->lastUpdate = 0;

    list($this->localname) = split('@', $this->username);

    $this->load();
  }

  /**
   * Alternate load method.
   */
  function load () {
    $this->plan = implode('', file("http://www.livejournal.com/customview.cgi?username={$this->localname}&styleid=101"));
  }

  /**
   * Return formatted plan contents for display.
   * @param user User viewing plan.
   * @public
   * @returns Plan
   */
  function displayPlan (&$user) {
    $out = '<tt>';
    if (!$user->planwatch->inPlanwatch($this)) {
      $out .= "<a href=\"" . PW_URL_BASE . "add.php?add=" . $this->username . ";trans=t\" title=\"Add " . $this->username . " to my planwatch\">(Add to my planwatch)</a><br />\n";
    } else {
      $out .= "<a href=\"" . PW_URL_BASE . "add.php?add=" . $this->username . ";trans=t;remove=t\" title=\"Remove " . $this->username . " from my planwatch\">(Remove from my planwatch)</a><br />\n";
    }

    $plan = $this->getPlan($user);
    if (!empty($plan)) {
      $out .= "Login name: <a href=\"http://www.livejournal.com/users/{$this->localname}/\" target=\"_blank\" title=\"view {$this->localname}'s livejournal\">{$this->username}</a><br />\n";
      $out .= "Last login: ???<br />\n";
      $out .= "Last update: ???</tt><br /><br />\n";
      $out .= Planworld::addLinks($this->getPlan($user), $user->getUsername(), 'livejournal.com') . "\n";
    } else {
      $out .= "Login name: " . $this->username . "<br />\n";
      $out .= "Last login: ???<br />\n";
      $out .= "Last update: ???<br />\n";
      $out .= "Plan:<br />\n";
      $out .= "[Sorry, could not find \"" . $this->username . "\"]</tt>\n";
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
}

?>
