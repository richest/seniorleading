<?php

function thesis_classic_defaults() {
	$all = array (
  'thesis_classic_css' => '&body
&links
&container
&menu
&header
&title
&tagline
&columns
&post
&pullquotes
&comments_intro
&comments_closed
&comments
&reply_edit
&comment_form
&cf_title
&input
&submit
&cancel
&login_alert
&archive_intro
&prev_next
&archive_links
&sidebar
&widgets
&footer',
  'thesis_classic_boxes' => 
  array (
    'thesis_html_container' => 
    array (
      'thesis_html_container_1348009564' => 
      array (
        'id' => 'header',
        '_name' => 'Header',
      ),
      'thesis_html_container_1348009571' => 
      array (
        'class' => 'columns',
        '_admin' => 
        array (
          'open' => true,
        ),
        '_name' => 'Columns',
      ),
      'thesis_html_container_1348009575' => 
      array (
        'id' => 'footer',
        '_name' => 'Footer',
      ),
      'thesis_html_container_1348010954' => 
      array (
        'class' => 'content',
        '_admin' => 
        array (
          'open' => true,
        ),
        '_name' => 'Content Column',
      ),
      'thesis_html_container_1348010964' => 
      array (
        'class' => 'sidebar',
        '_name' => 'Sidebar',
      ),
      'thesis_html_container_1348093642' => 
      array (
        'id' => 'container',
        '_admin' => 
        array (
          'open' => true,
        ),
        '_name' => 'Container',
      ),
      'thesis_html_container_1348165494' => 
      array (
        'class' => 'byline small',
        '_name' => 'Byline',
      ),
      'thesis_html_container_1348174194' => 
      array (
        'html' => 'p',
        '_name' => 'Num Comments Wrapper',
      ),
      'thesis_html_container_1348608649' => 
      array (
        'id' => 'archive_intro',
        'class' => 'post_box top',
        '_name' => 'Archive Intro',
      ),
      'thesis_html_container_1348701154' => 
      array (
        'class' => 'prev_next',
        '_name' => 'Prev/Next',
      ),
      'thesis_html_container_1348841704' => 
      array (
        'html' => 'p',
        'class' => 'comment_head',
        '_name' => 'Comment Head',
      ),
      'thesis_html_container_1348886177' => 
      array (
        'class' => 'headline_area',
        '_name' => 'Headline Area',
      ),
    ),
    'thesis_wp_nav_menu' => 
    array (
      'thesis_wp_nav_menu_1348009742' => 
      array (
        'menu' => '3',
        '_name' => 'Nav Menu',
      ),
    ),
    'thesis_post_box' => 
    array (
      'thesis_post_box_1348010947' => 
      array (
        'schema' => 'article',
        '_admin' => 
        array (
          'open' => true,
        ),
        '_name' => 'Home Post Box',
      ),
      'thesis_post_box_1348607689' => 
      array (
        '_admin' => 
        array (
          'open' => true,
        ),
        '_name' => 'Single Post Box',
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
        '_name' => 'Widgets 1',
      ),
    ),
    'thesis_post_author' => 
    array (
      'thesis_post_box_1348010947_thesis_post_author' => 
      array (
        'intro' => 'by',
        '_parent' => 'thesis_post_box_1348010947',
      ),
      'thesis_post_box_1348607689_thesis_post_author' => 
      array (
        'intro' => 'by',
        '_parent' => 'thesis_post_box_1348607689',
      ),
    ),
    'thesis_post_date' => 
    array (
      'thesis_post_box_1348010947_thesis_post_date' => 
      array (
        'intro' => 'on',
        '_parent' => 'thesis_post_box_1348010947',
      ),
      'thesis_post_box_1348607689_thesis_post_date' => 
      array (
        'intro' => 'on',
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
    'thesis_comment_avatar' => 
    array (
      'thesis_comments_1348716667_thesis_comment_avatar' => 
      array (
        'size' => '44',
        '_parent' => 'thesis_comments_1348716667',
      ),
    ),
    'thesis_comment_date' => 
    array (
      'thesis_comments_1348716667_thesis_comment_date' => 
      array (
        'separator' => 'at',
        '_parent' => 'thesis_comments_1348716667',
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
        'intro' => 'in',
        'separator' => ',',
        '_parent' => 'thesis_post_box_1348010947',
      ),
    ),
    'thesis_post_tags' => 
    array (
      'thesis_post_box_1348010947_thesis_post_tags' => 
      array (
        'intro' => 'Tagged as:',
        'separator' => ',',
        '_parent' => 'thesis_post_box_1348010947',
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
        '_name' => 'Text Box 1',
      ),
    ),
    'thesis_wp_query_test' => 
    array (
      'thesis_wp_query_test' => 
      array (
        'queries' => 
        array (
          'on' => true,
        ),
      ),
    ),
  ),
  'thesis_classic_packages' => 
  array (
    'thesis_package_nav' => 
    array (
      'thesis_package_nav_1347990561' => 
      array (
        '_name' => 'Nav Menu',
        '_ref' => 'menu',
        'font-size' => '12',
        'text-transform' => 'uppercase',
        'letter-spacing' => '1',
        'link' => '111',
        'link-bg' => 'eee',
        'link-hover-bg' => 'ddd',
        'link-current-bg' => 'fff',
        'padding-top' => '9',
        'padding-right' => '11',
        'padding-bottom' => '9',
        'padding-left' => '11',
      ),
    ),
    'thesis_package_columns' => 
    array (
      'thesis_package_columns_1348079804' => 
      array (
        '_name' => 'Columns',
        '_ref' => 'columns',
        '_css' => '.columns { background: url(\\\'images/dot-ddd.gif\\\') 640px 0 repeat-y; }',
        '1-selector' => '.content',
        '1-width' => '640',
        '1-float' => 'left',
        '2-selector' => '.sidebar',
        '2-width' => '259',
        '2-float' => 'right',
        '2-padding-top' => '$single',
        '2-padding-right' => '$half',
        '2-padding-left' => '$half',
        '3-width' => '223',
        '3-float' => 'left',
        '3-padding-right' => '11',
        '3-padding-left' => '11',
      ),
    ),
    'thesis_package_post_format' => 
    array (
      'thesis_package_post_format_1348080857' => 
      array (
        '_name' => 'Post Styles',
        '_ref' => 'post',
        '_css' => '.post_box { padding: $single $single 0 $half; border-top: 1px dotted $bc2; }
.top { border-top: 0; }
.headline_area { margin-bottom: $single; }
.byline { color: $c2; }
.headline a, .byline a:hover, .num_comments { color: $c1; }
.headline a:hover { color: $links; }
.byline a { color: $c2; border-bottom: 1px solid $bc1; }
.byline a, .post_author, .post_date { text-transform: uppercase; letter-spacing: 1px; }
.byline .post_edit { margin-left: $half; }
.byline .post_edit:first-child { margin-left: 0; }
.author_by, .date_on, .post_cats_intro { font-style: italic; }
.post_content h4 { font-weight: bold; }
.post_content a { text-decoration: underline; }
.post_content a:hover { text-decoration: none; }
.post_content .frame { padding: $half; background: $bg1; border: 1px solid $bc1; }
.post_content blockquote { margin-left: $half; padding-left: $half; color: $c3; border-left: 1px solid $bc1; }
.num_comments_link { color: $c3; text-decoration: none; }
.num_comments_link:hover { text-decoration: underline; }
.bracket, .num_comments { font-size: $single; }
.bracket { color: $c6; }
.post_box .post_image { margin-bottom: $single; }
.post_box .post_image_box, .post_box .wp-caption { margin-bottom: $single; padding: $half; background: $bg1; border: 1px solid $bc1; }
.post_box .post_image_box img, .post_box .wp-caption img { display: block; margin-bottom: $half; }
.post_box .wp-caption p { font-size: 13px; line-height: 21px; margin-bottom: 0; }
.post_box .wp-caption p a { text-decoration: underline; }
.post_box .wp-caption p a:hover { text-decoration: none; }
.post_box .post_tags { color: $c3; }
.post_box .post_tags a:hover { text-decoration: underline; }
.post_box .alert, .post_box .note { margin-bottom: $single; padding: $half; }
.post_box .alert { background: $bg4; }
.post_box .note { background: $bg1; }
.post_box .alert p:last-child, .post_box .note p:last-child { margin-bottom: 0; }
.post_box pre { padding: $half; background: $bg1; overflow: auto; clear: both; }
.landing .post_box { padding-right: $half; }
.landing .headline_area { text-align: center; }',
        'list-style-type' => 'square',
        'list-indent' => 
        array (
          'on' => true,
        ),
        'typography' => '602',
      ),
      'thesis_package_post_format_1351012936' => 
      array (
        '_name' => 'Sidebar Styles',
        '_ref' => 'sidebar',
        '_selector' => '.sidebar',
        '_css' => '.sidebar .text_box { margin-bottom: $s_double; }
.sidebar .thesis_email_form { margin-bottom: $s_double; }
.sidebar .thesis_email_form .input_text { width: 100%; margin-bottom: $half; }
.sidebar .input_submit { font-size: 16px; padding: 6px; }',
        'text-font-size' => '13',
        'subhead-font-variant' => 'small-caps',
        'subhead-letter-spacing' => '1',
        'typography' => '233',
      ),
    ),
    'thesis_package_basic' => 
    array (
      'thesis_package_basic_1348091749' => 
      array (
        '_name' => 'Body',
        '_ref' => 'body',
        'font-family' => 'georgia',
        'color' => '$c1',
        'padding-top' => '$single',
      ),
      'thesis_package_basic_1348093016' => 
      array (
        '_name' => 'Site Tagline',
        '_ref' => 'tagline',
        '_selector' => '#site_tagline',
        'font-size' => '16',
        'line-height' => '1.375em',
        'color' => '$c2',
      ),
      'thesis_package_basic_1348093203' => 
      array (
        '_name' => 'Site Title',
        '_ref' => 'title',
        '_selector' => '#site_title',
        '_css' => '#site_title a { color: $c1; }
#site_title a:hover { color: $links; }',
        'font-size' => '42',
        'font-weight' => 'bold',
        'typography' => '937',
      ),
      'thesis_package_basic_1348581489' => 
      array (
        '_name' => 'Header',
        '_ref' => 'header',
        '_selector' => '#header',
        '_css' => '.landing #header { padding-top: 0; text-align: center; }',
        'border-width' => '0 0 3px 0',
        'border-style' => 'double',
        'border-color' => '$bc1',
        'padding-top' => '$single',
        'padding-right' => '$half',
        'padding-bottom' => '$single',
        'padding-left' => '$half',
      ),
      'thesis_package_basic_1348594994' => 
      array (
        '_name' => 'Footer',
        '_ref' => 'footer',
        '_selector' => '#footer',
        '_css' => '#footer a { color: $c2; border-bottom: 1px solid $bc1; }
#footer a:hover { color: $c1; }
.landing #footer { text-align: center; }',
        'font-size' => '13',
        'line-height' => '21',
        'text-align' => 'right',
        'color' => '$c2',
        'border-width' => '3px 0 0 0',
        'border-style' => 'double',
        'border-color' => '$bc1',
        'padding-top' => '$half',
        'padding-right' => '$half',
        'padding-bottom' => '$half',
        'padding-left' => '$half',
      ),
      'thesis_package_basic_1348602837' => 
      array (
        '_name' => 'Container',
        '_ref' => 'container',
        '_selector' => '#container',
        '_css' => '.landing #container { width: 628px; }',
        'width' => '900',
        'margin-right' => 'auto',
        'margin-left' => 'auto',
      ),
      'thesis_package_basic_1348608713' => 
      array (
        '_name' => 'Archive Intro',
        '_ref' => 'archive_intro',
        '_selector' => '#archive_intro',
        '_css' => '.archive_title { margin-bottom: $single; }',
        'border-width' => '0 0 2px 0',
        'border-style' => 'solid',
        'border-color' => '$bc1',
      ),
      'thesis_package_basic_1348802788' => 
      array (
        '_name' => 'Comments Intro',
        '_ref' => 'comments_intro',
        '_selector' => '.comments_intro',
        '_css' => '.comments_intro a { text-decoration: underline; }
.comments_intro a:hover { text-decoration: none; }',
        'font-size' => '16',
        'color' => '$c3',
        'margin-top' => '$double',
        'margin-bottom' => '$half',
        'padding-right' => '$single',
        'padding-left' => '$half',
      ),
      'thesis_package_basic_1348854210' => 
      array (
        '_name' => 'Comment Form Title',
        '_ref' => 'cf_title',
        '_selector' => '#comment_form_title',
        'font-size' => '20',
        'color' => '$c3',
        'border-width' => '0 0 1px 0',
        'border-style' => 'dotted',
        'border-color' => '$bc2',
        'margin-top' => '$double',
        'margin-right' => '-$single',
        'margin-left' => '-$half',
        'padding-right' => '$single',
        'padding-bottom' => '$half',
        'padding-left' => '$half',
      ),
      'thesis_package_basic_1348854486' => 
      array (
        '_name' => 'Comment Form',
        '_ref' => 'comment_form',
        '_selector' => '#commentform',
        '_css' => '#commentform label { display: block; }
#commentform p { margin-bottom: $half; }
#commentform p a { text-decoration: underline; }
#commentform p a:hover { text-decoration: none; }
#commentform p .required { color: $c5; }
.comment_moderated { font-weight: bold; }
#commentform .input_text { width: 50%; }
#commentform textarea.input_text { width: 100%; }
.comment #commentform { padding-right: 0; padding-left: 0; }
.comment #comment_form_title { margin-top: 0; }
.children #commentform, .children #comment_form_title { margin-left: -$single; padding-left: $single; }',
        'font-size' => '16',
        'margin-bottom' => '$double',
        'padding-right' => '$single',
        'padding-left' => '$half',
        'typography' => '602',
      ),
      'thesis_package_basic_1348854844' => 
      array (
        '_name' => 'Comment Cancel',
        '_ref' => 'cancel',
        '_selector' => '#cancel-comment-reply-link',
        '_css' => '#cancel-comment-reply-link { border-top-color: #fa5a5a; border-left-color: #fa5a5a; float: right; }',
        'font-size' => '11',
        'line-height' => '1em',
        'text-transform' => 'uppercase',
        'letter-spacing' => '1',
        'color' => 'fff',
        'background-color' => '$bg3',
        'border-width' => '2',
        'border-style' => 'solid',
        'border-color' => 'ac0000',
        'padding-top' => '5px',
        'padding-right' => '7px',
        'padding-bottom' => '5px',
        'padding-left' => '7px',
      ),
      'thesis_package_basic_1348855243' => 
      array (
        '_name' => 'Login Alert',
        '_ref' => 'login_alert',
        '_selector' => '.login_alert',
        'font-weight' => 'bold',
        'background-color' => '$bg1',
        'border-width' => '1px',
        'border-style' => 'solid',
        'border-color' => '$bc1',
      ),
      'thesis_package_basic_1348881019' => 
      array (
        '_name' => 'Comment Reply & Edit',
        '_ref' => 'reply_edit',
        '_selector' => '.comment-reply-link, .comment_edit',
        '_css' => '.comment-reply-link:hover, .comment_edit:hover { text-decoration: underline; }',
        'font-size' => '12',
        'text-transform' => 'uppercase',
        'letter-spacing' => '1',
        'color' => '$c2',
      ),
      'thesis_package_basic_1348983394' => 
      array (
        '_name' => 'Comments Closed',
        '_ref' => 'comments_closed',
        '_selector' => '.comments_closed',
        'font-size' => '13',
        'color' => '$c2',
        'margin-right' => '$single',
        'margin-bottom' => '$single',
        'margin-left' => '$half',
        'typography' => '480',
      ),
      'thesis_package_basic_1351010263' => 
      array (
        '_name' => 'Prev/Next',
        '_ref' => 'prev_next',
        '_selector' => '.prev_next',
        '_css' => '.prev_next { clear: both; }
.prev_next a:hover { text-decoration: underline; }
.prev_next .next_posts { float: right; }
.prev_next:after { $clearfix }',
        'font-size' => '16',
        'color' => '$c3',
        'border-width' => '2px 0 0 0',
        'border-style' => 'solid',
        'border-color' => '$bc1',
        'padding-top' => '$single',
        'padding-right' => '$single',
        'padding-bottom' => '$single',
        'padding-left' => '$half',
        'typography' => '602',
      ),
      'thesis_package_basic_1351010715' => 
      array (
        '_name' => 'Archive Links',
        '_ref' => 'archive_links',
        '_selector' => '.previous_posts, .next_posts',
        'font-size' => '12',
        'text-transform' => 'uppercase',
        'letter-spacing' => '2',
      ),
      'thesis_package_basic_1351013909' => 
      array (
        '_name' => 'Pullquotes',
        '_ref' => 'pullquotes',
        '_selector' => '.post_content blockquote.right, .post_content blockquote.left',
        '_css' => '.post_content blockquote.right, .post_content blockquote.left { padding-left: 0; border: 0; }
.post_content blockquote.right p, .post_content blockquote.left p { margin-bottom: 0; }',
        'font-size' => '26',
        'width' => '45%',
        'margin-bottom' => '$half',
        'typography' => '271',
      ),
    ),
    'thesis_package_links' => 
    array (
      'thesis_package_links_1348180779' => 
      array (
        '_name' => 'Links',
        '_ref' => 'links',
        'link' => '$links',
        'link-decoration' => 'none',
      ),
    ),
    'thesis_package_wp_nav' => 
    array (
      'thesis_package_wp_nav_1348267619' => 
      array (
        '_name' => 'Menu',
        '_ref' => 'menu',
        'font-size' => '12',
        'text-transform' => 'uppercase',
        'letter-spacing' => '1',
        'link' => '$c1',
        'link-bg' => '$bg1',
        'link-hover-bg' => '$bg2',
        'link-current-bg' => 'fff',
        'padding-top' => '9',
        'padding-right' => '$half',
        'padding-bottom' => '9',
        'padding-left' => '$half',
        'border-type' => 'tabbed',
        'border-width' => '1',
        'border-color' => '$bc1',
      ),
    ),
    'thesis_package_wp_widgets' => 
    array (
      'thesis_package_wp_widgets_1348355558' => 
      array (
        '_name' => 'Widgets',
        '_ref' => 'widgets',
        '_css' => '.widget li a:hover, .widget p a { text-decoration: underline; }
.widget p a:hover { text-decoration: none; }
.search-form .input_text { width: 100%; }',
        'text-font-size' => '13',
        'text-margin-bottom' => '$s_double',
        'subhead-font-variant' => 'small-caps',
        'subhead-letter-spacing' => '1',
        'subhead-margin-bottom' => '$s_half',
        'list-style-type' => 'none',
        'list-item-margin' => 'half',
        'typography' => '233',
      ),
    ),
    'thesis_package_wp_comment' => 
    array (
      'thesis_package_wp_comment_1348769068' => 
      array (
        '_name' => 'Comments',
        '_ref' => 'comments',
        '_css' => '#comments { list-style: none; margin-bottom: $double; }
.children .comment { padding-top: 0; padding-right: 0; }
.comment .avatar { float: right; margin-left: $half; }
.comment_date, .comment_edit { font-size: 12px; line-height: $single; margin-left: $half; }
.comment > .comment_head { margin-bottom: $half; }
.children .comment > .comment_head { margin-bottom: 0; }',
        'text-font-size' => '14',
        'subhead-font-size' => '16',
        'subhead-font-weight' => 'bold',
        'subhead-line-height' => '$single',
        'list-style-type' => 'square',
        'list-indent' => 
        array (
          'on' => '1',
        ),
        'typography' => '480',
        'comments-border-width' => '0 0 1px 0',
        'comments-border-style' => 'dotted',
        'comments-border-color' => '$bc1',
        'comments-padding-top' => '$single',
        'comments-padding-right' => '$single',
        'comments-padding-left' => '$half',
        'nested-border-width' => '0 0 0 1px',
        'nested-border-style' => 'solid',
        'nested-border-color' => '$bc1',
        'nested-margin-top' => '$single',
        'nested-padding-left' => '$single',
      ),
    ),
    'thesis_package_input' => 
    array (
      'thesis_package_input_1348865259' => 
      array (
        '_name' => 'Input',
        '_ref' => 'input',
        '_selector' => '.input_text',
        '_css' => '.input_text { border-right-color: $bc1; border-bottom-color: $bc1; }
.input_text:focus { border-right-color: $bc7; border-bottom-color: $bc7; }
textarea.input_text { line-height: $single; }',
        'color' => '$c1',
        'background-color' => '$bg1',
        'box-sizing' => 'border-box',
        'border-width' => '1px',
        'border-style' => 'solid',
        'border-color' => '$bc6',
        'padding-top' => '4',
        'padding-right' => '4',
        'padding-bottom' => '4',
        'padding-left' => '4',
        'focus-background-color' => 'fff',
        'focus-border-color' => '$bc4',
      ),
      'thesis_package_input_1348869729' => 
      array (
        '_name' => 'Submit',
        '_ref' => 'submit',
        '_selector' => '.input_submit',
        '_css' => '.input_submit { border-top-color: $bc7; border-left-color: $bc7; cursor: pointer; overflow: visible; }
.input_submit:hover { color: $c4; }',
        'font-family' => 'georgia',
        'font-size' => '20',
        'font-weight' => 'bold',
        'background-image' => 'images/submit-bg.gif',
        'border-width' => '3',
        'border-style' => 'double',
        'border-color' => '$bc5',
        'padding-top' => '8',
        'padding-right' => '8',
        'padding-bottom' => '8',
        'padding-left' => '8',
      ),
    ),
    'thesis_package_wp_comments' => 
    array (
      'thesis_package_wp_comments_1348876315' => 
      array (
        '_name' => 'Comments',
        '_ref' => 'comments',
        '_css' => '#comments { list-style-type: none; margin-bottom: $double; border-top: 1px dotted $bc2; }
.children .comment { padding-top: 0; padding-right: 0; padding-bottom: 0; }
.comment .avatar { float: right; margin-left: $half; }
.comment .comment_head { margin-bottom: $half; }
.children .comment_head { margin-bottom: 0; }
.comment_date { font-size: 12px; color: $c2; margin-left: $half; }
.comment_edit { float: right; }
.comment_date a { color: $c2; }
.comment_text a, .comment_head a:hover { text-decoration: underline; }
.comment_text a:hover { text-decoration: none; }',
        'text-font-size' => '16',
        'subhead-font-size' => '18',
        'subhead-font-weight' => 'bold',
        'subhead-line-height' => '$single',
        'list-style-type' => 'square',
        'list-indent' => 
        array (
          'on' => '1',
        ),
        'typography' => '602',
        'comments-border-width' => '0 0 1px 0',
        'comments-border-style' => 'dotted',
        'comments-border-color' => '$bc2',
        'comments-padding-top' => '$single',
        'comments-padding-right' => '$single',
        'comments-padding-bottom' => '$single',
        'comments-padding-left' => '$half',
        'nested-border-width' => '0 0 0 1px',
        'nested-border-style' => 'solid',
        'nested-border-color' => '$bc1',
        'nested-margin-top' => '$single',
        'nested-padding-left' => '$single',
        'author-background-color' => 'e7f8fb',
        'nested-author-background-color' => 'transparent',
        'nested-author-border-width' => '0 0 0 2px',
        'nested-author-border-style' => 'solid',
        'nested-author-border-color' => 'bde0e6',
      ),
    ),
  ),
  'thesis_classic_vars' => 
  array (
    'var_1349036755' => 
    array (
      'name' => 'Text 1',
      'ref' => 'c1',
      'css' => '#111',
    ),
    'var_1349036771' => 
    array (
      'name' => 'Text 2',
      'ref' => 'c2',
      'css' => '#888',
    ),
    'var_1349039257' => 
    array (
      'name' => 'Text 3',
      'ref' => 'c3',
      'css' => '#666',
    ),
    'var_1349039300' => 
    array (
      'name' => 'Text 4',
      'ref' => 'c4',
      'css' => '#090',
    ),
    'var_1349039403' => 
    array (
      'name' => 'Text 5',
      'ref' => 'c5',
      'css' => '#d00',
    ),
    'var_1349039415' => 
    array (
      'name' => 'Text 6',
      'ref' => 'c6',
      'css' => '#ccc',
    ),
    'var_1349039427' => 
    array (
      'name' => 'Border 1',
      'ref' => 'bc1',
      'css' => '#ddd',
    ),
    'var_1349039450' => 
    array (
      'name' => 'Border 2',
      'ref' => 'bc2',
      'css' => '#bbb',
    ),
    'var_1349039460' => 
    array (
      'name' => 'Border 3',
      'ref' => 'bc3',
      'css' => '#eee',
    ),
    'var_1349039471' => 
    array (
      'name' => 'Border 4',
      'ref' => 'bc4',
      'css' => '#777',
    ),
    'var_1349039480' => 
    array (
      'name' => 'Border 5',
      'ref' => 'bc5',
      'css' => '#999',
    ),
    'var_1349039496' => 
    array (
      'name' => 'BG 1',
      'ref' => 'bg1',
      'css' => '#eee',
    ),
    'var_1349039516' => 
    array (
      'name' => 'BG 2',
      'ref' => 'bg2',
      'css' => '#ddd',
    ),
    'var_1349039523' => 
    array (
      'name' => 'BG 3',
      'ref' => 'bg3',
      'css' => '#d00',
    ),
    'var_1349039554' => 
    array (
      'name' => 'single',
      'ref' => 'single',
      'css' => '25px',
    ),
    'var_1349039577' => 
    array (
      'name' => 'half',
      'ref' => 'half',
      'css' => '13px',
    ),
    'var_1349039585' => 
    array (
      'name' => 'double',
      'ref' => 'double',
      'css' => '50px',
    ),
    'var_1349039761' => 
    array (
      'name' => 'Links',
      'ref' => 'links',
      'css' => '#d00',
    ),
    'var_1349043738' => 
    array (
      'name' => 'Border 6',
      'ref' => 'bc6',
      'css' => '#aaa',
    ),
    'var_1349043753' => 
    array (
      'name' => 'Border 7',
      'ref' => 'bc7',
      'css' => '#ccc',
    ),
    'var_1351010515' => 
    array (
      'name' => 'clearfix',
      'ref' => 'clearfix',
      'css' => 'content: \\".\\"; display: block; height: 0; clear: both; visibility: hidden;',
    ),
    'var_1351012499' => 
    array (
      'name' => 'BG4',
      'ref' => 'bg4',
      'css' => '#ff9',
    ),
    'var_1351013213' => 
    array (
      'name' => 'sidebar single',
      'ref' => 's_single',
      'css' => '19px',
    ),
    'var_1351013233' => 
    array (
      'name' => 'sidebar double',
      'ref' => 's_double',
      'css' => '38px',
    ),
    'var_1351013439' => 
    array (
      'name' => 'sidebar half',
      'ref' => 's_half',
      'css' => '10px',
    ),
  ),
  'thesis_classic_templates' => 
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
          1 => 'thesis_post_box_1348010947_thesis_post_content',
          2 => 'thesis_html_container_1348174194',
        ),
        'thesis_html_container_1348886177' => 
        array (
          0 => 'thesis_post_box_1348010947_thesis_post_headline',
          1 => 'thesis_html_container_1348165494',
        ),
        'thesis_html_container_1348165494' => 
        array (
          0 => 'thesis_post_box_1348010947_thesis_post_author',
          1 => 'thesis_post_box_1348010947_thesis_post_date',
          2 => 'thesis_post_box_1348010947_thesis_post_edit',
        ),
        'thesis_html_container_1348174194' => 
        array (
          0 => 'thesis_post_box_1348010947_thesis_post_num_comments',
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
        'thesis_post_box_1348607689' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_headline',
          1 => 'thesis_post_box_1348607689_thesis_post_author',
          2 => 'thesis_post_box_1348607689_thesis_post_edit',
          3 => 'thesis_post_box_1348607689_thesis_post_content',
        ),
        'thesis_comments_1348716667' => 
        array (
          0 => 'thesis_comments_1348716667_thesis_comment_author',
          1 => 'thesis_comments_1348716667_thesis_comment_date',
          2 => 'thesis_comments_1348716667_thesis_comment_edit',
          3 => 'thesis_comments_1348716667_thesis_comment_text',
          4 => 'thesis_comments_1348716667_thesis_comment_reply',
        ),
        'thesis_comment_form_1348843091' => 
        array (
          0 => 'thesis_comment_form_1348843091_thesis_comment_form_title',
          1 => 'thesis_comment_form_1348843091_thesis_comment_form_cancel',
          2 => 'thesis_comment_form_1348843091_thesis_comment_form_name',
          3 => 'thesis_comment_form_1348843091_thesis_comment_form_email',
          4 => 'thesis_comment_form_1348843091_thesis_comment_form_url',
          5 => 'thesis_comment_form_1348843091_thesis_comment_form_comment',
          6 => 'thesis_comment_form_1348843091_thesis_comment_form_submit',
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
        ),
        'thesis_html_container_1348886177' => 
        array (
          0 => 'thesis_post_box_1348010947_thesis_post_headline',
          1 => 'thesis_html_container_1348165494',
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
        'thesis_html_container_1348174194' => 
        array (
          0 => 'thesis_post_box_1348010947_thesis_post_num_comments',
        ),
        'thesis_post_box_1348607689' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_headline',
          1 => 'thesis_post_box_1348607689_thesis_post_author',
          2 => 'thesis_post_box_1348607689_thesis_post_edit',
          3 => 'thesis_post_box_1348607689_thesis_post_content',
        ),
        'thesis_comments_1348716667' => 
        array (
          0 => 'thesis_comments_1348716667_thesis_comment_author',
          1 => 'thesis_comments_1348716667_thesis_comment_date',
          2 => 'thesis_comments_1348716667_thesis_comment_edit',
          3 => 'thesis_comments_1348716667_thesis_comment_text',
          4 => 'thesis_comments_1348716667_thesis_comment_reply',
        ),
        'thesis_comment_form_1348843091' => 
        array (
          0 => 'thesis_comment_form_1348843091_thesis_comment_form_title',
          1 => 'thesis_comment_form_1348843091_thesis_comment_form_cancel',
          2 => 'thesis_comment_form_1348843091_thesis_comment_form_name',
          3 => 'thesis_comment_form_1348843091_thesis_comment_form_email',
          4 => 'thesis_comment_form_1348843091_thesis_comment_form_url',
          5 => 'thesis_comment_form_1348843091_thesis_comment_form_comment',
          6 => 'thesis_comment_form_1348843091_thesis_comment_form_submit',
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
          1 => 'thesis_post_box_1348607689_thesis_post_content',
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
        'thesis_html_container_1348009571' => 
        array (
          0 => 'thesis_html_container_1348010964',
        ),
        'thesis_html_container_1348010964' => 
        array (
          0 => 'thesis_wp_widgets_1348079687',
        ),
        'thesis_post_box_1348010947' => 
        array (
          0 => 'thesis_post_box_1348010947_thesis_post_edit',
          1 => 'thesis_post_box_1348010947_thesis_post_date',
          2 => 'thesis_post_box_1348010947_thesis_post_author',
          3 => 'thesis_post_box_1348010947_thesis_post_headline',
          4 => 'thesis_post_box_1348010947_thesis_post_content',
        ),
        'thesis_comments_1348716667' => 
        array (
          0 => 'thesis_comments_1348716667_thesis_comment_author',
          1 => 'thesis_comments_1348716667_thesis_comment_date',
          2 => 'thesis_comments_1348716667_thesis_comment_edit',
          3 => 'thesis_comments_1348716667_thesis_comment_text',
          4 => 'thesis_comments_1348716667_thesis_comment_reply',
        ),
        'thesis_comment_form_1348843091' => 
        array (
          0 => 'thesis_comment_form_1348843091_thesis_comment_form_title',
          1 => 'thesis_comment_form_1348843091_thesis_comment_form_cancel',
          2 => 'thesis_comment_form_1348843091_thesis_comment_form_name',
          3 => 'thesis_comment_form_1348843091_thesis_comment_form_email',
          4 => 'thesis_comment_form_1348843091_thesis_comment_form_url',
          5 => 'thesis_comment_form_1348843091_thesis_comment_form_comment',
          6 => 'thesis_comment_form_1348843091_thesis_comment_form_submit',
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
          1 => 'thesis_comments_intro',
          2 => 'thesis_comments_1348716667',
          3 => 'thesis_comment_form_1348843091',
        ),
        'thesis_post_box_1348607689' => 
        array (
          0 => 'thesis_html_container_1348886177',
          1 => 'thesis_post_box_1348607689_thesis_post_content',
        ),
        'thesis_html_container_1348886177' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_headline',
          1 => 'thesis_html_container_1348165494',
        ),
        'thesis_html_container_1348165494' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_author',
          1 => 'thesis_post_box_1348607689_thesis_post_date',
          2 => 'thesis_post_box_1348607689_thesis_post_edit',
        ),
        'thesis_comments_1348716667' => 
        array (
          0 => 'thesis_comments_1348716667_thesis_comment_avatar',
          1 => 'thesis_html_container_1348841704',
          2 => 'thesis_comments_1348716667_thesis_comment_text',
          3 => 'thesis_comments_1348716667_thesis_comment_reply',
          4 => 'thesis_comments_1348716667_thesis_comment_edit',
        ),
        'thesis_html_container_1348841704' => 
        array (
          0 => 'thesis_comments_1348716667_thesis_comment_author',
          1 => 'thesis_comments_1348716667_thesis_comment_date',
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
        'thesis_post_box_1348010947' => 
        array (
          0 => 'thesis_post_box_1348010947_thesis_post_edit',
          1 => 'thesis_post_box_1348010947_thesis_post_date',
          2 => 'thesis_post_box_1348010947_thesis_post_author',
          3 => 'thesis_post_box_1348010947_thesis_post_headline',
          4 => 'thesis_post_box_1348010947_thesis_post_content',
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
        ),
        'thesis_post_box_1348607689' => 
        array (
          0 => 'thesis_html_container_1348886177',
          1 => 'thesis_post_box_1348607689_thesis_post_content',
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
        'thesis_post_box_1348010947' => 
        array (
          0 => 'thesis_post_box_1348010947_thesis_post_edit',
          1 => 'thesis_post_box_1348010947_thesis_post_date',
          2 => 'thesis_post_box_1348010947_thesis_post_author',
          3 => 'thesis_post_box_1348010947_thesis_post_headline',
          4 => 'thesis_post_box_1348010947_thesis_post_content',
        ),
        'thesis_comments_1348716667' => 
        array (
          0 => 'thesis_comments_1348716667_thesis_comment_author',
          1 => 'thesis_comments_1348716667_thesis_comment_date',
          2 => 'thesis_comments_1348716667_thesis_comment_edit',
          3 => 'thesis_comments_1348716667_thesis_comment_text',
          4 => 'thesis_comments_1348716667_thesis_comment_reply',
        ),
        'thesis_comment_form_1348843091' => 
        array (
          0 => 'thesis_comment_form_1348843091_thesis_comment_form_title',
          1 => 'thesis_comment_form_1348843091_thesis_comment_form_cancel',
          2 => 'thesis_comment_form_1348843091_thesis_comment_form_name',
          3 => 'thesis_comment_form_1348843091_thesis_comment_form_email',
          4 => 'thesis_comment_form_1348843091_thesis_comment_form_url',
          5 => 'thesis_comment_form_1348843091_thesis_comment_form_comment',
          6 => 'thesis_comment_form_1348843091_thesis_comment_form_submit',
        ),
      ),
    ),
  ),
);
	foreach ($all as $key => $data)
		update_option($key, (strpos($key, 'css') ? strip_tags($data) : $data));
}
wp_cache_flush();