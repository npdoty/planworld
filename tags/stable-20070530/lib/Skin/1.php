<?php
/* $Id: 1.php,v 1.4.2.2 2003/11/02 16:12:35 seth Exp $ */

/* includes */
require_once($_base . 'lib/Planworld.php');

define('PW_SKIN_ID', 1);

/**
 * Functions for use with skin 1 (basic).
 */
class Skin {

  /**
   * string Skin::getIncludeFile ($id)
   * Returns the corresponding include for $id.
   */
  function getIncludeFile ($id) {
    if (is_object($id)) {
      return 'finger.inc';
    } else {
      switch (strtolower($id)) {
      case 'about':
	return 'about.inc';
      case 'prefs':
	return 'prefs.inc';
      case 'prefs2':
        return 'prefs2.inc';
      case 'stats':
	return 'stats.inc';
      case 'snitch':
	return 'snitch.inc';
      case 'snoop':
	return 'snoop.inc';
      case 'edit_pw':
	return 'planwatchedit.inc';
      case 'edit_plan':
	return 'edit.inc';
      case 'help':
	return 'faq.inc';
      case 'del_plan':
	return 'delete.inc';
      case 'whois':
	return 'whois.inc';
      case 'who':
	return 'who.inc';
      case 'last':
	return 'last.inc';
      case 'new':
	return 'new.inc';
      case 'preview':
	return 'preview.inc';
      case 'archiving':
	return 'archive.inc';
      case 'stuff':
	return 'stuff.inc';
	  case 'alumni':
	return 'alumni.inc';
      default:
	return 'home.inc';
      }
    }
  }

  /**
   * string Skin::getTitleString ($id)
   * Returns the corresponding title for $id.
   */
  function getTitleString ($id) {
    if (is_object($id)) {
      return "Finger " . $id->getUsername();
    } else {
      switch (strtolower($id)) {
      case 'about':
	return 'about';
      case 'prefs':
	return 'change your preferences';
      case 'prefs2':
        return 'additional preferences';
      case 'stats':
	return 'statistics';
      case 'snitch':
	return 'snitch';
      case 'snoop':
	return 'snoop';
      case 'edit_pw':
	return 'edit your planwatch';
      case 'edit_plan':
	return 'edit your plan';
      case 'help':
	return 'frequently asked questions';
      case 'del_plan':
	return 'edit your plan';
      case 'whois':
	return 'whois';
      case 'preview':
	return 'preview your plan';
      case 'archiving':
	return 'view the archives';
      case 'who':
	return 'online users';
      case 'stuff':
	return 'miscellaneous stuff';
      case 'last':
        return 'last';
      case 'new':
        return 'new';
      case 'alumni':
        return 'move to your alumni account';
      default:
	return 'home';
      }
    }
  }

  /**
   * string Skin::GetThemeDir ($tid)
   * Return the path to theme-specific stylesheets for theme $tid
   */
  function getThemeDir ($tid) {
    $dbh = Planworld::_connect();

    $query = "SELECT dir FROM themes WHERE id={$tid}";

    /* execute the query */
    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $row = $result->fetchRow();
      return $row['dir'];
    } else {
      return PLANWORLD_ERROR;
    }
  }

  /**
   * array Skin::getThemeList ()
   * Returns a list of themes available for this skin.
   */
  function getThemeList () {
    $dbh = Planworld::_connect();

    $query = "SELECT * FROM themes WHERE skin_id=" . PW_SKIN_ID . " ORDER BY name";

    /* execute the query */
    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $return = array();
      while ($row = $result->fetchRow()) {
	$return[] = array('ID' => $row['id'],
			  'Name' => $row['name']);

      }
      return $return;
    } else {
      return PLANWORLD_ERROR;
    }
  }
}

?>
