<?php
$str = <<<CSS
/******************* Colours ********************/ 
body {
        background:             $body_bg;
        color:                  $body_fg;
}

h1,h2,h3,h4,h5,h6 {
        color:                  $title_fg;
}

table h1, table h2, table h3, table h4, table h5, table h6 {
        color:                  $title_fg2;
}

.header-container {
        background:             $header_bg;
        color:                  $header_fg;
}

.menu-container {
        background:             $menu_bg;
        color:                  $menu_fg;
        border-color:           $menu_border;
}

.footer-container {
        background:             $footer_bg; 
        color:                  $footer_fg;
        border-color:           $footer_border;
}

.footer-container a {
        color:                  $footer_fg;
}

.page-container {
        background:             $pagecontainer_bg;
        color:                  $pagecontainer_fg;
}

.main-content-container {
        background:             $maincontentcontainer_bg; /* should match background colour of left column */
        color:                  $maincontentcontainer_fg;
        border-left:            1px solid $header_bg;
        border-right:           1px solid $header_bg;
}

.main-content-container a {
        color:                  $menu_fg;
}

.sidebar {
        background:             $left_bg;
        color:                  $left_fg;
}

.center {
        background:             $center_bg;
        color:                  $center_fg;
}

.center img {
        border-color:           $header_bg;
        box-shadow:             0 0 6px $header_bg;
}

.box-border-color {
        border-color:           $header_bg;
}

.box-background-color {
        background:             $left_bg;
}

select {
        color:                  $body_fg;
}

table {
        border:                 none;/*1px solid $table_border;*/
}

table th {
        color:                  $table_th_fg;
        border:                 none;/*1px solid $table_border;*/
}

table td {
        color:                  $table_td_fg;
        border:                 none;/*1px solid $table_border;*/
}

table td a {
        color:                  $menu_fg; /*$table_td_fg;*/
}

table tr.even {
        background:             $table_row_even;
}

table tr.odd {
        background:             $table_row_odd;
}

table tr.even_hover {
        background:             $menu_focus_on_bg; /*$table_row_even_hover;*/
}

table tr.even_hover td, table tr.even_hover td a {
        color:                  $menu_focus_on_fg;
}

table tr.even_hover td h3 {
        color:                  $header_fg;
}

table tr.odd_hover {
        background:             $menu_focus_on_bg; /*$table_row_odd_hover;*/
}

table tr.odd_hover td, table tr.odd_hover td a {
        color:                  $menu_focus_on_fg;
}

table tr.odd_hover td h3 {
        color:                  $header_fg;
}

.downloads-page-container table, .links-page-container table, .newsletters-container table {
        border:                 1px solid $table_border;
}

.downloads-page-container table th, .links-page-container table th, .newsletters-container table th {
        border:                 1px solid $table_border;
}

.downloads-page-container table td, .links-page-container table td, .newsletters-container table td {
        border:                 1px solid $table_border;
}

.article-title {
        color:                  $title_fg;
}

.article-sub-title {
        color:                  $title_fg;
}

.article-sub-title a {
        color:                  $title_fg;
}

.article-sub-title2 {
        color:                  $title_fg;
}

.sf-menu a, .sf-menu a:visited, .sf-menu a:link  { /* visited pseudo selector so IE6 applies text colour*/
        color:			$menu_fg;
}

.sf-menu a {
        border-color:           $menu_border;
}

.sf-menu li {
        background:		$menu_bg;
}

.sf-menu li li {
        background:		$menu_bg;
}

.sf-menu li li li {
        background:		$menu_bg;
}

.sf-menu li:hover, .sf-menu li.sfHover,
.sf-menu a:focus, .sf-menu a:hover, .sf-menu a:active {
        background:		$menu_focus_off_bg; /* colour when focus shifts off a menu */
        color:                  $menu_focus_off_fg;
}

.sf-menu a:focus, .sf-menu a:hover, .sf-menu a:active {
        background:		$menu_focus_on_bg; /* colour when focus on a submenu */
        color:                  $menu_focus_on_fg;
}

ul.thumbs li.selected a.thumb {
        background:             $gallery_thumbs_bg;
}

div.pagination span.current {
        background:             $gallery_pagination_bg;
        border-color:           $gallery_thumbs_bg;
        color:                  $gallery_pagination_fg;
}
CSS;
echo($str);
?>
