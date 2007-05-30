<?php
/* $Id: RemoteUser.php,v 1.6.2.4 2003/11/02 16:29:03 seth Exp $ */

/* includes */
require_once($_base . 'lib/Planworld.php');
require_once($_base . 'lib/User.php');
/** TEMPORARY */
require_once($_base . 'backend/epi-utils.php');

/**
 * Contains additional logic for remote planworld users.
 */
class RemoteUser extends User {
  var $localname;
  var $host;
  var $nodeinfo;

  /**
   * Constructor.
   * @param uid User to initialize.
   * @public
   * @returns bool
   */
  function RemoteUser ($uid, $nodeinfo=null) {
    /* establish a database connection */
    $this->dbh = &Planworld::_connect();

    $this->type = 'planworld';

    $this->username = $uid;

    list($this->localname, $this->host) = split('@', $this->username);

    if (isset($nodeinfo)) {
      $this->nodeinfo = $nodeinfo;
    } else {
      $this->nodeinfo = Planworld::getNodeInfo($host);
    }
    
    /* check if this user exists */
    if (!$this->isUser()) {
      $this->valid = true;
      $this->userID = Planworld::addUser($this->username);
    }
    $this->load();
  }

  /**
   * Calls a remote method via xml-rpc.
   * @param method Method to call.
   * @param params Parameters to use.
   * @private
   */
  function _call ($method, $params=null) {
    return xu_rpc_http_concise(array('method' => $method,
				     'args'   => $params, 
				     'host'   => $this->nodeinfo['Hostname'], 
				     'uri'    => $this->nodeinfo['Path'], 
				     'port'   => $this->nodeinfo['Port'], 
				     'debug'  => 0)); // 0=none, 1=some, 2=more
  }

  /**
   * Forces fetching of remote login / update times.
   * @public
   */
  function forceUpdate () {
    $this->forceUpdateLastLogin();
    $this->forceUpdateLastUpdate();
    $this->save();
  }

  /**
   * Forced fetching of last login.
   * @private
   */
  function forceUpdateLastLogin () {
    if ($this->nodeinfo['Version'] < 2) {
      $val = $this->_call('users.getLastLogin', $this->localname);
    } else {
      $val = $this->_call('planworld.user.getLastLogin', $this->localname);
    }
    if (is_int($val)) {
      $this->setLastLogin($val);
    } else {
      /* some sort of error */
    }
  }

  /**
   * Forced fetching of last update.
   * @private
   */
  function forceUpdateLastUpdate () {
    if ($this->nodeinfo['Version'] < 2) {
      $val = $this->_call('users.getLastUpdate', $this->localname);
    } else {
      $val = $this->_call('planworld.user.getLastUpdate', $this->localname);
    }
    if (is_int($val)) {
      $this->setLastUpdate($val);
    } else {
      /* some sort of error */
    }
  }

    /**
     * Return formatted plan contents for display.
     * @param user User viewing plan.
     * @public
     * @returns Plan
     */
    function displayPlan ($user, $plan=null, $ts=null) {
      $plan_txt = $this->getPlan($user, $ts);      
      
      $out = '';
      if (!$user->planwatch->inPlanwatch($this)) {
	$out .= "<tt><a href=\"" . PW_URL_BASE . "add.php?add=" . $this->username . ";trans=t\" title=\"Add " . $this->username . " to my planwatch\">(Add to my planwatch)</a></tt><br />\n";
      } else {
	$out .= "<tt><a href=\"" . PW_URL_BASE . "add.php?add=" . $this->username . ";trans=t;remove=t\" title=\"Remove " . $this->username . " from my planwatch\">(Remove from my planwatch)</a></tt><br />\n";
      }

      $out .= "<tt>Login name: {$this->username}";

      /* user doesn't exist */
      if (!$this->isUser() || ($this->lastLogin == 0 && $this->lastUpdate == 0)) {
	$out .= "<br />\n";
	$out .= "Last login: ???<br />\n";
	$out .= "Last update: ???<br />\n";
	$out .= "Plan:<br />\n";
	$out .= "[Sorry, could not find \"{$this->username}\"]</tt>\n";
      } else if ($this->lastUpdate == 0) {
	$out .= " (<a href=\"#\" onclick=\"return send('" . $this->username . "');\" title=\"send to " . $this->username . "\">send</a>)<br />\n";
	$out .= "Last login: " . Planworld::getDisplayDate($this->lastLogin) . "<br />\n";
	$out .= "Last update: Never<br />\n";
	$out .= "Plan:<br />\n";
	$out .= "[No Plan]</tt>\n";
      } else {
	$out .= " (<a href=\"#\" onclick=\"return send('" . $this->username . "');\" title=\"send to " . $this->username . "\">send</a>)<br />\n";
	$out .= "Last login: " . Planworld::getDisplayDate($this->lastLogin) . "<br />\n";
	$out .= "Last updated: " . Planworld::getDisplayDate($this->lastUpdate) . "<br />\n";
	if (isset($ts)) {
	  $out .= "Date posted: " . Planworld::getDisplayDate($ts) . "<br />\n";
	}
	$out .= "Plan:</tt>\n";

        if (empty($plan_txt))
          $plan_txt = "<tt><br />\n[No plan]</tt>";

	/* only wordwrap if a text plan */
	if (Planworld::isText($plan_txt)) {
	  $out .= Planworld::addLinks(wordwrap($plan_txt, 76, "\n", 1), $user->getUsername(), $this->host);
	} else {
	  $out .= Planworld::addLinks($plan_txt, $user->getUsername(), $this->host);
	}
      }
      return $out;
    }

  /**
   * Fetches this user's plan.
   * @public
   * @param user Requesting user (object)
   * @param date Date (for archived entries)
   * @returns string
   */
  function getPlan (&$user, $ts=null) {
    if (!isset($ts)) {
      if ($this->nodeinfo['Version'] < 2) {
        $val = $this->_call('plan.getText', array($this->localname, $user->getUsername() . '@' . PW_NAME, $user->getSnitch()));
      } else {
        $val = $this->_call('planworld.user.getPlan', array($this->localname, $user->getUsername() . '@' . PW_NAME, $user->getSnitch()));
      }
      return $val->scalar;
    } else {
      return Archive::getEntry($this->userID, $ts);
    }
    /*
    if (!is_array($val)) {
      print_r($val);
      return $val;
    } else {
      // some sort of error
    }
    */
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
