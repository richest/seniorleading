<?php

function thesis_classic_r_defaults() {
	return array (
  'css' => '/*---:[ layout structure ]:---*/
body {
	font-family: $font;
	font-size: $f_text;
	line-height: $h_text;
	color: $text1;
	background-color: $color3;
	padding-top: $x_single;
}
body.landing {
	padding-top: 0;
}
.container {
	width: $w_total;
	margin: 0 auto;
}
.landing .container {
	width: $w_content;
}
.columns, .columns > .content, .columns > .sidebar {
	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
}
.columns > .content {
	width: $w_content;
	$column1
	border-style: solid;
	border-color: $color1;
}
.columns > .sidebar {
	$column2
	padding: $x_single $x_single 0 $x_single;
}
/*---:[ links ]:---*/
a {
	color: $links;
	text-decoration: none;
}
p a {
	text-decoration: underline;
}
p a:hover {
	text-decoration: none;
}
/*---:[ nav menu ]:---*/
.menu {
	position: relative;
	z-index: 50;
	list-style: none;
	border-width: 0 0 1px 1px;
	border-style: solid;
}
.menu li {
	position: relative;
	float: left;
	margin-bottom: -1px;
}
.menu .sub-menu {
	position: absolute;
	left: -1px;
	display: none;
	list-style: none;
	z-index: 110;
	margin-top: -1px;
}
.menu .sub-menu .sub-menu {
	top: 0;
	left: $submenu;
	margin: 0 0 0 -1px;
}
.menu li:hover > .sub-menu {
	display: block;
}
.menu .sub-menu li {
	width: $submenu;
	clear: both;
}
.menu a, .menu_control {
	display: block;
	$menu
	line-height: 1em;
	text-transform: uppercase;
	letter-spacing: 1px;
	color: $text1;
	border-width: 1px 1px 1px 0;
	border-style: solid;
	background-color: $color2;
	padding: 0.75em 1em;
}
.menu a:hover {
	background-color: $color1;
}
.menu_control {
	display: none;
	background-color: $color3;
}
.menu .sub-menu a {
	border-left-width: 1px;
}
.menu, .menu a, .menu .sub-menu {
	border-color: $color1;
}
.menu .current-menu-item > a {
	border-bottom-color: $color3;
	background-color: $color3;
	cursor: text;
}
.menu .sub-menu .current-menu-item > a {
	border-bottom-color: $color1;
}
/*---:[ header ]:---*/
.header {
	border-bottom: 3px double $color1;
	padding: $x_single;
}
.landing .header {
	text-align: center;
}
#site_title {
	$title
	line-height: 1.32em;
	font-weight: bold;
	color: $title_color;
}
#site_title a {
	color: $title_color;
}
#site_title a:hover {
	color: $links;
}
#site_tagline {
	$tagline
	line-height: 1.32em;
}
/*---:[ golden ratio typography with spaced paragraphs ]:---*/
.grt, .grt h3 {
	font-size: $f_text;
	line-height: $h_text;
}
.grt .headline {
	$headline
	margin: 0;
}
.grt h2 {
	$subhead
	margin-top: $x_3over2;
	margin-bottom: $x_half;
}
.grt .small, .grt .caption {
	font-size: $f_aux;
	line-height: $h_aux;
}
.grt .drop_cap {
	font-size: $x_double;
	line-height: 1em;
	margin-right: 0.15em;
	float: left;
}
.grt p, .grt ul, .grt ol, .grt blockquote, .grt pre, .grt dl, .grt dd, .grt .center, .grt .block, .grt .caption, .post_box .aligncenter, .post_box .alignnone, .post_box .post_image, .post_box .post_image_box, .post_box .wp-caption, .post_box .wp-post-image, .post_box .alert, .post_box .note, .headline_area {
	margin-bottom: $x_single;
}
.grt ul, .grt ol, .grt .stack {
	margin-left: $x_single;
}
.grt ul ul, .grt ul ol, .grt ol ul, .grt ol ol, .wp-caption p, .post_box .alert p:last-child, .post_box .note p:last-child, .post_content blockquote.right p, .post_content blockquote.left p {
	margin-bottom: 0;
}
.grt .left, .post_box .alignleft, .post_box .ad_left {
	margin-bottom: $x_single;
	margin-right: $x_single;
}
.grt .right, .post_box .alignright, .post_box .ad {
	margin-bottom: $x_single;
	margin-left: $x_single;
}
.grt .caption {
	margin-top: -$x_half;
	color: $text2;
}
/*---:[ golden ratio pullquotes ]:---*/
.grt blockquote.right, .grt blockquote.left {
	$pullquote
	width: 45%;
	margin-bottom: $x_half;
}
.grt blockquote.right, .grt blockquote.left { 
	padding-left: 0;
	border: 0;
}
/*---:[ post box styles ]:---*/
.post_box {
	padding: $x_single $x_single 0 $x_single;
	border-top: 1px dotted $color1;
}
.top {
	border-top: 0;
}
.post_box .headline, .headline a {
	color: $headline_color;
}
.headline a:hover {
	color: $links;
}
.byline, .byline a {
	color: $text2;
}
.byline a {
	border-bottom: 1px solid $color1;
}
.byline a, .post_author, .post_date {
	text-transform: uppercase;
	letter-spacing: 1px;
}
.byline a:hover, .num_comments {
	color: $text1;
}
.byline .post_edit {
	margin-left: $x_half;
}
.byline .post_edit:first-child {
	margin-left: 0;
}
.post_author_intro, .post_date_intro, .byline .post_cats_intro {
	font-style: italic;
}
.post_box h2, .post_box h3 {
	color: $subhead_color;
}
.post_box h3 {
	font-weight: bold;
}
.post_box ul {
	list-style-type: square;
}
.post_box blockquote {
	$blockquote
	margin-left: $x_half;
	padding-left: $x_half;
	border-left: 1px solid $color1;
}
.post_box code {
	$code
}
.post_box pre {
	$pre
	background-color: $color2;
	padding: $x_half;
	-webkit-tab-size: 4;
	-moz-tab-size: 4;
	tab-size: 4;
}
.post_content li a {
	text-decoration: underline;
}
.post_content li a:hover {
	text-decoration: none;
}
.post_box .frame, .post_box .post_image_box, .post_box .wp-caption {
	border: 1px solid $color1;
	background-color: $color2;
	padding: $x_half;
}
.post_box .wp-caption img, .post_box .post_image_box .post_image, .post_box .thumb {
	margin-bottom: $x_half;
}
.wp-caption.aligncenter img {
	margin-right: auto;
	margin-left: auto;
}
.wp-caption .wp-caption-text .wp-smiley {
	display: inline;
	margin-bottom: 0;
}
.post_box .wp-caption p {
	font-size: $f_aux;
	line-height: $h_aux;
}
.post_box .author_description {
	border-top: 1px dotted $color1;
	padding-top: $x_single;
}
.post_box .author_description_intro {
	font-weight: bold;
}
.post_box .avatar {
	$avatar
	float: right;
	clear: both;
	margin-left: $x_half;
}
.post_box .author_description .avatar {
	$bio_avatar
	float: left;
	margin-right: $x_half;
	margin-left: 0;
}
.post_box .post_cats, .post_box .post_tags {
	color: $text2;
}
.post_box .alert, .post_box .note {
	padding: $x_half;
}
.post_box .alert {
	background-color: #ff9;
	border: 1px solid #e6e68a;
}
.post_box .note {
	background-color: $color2;
	border: 1px solid $color1;
}
.landing .headline_area {
	text-align: center;
}
/*---:[ other post box styles ]:---*/
.num_comments_link {
	display: inline-block;
	color: $text2;
	text-decoration: none;
	margin-bottom: $x_single;
}
.num_comments_link:hover {
	text-decoration: underline;
}
.bracket, .num_comments {
	font-size: $x_single;
}
.bracket {
	color: $color1;
}
/*---:[ misc. content elements ]:---*/
.archive_intro {
	border-width: 0 0 1px 0;
	border-style: solid;
	border-color: $color1;
}
.archive_intro .headline {
	margin-bottom: $x_single;
}
.prev_next {
	clear: both;
	color: $text2;
	border-top: 1px solid $color1;
	padding: $x_half $x_single;
}
.prev_next .next_posts {
	float: right;
}
.previous_posts, .next_posts {
	display: block;
	font-size: $f_aux;
	line-height: $h_aux;
	text-transform: uppercase;
	letter-spacing: 2px;
}
.previous_posts a:hover, .next_posts a:hover {
	text-decoration: underline;
}
/*---:[ comments ]:---*/
#comments {
	margin-top: $x_double;
}
.comments_intro {
	color: $text2;
	margin-bottom: $x_half;
	padding: 0 $x_single;
}
.comments_closed {
	font-size: $f_aux;
	line-height: $h_aux;
	color: $text2;
	margin: 0 $x_single $x_single $x_single;
}
.comment_list {
	list-style-type: none;
	margin-bottom: $x_double;
	border-top: 1px dotted $color1;
}
.comment {
	border-bottom: 1px dotted $color1;
	padding: $x_single;
}
.children .comment {
	list-style-type: none;
	margin-top: $x_single;
	border-left: 1px solid $color1;
	border-bottom: 0;
	padding: 0 0 0 $x_single;
}
.children .bypostauthor {
	background-color: transparent;
	border-color: $links;
}
.comment .comment_head {
	margin-bottom: $x_half;
}
.children .comment_head {
	margin-bottom: 0;
}
.comment .comment_author {
	font-weight: bold;
}
.comment_date {
	font-size: $f_aux;
	margin-left: $x_half;
	color: $text2;
}
.comment_date a {
	color: $text2;
}
.comment_footer a {
	font-size: $f_aux;
	line-height: $h_aux;
	color: $text2;
	text-transform: uppercase;
	letter-spacing: 1px;
	margin-left: $x_half;
}
.comment_head a:hover, .comment_footer a:hover, .comment_nav a:hover {
	text-decoration: underline;
}
.comment_footer a:first-child {
	margin-left: 0;
}
.comment .avatar {
	$comment_avatar
	float: right;
	margin-left: $x_half;
}
.comment_nav {
	font-size: $f_aux;
	line-height: $h_aux;
	text-transform: uppercase;
	letter-spacing: 1px;
	border-style: dotted;
	border-color: $color1;
	padding: $x_half $x_single;
}
.comment_nav_top {
	border-width: 1px 0 0 0;
}
.comment_nav_bottom {
	margin: -$x_double 0 $x_double 0;
	border-width: 0 0 1px 0;
}
.next_comments {
	float: right;
}
/*---:[ inputs ]:---*/
.input_text {
	font-size: inherit;
	line-height: 1em;
	font-family: inherit;
	font-weight: inherit;
	color: $text1;
	border: 1px solid $color1;
	background-color: $color2;
	padding: 0.35em;
	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
}
.input_text:focus {
	border-color: $color2;
	background-color: $color3;
}
textarea.input_text {
	line-height: $h_text;
}
.input_submit {
	font-size: $f_subhead;
	line-height: 1em;
	font-family: inherit;
	font-weight: bold;
	border: 3px double $color1;
	background: $color2 url(\\\'images/bg-button.png\\\') repeat-x;
	padding: 0.5em;
	cursor: pointer;
	overflow: visible;
}
/*---:[ comment form ]:---*/
#commentform {
	margin: $x_double 0;
	padding: 0 $x_single;
}
.comment #commentform {
	margin-top: 0;
	padding-right: 0;
	padding-left: 0;
}
.comment_form_title {
	$subhead
	color: $subhead_color;
	margin: 0 -$x_single;
	border-bottom: 1px dotted $color1;
	padding: 0 $x_single $x_half $x_single;
}
#commentform label {
	display: block;
}
#commentform p {
	margin-bottom: $x_half;
}
#commentform p .required {
	color: #d00;
}
.comment_moderated {
	font-weight: bold;
}
#commentform .input_text {
	width: 50%;
}
#commentform textarea.input_text {
	width: 100%;
}
#cancel-comment-reply-link {
	float: right;
	font-size: $f_aux;
	line-height: inherit;
	text-transform: uppercase;
	letter-spacing: 1px;
	color: $links;
}
#cancel-comment-reply-link:hover {
	text-decoration: underline;
}
.login_alert {
	font-weight: bold;
	border: 1px solid $color1;
	background-color: $color2;
}
/*---:[ sidebar ]:---*/
.sidebar {
	$sidebar
}
.sidebar .headline, .sidebar .sidebar_heading, .sidebar .widget_title {
	$sidebar_heading
}
.sidebar .sidebar_heading, .sidebar .widget_title {
	font-variant: small-caps;
	letter-spacing: 1px;
	margin-bottom: $s_x_half;
}
.sidebar .input_submit {
	font-size: inherit;
}
.sidebar p, .sidebar ul, .sidebar ol, .sidebar blockquote, .sidebar pre, .sidebar dl, .sidebar dd, .sidebar .left, .sidebar .alignleft, .sidebar .ad_left, .sidebar .right, .sidebar .alignright, .sidebar .ad, .sidebar .center, .sidebar .aligncenter, .sidebar .block, .sidebar .alignnone {
	margin-bottom: $s_x_single;
}
.sidebar .left, .sidebar .alignleft, .sidebar .ad_left {
	margin-right: $s_x_single;
}
.sidebar ul ul, .sidebar ul ol, .sidebar ol ul, .sidebar ol ol, .sidebar .right, .sidebar .alignright, .sidebar .ad, .sidebar .stack {
	margin-left: $s_x_single;
}
.sidebar ul ul, .sidebar ul ol, .sidebar ol ul, .sidebar ol ol, .wp-caption p, .sidebar .post_excerpt p {
	margin-bottom: 0;
}
.widget, .sidebar .text_box, .sidebar .thesis_email_form, .sidebar .query_box {
	margin-bottom: $s_x_double;
}
.sidebar .thesis_email_form .input_text, .widget li {
	margin-bottom: $s_x_half;
}
.sidebar .search-form .input_text, .sidebar .thesis_email_form .input_text {
	width: 100%;
}
.sidebar .query_box .post_author, .sidebar .query_box .post_date {
	color: $text2;
}
.sidebar .post_content, .widget li ul, .widget li ol {
	margin-top: $s_x_half;
}
.widget ul {
	list-style-type: none;
}
.widget li a:hover {
	text-decoration: underline;
}
/*---:[ footer ]:---*/
.footer {
	font-size: $f_aux;
	line-height: $h_aux;
	text-align: right;
	color: $text2;
	border-top: 3px double $color1;
	padding: $x_half $x_single;
}
.footer a {
	color: $text2;
}
.footer a:hover {
	color: $text1;
}
.landing .footer {
	text-align: center;
}
/*---:[ media queries ]:---*/
@media all and (max-width: $w_total) {
	body {
		padding-top: 0;
	}
	.container, .landing .container {
		width: auto;
		max-width: $w_content;
	}
	.header {
		border-top: 1px solid $color1;
	}
	.landing .header {
		border-top: 0;
	}
	.columns > .content {
		float: none;
		width: 100%;
		border: 0;
	}
	.columns > .sidebar {
		float: none;
		width: 100%;
		border-top: 3px double $color1;
	}
	.menu_control {
		display: block;
		width: 100%;
		border-width: 0;
		background-color: $color3;
		padding: 1em $x_single;
		cursor: pointer;
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
	}
	.menu {
		display: none;
		width: 100%;
		border-width: 1px 0 0 0;
		clear: both;
	}
	.show_menu {
		display: block;
	}
	.menu .sub-menu {
		position: static;
		display: block;
		margin: 0;
		border-top: 1px solid $color1;
		padding-left: $x_single;
	}
	.menu li {
		width: 100%;
		float: none;
		margin-bottom: 0;
	}
	.menu .sub-menu li {
		width: 100%;
	}
	.menu a {
		border-width: 1px 1px 0 0;
		background-color: $color3;
		padding: 1em $x_single;
	}
	.menu .current-menu-item > a {
		background-color: $color2;
	}
	.menu > li > a {
		border-left-width: 1px;
	}
	.menu li:first-child > a:first-child {
		border-top-width: 0;
	}
	.sidebar .search-form .input_text, .sidebar .thesis_email_form .input_text {
		width: 50%;
	}
}
@media all and (max-width: $w_content) {
	.menu a {
		border-right-width: 0;
	}
	.menu > li > a {
		border-left-width: 0;
	}
	.post_box .frame, .post_box .post_image_box, .post_box .wp-caption {
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
	}
}
@media all and (max-width: 450px) {
	.menu a, .menu_control {
		padding: 1em $x_half;
	}
	.header, .columns > .sidebar, .post_box, .prev_next, .comments_intro, .comment, .comment_nav, #commentform, .comment_form_title, .footer {
		padding-right: $x_half;
		padding-left: $x_half;
	}
	.menu .sub-menu, .children .comment {
		padding-left: $x_half;
	}
	.comments_closed, .login_alert {
		margin-right: $x_half;
		margin-left: $x_half;
	}
	.comment_form_title {
		margin-left: -$x_half;
		margin-right: -$x_half;
	}
	.right, .alignright, img[align=\\"right\\"], .left, .alignleft, img[align=\\"left\\"] {
		float: none;
	}
	.grt .right, .grt .left, .post_box .alignright, .post_box .alignleft, .grt blockquote.right, .grt blockquote.left {
		margin-right: 0;
		margin-left: 0;
	}
	.post_author:after {
		content: \\\'\\\\a\\\';
		height: 0;
		white-space: pre;
		display: block;
	}
	.grt blockquote.right, .grt blockquote.left, #commentform .input_text, .sidebar .search-form .input_text, .sidebar .thesis_email_form .input_text {
		width: 100%;
	}
	.post_box blockquote {
		margin-left: 0;
	}
	.comment_date {
		display: none;
	}
}
/*---:[ clearfix ]:---*/
.columns:after, .menu:after, .post_box:after, .post_content:after, .author_description:after, .sidebar:after, .query_box:after, .prev_next:after, .comment_text:after, .comment_nav:after {
	$z_clearfix
}',
  'boxes' => 
  array (
    'thesis_html_container' => 
    array (
      'thesis_html_container_1348009564' => 
      array (
        'id' => 'header',
        'class' => 'header',
        '_id' => 'header',
        '_name' => 'Header',
      ),
      'thesis_html_container_1348009571' => 
      array (
        'class' => 'columns',
        '_admin' => 
        array (
          'open' => true,
        ),
        '_id' => 'columns',
        '_name' => 'Columns',
      ),
      'thesis_html_container_1348009575' => 
      array (
        'class' => 'footer',
        '_id' => 'footer',
        '_name' => 'Footer',
      ),
      'thesis_html_container_1348010954' => 
      array (
        'class' => 'content',
        '_admin' => 
        array (
          'open' => true,
        ),
        '_id' => 'content',
        '_name' => 'Content Column',
      ),
      'thesis_html_container_1348010964' => 
      array (
        'class' => 'sidebar',
        '_id' => 'sidebar',
        '_name' => 'Sidebar',
      ),
      'thesis_html_container_1348093642' => 
      array (
        'class' => 'container',
        '_admin' => 
        array (
          'open' => true,
        ),
        '_id' => 'container',
        '_name' => 'Container',
      ),
      'thesis_html_container_1348165494' => 
      array (
        'class' => 'byline small',
        '_name' => 'Byline',
      ),
      'thesis_html_container_1348608649' => 
      array (
        'class' => 'archive_intro post_box grt top',
        '_name' => 'Archive Intro',
      ),
      'thesis_html_container_1348701154' => 
      array (
        'class' => 'prev_next',
        '_id' => 'prev_next',
        '_name' => 'Prev/Next',
      ),
      'thesis_html_container_1348841704' => 
      array (
        'class' => 'comment_head',
        '_name' => 'Comment Head',
      ),
      'thesis_html_container_1348886177' => 
      array (
        'class' => 'headline_area',
        '_name' => 'Headline Area',
      ),
      'thesis_html_container_1365640887' => 
      array (
        'id' => 'comments',
        '_id' => 'post_comments',
        '_name' => 'Post Comments',
      ),
      'thesis_html_container_1365640949' => 
      array (
        'id' => 'comments',
        '_id' => 'page_comments',
        '_name' => 'Page Comments',
      ),
      'thesis_html_container_1366209424' => 
      array (
        'class' => 'comment_footer',
        '_name' => 'Comment Footer',
      ),
    ),
    'thesis_wp_nav_menu' => 
    array (
      'thesis_wp_nav_menu_1348009742' => 
      array (
        'control' => 
        array (
          'yes' => true,
        ),
        '_name' => 'Nav Menu',
      ),
    ),
    'thesis_post_box' => 
    array (
      'thesis_post_box_1348010947' => 
      array (
        'class' => 'grt',
        'schema' => 'article',
        '_admin' => 
        array (
          'open' => true,
        ),
        '_name' => 'Home Post Box',
      ),
      'thesis_post_box_1348607689' => 
      array (
        'class' => 'grt',
        'schema' => 'article',
        '_admin' => 
        array (
          'open' => true,
        ),
        '_name' => 'Post/Page Post Box',
      ),
    ),
    'thesis_post_headline' => 
    array (
      'thesis_post_box_1348010947_thesis_post_headline' => 
      array (
        'html' => 'h2',
        'link' => 
        array (
          'on' => true,
        ),
        '_parent' => 'thesis_post_box_1348010947',
      ),
    ),
    'thesis_wp_widgets' => 
    array (
      'thesis_wp_widgets_1348079687' => 
      array (
        '_id' => 'sidebar',
        '_name' => 'Sidebar Widgets',
      ),
    ),
    'thesis_post_author' => 
    array (
      'thesis_post_box_1348010947_thesis_post_author' => 
      array (
        'intro' => 'by',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
      ),
      'thesis_post_box_1348607689_thesis_post_author' => 
      array (
        'intro' => 'by',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
      ),
    ),
    'thesis_post_date' => 
    array (
      'thesis_post_box_1348010947_thesis_post_date' => 
      array (
        'intro' => 'on',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
      ),
      'thesis_post_box_1348607689_thesis_post_date' => 
      array (
        'intro' => 'on',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
      ),
    ),
    'thesis_comments' => 
    array (
      'thesis_comments_1348716667' => 
      array (
        '_name' => 'Comments',
      ),
    ),
    'thesis_comment_form' => 
    array (
      'thesis_comment_form_1348843091' => 
      array (
        '_name' => 'Comment Form',
      ),
    ),
    'thesis_post_categories' => 
    array (
      'thesis_post_box_1348010947_thesis_post_categories' => 
      array (
        'html' => 'div',
        'intro' => 'in',
        'separator' => ',',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
      ),
      'thesis_post_box_1348607689_thesis_post_categories' => 
      array (
        'html' => 'div',
        'intro' => 'in',
        'separator' => ',',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
      ),
    ),
    'thesis_post_tags' => 
    array (
      'thesis_post_box_1348010947_thesis_post_tags' => 
      array (
        'intro' => 'Tagged as:',
        'separator' => ',',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
      ),
      'thesis_post_box_1348607689_thesis_post_tags' => 
      array (
        'intro' => 'Tagged as:',
        'separator' => ',',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
      ),
    ),
    'thesis_previous_post_link' => 
    array (
      'thesis_previous_post_link' => 
      array (
        'html' => 'p',
        'intro' => 'Previous post:',
      ),
    ),
    'thesis_next_post_link' => 
    array (
      'thesis_next_post_link' => 
      array (
        'html' => 'p',
        'intro' => 'Next post:',
      ),
    ),
    'thesis_text_box' => 
    array (
      'thesis_text_box_1350230891' => 
      array (
        '_id' => 'sidebar',
        '_name' => 'Sidebar Text Box',
      ),
    ),
    'thesis_comment_text' => 
    array (
      'thesis_comments_1348716667_thesis_comment_text' => 
      array (
        'class' => 'grt',
        '_parent' => 'thesis_comments_1348716667',
      ),
    ),
    'thesis_comments_nav' => 
    array (
      'thesis_comments_nav_1366218263' => 
      array (
        'class' => 'comment_nav_top',
        '_name' => 'Comment Nav Top',
      ),
      'thesis_comments_nav_1366218280' => 
      array (
        'class' => 'comment_nav_bottom',
        '_name' => 'Comment Nav Bottom',
      ),
    ),
    'thesis_wp_featured_image' => 
    array (
      'thesis_post_box_1348607689_thesis_wp_featured_image' => 
      array (
        'link' => 
        array (
          'link' => false,
        ),
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
      ),
      'thesis_post_box_1348010947_thesis_wp_featured_image' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
      ),
    ),
    'thesis_post_thumbnail' => 
    array (
      'thesis_post_box_1348010947_thesis_post_thumbnail' => 
      array (
        'alignment' => 'left',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
      ),
      'thesis_post_box_1348607689_thesis_post_thumbnail' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
      ),
    ),
    'thesis_post_image' => 
    array (
      'thesis_post_box_1348607689_thesis_post_image' => 
      array (
        'link' => 
        array (
          'link' => false,
        ),
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
      ),
      'thesis_post_box_1348010947_thesis_post_image' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
      ),
    ),
    'thesis_post_author_avatar' => 
    array (
      'thesis_post_box_1348010947_thesis_post_author_avatar' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
      ),
      'thesis_post_box_1348607689_thesis_post_author_avatar' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
      ),
    ),
    'thesis_post_author_description' => 
    array (
      'thesis_post_box_1348010947_thesis_post_author_description' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
      ),
      'thesis_post_box_1348607689_thesis_post_author_description' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
      ),
    ),
    'thesis_post_num_comments' => 
    array (
      'thesis_post_box_1348010947_thesis_post_num_comments' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
      ),
      'thesis_post_box_1348607689_thesis_post_num_comments' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
      ),
    ),
    'thesis_comment_avatar' => 
    array (
      'thesis_comments_1348716667_thesis_comment_avatar' => 
      array (
        '_id' => 'comments',
        '_parent' => 'thesis_comments_1348716667',
      ),
    ),
    'thesis_comment_date' => 
    array (
      'thesis_comments_1348716667_thesis_comment_date' => 
      array (
        '_id' => 'comments',
        '_parent' => 'thesis_comments_1348716667',
      ),
    ),
  ),
  'vars' => 
  array (
    'var_1349039554' => 
    array (
      'name' => 'Spacing: Single',
      'ref' => 'x_single',
      'css' => '26px',
    ),
    'var_1349039577' => 
    array (
      'name' => 'Spacing: Half',
      'ref' => 'x_half',
      'css' => '13px',
    ),
    'var_1349039585' => 
    array (
      'name' => 'Spacing: Double',
      'ref' => 'x_double',
      'css' => '52px',
    ),
    'var_1349039761' => 
    array (
      'name' => 'Links',
      'ref' => 'links',
      'css' => '#d00',
    ),
    'var_1351010515' => 
    array (
      'name' => 'Clearfix',
      'ref' => 'z_clearfix',
      'css' => 'content: \\".\\"; display: block; height: 0; clear: both; visibility: hidden;',
    ),
    'var_1360768628' => 
    array (
      'name' => 'Primary Text Color',
      'ref' => 'text1',
      'css' => '#111',
    ),
    'var_1360768650' => 
    array (
      'name' => 'Secondary Text Color',
      'ref' => 'text2',
      'css' => '#888',
    ),
    'var_1360768659' => 
    array (
      'name' => 'Color 1',
      'ref' => 'color1',
      'css' => '#ddd',
    ),
    'var_1360768669' => 
    array (
      'name' => 'Color 2',
      'ref' => 'color2',
      'css' => '#eee',
    ),
    'var_1360768678' => 
    array (
      'name' => 'Color 3',
      'ref' => 'color3',
      'css' => '#fff',
    ),
    'var_1362537256' => 
    array (
      'name' => 'Font Size: Sub-headline',
      'ref' => 'f_subhead',
      'css' => '20px',
    ),
    'var_1362537274' => 
    array (
      'name' => 'Font Size: Text',
      'ref' => 'f_text',
      'css' => '16px',
    ),
    'var_1362537289' => 
    array (
      'name' => 'Font Size: Auxiliary',
      'ref' => 'f_aux',
      'css' => '13px',
    ),
    'var_1362580685' => 
    array (
      'name' => 'Line Height: Text',
      'ref' => 'h_text',
      'css' => '26px',
    ),
    'var_1362580697' => 
    array (
      'name' => 'Line Height: Auxiliary Text',
      'ref' => 'h_aux',
      'css' => '22px',
    ),
    'var_1362588614' => 
    array (
      'name' => 'Spacing: 1.5',
      'ref' => 'x_3over2',
      'css' => '39px',
    ),
    'var_1362696253' => 
    array (
      'name' => 'Width: Content',
      'ref' => 'w_content',
      'css' => '617px',
    ),
    'var_1362696268' => 
    array (
      'name' => 'Width: Sidebar',
      'ref' => 'w_sidebar',
      'css' => '280px',
    ),
    'var_1362697011' => 
    array (
      'name' => 'Width: Total',
      'ref' => 'w_total',
      'css' => '897px',
    ),
    'var_1362757553' => 
    array (
      'name' => 'Font: Primary',
      'ref' => 'font',
      'css' => 'Georgia, "Times New Roman", Times, serif',
    ),
    'var_1362767543' => 
    array (
      'name' => 'Sidebar Spacing: Half',
      'ref' => 's_x_half',
      'css' => '10px',
    ),
    'var_1362767589' => 
    array (
      'name' => 'Sidebar Spacing: Single',
      'ref' => 's_x_single',
      'css' => '19px',
    ),
    'var_1362767601' => 
    array (
      'name' => 'Sidebar Spacing: 1.5',
      'ref' => 's_x_3over2',
      'css' => '29px',
    ),
    'var_1362768690' => 
    array (
      'name' => 'Sidebar Spacing: Double',
      'ref' => 's_x_double',
      'css' => '38px',
    ),
    'var_1363019458' => 
    array (
      'name' => 'Site Title Color',
      'ref' => 'title_color',
      'css' => '#111',
    ),
    'var_1363458877' => 
    array (
      'name' => 'Site Title',
      'ref' => 'title',
      'css' => 'font-size: 42px;',
    ),
    'var_1363459110' => 
    array (
      'name' => 'Tagline',
      'ref' => 'tagline',
      'css' => 'font-size: 16px;
	color: #888;',
    ),
    'var_1363467168' => 
    array (
      'name' => 'Nav Menu',
      'ref' => 'menu',
      'css' => 'font-size: 13px;',
    ),
    'var_1363467273' => 
    array (
      'name' => 'Sub-headline',
      'ref' => 'subhead',
      'css' => 'font-size: 20px;
	line-height: 31px;',
    ),
    'var_1363467831' => 
    array (
      'name' => 'Headline',
      'ref' => 'headline',
      'css' => 'font-size: 26px;
	line-height: 39px;',
    ),
    'var_1363537291' => 
    array (
      'name' => 'Sidebar',
      'ref' => 'sidebar',
      'css' => 'font-size: 13px;
	line-height: 19px;',
    ),
    'var_1363621601' => 
    array (
      'name' => 'Blockquote',
      'ref' => 'blockquote',
      'css' => 'color: #888;',
    ),
    'var_1363621659' => 
    array (
      'name' => 'Code',
      'ref' => 'code',
      'css' => 'font-family: Consolas, Monaco, Menlo, Courier, Verdana, sans-serif;',
    ),
    'var_1363621686' => 
    array (
      'name' => 'Pre-formatted Code',
      'ref' => 'pre',
      'css' => 'font-family: Consolas, Monaco, Menlo, Courier, Verdana, sans-serif;',
    ),
    'var_1363621701' => 
    array (
      'name' => 'Sidebar Heading',
      'ref' => 'sidebar_heading',
      'css' => 'font-size: 17px;
	line-height: 24px;',
    ),
    'var_1363633021' => 
    array (
      'name' => 'Headline Color',
      'ref' => 'headline_color',
      'css' => '#111',
    ),
    'var_1363633037' => 
    array (
      'name' => 'Sub-headline Color',
      'ref' => 'subhead_color',
      'css' => '#111',
    ),
    'var_1363989059' => 
    array (
      'name' => 'Author Avatar',
      'ref' => 'avatar',
      'css' => 'width: 61px;
	height: 61px;',
    ),
    'var_1364573035' => 
    array (
      'name' => 'Comment Avatar',
      'ref' => 'comment_avatar',
      'css' => 'width: 52px;
	height: 52px;',
    ),
    'var_1364921879' => 
    array (
      'name' => 'Author Description Avatar',
      'ref' => 'bio_avatar',
      'css' => 'width: 78px;
	height: 78px;',
    ),
    'var_1364931901' => 
    array (
      'name' => 'Pullquote',
      'ref' => 'pullquote',
      'css' => 'font-size: 26px;
	line-height: 36px;',
    ),
    'var_1366555361' => 
    array (
      'name' => 'Navigation Submenu',
      'ref' => 'submenu',
      'css' => '10.5625em',
    ),
    'var_1367605257' => 
    array (
      'name' => 'Content Column',
      'ref' => 'column1',
      'css' => 'float: left;
	border-width: 0 1px 0 0;',
    ),
    'var_1367605279' => 
    array (
      'name' => 'Sidebar Column',
      'ref' => 'column2',
      'css' => 'width: $w_sidebar;
	float: right;',
    ),
  ),
  'templates' => 
  array (
    'home' => 
    array (
      'boxes' => 
      array (
        'thesis_html_body' => 
        array (
          0 => 'thesis_html_container_1348093642',
        ),
        'thesis_html_container_1348093642' => 
        array (
          0 => 'thesis_wp_nav_menu_1348009742',
          1 => 'thesis_html_container_1348009564',
          2 => 'thesis_html_container_1348009571',
          3 => 'thesis_html_container_1348009575',
        ),
        'thesis_html_container_1348009564' => 
        array (
          0 => 'thesis_site_title',
          1 => 'thesis_site_tagline',
        ),
        'thesis_html_container_1348009571' => 
        array (
          0 => 'thesis_html_container_1348010954',
          1 => 'thesis_html_container_1348010964',
        ),
        'thesis_html_container_1348010954' => 
        array (
          0 => 'thesis_wp_loop',
          1 => 'thesis_html_container_1348701154',
        ),
        'thesis_wp_loop' => 
        array (
          0 => 'thesis_post_box_1348010947',
        ),
        'thesis_post_box_1348010947' => 
        array (
          0 => 'thesis_html_container_1348886177',
          1 => 'thesis_post_box_1348010947_thesis_wp_featured_image',
          2 => 'thesis_post_box_1348010947_thesis_post_thumbnail',
          3 => 'thesis_post_box_1348010947_thesis_post_content',
          4 => 'thesis_post_box_1348010947_thesis_post_tags',
          5 => 'thesis_post_box_1348010947_thesis_post_num_comments',
        ),
        'thesis_html_container_1348886177' => 
        array (
          0 => 'thesis_post_box_1348010947_thesis_post_author_avatar',
          1 => 'thesis_post_box_1348010947_thesis_post_headline',
          2 => 'thesis_html_container_1348165494',
        ),
        'thesis_html_container_1348165494' => 
        array (
          0 => 'thesis_post_box_1348010947_thesis_post_author',
          1 => 'thesis_post_box_1348010947_thesis_post_date',
          2 => 'thesis_post_box_1348010947_thesis_post_edit',
          3 => 'thesis_post_box_1348010947_thesis_post_categories',
        ),
        'thesis_html_container_1348701154' => 
        array (
          0 => 'thesis_next_posts_link',
          1 => 'thesis_previous_posts_link',
        ),
        'thesis_html_container_1348010964' => 
        array (
          0 => 'thesis_text_box_1350230891',
          1 => 'thesis_wp_widgets_1348079687',
        ),
        'thesis_html_container_1348009575' => 
        array (
          0 => 'thesis_attribution',
          1 => 'thesis_wp_admin',
        ),
      ),
    ),
    'archive' => 
    array (
      'boxes' => 
      array (
        'thesis_html_body' => 
        array (
          0 => 'thesis_html_container_1348093642',
        ),
        'thesis_html_container_1348093642' => 
        array (
          0 => 'thesis_wp_nav_menu_1348009742',
          1 => 'thesis_html_container_1348009564',
          2 => 'thesis_html_container_1348009571',
          3 => 'thesis_html_container_1348009575',
        ),
        'thesis_html_container_1348009564' => 
        array (
          0 => 'thesis_site_title',
          1 => 'thesis_site_tagline',
        ),
        'thesis_html_container_1348009571' => 
        array (
          0 => 'thesis_html_container_1348010954',
          1 => 'thesis_html_container_1348010964',
        ),
        'thesis_html_container_1348010954' => 
        array (
          0 => 'thesis_html_container_1348608649',
          1 => 'thesis_wp_loop',
          2 => 'thesis_html_container_1348701154',
        ),
        'thesis_html_container_1348608649' => 
        array (
          0 => 'thesis_archive_title',
          1 => 'thesis_archive_content',
        ),
        'thesis_wp_loop' => 
        array (
          0 => 'thesis_post_box_1348010947',
        ),
        'thesis_post_box_1348010947' => 
        array (
          0 => 'thesis_html_container_1348886177',
          1 => 'thesis_post_box_1348010947_thesis_post_num_comments',
        ),
        'thesis_html_container_1348886177' => 
        array (
          0 => 'thesis_post_box_1348010947_thesis_post_author_avatar',
          1 => 'thesis_post_box_1348010947_thesis_post_headline',
          2 => 'thesis_html_container_1348165494',
        ),
        'thesis_html_container_1348165494' => 
        array (
          0 => 'thesis_post_box_1348010947_thesis_post_author',
          1 => 'thesis_post_box_1348010947_thesis_post_date',
          2 => 'thesis_post_box_1348010947_thesis_post_edit',
        ),
        'thesis_html_container_1348701154' => 
        array (
          0 => 'thesis_next_posts_link',
          1 => 'thesis_previous_posts_link',
        ),
        'thesis_html_container_1348010964' => 
        array (
          0 => 'thesis_text_box_1350230891',
          1 => 'thesis_wp_widgets_1348079687',
        ),
        'thesis_html_container_1348009575' => 
        array (
          0 => 'thesis_attribution',
          1 => 'thesis_wp_admin',
        ),
      ),
    ),
    'custom_1348591137' => 
    array (
      'title' => 'Landing Page',
      'options' => 
      array (
        'thesis_html_body' => 
        array (
          'class' => 'landing',
        ),
      ),
      'boxes' => 
      array (
        'thesis_html_body' => 
        array (
          0 => 'thesis_html_container_1348093642',
        ),
        'thesis_html_container_1348093642' => 
        array (
          0 => 'thesis_html_container_1348009564',
          1 => 'thesis_html_container_1348010954',
          2 => 'thesis_html_container_1348009575',
        ),
        'thesis_html_container_1348009564' => 
        array (
          0 => 'thesis_site_title',
          1 => 'thesis_site_tagline',
        ),
        'thesis_html_container_1348010954' => 
        array (
          0 => 'thesis_wp_loop',
        ),
        'thesis_wp_loop' => 
        array (
          0 => 'thesis_post_box_1348607689',
        ),
        'thesis_post_box_1348607689' => 
        array (
          0 => 'thesis_html_container_1348886177',
          1 => 'thesis_post_box_1348607689_thesis_wp_featured_image',
          2 => 'thesis_post_box_1348607689_thesis_post_image',
          3 => 'thesis_post_box_1348607689_thesis_post_content',
        ),
        'thesis_html_container_1348886177' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_headline',
          1 => 'thesis_html_container_1348165494',
        ),
        'thesis_html_container_1348165494' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_edit',
        ),
        'thesis_html_container_1348009575' => 
        array (
          0 => 'thesis_attribution',
          1 => 'thesis_wp_admin',
        ),
      ),
    ),
    'single' => 
    array (
      'boxes' => 
      array (
        'thesis_html_body' => 
        array (
          0 => 'thesis_html_container_1348093642',
        ),
        'thesis_html_container_1348093642' => 
        array (
          0 => 'thesis_wp_nav_menu_1348009742',
          1 => 'thesis_html_container_1348009564',
          2 => 'thesis_html_container_1348009571',
          3 => 'thesis_html_container_1348009575',
        ),
        'thesis_html_container_1348009564' => 
        array (
          0 => 'thesis_site_title',
          1 => 'thesis_site_tagline',
        ),
        'thesis_html_container_1348009571' => 
        array (
          0 => 'thesis_html_container_1348010954',
          1 => 'thesis_html_container_1348010964',
        ),
        'thesis_html_container_1348010954' => 
        array (
          0 => 'thesis_wp_loop',
          1 => 'thesis_html_container_1348701154',
        ),
        'thesis_wp_loop' => 
        array (
          0 => 'thesis_post_box_1348607689',
          1 => 'thesis_html_container_1365640887',
        ),
        'thesis_post_box_1348607689' => 
        array (
          0 => 'thesis_html_container_1348886177',
          1 => 'thesis_post_box_1348607689_thesis_wp_featured_image',
          2 => 'thesis_post_box_1348607689_thesis_post_image',
          3 => 'thesis_post_box_1348607689_thesis_post_content',
          4 => 'thesis_post_box_1348607689_thesis_post_tags',
          5 => 'thesis_post_box_1348607689_thesis_post_author_description',
        ),
        'thesis_html_container_1348886177' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_author_avatar',
          1 => 'thesis_post_box_1348607689_thesis_post_headline',
          2 => 'thesis_html_container_1348165494',
        ),
        'thesis_html_container_1348165494' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_author',
          1 => 'thesis_post_box_1348607689_thesis_post_date',
          2 => 'thesis_post_box_1348607689_thesis_post_edit',
          3 => 'thesis_post_box_1348607689_thesis_post_categories',
        ),
        'thesis_html_container_1365640887' => 
        array (
          0 => 'thesis_comments_intro',
          1 => 'thesis_comments_nav_1366218263',
          2 => 'thesis_comments_1348716667',
          3 => 'thesis_comments_nav_1366218280',
          4 => 'thesis_comment_form_1348843091',
        ),
        'thesis_comments_1348716667' => 
        array (
          0 => 'thesis_html_container_1348841704',
          1 => 'thesis_comments_1348716667_thesis_comment_text',
          2 => 'thesis_html_container_1366209424',
        ),
        'thesis_html_container_1348841704' => 
        array (
          0 => 'thesis_comments_1348716667_thesis_comment_avatar',
          1 => 'thesis_comments_1348716667_thesis_comment_author',
          2 => 'thesis_comments_1348716667_thesis_comment_date',
        ),
        'thesis_html_container_1366209424' => 
        array (
          0 => 'thesis_comments_1348716667_thesis_comment_reply',
          1 => 'thesis_comments_1348716667_thesis_comment_permalink',
          2 => 'thesis_comments_1348716667_thesis_comment_edit',
        ),
        'thesis_comment_form_1348843091' => 
        array (
          0 => 'thesis_comment_form_1348843091_thesis_comment_form_cancel',
          1 => 'thesis_comment_form_1348843091_thesis_comment_form_title',
          2 => 'thesis_comment_form_1348843091_thesis_comment_form_name',
          3 => 'thesis_comment_form_1348843091_thesis_comment_form_email',
          4 => 'thesis_comment_form_1348843091_thesis_comment_form_url',
          5 => 'thesis_comment_form_1348843091_thesis_comment_form_comment',
          6 => 'thesis_comment_form_1348843091_thesis_comment_form_submit',
        ),
        'thesis_html_container_1348701154' => 
        array (
          0 => 'thesis_next_post_link',
          1 => 'thesis_previous_post_link',
        ),
        'thesis_html_container_1348010964' => 
        array (
          0 => 'thesis_text_box_1350230891',
          1 => 'thesis_wp_widgets_1348079687',
        ),
        'thesis_html_container_1348009575' => 
        array (
          0 => 'thesis_attribution',
          1 => 'thesis_wp_admin',
        ),
      ),
    ),
    'page' => 
    array (
      'boxes' => 
      array (
        'thesis_html_body' => 
        array (
          0 => 'thesis_html_container_1348093642',
        ),
        'thesis_html_container_1348093642' => 
        array (
          0 => 'thesis_wp_nav_menu_1348009742',
          1 => 'thesis_html_container_1348009564',
          2 => 'thesis_html_container_1348009571',
          3 => 'thesis_html_container_1348009575',
        ),
        'thesis_html_container_1348009564' => 
        array (
          0 => 'thesis_site_title',
          1 => 'thesis_site_tagline',
        ),
        'thesis_html_container_1348009571' => 
        array (
          0 => 'thesis_html_container_1348010954',
          1 => 'thesis_html_container_1348010964',
        ),
        'thesis_html_container_1348010954' => 
        array (
          0 => 'thesis_wp_loop',
        ),
        'thesis_wp_loop' => 
        array (
          0 => 'thesis_post_box_1348607689',
          1 => 'thesis_html_container_1365640949',
        ),
        'thesis_post_box_1348607689' => 
        array (
          0 => 'thesis_html_container_1348886177',
          1 => 'thesis_post_box_1348607689_thesis_wp_featured_image',
          2 => 'thesis_post_box_1348607689_thesis_post_image',
          3 => 'thesis_post_box_1348607689_thesis_post_content',
        ),
        'thesis_html_container_1348886177' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_author_avatar',
          1 => 'thesis_post_box_1348607689_thesis_post_headline',
          2 => 'thesis_html_container_1348165494',
        ),
        'thesis_html_container_1348165494' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_author',
          1 => 'thesis_post_box_1348607689_thesis_post_date',
          2 => 'thesis_post_box_1348607689_thesis_post_edit',
        ),
        'thesis_html_container_1365640949' => 
        array (
          0 => 'thesis_comments_intro',
          1 => 'thesis_comments_nav_1366218263',
          2 => 'thesis_comments_1348716667',
          3 => 'thesis_comments_nav_1366218280',
          4 => 'thesis_comment_form_1348843091',
        ),
        'thesis_comments_1348716667' => 
        array (
          0 => 'thesis_html_container_1348841704',
          1 => 'thesis_comments_1348716667_thesis_comment_text',
          2 => 'thesis_html_container_1366209424',
        ),
        'thesis_html_container_1348841704' => 
        array (
          0 => 'thesis_comments_1348716667_thesis_comment_avatar',
          1 => 'thesis_comments_1348716667_thesis_comment_author',
          2 => 'thesis_comments_1348716667_thesis_comment_date',
        ),
        'thesis_html_container_1366209424' => 
        array (
          0 => 'thesis_comments_1348716667_thesis_comment_reply',
          1 => 'thesis_comments_1348716667_thesis_comment_permalink',
          2 => 'thesis_comments_1348716667_thesis_comment_edit',
        ),
        'thesis_comment_form_1348843091' => 
        array (
          0 => 'thesis_comment_form_1348843091_thesis_comment_form_cancel',
          1 => 'thesis_comment_form_1348843091_thesis_comment_form_title',
          2 => 'thesis_comment_form_1348843091_thesis_comment_form_name',
          3 => 'thesis_comment_form_1348843091_thesis_comment_form_email',
          4 => 'thesis_comment_form_1348843091_thesis_comment_form_url',
          5 => 'thesis_comment_form_1348843091_thesis_comment_form_comment',
          6 => 'thesis_comment_form_1348843091_thesis_comment_form_submit',
        ),
        'thesis_html_container_1348010964' => 
        array (
          0 => 'thesis_text_box_1350230891',
          1 => 'thesis_wp_widgets_1348079687',
        ),
        'thesis_html_container_1348009575' => 
        array (
          0 => 'thesis_attribution',
          1 => 'thesis_wp_admin',
        ),
      ),
    ),
  ),
);
}
