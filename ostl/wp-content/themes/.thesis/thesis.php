<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
final class thesis {
	public $version = '2.1';
	public $box_admin = array();

	public function __construct() {
		$this->constants();
		$this->environment = is_user_logged_in() && current_user_can('edit_theme_options') ?
			(!empty($_GET['thesis_editor']) && $_GET['thesis_editor'] === '1' ?
				'editor' : (!empty($_GET['thesis_canvas']) ?
				'canvas' : (is_admin() ?
				(defined('DOING_AJAX') && DOING_AJAX === true ?
					'ajax' : (!empty($_GET['page']) && $_GET['page'] == 'thesis' ?
					'thesis' :
					'admin')) :
				false))) :
			false;
		$this->wp_customize = is_user_logged_in() && (!empty($_REQUEST['wp_customize']) || $GLOBALS['pagenow'] === 'customize.php') ? true : false;
	}

	private function constants() {
		# Dirs
		define('THESIS_LIB', TEMPLATEPATH . '/lib');
		define('THESIS_ADMIN', THESIS_LIB . '/admin');
		define('THESIS_CORE', THESIS_LIB . '/core');
		define('THESIS_JS', THESIS_LIB . '/js');
		define('THESIS_SKINS', THESIS_LIB . '/skins');
		# URLs
		define('THESIS_URL', get_bloginfo('template_url')); #wp
		define('THESIS_CSS_URL', THESIS_URL . '/lib/css');
		define('THESIS_JS_URL', THESIS_URL . '/lib/js');
		define('THESIS_IMAGES_URL', THESIS_URL . '/lib/images');
		# User dirs
		define('THESIS_USER', WP_CONTENT_DIR . '/thesis');
		define('THESIS_USER_SKINS', THESIS_USER . '/skins');
		define('THESIS_USER_BOXES', THESIS_USER . '/boxes');
		define('THESIS_USER_PACKAGES', THESIS_USER . '/packages');
		# User URLs
		define('THESIS_USER_URL', content_url('thesis'));
		define('THESIS_USER_SKINS_URL', THESIS_USER_URL . '/skins');
		define('THESIS_USER_BOXES_URL', THESIS_USER_URL . '/boxes');
		define('THESIS_USER_PACKAGES_URL', THESIS_USER_URL . '/packages');
		if (is_multisite())
			define('THESIS_MS_CSS_VAL', substr(str_rot13(md5("ms-css_{$GLOBALS['blog_id']}")), 0, 5));
	}

	public function launch() {
		require_once(THESIS_CORE . '/api.php');
		require_once(THESIS_CORE . '/box.php');
		require_once(THESIS_CORE . '/manager.php');
		require_once(THESIS_CORE . '/skin.php');
		require_once(THESIS_CORE . '/skins.php');
		require_once(THESIS_CORE . '/wp.php');
		$this->api = new thesis_api;
		$this->wp = new thesis_wp;
		$this->skins = new thesis_skins;
		if (!empty($this->skins->skin['class']) && class_exists($this->skins->skin['class']) && is_subclass_of($this->skins->skin['class'], 'thesis_skin'))
			$this->skin = new $this->skins->skin['class']($this->skins->skin);
		if (is_admin()) {
			require_once(THESIS_ADMIN . '/admin.php');
			require_once(THESIS_ADMIN . '/filesystem.php');
			$this->admin = new thesis_admin;
		}
		if (defined('THESIS_USER_SKIN') && file_exists(THESIS_USER_SKIN . '/custom.php'))
			include_once(THESIS_USER_SKIN . '/custom.php');
		if (file_exists(THESIS_USER . '/master.php'))
			include_once(THESIS_USER . '/master.php');
		if (is_multisite()) {
			$nopriv = !is_user_logged_in() ? '_nopriv' : '';
			add_action("admin_post{$nopriv}_thesis_do_css", array($this, 'ms_css'));
		}
	}

	public function ms_css() {
		global $thesis;
		$css = get_option('thesis_raw_css') ? get_option('thesis_raw_css') : file_get_contents(THESIS_USER_SKIN . '/css.css');
		header('Content-Type: text/css', true, 200);
		printf('%s', strip_tags($css));
		exit;
	}
}
$thesis = new thesis; # Tee hee, sneaky!
$thesis->launch();