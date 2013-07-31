<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_stepwise_upgrade {
	function __construct() {
		$o = (array) maybe_unserialize(get_option('thesis_options')); #wp
		$z = (array) maybe_unserialize(get_option('thesis_design_options')); #wp
		# do nothing if version 1.8 or 1.8.1 is installed. upgrade to the new options will occur other places.
		if ($o['version'] === '1.8' || $o['version'] === '1.8.1')
			return;
		elseif (!empty($o)) # pass options on to upgrade control if they're there
			$this->upgrade_control($o, $z);
		else # do nothing if nothing
			return;
	}

	function upgrade_control($t, $d, $g = null) {
		if (!$t['version']) # pre 1.5 test
			$this->upgrade_pre_one_five($t, $d);
		elseif ($t['version'] === '1.5' || $t['version'] === '1.5.1')
			$this->upgrade_onefive_to_onesix($t, $d);
		elseif ($t['version'] === '1.6')
			$this->upgrade_onesix_to_oneseven($t, $d);
		elseif ($t['version'] === '1.7')
			$this->upgrade_oneseven_to_oneeight($t, $d, $g);
		elseif ($t['version'] === '1.8' || $t['version'] === '1.8.1') {
			update_option('new_thesis_options', $t); # change to thesis_options
			update_option('new_thesis_design_options', $o); # change to thesis_design_options
		}
	}

	function upgrade_pre_one_five($t, $d) { # 1.0-1.4 thesis_options into 1.5 options ==> no change between 1.3-1.3.1, 1.3.2-1.3.3
		$o = array();
		$z = array();
		$o['head'] = array(
			'title' => array(
				'title' => isset($t['title_home_name']) ? (bool) $t['title_home_name'] : true, # 1.1 - 1.4
				'tagline' => isset($t['title_home_tagline']) ? (bool) $t['title_home_tagline'] : true, # 1.1 - 1.4
				'tagline_first'=> (bool) $t['title_tagline_first'], # 1.1-1.4
				'branded' => (bool) $t['title_branded'],
				'separator' => isset($t['title_separator']) ? $t['title_separator'] : false),
			'noindex' => array(
				'category' => false,
				'tag' => isset($t['tags_noindex']) ? (bool) $t['tags_noindex'] : true,  # 1.2-1.4
				'author' => true,
				'day' => true,
				'month' => true,
				'year' => true),
			'canonical' => true,
			'version' => isset($t['show_version']) ? (bool) $t['show_version'] : true # 1.3.2 ==> I think this will change somewhere b/t 1.3.2 and 1.5 **
		);
		$o['style'] = array(
			'custom' => (bool) $t['use_custom_style'] # 1.0 -1.4
		);
		$o['feed'] = array(
			'url' => !empty($t['feed_url']) ? $t['feed_url'] : false # 1.0 - 1.4
		);
		$o['scripts'] = array(
			'header' => isset($t['mint']) ? $t['mint'] : (isset($t['header_scripts']) ? $t['header_scripts'] : false), # 1.0-1.1=mint, 1.2-1.4=header_scripts
			'footer' => isset($t['analytics']) ? $t['analytics'] : (isset($t['footer_scripts']) ? $t['footer_scripts'] : false) # 1.0-1.1=mint, 1.2-1.4=header_scripts
		);

		# Home Page options
		$o['home'] = array(
			'meta' => array(
				'description' => isset($t['meta_description']) ? sprintf('%s', htmlentities(trim($t['meta_description']))) : false, # 1.4
				'keywords' => isset($t['meta_keywords']) ? sprintf('%s', htmlentities(trim($t['meta_keywords']))) : false # 1.3.2 will change again before 1.5
			),
			'features' => isset($d['teasers']) ? (int) $d['teasers'] : 2 # 1.4
		);

		# Display options
		$o['display'] = array(
		 	'header' => array(
				'title' => (bool) $t['show_title'], // 1.0 - 1.4
				'tagline' => (bool) $t['show_tagline'], // 1.0 - 1.4
			),
			'byline' => array(
				'author' => array(
					'show' => (bool) $t['show_author'], // 1.0-1.4
					'link' => (bool) $t['link_author_names'], //1.2-1.4
					'nofollow' => (bool) $t['author_nofollow'] //1.2-1.4
				),
				'date' => array(
					'show' => (bool) $t['show_date'] // 1.0 - 1.4
				),
				'num_comments' => array(
					'show' => (bool) $t['show_num_comments'] //1.2-1.4
				),
				'categories' => array(
					'show' => (bool) $t['show_categories'] //1.2-1.4
				),
				'page' => array(
					'author' => (bool) $t['show_author_on_pages'], // 1.0-1.4
					'date' => (bool) $t['show_date_on_pages'] // 1.0-1.4
				)
			),
			'posts' => array(
				'excerpts' => false,
				'read_more_text' => !empty($t['read_more_text']) ? sprintf('%s', htmlentities(trim($t['read_more_text']))) : false, //1.2-1.4
				'nav' => isset($t['show_post_nav']) ? (bool) $t['show_post_nav'] : true //1.2-1.4
			),
			'archives' => array(
				'style' => isset($t['archive_style']) ? $t['archive_style'] : 'titles' //1.2-1.4
			),
			'tags' => array(
				'single' => isset($t['use_tags']) ? (bool) $t['use_tags'] : (isset($t['tags_single']) ? (bool) $t['tags_single'] : true), // need to revisit this [1.2 current]
				'index' => (bool) $t['tags_index'], //1.2-1.4
				'nofollow' => isset($t['tags_nofollow']) ? (bool) $t['tags_nofollow'] : true //1.2-1.4
			),
			'comments' => array(
				'numbers' => isset($t['show_comment_numbers']) ? (bool) $t['show_comment_numbers'] : false,  //1.2-1.4
				'allowed_tags' => false,
				'disable_pages' => (bool) $t['disable_comments'], //1.2-1.4
				'avatar_size' => isset($t['avatar_size']) ? (int) $t['avatar_size'] : 44
			),
			'sidebars' => array(
				'default_widgets' => true
			),
			'admin' => array(
				'edit_post' => isset($t['edit_post_link']) ? (bool) $t['edit_post_link'] : true, //1.2-1.4
				'edit_comment' => isset($t['edit_comment_link']) ? (bool) $t['edit_comment_link'] : true,
				'link' => isset($t['admin_link']) ? (bool) $t['admin_link'] : true
			)
		);
		$np = explode(',', $t['nav_menu_pages']); //1.0-1.4
		$nps = array(); // nav pages array
		foreach ($np as $id) {
			$nps[(int) $id]['show'] = true; // at this point, only nav pages being shown are in the array 1.0-1.4
			$nps[(int) $id]['text'] = sprintf('%s', get_the_title((int) $id)); #wp
		}
		// Nav menu
		$o['nav'] = array(
			'pages' => (!empty($nps) ? $nps : false), // 1.0-1.4
			'style' => false,
			'categories' => !empty($t['nav_category_pages']) ? implode(',', array_map('intval', explode(',', $t['nav_category_pages']))) : false, // 1.0-1.4
			'links' => !empty($t['nav_link_category']) ? (int) $t['nav_link_category'] : false,
			'home' => array(
				'show' => true,
				'text' => !empty($t['nav_home_text']) ? sprintf('%s', strip_tags(stripslashes($t['nav_home_text']))) : false, // 1.0-1.4
				'nofollow' => false
			),
			'feed' => array(
				'show' => (bool) $t['show_feed_link'], // 1.0-1.4
				'text' => !empty($t['feed_link_text']) ? sprintf('%s', strip_tags(stripslashes($t['feed_link_text']))) : false, // 1.0-1.4
				'nofollow' => true
			)
		);

		// Post images and thumbnails
		$o['image'] = array(
			'post' => array(
				'x' => isset($d['post_image_horizontal']) ? $d['post_image_horizontal'] : 'flush',
				'y' => isset($d['post_image_vertical']) ? $d['post_image_vertical'] : 'before-headline',
				'frame' => isset($d['post_image_frame']) ? (bool) $d['post_image_frame'] : false,
				'single' => isset($d['post_image_single']) ? (bool) $d['post_image_single'] : true,
				'archives' => isset($d['post_image_archives']) ? (bool) $d['post_image_archives'] : true
			),
			'thumb' => array(
				'x' => isset($d['thumb_horizontal']) ? $d['thumb_horizontal'] : 'left',
				'y' => isset($d['thumb_vertical']) ? $d['thumb_vertical'] : 'before-post',
				'frame' => isset($d['thumb_frame']) ? $d['thumb_frame'] : false,
				'width' => isset($d['thumb_size']['width']) ? $d['thumb_size']['width'] : 66,
				'height' => isset($d['thumb_size']['height']) ? $d['thumb_size']['height'] : 66
			),
			'fopen' => true
		);
		
		// Save button text
		$o['save_button_text'] = !empty($t['save_button_text']) ? sprintf('%s', strip_tags(stripslashes($t['save_button_text']))) : false; // 1.0-1.4

		// Thesis version
		$o['version'] = '1.5'; // remember, this takes versions 1.0-1.4 and upgrades them to 1.5. we'll keep going later on.		
		
	/************************ Design Options Below **************************************/	
	
		$z['fonts'] = array(
			'families' => array(
				'body' => !empty($d['font_body']) ? $d['font_body'] : 'georgia', // 1.1-1.4
				'subheads' => !empty($d['font_content_subheads_family']) ? $d['font_content_subheads_family'] : false, // 1.1-1.4
				'nav_menu' => !empty($d['font_nav_family']) ? $d['font_nav_family'] : false, // 1.1-1.4
				'header' => !empty($d['font_header_family']) ? $d['font_header_family'] : false, // 1.1-1.4
				'tagline' => !empty($d['font_header_tagline_family']) ? $d['font_header_tagline_family'] : false, // 1.1-1.4
				'headlines' => !empty($d['font_headlines_family']) ? $d['font_headlines_family'] : false, // 1.1-1.4
				'bylines' => !empty($d['font_bylines_family']) ? $d['font_bylines_family'] : false, // 1.1-1.4
				'code' => 'consolas',
				'multimedia_box' => !empty($d['font_multimedia_family']) ? $d['font_multimedia_family'] : false, //1.1-1.4
				'sidebars' => !empty($d['font_sidebars_family']) ? $d['font_sidebars_family'] : false, //1.1-1.4
				'sidebar_headings' => !empty($d['font_sidebars_headings_family']) ? $d['font_sidebars_headings_family'] : false, //1.1-1.4
				'footer' => !empty($d['font_footer_family']) ? $d['font_footer_family'] : false //1.1-1.4
			),
			'sizes' => array(
				'content' => !empty($d['font_content_size']) ? (int) $d['font_content_size'] : 14, // 1.1-1.4
				'nav_menu' => !empty($d['font_nav_size']) ? (int) $d['font_nav_size'] : 11, //1.1-1.4
				'header' => !empty($d['font_header_size']) ? (int) $d['font_header_size'] : 36, //1.1-1.4
				'tagline' => 14,
				'headlines' => !empty($d['font_headlines_size']) ? (int) $d['font_headlines_size'] : 22, //1.1-1.4
				'bylines' => !empty($d['font_bylines_size']) ? (int) $d['font_bylines_size'] : 10, //1.1-1.4
				'code' => 12,
				'multimedia_box' => !empty($d['font_multimedia_size']) ? (int) $d['font_multimedia_size'] : 13, //1.1-1.4
				'sidebars' => !empty($d['font_sidebars_size']) ? (int) $d['font_sidebars_size'] : 13, //1.1-1.4
				'sidebar_headings' => 13,
				'footer' => !empty($d['font_footer_size']) ? (int) $d['font_footer_size'] : 12 //1.1-1.4
			)
		);
		
		$c = isset($d['num_columns']) ? ($d['num_columns'] == 3 ? $d['width_content_3'] : ($d['num_columns'] == 2 ? $d['width_content_2'] : ($d['num_columns'] == 1 ? $d['width_content_1'] : 540))) : 480;
		
		$z['layout'] = array(
			'columns' => isset($d['num_columns']) ? $d['num_columns'] : 3,
			'widths' => array(
				'content' => $c,
				'sidebar_1' => !empty($d['width_sidebar']) ? (int) $d['width_sidebar'] : 201, //1.1-1.4 only 1.0 will return nothing, so default must be 201px
				'sidebar_2' => !empty($d['width_sidebar']) ? (int) $d['width_sidebar'] : 201 //1.1-1.4 see above, dummy
			),
			'order' => isset($d['column_order']) ? $d['column_order'] : 'normal', // 1.3-1.4 possibles are normal, invert and (int) 0
			'framework' => isset($d['html_framework']) ? $d['html_framework'] : 'page', //1.3-1.4
			'page_padding' => 1
		);
		
		
		if ($d['teaser_content']) {
			$z['teasers'] = array();
			$tnames = array('headline'=>__('post title', 'thesis'), 'author'=>__('author name', 'thesis'), 'date'=>__('date', 'thesis'), 'edit'=>__('edit post link', 'thesis'), 'category'=>__('primary category', 'thesis'), 'excerpt'=>__('post excerpt', 'thesis'), 'tags'=>__('tags', 'thesis'), 'comments'=>__('number of comments link', 'thesis'), 'link'=>__('link to full article', 'thesis')); #wp
			foreach ($d['teaser_content'] as $section) {
				$z['teasers']['options'][$section]['name'] = $tnames[$section];
				$z['teasers']['options'][$section]['show'] = (bool) $d['teaser_options'][$section];
			}
			$z['teasers']['date'] = array('format' => $d['teaser_date'], 'font_sizes' => $d['teaser_date_custom']);
			$z['teasers']['font_sizes'] = $d['teaser_font_sizes'];
			$z['teasers']['link_text'] = !empty($d['teaser_link']) ? $d['teaser_link'] : (!empty($d['teaser_link_text']) ? $d['teaser_link_text'] : false);
		}
		else {
			$z['teasers'] = array(
				'options' => array(
					'headline' => array(
						'name' => __('post title', 'thesis'),
						'show' => true
					),
					'author' => array(
						'name' => __('author name', 'thesis'),
						'show' => false
					),
					'date' => array(
						'name' => __('date', 'thesis'),
						'show' => true
					),
					'edit' => array(
						'name' => __('edit post link', 'thesis'),
						'show' => true
					),
					'category' => array(
						'name' => __('primary category', 'thesis'),
						'show' => false
					),
					'excerpt' => array(
						'name' => __('post excerpt', 'thesis'),
						'show' => true
					),
					'tags' => array(
						'name' => __('tags', 'thesis'),
						'show' => false
					),
					'comments' => array(
						'name' => __('number of comments link', 'thesis'),
						'show' => false
					),
					'link' => array(
						'name' => __('link to full article', 'thesis'),
						'show' => true
					)
				),
				'date' => array(
					'format' => 'standard',
					'custom' => 'F j, Y'
				),
				'font_sizes' => array(
					'headline' => 16,
					'author' => 10,
					'date' => 10,
					'category' => 10,
					'excerpt' => 12,
					'tags' => 11,
					'comments' => 10,
					'link' => 12
				),
				'link_text' => false,
			); #wp
		}

		// Feature box variables
		$z['feature_box'] = array(
			'position' => isset($d['feature_box']) ? $d['feature_box'] : false,
			'status' => isset($d['feature_box_condition']) ? $d['feature_box_condition'] : false,
			'after_post' => isset($d['feature_box_after_post']) ? $d['feature_box_after_post'] : false,
			'content' => false // don't think there are old options to fill this. could be feature_box_content but that is nowhere to be found!
		);
		$iat = array();
		foreach($t['image_alt_tags'] as $img => $tag) {
			$img = sprintf('%s', strip_tags(stripslashes($img)));
			$tag = sprintf('%s', strip_tags(stripslashes($tag)));
			$iat[$img] = $tag;
		}
		// Multimedia box
		$z['multimedia_box'] = array(
			'status' => !empty($t['multimedia_box']) ? sprintf('%s', $t['multimedia_box']) : 'image', // 1.0-1.4
			'alt_tags' => !empty($iat) ? (array) $iat : false, // 1.0-1.4
			'link_urls' => !empty($t['image_link_urls']) ? (array) $t['image_link_urls'] : false,
			'video' => !empty($t['video_code']) ? sprintf('%s', $t['video_code']) : false, // 1.0-1.4
			'code' => !empty($t['custom_code']) ? sprintf('%s', $t['custom_code']) : false // 1.0-1.4
		);
				
		// pass back to upgrade control
		$this->upgrade_control($o, $z);
			
	} // close function
	
	function upgrade_onefive_to_onesix($t, $d) {
		$o = array();
		$z = array();
		
		$o['head'] = $t['head'];

		// Syndication/feed
		$o['feed'] = $t['feed'];

		// Header and footer scripts
		$o['scripts'] = $t['scripts'];

		// Home Page options
		$o['home'] = $t['home'];

		// Display options
		$o['display'] = $t['display'];
		
		// Nav menu
		$o['nav'] = $t['nav'];
		$o['nav']['submenu_width'] = 150;
		$o['nav']['border'] = 1;
		unset($o['nav']['style']);

		// Post images and thumbnails
		$o['image'] = $t['image'];
		$o['image']['post']['frame'] = empty($t['image']['post']['frame']) ? 'off' : 'on';
		$o['image']['thumb']['frame'] = empty($t['image']['thumb']['frame']) ? 'off' : 'on';

		// Save button text
		$o['save_button_text'] = $t['save_button_text'];

		// Thesis version
		$o['version'] = '1.6';
		
		/******* design options ********/
		$z['fonts'] = $d['fonts'];

		// Layout colors - these are defaults set in this version
		$z['colors'] = array('background' => 'fff','text' => '111','shadow' => false,'page' => 'fff','link' => '2361a1','header' => '111','tagline' => '888','headlines' => '111','subheads' => '111','bylines'=> '888','code' => '111','sidebars' => '111','sidebar_headings' => '555','footer' => '888');

		$z['borders'] = array(
			'show' => true,
			'color' => 'ddd'
		);

		$z['style']['custom'] = (bool) $t['style']['custom'];

		$z['layout'] = $d['layout'];

		$z['nav'] = array(
			'link' => array('color' => '111','hover' => '111','current' => '111','parent' => '111'),
			'background' => array('link' => 'efefef','hover' => 'ddd','current' => 'fff','parent' => 'f0eec2'),
			'border' => array('width' => 1, 'color' => 'ddd'),
			'submenu_width' => 150);

		$z['teasers'] = $d['teasers'];

		// Feature box variables
		$z['feature_box'] = $d['feature_box'];

		// Multimedia box
		$z['multimedia_box'] = $d['multimedia_box'];
		$z['multimedia_box']['color'] = '111';
		$z['multimedia_box']['background'] = array('image' => 'eee', 'video' => '000', 'code' => 'eee');
						
		$this->upgrade_control($o, $z);
			
	}// close method
	
	function upgrade_onesix_to_oneseven($t, $d) {
		$o = array(); // site options
		$z = array(); // dezign options
		$p = array(); // page options

		/***** Site Options *****/
		
		// head
		$o['head']['title']['branded'] = $t['head']['title']['branded'];
		$o['head']['title']['separator'] = $t['head']['title']['separator'];
		
		$o['head']['meta']['robots']['noindex'] = $t['head']['noindex'];
		$o['head']['meta']['robots']['noindex']['sub'] = true;
		$o['head']['meta']['robots']['nofollow'] = array(
			'sub' => false,
			'category' => false,
			'tag' => true,
			'author' => true,
			'day' => true,
			'month' => true,
			'year' => true
		);
		$o['head']['meta']['robots']['noarchive'] = array(
			'sub' => false,
			'category' => false,
			'tag' => false,
			'author' => false,
			'day' => false,
			'month' => false,
			'year' => false
		);
		$o['head']['meta']['robots']['noodp'] = true;
		$o['head']['meta']['robots']['noydir'] = true;
		
		$o['head']['links']['canonical'] = $t['head']['canonical']; // check
		
		$o['head']['feed']['url'] = $t['feed']['url']; // check
		
		$o['head']['scripts'] = isset($t['scripts']['header']) ? $t['scripts']['header'] : false; // check
		
		// misc javascripts
		$o['javascript']['scripts'] = isset($t['scripts']['footer']) ? $t['scripts']['footer'] : false;
		
		// nav
		$o['nav'] = $t['nav'];
		
		// comments
		$o['comments']['disable_pages'] = (bool) $t['display']['comments']['disable_pages']; // check
		$o['comments']['show_closed'] = false;
		
		// display
		$o['display'] = $t['display'];
		unset($o['display']['comments']);
		
		// save button. aww.
		$o['save_button_text'] = $t['save_button_text'];
		
		// version
		$o['version'] = '1.7';
		
		/***** Design Options *****/
		
		$z['fonts'] = $d['fonts'];
		
		$z['colors'] = $d['colors'];
		
		$z['borders'] = $d['borders'];
		
		$z['nav'] = $d['nav'];
		
		$z['layout'] = $d['layout'];
		$z['layout']['custom'] = (bool) $d['style']['custom']; // check
		
		$z['javascript'] = array(
			'libs' => array(
				'jquery' => false,
				'jquery_ui' => false,
				'prototype' => false,
				'scriptaculous' => false,
				'mootools' => false,
				'dojo' => false,
				'swfobject' => false,
				'yui' => false,
				'ext' => false,
				'chrome' => false
			),
			'scripts' => false
		);
		
		$z['image'] = $t['image']; // check
		
		$z['comments'] = array(
			'comments' => array(
				'show' => true,
				'title' => __('comments', 'thesis'),
				'options' => array(
					'meta' => array(
						'avatar' => array(
							'show' => true,
							'title' => __('avatar', 'thesis'),
							'options' => array(
								'size' => (int) $t['display']['comments']['avatar_size'] // check
							)
						),
						'number' => array(
							'show' => (bool) $t['display']['comments']['numbers'], // check
							'title' => __('comment numbers', 'thesis')
						),
						'author' => array(
							'show' => true,
							'title' => __('comment author', 'thesis')
						),
						'date' => array(
							'show' => true,
							'title' => __('comment date', 'thesis'),
							'options' => array(
								'time' => true,
								'date_format' => 'F j, Y'
							)
						),
						'edit' => array(
							'show' => true,
							'title' => __('edit comment link', 'thesis')
						)
					),
					'body' => array(
						'text' => array(
							'show' => true,
							'title' => __('comment text', 'thesis')
						),
						'reply' => array(
							'show' => true,
							'title' => __('comment reply link', 'thesis')
						)
					)
				)
			),
			'form' => array(
				'show' => true,
				'title' => __('comment form', 'thesis'),
				'options' => false
			),
			'trackbacks' => array(
				'show' => true,
				'title' => __('trackbacks', 'thesis'),
				'options' => array(
					'date' => false,
					'date_format' => 'F j, Y' // check to see if custom option exists
				)
			)
		);
		
		$z['teasers'] = $d['teasers'];
		
		$z['feature_box'] = $d['feature_box']; // check feature_box['content']
		
		$z['multimedia_box'] = $d['multimedia_box'];
		
		/***** Page Options *****/
		
		$p['home']= array(
			'head' => array(
				'title' => false,
				'meta' => array(
					'robots' => array(
						'noindex' => false,
						'nofollow' => false,
						'noarchive' => false
					),
					'description' => $t['home']['meta']['description'], // check
					'keywords' => $t['home']['meta']['keywords'] // check
				)
			),
			'body' => array(
				'content' => array(
					'features' => $t['home']['features'] // check
				)
			),
			'javascript' => array(
				'libs' => false,
				'scripts' => false
			)
		);
		$p['categories'] = array();
		$p['tags'] = array();	
		
		$this->upgrade_control($o, $z, $p);
		
	} // close method
	
	function upgrade_oneseven_to_oneeight($t, $d, $g) {
		$g = empty($g) ? (array) maybe_unserialize(get_option('thesis_pages')) : $g; #wp
		$o = array(); // site options
		$z = array(); // dezign options
		
		/***** Site Options *****/
		
		$o['head'] = $t['head'];			
		$o['javascript'] = $t['javascript'];			
		$o['nav'] = $t['nav'];			
		$o['home'] = $g['home']['head'];			
		$o['publishing']['wlw'] = false;			
		$o['custom']['stylesheet'] = $z['layout']['custom'];			
		$o['save_button_text'] = $t['save_button_text'];			
		$o['version'] = '1.8';
		
		/***** Design Options *****/
		
		$z['fonts'] = $d['fonts'];			
		$z['colors'] = $d['colors'];			
		$z['borders'] = $d['borders'];			
		$z['nav'] = $d['nav'];			
		$z['layout'] = $d['layout'];			
		$z['javascript'] = $d['javascript'];			
		$z['display'] = $t['display'];			
		$z['image'] = $d['image'];
		$z['comments'] = $d['comments'];
		$z['teasers'] = $d['teasers'];
		$z['home']['body']['content']['features'] = $g['home']['body']['content']['features'];
		$z['home']['javascript'] = $g['home']['javascript'];
		$z['feature_box'] = $d['feature_box'];
		$z['multimedia_box'] = $d['multimedia_box'];
		
		$this->upgrade_control($o, $z);
			
	} // close method
	
} // close class

/*** design options ***/
// Home page layout variables
#var $home_layout; //new not used. design_options['teasers'] is used instead
/***** unclear where these things go *****
var $link_tags; 1.0
var $custom_field_keys; 1.0
var $custom_field_slug; 1.1
use_mod has no home.
#var $feature_box_content; //new not sure wtf is up with this one. doesn't seem to be used
*****/