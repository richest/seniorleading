<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_wp {
	private $widgets = array(			// (array) core Thesis widget classes
		'thesis_search_widget',
		'thesis_widget_subscriptions',
		'thesis_widget_google_cse',
		'thesis_killer_recent_entries');

	public function __construct() {
		define('THESIS_WP', THESIS_CORE . '/wp');
		require_once(THESIS_WP . '/home_seo.php');
		require_once(THESIS_WP . '/post_meta.php');
		require_once(THESIS_WP . '/terms.php');
		require_once(THESIS_WP . '/widgets.php');
		$this->actions();
		$this->filters();
		$this->widgets();
		$this->home = new thesis_home_seo;
		$terms = new thesis_terms;
		$this->terms = $terms->terms;
		new thesis_dashboard_rss;
	}

	private function actions() {
		load_theme_textdomain('thesis', THESIS_LIB . '/languages');
		add_theme_support('menus');
		remove_action('wp_head', 'wp_generator');
		remove_action('wp_head', 'start_post_rel_link');
		remove_action('wp_head', 'index_rel_link');
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
		remove_action('wp_head', 'parent_post_rel_link');
		remove_action('wp_head', 'rel_canonical');
		remove_action('wp_head', 'wp_shortlink_wp_head');
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'wlwmanifest_link');
		add_action('thesis_hook_head', 'wp_head');
		add_action('thesis_hook_body_bottom', 'wp_footer');
		add_action('init', array($this, 'post_meta'));
	}

	private function filters() {
		add_filter('post_class', array($this, 'post_class'));
		$capital_P = array(
			'the_content',
			'the_title',
			'comment_text');
		foreach ($capital_P as $dangit)
			remove_filter($dangit, 'capital_P_dangit'); # Dagnabbit.
	}

	private function widgets() {
		if (is_array($this->widgets))
			foreach ($this->widgets as $widget)
				register_widget($widget);
	}

	public function post_meta() {
		global $pagenow; #wp
		if (is_admin() && in_array($pagenow, array('post.php', 'page.php', 'post-new.php'))) {
			add_action('init', array($this, 'init_post_meta'), 11);
			wp_enqueue_style('thesis-edit', THESIS_CSS_URL . '/edit.css'); #wp
			wp_enqueue_script('jquery-ui-core'); #wp
			wp_enqueue_script('thesis-edit', THESIS_JS_URL . '/edit.js'); #wp
		}
	}

	public function init_post_meta() {
		$this->post_meta = apply_filters('thesis_post_meta', array());
		$tabindex = 60;
		if (!is_array($this->post_meta)) return;
		foreach ($this->post_meta as $class => $meta) {
			new thesis_post_meta($class, $meta, $tabindex);
			$tabindex = $tabindex + 30;
		}
	}

	public function filter($content, $filters = array()) {
		if (empty($filters) || !is_array($filters)) return;
		foreach ($filters as $filter => $priority)
			if (!empty($priority) && is_numeric($priority))
				add_filter($content, $filter, $priority);
			else
				add_filter($content, $filter);
	}

	public function post_class($classes) {
		unset($classes[array_search('hentry', $classes)]);
		return $classes;
	}

	public function check($access = false) {
		$access = $access ? $access : 'edit_theme_options';
		if (!current_user_can($access))
			wp_die(__('Easy there, homeh. You don&#8217;t have admin privileges to change Thesis settings.', 'thesis'));
	}

	public function nonce($nonce, $action) {
		if (!$nonce || !$action)
			die(__('Your nonce check is incorrect. Check the nonce name and action and try again.', 'thesis'));
		if (!wp_verify_nonce($nonce, $action))
			die(__('Whoa, are you trying to hack this WordPress installation? If so, your pathetic attempt has been denied. If not, please try your action again :D', 'thesis'));
	}

	public function feed_url() {
		global $thesis;
		$feed_url = !empty($thesis->site->head['feed']['url']) ? stripslashes($thesis->site->head['feed']['url']) : get_bloginfo(get_default_feed() . '_url'); #wp
		return apply_filters('thesis_feed_url', $feed_url);
	}

	public function author($author_id, $field = false) { // fields: ID, user_login, user_nicename, display_name, user_email, user_url, user_registered, user_status
		if (!$author_id) return;
		$author = get_userdata($author_id); #wp
		return $field ? $author->data->$field : $author->data;
	}

	public function language_attributes() {
		$attributes = array();
		if ($dir = get_bloginfo('text_direction')) #wp
			$attributes[] = "dir=\"$dir\"";
		if ($lang = get_bloginfo('language')) #wp
			$attributes[] = "lang=\"$lang\"";
		$attributes = !empty($attributes) ? ' ' . implode(' ', $attributes) : '';
		return apply_filters('thesis_language_attributes', $attributes);
	}
}