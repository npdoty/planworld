<?php
/**
 * $Id: stuff.inc,v 1.1 2002/09/10 16:19:41 seth Exp $
 * Extra stuff.
 */

if (isset($_GET['s'])) {
  switch($_GET['s']) {
  case 'cookie':
    if (isset($_GET['a'])) {
      switch($_GET['a']) {
      case 'add':
	require($_base . "layout/{$skin['id']}/cookie_add.tpl");
	break;
      case 'approve':
	require($_base . "layout/{$skin['id']}/cookie_approve.tpl");
	break;
      case 'edit':
	require($_base . "layout/{$skin['id']}/cookie_edit.tpl");
	break;
      default:
	require($_base . "layout/{$skin['id']}/cookie_add.tpl");
	break;
      }
      break;
    } else {
      require($_base . "layout/{$skin['id']}/cookie_add.tpl");
      break;
    }
  case 'admin':
    require($_base . "layout/{$skin['id']}/admin.tpl");
    break;
  case 'news':
    if (isset($_GET['a'])) {
      switch($_GET['a']) {
      case 'list':
	require($_base . "layout/{$skin['id']}/news_list.tpl");
	break;
      case 'edit':
	require($_base . "layout/{$skin['id']}/news_edit.tpl");
	break;
      case 'add':
	require($_base . "layout/{$skin['id']}/news_add.tpl");
	break;
      default:
	require($_base . "layout/{$skin['id']}/news_list.tpl");
	break;
      }
      break;
    } else {
      require($_base . "layout/{$skin['id']}/news_list.tpl");
      break;
    }
  default:
    require($_base . "layout/{$skin['id']}/cookie_add.tpl");
    break;
  }
}

?>