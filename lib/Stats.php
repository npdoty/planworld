<?php
/**
 * $Id: Stats.php,v 1.4.2.2 2003/11/02 16:12:35 seth Exp $
 * Statistics utility class (Stats::)
 */

require_once($_base . 'lib/Planworld.php');
require_once($_base . 'lib/User.php');

/**
 * Statistics functions
 */
class Stats {
  /**
   * void Stats::addHit()
   * Increment the number of site-wide hits
   */
  function addHit () {
    $dbh = &Planworld::_connect();

    $dbh->query('UPDATE LOW_PRIORITY globalstats SET totalhits=totalhits + 1');
  }

  /**
   * int Stats::getNumHits ()
   * Returns the number of sitewide page views
   */
  function getNumHits () {
    $dbh = &Planworld::_connect();

    $query = "SELECT totalhits FROM globalstats";

    /* execute the query */
    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $row = $result->fetchRow();
      return (int) $row['totalhits'];
    } else {
      return PLANWORLD_ERROR;
    }
  }
  
  /**
   * int Stats::getNumUsers ()
   * Returns the number of users that this system knows about
   */
  function getNumUsers () {
    $dbh = &Planworld::_connect();


    $query = "SELECT COUNT(*) as count FROM users";
    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $row = $result->fetchRow();
      return (int) $row['count'];
    } else {
      return PLANWORLD_ERROR;
    }
  }

  /**
   * int Stats::getTotalPlanViews ()
   * Returns the number of site-wide plan views
   */
  function getTotalPlanViews () {
    $dbh = &Planworld::_connect();

    $query = "SELECT SUM(views) AS total FROM users";

    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $row = $result->fetchRow();
      return (int) $row['total'];
    } else {
      return PLANWORLD_ERROR;
    }
  }

  /**
   * int Stats::getNumViews ($user)
   * Returns the number of views that $user has had
   */
  function getNumViews (&$user) {
    $dbh = &Planworld::_connect();

    $query = "SELECT views FROM users WHERE id=" . $user->getUserID();

    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $row = $result->fetchRow();
      return (int) $row['views'];
    } else {
      return PLANWORLD_ERROR;
    }
  }

  /**
   * int Stats::getNumLoggedIn ($since)
   * Returns the number of people who have logged in (since $since)
   */
  function getNumLoggedIn ($since=null) {
    $dbh = &Planworld::_connect();

    if (isset($since)) {
      $query = "SELECT COUNT(*) as count FROM users WHERE remote='N' AND last_login > (" . (mktime() - $since) . ")";
    } else {
      $query = "SELECT COUNT(*) as count FROM users WHERE remote='N' AND last_login > 0";
    }

    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $row = $result->fetchRow();
      return (int) $row['count'];
    } else {
      return PLANWORLD_ERROR;
    }
  }

  /**
   * int Stats::getNumPlans ()
   * Returns the number of plans (update in the past $since seconds)
   */
  function getNumPlans ($since=null) {
    $dbh = &Planworld::_connect();

    if (isset($since)) {
      $query = "SELECT COUNT(*) as count FROM users WHERE remote='N' AND last_update > (" . (mktime() - $since) . ")";
    } else {
      $query = "SELECT COUNT(*) as count FROM plans";
    }

    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $row = $result->fetchRow();
      return (int) $row['count'];
    } else {
      return PLANWORLD_ERROR;
    }
  }

  /**
   * int Stats::getNumSnitchRegistered ()
   * Returns the number of snitch-registered users
   */
  function getNumSnitchRegistered () {
    $dbh = &Planworld::_connect();

    $query = "SELECT COUNT(*) as count FROM users WHERE remote='N' AND snitch='Y'";

    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $row = $result->fetchRow();
      return (int) $row['count'];
    } else {
      return PLANWORLD_ERROR;
    }
  }

  /**
   * int Stats::getNumCookies ($contrib)
   * Returns the number of cookies (user-contributed, if $contrib)
   */
  function getNumCookies ($contrib=false) {
    $dbh = &Planworld::_connect();

    if ($contrib) {
      $query = "SELECT count(*) as count FROM cookies WHERE s_uid != 0 AND approved='Y'";
    } else {
      $query = "SELECT count(*) as count FROM cookies WHERE approved='Y'";
    }

    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $row = $result->fetchRow();
      return (int) $row['count'];
    } else {
      return PLANWORLD_ERROR;
    }
  }

  /**
   * int Stats::getNumArchiveEntries ()
   * Returns the number of archive entries.
   */
  function getNumArchiveEntries () {
    $dbh = Planworld::_connect();

    $query = "SELECT COUNT(*) AS count FROM archive";
    return (int) Planworld::query($query, 'count');
  }

}

?>
