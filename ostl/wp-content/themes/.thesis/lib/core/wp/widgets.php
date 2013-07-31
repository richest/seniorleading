<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_search_widget extends WP_Widget {
	function thesis_search_widget() {
		$widget_ops = array('classname' => 'search-form', 'description' => __('The WordPress search form with helpful options that make it more flexible.', 'thesis'));
		$control_ops = array('id_base' => 'thesis-search-widget');
		$this->WP_Widget('thesis-search-widget', __('Thesis &raquo; Search Widget', 'thesis'), $widget_ops, $control_ops);
	}

	function widget($args, $instance) {
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		$default_value = $instance['default_value'];
		$type = isset($instance['show_submit']) ? 'submit' : 'hidden';
		$submit_value = $instance['submit_value'];
		echo
			"$before_widget\n",
			($title ? $before_title . esc_attr($title) . "$after_title\n" : ''),
			"<form class=\"search_form\" method=\"get\" action=\"", home_url(), "\">\n",
			"\t<p>\n",
			"\t\t<input class=\"input_text\" type=\"text\" id=\"s\" name=\"s\" value=\"", esc_html($default_value), "\" onfocus=\"if (this.value == '", esc_html($default_value), "') {this.value = '';}\" onblur=\"if (this.value == '') {this.value = '", esc_html($default_value), "';}\" />\n",
			"\t\t<input type=\"$type\" id=\"searchsubmit\" value=\"", esc_attr($submit_value), "\" />\n",
			"\t</p>\n",
			"</form>\n",
			"$after_widget\n";
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = (string) strip_tags(stripslashes($new_instance['title']));
		$instance['default_value'] = (string) strip_tags(stripslashes($new_instance['default_value']));
		$instance['show_submit'] = $new_instance['show_submit'];
		$instance['submit_value'] = $new_instance['submit_value'];
		if (get_option('thesis_widget_search'))
			delete_option('thesis_widget_search');
		return $instance;
	}

	function form($instance){
		global $thesis;
		$old_option = get_option('thesis_widget_search');
		$title = !empty($old_option['thesis-search-title']) ? $old_option['thesis-search-title'] : '';
		$field_value = apply_filters('thesis_search_form_value', __('To search, type and hit enter', 'thesis'));
		$defaults = array(
			'title' => $title,
			'default_value' => $field_value,
			'show_submit' => 'false',
			'submit_value' => $thesis->api->strings['search']);
		$instance = wp_parse_args((array) $instance, $defaults);
		echo
			"<p>\n",
			"\t<label for=\"", $this->get_field_id('title'), "\">", __('Title', 'thesis'), "</label>\n",
			"\t<input type=\"text\" id=\"", $this->get_field_id('title'), "\" name=\"", $this->get_field_name('title'), "\" value=\"", esc_attr($instance['title']), "\" style=\"width:90%;\" />\n",
			"</p>\n",
			"<p>\n",
			"\t<label for=\"", $this->get_field_id('default_value'), "\">", __('Search Field Text:', 'thesis'), "</label>\n",
			"\t<input type=\"text\" id=\"", $this->get_field_id('default_value'), "\" name=\"", $this->get_field_name('default_value'), "\" value=\"", esc_attr($instance['default_value']), "\" style=\"width:90%;\" />\n",
			"</p>\n",
			"<p>\n",
			"\t<input type=\"checkbox\" class=\"checkbox\" ", checked( $instance['show_submit'], 'true' ), " id=\"", $this->get_field_id('show_submit'), "\" name=\"", $this->get_field_name('show_submit'), "\" value=\"true\" />\n",
			"\t<label for=\"", $this->get_field_id('show_submit'), "\">", __('Display Submit Button', 'thesis'), "</label>\n",
			"</p>\n",
			"<p>\n",
			"\t<label for=\"", $this->get_field_id('submit_value'), "\">", $thesis->api->strings['submit_button_text'], ':', "</label>\n",
			"\t<input type=\"text\" id=\"", $this->get_field_id('submit_value'), "\" name=\"", $this->get_field_name('submit_value'), "\" value=\"", esc_attr($instance['submit_value']), "\" style=\"width:90%;\" />\n",
			"</p>\n";
	}
}

class thesis_widget_subscriptions extends WP_Widget {
	function thesis_widget_subscriptions() {
		$widget_ops = array(
			'classname' => 'thesis_widget_subscriptions',
			'description' => __('Provide visitors to your site a link to your RSS feed, a description of your RSS subscription options, and information about how to contact you via email.', 'thesis'));
		$control_ops = array(
			'id_base' => 'thesis-subscriptions');
		$this->WP_Widget('thesis-subscriptions', __('Thesis &raquo; Subscriptions', 'thesis'), $widget_ops, $control_ops);
	}

	function widget($args, $instance) {
		global $thesis;
		extract($args);
		$list = '';
		if (!empty($instance['rss_text']) || !empty($instance['email']))
			$list =
				"<ul>\n".
				(!empty($instance['rss_text']) ?
				"\t<li class=\"sub_rss\"><a href=\"" . esc_url($thesis->wp->feed_url()) . '">' . esc_attr__($instance['rss_text']) . "</a></li>\n" : '').
				(!empty($instance['email']) ?
				"\t<li class=\"sub_email\">" . $instance['email'] . "</li>\n" : '').
				"</ul>\n";
		echo
			"$before_widget\n",
			$before_title, esc_attr__($instance['title']), "$after_title\n",
			(!empty($instance['description']) ?
			"<p>" . wp_kses_data($instance['description']) . "</p>\n" : ''),
			$list,
			"$after_widget\n";
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = sprintf('%s', strip_tags(stripslashes($new_instance['title'])));
		$instance['description'] = sprintf('%s', wp_kses_data($new_instance['description']));
		$instance['rss_text'] = sprintf('%s', strip_tags(stripslashes($new_instance['rss_text'])));
		$instance['email'] = sprintf('%s', wp_kses_data($new_instance['email']));
		if (get_option('thesis_widget_subscriptions'))
			delete_option('thesis_widget_subscriptions');
		return $instance;
	}

	function form($instance) {
		$old_options = get_option('thesis_widget_subscriptions');
		$title = !empty($old_options['thesis-subscriptions-title']) ? $old_options['thesis-subscriptions-title'] : '';
		$description = !empty($old_options['thesis-subscriptions-description']) ? $old_options['thesis-subscriptions-description'] : '';
		$rss_text = !empty($old_options['thesis-subscriptions-rss-text']) ? $old_options['thesis-subscriptions-rss-text'] : '';
		$email = !empty($old_options['thesis-subscriptions-email']) ? $old_options['thesis-subscriptions-email'] : '';
		$defaults = array(
			'title' => $title,
			'description' => $description,
			'rss_text' => $rss_text,
			'email' => $email);
		$instance = wp_parse_args((array) $instance, $defaults);
		echo
			"<p>\n",
			"\t<label for=\"", $this->get_field_id('title'), "\">", __('Title:', 'thesis'), "</label>\n",
			"\t<input class=\"widefat\" type=\"text\" id=\"", $this->get_field_id('title'), "\" name=\"", $this->get_field_name('title'), "\" value=\"", esc_attr($instance['title']), "\" />\n",
			"</p>\n",
			"<p>\n",
			"\t<label for=\"", $this->get_field_id('description'), "\">", __('Describe your subscription options:', 'thesis'), "</label>\n",
			"\t<textarea class=\"widefat\" rows=\"8\" cols=\"10\" id=\"", $this->get_field_id('description'), "\" name=\"", $this->get_field_name('description'), "\">", esc_textarea($instance['description']), "</textarea>\n",
			"</p>\n",
			"<p>\n",
			"\t<label for=\"", $this->get_field_id('rss_text'), "\">", __('<acronym title="Really Simple Syndication">RSS</acronym> link text:', 'thesis'), "</label>\n",
			"\t<input class=\"widefat\" type=\"text\" id=\"", $this->get_field_id('rss_text'), "\" name=\"", $this->get_field_name('rss_text'), "\" value=\"", esc_attr($instance['rss_text']), "\" />\n",
			"</p>\n",
			"<p>\n",
			"\t<label for=\"", $this->get_field_id('email'), "\">", __('Email link and text', 'thesis'), "</label>\n",
			"\t<textarea class=\"widefat\" rows=\"8\" cols=\"10\" id=\"", $this->get_field_id('email'), "\" name=\"", $this->get_field_name('email'), "\">", esc_textarea($instance['email']), "</textarea>\n",
			"</p>\n";	
	}
}

class thesis_widget_google_cse extends WP_Widget {
	function thesis_widget_google_cse() {
		$widget_ops = array(
			'classname' => 'thesis_widget_google_cse',
			'description' => __('Add Google Custom Search to your site by pasting your code here.', 'thesis'));
		$control_ops = array(
			'id_base' => 'thesis-google-cse');
		$this->WP_Widget('thesis-google-cse', __('Thesis &raquo; Google Custom Search', 'thesis'), $widget_ops, $control_ops);
	}

	function widget($args, $instance) {
		extract($args);
		$title = $instance['title'];
		$code = $instance['code'];
		if ($code)
			echo
				"$before_widget\n",
				($title ?
				$before_title . esc_attr__($title) . "$after_title\n" : ''),
				stripslashes($code), "\n",
				"$after_widget\n";
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = sprintf('%s', strip_tags(stripslashes($new_instance['title'])));
		$instance['code'] = $new_instance['code'];
		if (get_option('thesis_widget_google_cse'))
			delete_option('thesis_widget_google_cse');
		return $instance;
	}

	function form($instance) {
		$old_options = get_option('thesis_widget_google_cse');
		$title = !empty($old_options['thesis-google-cse-title']) ? $old_options['thesis-google-cse-title'] : '';
		$code = !empty($old_options['thesis-google-cse-code']) ? $old_options['thesis-google-cse-code'] : '';
		$defaults = array(
			'title' => $title,
			'code' => $code);
		$instance = wp_parse_args((array) $instance, $defaults);
		echo
			"<p>\n",
			"\t<label for=\"", $this->get_field_id('title'), "\">", __('Title:', 'thesis'), "</label>\n",
			"\t<input class=\"widefat\" type=\"text\" id=\"", $this->get_field_id('title'), "\" name=\"", $this->get_field_name('title'), "\" value=\"", esc_attr($instance['title']), "\" />\n",
			"</p>\n",
			"<p>\n",
			"\t<label for=\"", $this->get_field_id('code'), "\">", __('Google Custom Search Code', 'thesis'), "</label>\n",
			"\t<textarea class=\"widefat\" rows=\"8\" cols=\"10\" id=\"", $this->get_field_id('code'), "\" name=\"", $this->get_field_name('code'), "\">", esc_textarea($instance['code']), "</textarea>\n",
			"</p>\n";
	}
}

class thesis_killer_recent_entries extends WP_Widget {
	function thesis_killer_recent_entries() {
		$widget_ops = array(
			'classname' => 'thesis-killer-recent-entries',
			'description' => __('Add a customizable list of recent posts from any category on your site.', 'thesis'));
		$control_ops = array(
			'id_base' => 'thesis-killer-recent-entries');
		$this->WP_Widget('thesis-killer-recent-entries', __('Thesis &raquo; Killer Recent Entries', 'thesis'), $widget_ops, $control_ops);
	}

	function widget($args, $instance) {
		global $posts;
		extract($args);
		$entries = '';
		if (empty($instance['title'])) {
			if (!is_int($instance['cat'])) # all cats selected
				$title = __('More Recent Posts', 'thesis');
			else { # a cat has been selected, but keine title so we use the cat name
				$cat_info = get_term((int) $instance['cat'], 'category');
				$title = __($cat_info->name, 'thesis');
			}
		}
		else # title was input by user
			$title = $instance['title'];
		$offset = is_home() && $instance['cat'] == 'all' ? count($posts) : 0;
		$num = (int) $instance['numposts'];
		$cat_num = ($instance['cat'] == 'all' ? null : (int) $instance['cat']); # "all" if all, some integer if a specific cat
		$comms = (int) $instance['comments'];
		$thesis_kre_args = array(
			'offset' => $offset,
			'posts_per_page' => $num,
			'cat' => $cat_num);
		$thesis_kre_query = new WP_Query($thesis_kre_args);
		$out = $before_widget . $before_title . esc_attr($title) . $after_title . "<ul>";
		while ($thesis_kre_query->have_posts()) {
			$thesis_kre_query->the_post();
			$comments_number = (int) get_comments_number();
			$entries .=
				"<li><a href=\"" . esc_url(get_permalink($thesis_kre_query->post->ID)) . "\" title=\"" . __('Click to read ', 'thesis') . esc_attr__($thesis_kre_query->post->post_title, 'thesis') . "\" rel=\"bookmark\">" . esc_attr__($thesis_kre_query->post->post_title, 'thesis') . "</a>".
				($comms == 1 ?
				" <a href=\"" . esc_url(get_permalink($thesis_kre_query->post->ID)) . "#comments\"><span class=\"num_comments\" title=\"$comments_number ".
				($comments_number == 1 ?
				__("comment", 'thesis') : __("comments", 'thesis')).
				__(' on this post', 'thesis') . "\">$comments_number</span></a>" : '').
				"</li>\n";
		}
		echo
			"$before_widget\n",
			$before_title, esc_attr($title), "$after_title\n",
			"<ul>\n",
			$entries,
			"</ul>\n",
			"$after_widget\n";
		wp_reset_query();
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = sprintf('%s', strip_tags(stripslashes(trim($new_instance['title']))));
		$instance['numposts'] = sprintf('%d', (int) $new_instance['numposts']);
		$instance['cat'] = (is_int($instance['cat']) ? sprintf('%d', (int) $new_instance['cat']) : sprintf('%s', (string) $new_instance['cat']));
		$instance['comments'] = sprintf('%d', (int) $new_instance['comments']);
		return $instance;
	}

	function form($instance) {
		$this->handle_old_kre_instances();
		$defaults = array(
			'title' => null,
			'numposts' => 5,
			'cat' => null,
			'comments' => 0);
		$cat_options = $numposts_options = '';
		$instance = wp_parse_args((array) $instance, $defaults);
		$cats = get_categories();
		$all_cats = empty($instance['cat']) || !is_int($instance['cat']) ? ' selected="selected"' : '';
		foreach ($cats as $category) {
			$selected = $category->cat_ID == $instance['cat'] ? ' selected="selected"' : '';
			$cat_options .= "\t<option value=\"" . intval($category->cat_ID) . "\"$selected>" . __(esc_attr($category->name)) . "</option>\n";
		}
		for ($i = 1; $i <= 20; $i++) {
			$selected_n = $instance['numposts'] == $i ? ' selected="selected"' : '';
			$numposts_options .= "\t<option value=\"$i\"$selected_n>$i</option>\n";
		}
		echo
			"<p>\n",
			"\t<label for=\"", $this->get_field_id('title'), "\">", __('Title:', 'thesis'), "</label>\n",
			"\t<input class=\"widefat\" type=\"text\" id=\"", $this->get_field_id('title'), "\" name=\"", $this->get_field_name('title'), "\" value=\"", esc_attr($instance['title']), "\" />\n",
			"</p>\n",
			"<p>\n",
			"\t<label for=\"", $this->get_field_id('cat'), "\">", __('Show posts from this category:', 'thesis'), "</label>\n",
			"\t<select id=\"", $this->get_field_id('cat'), "\" name=\"", $this->get_field_name('cat'), "\" size=\"1\">\n",
			"\t\t<option value=\"all\"{$all_cats}>", __('All recent posts'), "</option>\n",
			$cat_options,
			"\t</select>\n",
			"</p>\n",
			"<p>\n",
			"\t<label for=\"", $this->get_field_id('numposts'), "\">", __('Number of posts to show:', 'thesis'), "</label>\n",
			"\t<select id=\"", $this->get_field_id('numposts'), "\" name=\"", $this->get_field_name('numposts'), "\" size=\"1\">\n",
			$numposts_options,
			"\t</select>\n",
			"</p>\n",
			"<p>\n",
			"\t<label for=\"", $this->get_field_id('comments'), "\">", __('Show number of comments?', 'thesis'), "</label>\n",
			"\t<input type=\"checkbox\" ", checked($instance['comments'], 1), " id=\"", $this->get_field_id('comments'), "\" name=\"", $this->get_field_name('comments'), "\" value=\"1\" />\n",
			"</p>\n";
	}

	function handle_old_kre_instances(){
		$old_instances = get_option('widget_killer_recent_entries');
		if ($old_instances) {
			$current_widgets = get_option('sidebars_widgets'); # all the widgets considered active. could be in "inacitve widgets" area
			$current_widgets_freeze = $current_widgets; # we're gonna get a diff in a few lines
			$settings = array();
			$categories = get_categories(); #wp # it is not cool to use strings when specifying categories. old thesis used cat->slug, so we are changing them to cat->cat_ID
			$cats_reduced = array(); 
			foreach ($categories as $cat)
				$cats_reduced[$cat->cat_ID] = $cat->slug; # making a smaller array to search
			foreach ($old_instances as $number => $old_settings) {
				$new_cat = array_search($old_settings['category'], $cats_reduced);
				if ($new_cat == false)
					$new_cat = 'all';
				$settings[$number] = array(
					'title' => $old_settings['title'],
					'numposts' => $old_settings['numposts'],
					'cat' => $new_cat,
					'comments' => $old_settings['comments']);
				$current_widgets['wp_inactive_widgets'][] = "thesis-killer-recent-entries-$number"; # adds *new* inactive KRE widgets to "inactive widgets" sidebar
			}
			$this->save_settings($settings);
			update_option('sidebars_widgets', $current_widgets);
			delete_option('widget_killer_recent_entries'); # I thought we'd never get here ;)
		}
	}
}

class thesis_dashboard_rss {
	private $feed = 'http://diythemes.com/thesis/feed/';

	function __construct() {
		add_action('wp_dashboard_setup', array($this, 'add'));
	}

	function add() {
		add_meta_box('thesis_news_widget', __('The latest from the <strong>DIY</strong>themes Blog', 'thesis'), array($this, 'widget'), 'dashboard', 'normal', 'high');
	}

	function widget() {
		$items = '';
		$rss = fetch_feed($this->feed);
		if (!is_wp_error($rss)) {
			$max_items = $rss->get_item_quantity(5);
			$rss_items = $rss->get_items(0, $max_items);
		}
		if (!empty($rss_items)) {
			$date_format = get_option('date_format');
			foreach ($rss_items as $item)
				$items .= "\t\t<li><a class=\"rsswidget\" href=\"" . esc_url($item->get_permalink()) . "\" title=\"" . esc_attr__($item->get_description(), 'thesis') . "\">" . esc_attr__($item->get_title(), 'thesis') . "</a> <span class=\"rss-date\">" . esc_attr__($item->get_date($date_format), 'thesis') . "</span></li>\n";
		}
		else
			$items .= "\t\t<li><a href=\"$this->feed\">" . __('Check out the <strong>DIY</strong>themes blog!', 'thesis') . "</a></li>\n";
		echo
			"<div class=\"rss-widget rss-thesis\">\n",
			"\t<ul>\n",
			$items,
			"\t</ul>\n",
			"</div>\n";
	}
}