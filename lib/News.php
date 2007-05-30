<?php
/**
 * $Id: News.php,v 1.1.2.1 2003/11/02 16:12:35 seth Exp $
 * News functions.
 */

require_once($_base . 'lib/Planworld.php');

class News {

  /**
   * array News::getCurrentNewsItems ()
   * Returns an array of the current live news items.
   */
  function getCurrentNewsItems () {
    $dbh = Planworld::_connect();

    $query = "SELECT id, news, date, live FROM news WHERE live='Y' ORDER BY date DESC";

    /* execute the query */
    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $return = array();
      while ($row = $result->fetchRow()) {
	$return[] = array('id' => $row['id'],
			  'date' => $row['date'],
			  'news' => $row['news'],
			  'live' => ($row['live'] == 'Y') ? true : false);
      }
      return $return;
    } else {
      return PLANWORLD_ERROR;
    }
  }

  function getAllNews () {
    $dbh = Planworld::_connect();

    $query = "SELECT id, news, date, live FROM news ORDER BY date DESC";

    /* execute the query */
    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $return = array();
      while ($row = $result->fetchRow()) {
	$return[] = array('id' => $row['id'],
			  'date' => $row['date'],
			  'news' => $row['news'],
			  'live' => ($row['live'] == 'Y') ? true : false);
      }
      return $return;
    } else {
      return PLANWORLD_ERROR;
    }
  }

  function get ($id) {
    $dbh = Planworld::_connect();

    $query = "SELECT id, news, date, live FROM news WHERE id={$id} ORDER BY date DESC";

    /* execute the query */
    $result = $dbh->query($query);
    if (isset($result) && !DB::isError($result)) {
      $row = $result->fetchRow();
      return array('id' => $row['id'],
		      'date' => $row['date'],
		      'news' => $row['news'],
		      'live' => ($row['live'] == 'Y') ? true : false);
    } else {
      return PLANWORLD_ERROR;
    }
  }

  function add ($content, $date, $live=false) {
    $dbh = Planworld::_connect();
    $id = (int) $dbh->nextId('news');

    $query = "INSERT INTO news (id, news, date, live) VALUES ({$id}, '" . addslashes($content) . "', {$date}";

    if ($live) {
      $query .= ", 'Y')";
    } else {
      $query .= ", 'N')";
    }

    Planworld::query($query);

    return $id;
  }

  function edit ($id, $content, $date, $live=false) {
    $dbh = Planworld::_connect();

    $query = "UPDATE news SET news='" . addslashes($content) . "', date={$date}";

    if ($live) {
      $query .= ", live='Y'";
    } else {
      $query .= ", live='N'";
    }

    $query .= " WHERE id={$id}";

    Planworld::query($query);
  }

  /**
   * void News::enliven ()
   * Make news items whose ids have been passed live.
   */
  function enliven ($list) {
    $dbh = Planworld::_connect();

    Planworld::query("UPDATE news SET live='N'");

    if (empty($list)) {
      return;
    } else {
      if (is_array($list)) {
	$query = "UPDATE news SET live='Y' WHERE";
	$query .= " id=" . $list[0]; 
	for ($i=1;$i<count($list);$i++) {
	  $query .= " OR id=" . $list[$i];
	}
      } else {
	$query = "UPDATE news SET live='Y' WHERE id={$list}";
      }
      Planworld::query($query);
    }
  }

  function remove ($list) {
    if (empty($list)) {
      return;
    } else {
      $dbh = Planworld::_connect();

      if (is_array($list)) {
	$query = "DELETE FROM news WHERE";
	$query .= " id=" . $list[0]; 
	for ($i=1;$i<count($list);$i++) {
	  $query .= " OR id=" . $list[$i];
	}
      } else {
	$query = "DELETE FROM news WHERE id={$list}";
      }
      Planworld::query($query);
    }
  }

}
?>
