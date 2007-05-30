<?php

/**
 * $Id: export.php,v 1.2.2.1 2003/11/24 14:54:06 seth Exp $
 * Archive exporting.
 */

/* includes */
require_once($_base . 'lib/Archive.php');
require_once($_base . 'lib/Skin/' . $_user->getSkin() . '.php');

if (isset($_GET['t']) && $_GET['t'] == 'html') {
  $type = 'html';
} else {
  $type = 'text';
}

if ($type == 'text') {
  header('Content-Type: text/plain');
} else if ($type == 'html') {
  echo "<html>\n<head>\n<title>planworld :: " . $_user->getUsername() . "</title>\n";
  require($_base . 'layout/' . $_user->getSkin() . '/themes/' . Skin::getThemeDir($_user->getTheme()) . '/styles.css');
  echo "</head>\n<body>\n";
  echo "<span class=\"content\">\n";
}

$entries = Archive::listEntries($_user, false, (isset($_GET['s']) ? $_GET['s'] : null), (isset($_GET['e']) ? $_GET['e'] : null));

foreach ($entries as $entry) {
  if (!empty($entry[1])) {
    $name = &$entry[1];
  } else {
    $name = "Unnamed Entry";
  }

  $text = Archive::getEntry($_user->getUserID(), $entry[0]);
  if ($type == 'text') {
    echo "---[ {$name} (" . date('n-j-y, g:i a', $entry[0]) . ") ]---\n";
    if (Planworld::isText($text)) {
      echo wordwrap(Planworld::unwrap($text), 76, "\n", 1) . "\n\n";
    } else {
      echo $text . "\n\n";
    }
  } else if ($type == 'html') {
    echo "<h3>{$name} (" . date('n-j-y, g:i a', $entry[0]) . ")</h3>\n";
    if (Planworld::isText($text)) {
      echo wordwrap($text, 76, "\n", 1) . "<hr />\n\n";
    } else {
      echo $text . "<hr />\n\n";
    }
  }
}

if ($type == 'html') {
  echo "</span>\n";
  echo "</body>\n</html>";
}

?>
