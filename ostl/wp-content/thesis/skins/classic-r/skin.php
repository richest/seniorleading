<?php
/*
Name: Classic Responsive
Author: Chris Pearson
Description: Elegant and versatile, the Classic Responsive Skin features clean lines and mathematical precision with an emphasis on typography.
Version: 1.0
Class: thesis_classic_r
License: DIYthemes Software Extensions License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-extensions-license-agreement/

Copyright 2013 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
*/
class thesis_classic_r extends thesis_skin {
	private $elements = array( // Display options with filter references
		'site' => array(
			'title' => 'thesis_site_title',
			'tagline' => 'thesis_site_tagline'),
		'loop' => array( // 'loop' has been added as a programmatic ID to these Boxes
			'author' => 'thesis_post_author_loop',
			'avatar' => 'thesis_post_author_avatar_loop',
			'description' => 'thesis_post_author_description_loop',
			'date' => 'thesis_post_date_loop',
			'wp_featured_image' => 'thesis_wp_featured_image_loop',
			'cats' => 'thesis_post_categories_loop',
			'tags' => 'thesis_post_tags_loop',
			'num_comments' => 'thesis_post_num_comments_loop',
			'image' => 'thesis_post_image_loop',
			'thumbnail' => 'thesis_post_thumbnail_loop'),
		'comments' => array( // 'comments' has been added as a programmatic ID to the date and avatar Boxes
			'post' => 'thesis_html_container_post_comments',
			'page' => 'thesis_html_container_page_comments',
			'date' => 'thesis_comment_date_comments',
			'avatar' => 'thesis_comment_avatar_comments'),
		'sidebar' => array( // 'sidebar' is the hook name for 'sidebar' and the programmatic ID for text and widgets
			'sidebar' => 'thesis_html_container_sidebar',
			'text' => 'thesis_text_box_sidebar',
			'widgets' => 'thesis_wp_widgets_sidebar'),
		'misc' => array(
			'attribution' => 'thesis_attribution',
			'wp_admin' => 'thesis_wp_admin'));

	protected function construct() { // Skin API pseudo-constructor; place hooks and filters here
		// implement display options
		foreach ($this->elements as $element => $items)
			if (is_array($items))
				foreach ($items as $item => $filter)
					if (empty($this->display[$element]['display'][$item]))
						add_filter("{$filter}_show", '__return_false');
		// the curly braces don't have a normal display filter, so handle those separately
		if (!empty($this->display['misc']['display']['braces'])) {
			add_filter('thesis_post_num_comments', array($this, 'num_comments'));
			add_filter('thesis_comments_intro', array($this, 'comments_intro'));
		}
		// the previous/next links (found on home, archive, and single templates) require special filtering based on page context
		add_filter('thesis_html_container_prev_next_show', array($this, 'prev_next'));
		// hook header_image_html(), a Skin API method, into the proper location for this Skin
		add_action('hook_bottom_header', array($this, 'header_image_html'));
	}

	protected function display() { // Skin API method for initiating display options; return an array in Thesis Options API array format
		global $thesis;
		return array( // use an options object set for simplified display controls
			'display' => array(
				'type' => 'object_set',
				'select' => __('Select content to display:', 'thesis'),
				'objects' => array(
					'site' => array(
						'type' => 'object',
						'label' => __('Site Title &amp; Tagline', $this->_class),
						'fields' => array(
							'display' => array(
								'type' => 'checkbox',
								'options' => array(
									'title' => __('Site title', $this->_class),
									'tagline' => __('Site tagline', $this->_class)),
								'default' => array(
									'title' => true,
									'tagline' => true)))),
					'loop' => array(
						'type' => 'object',
						'label' => __('Post/Page Output', $this->_class),
						'fields' => array(
							'display' => array(
								'type' => 'checkbox',
								'options' => array(
									'author' => __('Author', $this->_class),
									'avatar' => __('Author avatar', $this->_class),
									'description' => __('Author description (single template)', $this->_class),
									'date' => __('Date', $this->_class),
									'wp_featured_image' => __('WP featured image', $this->_class),
									'cats' => __('Categories', $this->_class),
									'tags' => __('Tags', $this->_class),
									'num_comments' => __('Number of comments (home and archive templates)', $this->_class),
									'image' => __('Thesis post image (single, page, and landing page templates)', $this->_class),
									'thumbnail' => __('Thesis thumbnail image (home template)', $this->_class)),
								'default' => array(
									'author' => true,
									'date' => true,
									'wp_featured_image' => true,
									'num_comments' => true)))),
					'comments' => array(
						'type' => 'object',
						'label' => __('Comments', $this->_class),
						'fields' => array(
							'display' => array(
								'type' => 'checkbox',
								'options' => array(
									'post' => __('Comments on posts', $this->_class),
									'page' => __('Comments on pages', $this->_class),
									'date' => __('Comment date', $this->_class),
									'avatar' => __('Comment avatar', $this->_class)),
								'default' => array(
									'post' => true,
									'date' => true,
									'avatar' => true)))),
					'sidebar' => array(
						'type' => 'object',
						'label' => __('Sidebar', $this->_class),
						'fields' => array(
							'display' => array(
								'type' => 'checkbox',
								'options' => array(
									'sidebar' => __('Sidebar', $this->_class),
									'text' => __('Sidebar Text Box', $this->_class),
									'widgets' => __('Sidebar Widgets', $this->_class)),
								'default' => array(
									'sidebar' => true,
									'text' => true,
									'widgets' => true)))),
					'misc' => array(
						'type' => 'object',
						'label' => __('Miscellaneous', $this->_class),
						'fields' => array(
							'display' => array(
								'type' => 'checkbox',
								'options' => array(
									'prev_next' => __('Previous/next post links (single template)', $this->_class),
									'attribution' => __('Skin attribution', $this->_class),
									'wp_admin' => __('WP admin link', $this->_class),
									'braces' => __('Iconic Classic Responsive Skin curly braces', $this->_class)),
								'default' => array(
									'prev_next' => true,
									'attribution' => true,
									'wp_admin' => true,
									'braces' => true)))))));
	}

	protected function design() { // Skin API method for initiating design options; return an array in Thesis Options API array format
		global $thesis;
		$css = $thesis->api->css->options; // shorthand for all options available in the CSS API
		$fsc = $nav = $thesis->api->css->font_size_color(); // the CSS API contains shorthand for font, size, and color options
		unset($nav['color']); // remove nav text color control
		$links['default'] = 'DD0000'; // default link color
		$links['gray'] = $thesis->api->colors->gray($links['default']); // array of 'hex' and 'rgb' values
		return array(
			'colors' => $this->color_scheme(array( // the Skin API contains a color_scheme() method for easy implementation
				'id' => 'colors',
				'colors' => array(
					'text1' => __('Primary Text', $this->_class),
					'text2' => __('Secondary Text', $this->_class),
					'links' => __('Links', $this->_class),
					'color1' => __('Borders &amp; Highlights', $this->_class),
					'color2' => __('Interior <abbr title="background">BG</abbr>s', $this->_class),
					'color3' => __('Site <abbr title="background">BG</abbr>', $this->_class)),
				'default' => array(
					'text1' => '111111',
					'text2' => '888888',
					'links' => $links['default'],
					'color1' => 'DDDDDD',
					'color2' => 'EEEEEE',
					'color3' => 'FFFFFF'),
				'scale' => array(
					'links' => $links['gray']['hex'],
					'color1' => 'DDDDDD',
					'color2' => 'EEEEEE',
					'color3' => 'FFFFFF'))),
			'elements' => array( // this is an object set containing all other design options for this Skin
				'type' => 'object_set',
				'label' => __('Layout, Fonts, Sizes, and Colors', $this->_class),
				'select' => __('Select a design element to edit:', $this->_class),
				'objects' => array(
					'layout' => array(
						'type' => 'object',
						'label' => __('Layout &amp; Dimensions', $this->_class),
						'fields' => array(
							'columns' => array(
								'type' => 'select',
								'label' => __('Layout', $this->_class),
								'options' => array(
									1 => __('1 column', $this->_class),
									2 => __('2 columns', $this->_class)),
								'default' => 2,
								'dependents' => array(2)),
							'order' => array(
								'type' => 'radio',
								'options' => array(
									'' => __('Content on the left', $this->_class),
									'right' => __('Content on the right', $this->_class)),
								'parent' => array(
									'columns' => 2)),
							'width-content' => array(
								'type' => 'text',
								'width' => 'tiny',
								'label' => __('Content Width', $this->_class),
								'tooltip' => __('The default content column width is 617px. The value you enter here is the entire width of the column, including padding and borders. The resulting width of your text in this column is based on your selected font and font size. We recommend using Chrome Developer Tools or Firebug for Firefox to inspect the text width if you need to achieve a precise value.', $this->_class),
								'description' => 'px',
								'default' => 617),
							'width-sidebar' => array(
								'type' => 'text',
								'width' => 'tiny',
								'label' => __('Sidebar Width', $this->_class),
								'tooltip' => __('The default sidebar column width is 280px. The value you enter here is the entire width of the column, including padding. The resulting width of your text in this column is based on your selected font and font size. We recommend using Chrome Developer Tools or Firebug for Firefox to inspect the text width if you need to achieve a precise value.', $this->_class),
								'description' => 'px',
								'default' => 280,
								'parent' => array(
									'columns' => 2)))),
					'font' => array(
						'type' => 'object',
						'label' => __('Font &amp; Size (Primary)', $this->_class),
						'fields' => array(
							'family' => array_merge($css['font']['fields']['font-family'], array('default' => 'georgia')),
							'size' => array_merge($css['font']['fields']['font-size'], array('default' => 16)))),
					'headline' => array(
						'type' => 'group',
						'label' => __('Headlines', $this->_class),
						'fields' => $fsc),
					'subhead' => array(
						'type' => 'group',
						'label' => __('Sub-headlines', $this->_class),
						'fields' => $fsc),
					'blockquote' => array(
						'type' => 'group',
						'label' => __('Blockquotes', $this->_class),
						'fields' => $fsc),
					'code' => array(
						'type' => 'group',
						'label' => __('Code: Inline &lt;code&gt;', $this->_class),
						'fields' => $fsc),
					'pre' => array(
						'type' => 'group',
						'label' => __('Code: Pre-formatted &lt;pre&gt;', $this->_class),
						'fields' => $fsc),
					'title' => array(
						'type' => 'object',
						'label' => __('Site Title', $this->_class),
						'fields' => $fsc),
					'tagline' => array(
						'type' => 'group',
						'label' => __('Site Tagline', $this->_class),
						'fields' => $fsc),
					'menu' => array(
						'type' => 'object',
						'label' => __('Nav Menu', $this->_class),
						'fields' => $nav),
					'sidebar' => array(
						'type' => 'group',
						'label' => __('Sidebar', $this->_class),
						'fields' => $fsc),
					'sidebar_heading' => array(
						'type' => 'group',
						'label' => __('Sidebar Headings', $this->_class),
						'fields' => $fsc))));
	}

	public function css_variables() { // Skin API method for modifying CSS variables
		// return an array containing active variable references as keys (not all keys need be included) with their new values
		global $thesis;
		$columns = !empty($this->design['layout']['columns']) && is_numeric($this->design['layout']['columns']) ?
			$this->design['layout']['columns'] : 2;
		$order = !empty($this->design['layout']['order']) && $this->design['layout']['order'] == 'right' ? true : false;
		$px['w_content'] = !empty($this->design['layout']['width-content']) && is_numeric($this->design['layout']['width-content']) ?
			abs($this->design['layout']['width-content']) : 617;
		$px['w_sidebar'] = !empty($this->design['layout']['width-sidebar']) && is_numeric($this->design['layout']['width-sidebar']) ?
			abs($this->design['layout']['width-sidebar']) : 280;
		$px['w_total'] = $px['w_content'] + ($columns == 2 ? $px['w_sidebar'] : 0);
		$vars['font'] = $thesis->api->fonts->family($font = !empty($this->design['font']['family']) ? $this->design['font']['family'] : 'georgia');
		$s['content'] = !empty($this->design['font']['size']) ? $this->design['font']['size'] : 16;
		// Determine typographical scale based on primary font size
		$f['content'] = $thesis->api->typography->scale($s['content']);
		/*	The final line height, $h['content'], is calculated in 3 iterations:
			1. Get the optimal line height for the current font + size
			2. Get an adjusted line height using optimal spacing for the current font + size
			3. Adjust the line height a final time with adjusted spacing for the current font + size

			Both the line height, $h['content'], and layout spacing, $x['content'], are calculated below: */
		$x['content'] = $thesis->api->typography->space($h['content'] = $thesis->api->typography->height($s['content'], ($w['content'] = $px['w_content'] - ($adjust = round(2 * $thesis->api->typography->height($s['content'], $px['w_content'] - ($first = round(2 * $thesis->api->typography->height($s['content'], false, $font), 0)) - 1, $font), 0)) - 1), $font));
		// Determine sidebar font, size, typographical scale, and spacing
		$sidebar_font = !empty($this->design['sidebar']['font']) ? $this->design['sidebar']['font'] : $font;
		$s['sidebar'] = !empty($this->design['sidebar']['font-size']) && is_numeric($this->design['sidebar']['font-size']) ?
			$this->design['sidebar']['font-size'] : $f['content']['aux'];
		$f['sidebar'] = $thesis->api->typography->scale($s['sidebar']);
		$x['sidebar'] = $thesis->api->typography->space($h['sidebar'] = $thesis->api->typography->height($s['sidebar'], ($w['sidebar'] = $px['w_sidebar'] - 2 * $x['content']['single']), $sidebar_font));
		// Set up an array containing numerical values that require a unit for CSS output
		$px['f_text'] = $f['content']['text'];
		$px['f_aux'] = $f['content']['aux'];
		$px['f_subhead'] = $f['content']['subhead'];
		$px['h_text'] = round($h['content'], 0);
		$px['h_aux'] = round($thesis->api->typography->height($f['content']['aux'], $w['content'], $font), 0);
		foreach ($x['content'] as $dim => $value)
			$px["x_$dim"] = $value;
		foreach ($x['sidebar'] as $dim => $value)
			$px["s_x_$dim"] = $value;
		// Add the 'px' unit to the $px array constructed above
		$vars = is_array($px) ? array_merge($vars, $thesis->api->css->unit($px)) : $vars;
		// Use the Colors API to set up proper CSS color references
		foreach (array('text1', 'text2', 'links', 'color1', 'color2', 'color3') as $color)
			$vars[$color] = !empty($this->design[$color]) ? $thesis->api->colors->css($this->design[$color]) : false;
		// Set up a modification array for individual typograhical overrides
		$elements = array(
			'menu' => array(
				'font-family' => false,
				'font-size' => $f['content']['aux']),
			'title' => array(
				'font-family' => false,
				'font-size' => $f['content']['title']),
			'tagline' => array(
				'font-family' => false,
				'font-size' => $f['content']['text'],
				'color' => !empty($vars['text2']) ? $vars['text2'] : false),
			'headline' => array(
				'font-family' => false,
				'font-size' => $f['content']['headline']),
			'subhead' => array(
				'font-family' => false,
				'font-size' => $f['content']['subhead']),
			'blockquote' => array(
				'font-family' => false,
				'font-size' => false,
				'color' => !empty($vars['text2']) ? $vars['text2'] : false),
			'code' => array(
				'font-family' => 'consolas',
				'font-size' => false,
				'color' => false),
			'pre' => array(
				'font-family' => 'consolas',
				'font-size' => false,
				'color' => false),
			'sidebar' => array(
				'font-family' => false,
				'font-size' => $f['sidebar']['text'],
				'color' => false),
			'sidebar_heading' => array(
				'font-family' => false,
				'font-size' => $f['sidebar']['subhead'],
				'color' => false));
		// Loop through the modification array to see if any fonts, sizes, or colors need to be overridden
		foreach ($elements as $name => $element) {
			foreach ($element as $p => $def)
				$e[$name][$p] = $p == 'font-family' ?
					(!empty($this->design[$name][$p]) ?
						"$p: ". $thesis->api->fonts->family($family[$name] = $this->design[$name][$p]). ';' : (!empty($def) ?
						"$p: ". $thesis->api->fonts->family($family[$name] = $def). ';' : false)) : ($p == 'font-size' ?
					(!empty($this->design[$name][$p]) && is_numeric($this->design[$name][$p]) ?
						"$p: ". ($size[$name] = $this->design[$name][$p]). "px;" : (!empty($def) ?
						"$p: ". ($size[$name] = $def). "px;" : false)) : ($p == 'color' ?
					(!empty($this->design[$name][$p]) ?
						"$p: ". $thesis->api->colors->css($this->design[$name][$p]). ';' : (!empty($def) ?
						"$p: $def;" : false)) : false));
			$e[$name] = array_filter($e[$name]);
		}
		foreach (array_filter($e) as $name => $element)
			$vars[$name] = implode("\n\t", $element);
		// Override content elements
		foreach (array('headline', 'subhead', 'blockquote', 'pre') as $name)
			if (!empty($size[$name]))
				$vars[$name] .= "\n\tline-height: ". ($line[$name] = round($thesis->api->typography->height($size[$name], $w['content'], !empty($family[$name]) ? $family[$name] : $font), 0)). "px;";
		// Override sidebar elements
		foreach (array('sidebar', 'sidebar_heading') as $name)
			if (!empty($size[$name]))
				$vars[$name] .= "\n\tline-height: ". round($thesis->api->typography->height($size[$name], $w['sidebar'], !empty($family[$name]) ? $family[$name] : $sidebar_font), 0). "px;";
		// Determine multi-use color variables
		foreach (array('title', 'headline', 'subhead') as $name)
			$vars["{$name}_color"] = !empty($this->design[$name]['color']) ?
				$thesis->api->colors->css($this->design[$name]['color']) : (!empty($vars['text1']) ? $vars['text1'] : false);
		// Set up property-value variables, which, unlike the other variables above, contain more than just a CSS value
		$vars['column1'] =
			"float: ". ($columns == 2 ? ($order ? 'right' : 'left') : 'none'). ";\n\t".
			"border-width: ". ($columns == 2 ? ($order ? '0 0 0 1px' : '0 1px 0 0') : '0'). ";";
		$vars['column2'] =
			"width: ". ($columns == 2 ? '$w_sidebar' : '100%'). ";\n\t".
			"float: ". ($columns == 2 ? ($order ? 'left' : 'right') : 'none'). ';'. ($columns == 1 ?
			"\n\tborder-top: 3px double \$color1;" : '');
		$vars['submenu'] = round((!empty($size['menu']) ? $size['menu'] : $px['f_aux']) * 13 / 16, 4). "em";
		$vars['pullquote'] =
			"font-size: ". $f['content']['headline']. "px;\n\t".
			"line-height: ". round($thesis->api->typography->height($f['content']['headline'], round(0.45 * $w['content'], 0), !empty($family['blockquote']) ? $family['blockquote'] : $font), 0). "px;";
		$vars['avatar'] =
			"width: ". ($avatar = $line['headline'] + $px['h_aux']). "px;\n\t".
			"height: {$avatar}px;";
		$vars['comment_avatar'] =
			"width: ". (2 * $px['h_text']). "px;\n\t".
			"height: ". (2 * $px['h_text']). "px;";
		foreach (array(2, 3, 4) as $factor)
			if (($bio_size = $factor * $px['h_text']) <= 96)
				$bio = $bio_size;
		$vars['bio_avatar'] =
			"width: {$bio}px;\n\t".
			"height: {$bio}px;";
		return array_filter($vars); // Filter the array to remove any null elements
	}

	protected function header_image() { // Skin API method; return the width of the header image container
		return (!empty($this->design['layout']['width-content']) && is_numeric($this->design['layout']['width-content']) ?
			abs($this->design['layout']['width-content']) : 617) +
			(!empty($this->design['layout']['width-sidebar']) && is_numeric($this->design['layout']['width-sidebar']) ?
			abs($this->design['layout']['width-sidebar']) : 280);
	}

	public function filter_css($css) { // Skin API method for filtering the CSS output whenever the stylesheet is rewritten
		return $css. (!empty($this->header_image) ?
		"\n#header {\n".
		"\tpadding: 0;\n".
		"}\n".
		"#header #site_title a, #header #site_tagline {\n".
		"\tdisplay: none;\n".
		"}\n" : '');
	}

	public function meta_viewport() { // Skin API method for adding viewport meta to the HTML <head>
		return 'width=device-width';
	}

	/*---:[ custom Classic Skin filters below this line ]:---*/

	public function prev_next() {
		global $wp_query;
		return (($wp_query->is_home || $wp_query->is_archive || $wp_query->is_search) && $wp_query->max_num_pages > 1) || ($wp_query->is_single && !empty($this->display['misc']['display']['prev_next'])) ? true : false;
	}

	public function num_comments($content) {
		return "<span class=\"bracket\">{</span> $content <span class=\"bracket\">}</span>";
	}

	public function comments_intro($text) {
		return "<span class=\"bracket\">{</span> $text <span class=\"bracket\">}</span>";
	}
}