<?php
include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");

header('Content-type: text/css');

$body_fg           = '#545454';/* do not change */

$body_bg           = '#E0E0E0';
$header_bg         = '#AFC846';
$header_fg         = '#73984A';
$title_fg          = '#012340';
$left_bg           = '#CBD171';
$menu_fg           = '#E0E0E0';
$menu_border       = '#E0E0E0';

$menu_focus_on_fg  = $menu_fg;
$menu_focus_off_fg = $menu_fg;

$menu_bg           = $header_fg;
$menu_focus_on_bg  = $header_bg;
$menu_focus_off_bg = $header_fg;

$left_fg = $body_fg;

$center_bg = '#fff';
$center_fg = $body_fg;

$pagecontainer_bg = $center_bg;
$pagecontainer_fg = $center_fg;

$maincontentcontainer_bg = $left_bg; /* faux column attempt */
$maincontentcontainer_fg = $center_fg;

$footer_bg     = $menu_bg;
$footer_fg     = $menu_fg;
$footer_border = $menu_border;

$gallery_pagination_bg = '#545454';
$gallery_pagination_fg = '#E0E0E0';
$gallery_thumbs_bg     = $gallery_pagination_bg;

$table_th_fg          = $title_fg;
$table_td_fg          = '#545454';
$table_border         = '#ccc';
$table_row_even       = '#f2f2f2';
$table_row_odd        = '#fff';
$table_row_even_hover = '#ddd';
$table_row_odd_hover  = '#ddd';

include(DIR_CSS . "/themes/theme.php");
?>