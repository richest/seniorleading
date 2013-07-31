<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_css {
	private $css;			// (string) skin CSS
	private $custom;		// (string) custom CSS
	private $packages;		// (object) CSS package controller
	private $vars;			// (object) CSS variable controller

	public function __construct($args) {
		global $thesis;
		if (!is_array($args)) return;
		if (!defined('THESIS_CSS'))
			define('THESIS_CSS', THESIS_SKIN . '/css');
		require_once(THESIS_CSS . '/packages.php');
		require_once(THESIS_CSS . '/variables.php');
		$thesis->api->css();
		extract($args); // $css, $custom, $packages, $user_packages, $vars
		$this->css = !empty($css) ? $css : '';
		$this->custom = !empty($custom) ? $custom : '';
		$this->packages = new thesis_packages(!empty($packages) && is_array($packages) ? $packages : array(), !empty($user_packages) && is_array($user_packages) ? $user_packages : false);
		$this->vars = new thesis_css_variables($vars);
		add_action('thesis_init_editor', array($this, 'init_editor'));
		add_action('thesis_init_canvas', array($this, 'init_canvas'));
		if ($thesis->environment == 'ajax')
			add_action('wp_ajax_live_css', array($this, 'live'));
	}

	public function init_editor() {
		add_action('thesis_editor_head', array($this, 'editor_head'));
		add_action('thesis_editor_scripts', array($this, 'editor_scripts'));
	}

	public function editor_head() {
		global $thesis;
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . THESIS_CSS_URL . "/css.css?ver={$thesis->version}\" />\n";
	}

	public function editor_scripts() {
		global $thesis;
		$scripts = array(
			'css' => THESIS_JS_URL . '/css.js',
			'js-color' => THESIS_JS_URL . '/jscolor/jscolor.js');
		foreach ($scripts as $script => $src)
			echo "<script src=\"$src?ver={$thesis->version}\"></script>\n";
	}

	public function editor() {
		global $thesis;
		return
//			"\t\t<div id=\"t_css_area\" data-style=\"box\">\n".
			"\t\t<div id=\"t_css_header\" data-style=\"box\">\n".
			"\t\t\t" . wp_nonce_field('thesis-save-css', '_wpnonce-thesis-save-css', true, false) . "\n".
			"\t\t\t<button id=\"t_save_css\" data-style=\"button save\">" . sprintf(__('%s CSS', 'thesis'), $thesis->api->strings['save']) . "</button>\n".
			"\t\t\t<ul>\n".
			"\t\t\t\t<li data-pane=\"skin\">" . sprintf(__('%1$s %2$s', 'thesis'), $thesis->api->strings['skin'], $thesis->api->base['css']) . "</li>\n".
			"\t\t\t\t<li data-pane=\"custom\">" . sprintf(__('%1$s %2$s', 'thesis'), $thesis->api->strings['custom'], $thesis->api->base['css']) . "</li>\n".
			"\t\t\t</ul>\n".
			"\t\t</div>\n".
			"\t\t<div id=\"t_css_area\" data-style=\"box\">\n".
			"\t\t\t<div class=\"pane pane_skin\">\n".
			"\t\t\t\t<textarea id=\"t_css_skin\" class=\"t_css_input\" data-style=\"box input\" spellcheck=\"false\">". esc_textarea($this->css) ."</textarea>\n".
			"\t\t\t</div>\n".
			"\t\t\t<div class=\"pane pane_custom\">\n".
			"\t\t\t\t<textarea id=\"t_css_custom\" class=\"t_css_input\" data-style=\"box input\" spellcheck=\"false\">". esc_textarea($this->custom) ."</textarea>\n".
			"\t\t\t</div>\n".
			"\t\t</div>\n".
			"\t\t<div id=\"t_css_items\" data-style=\"box\">\n".
			"\t\t\t<div id=\"t_packages\" class=\"t_items\" data-style=\"box\">\n".
			"\t\t\t\t<h3>{$thesis->api->strings['packages']}</h3>\n".
			$this->packages->items(4).
			"\t\t\t</div>\n".
			"\t\t\t<div id=\"t_vars\" class=\"t_items\" data-style=\"box\">\n".
			"\t\t\t\t<h3>{$thesis->api->strings['variables']} <button id=\"t_create_var\" data-style=\"button action\" data-type=\"var\">" . sprintf(__('%1$s %2$s', 'thesis'), $thesis->api->strings['create'], $thesis->api->strings['variable']) . "</button></h3>\n".
			$this->vars->items(4).
			"\t\t\t</div>\n".
			"\t\t</div>\n".
			"\t\t<div id=\"t_css_popup\" class=\"t_popup force_trigger\">\n".
			"\t\t\t<div class=\"t_popup_html\">\n".
			"\t\t\t</div>\n".
			"\t\t</div>\n";
	}

	public function init_canvas() {
		add_action('thesis_hook_head', array($this, 'canvas_head'));
	}

	public function canvas_head() {
		echo
			"<style type=\"text/css\">\n".
			$this->reset().
			"</style>\n".
			"<style id=\"t_live_css\" type=\"text/css\">\n".
			$this->update($this->css, $this->custom, true).
			"\n</style>\n";
	}

	public function live() {
		global $thesis;
		$thesis->wp->nonce($_POST['nonce'], 'thesis-save-css');
		$skin = !empty($_POST['skin']) ? $_POST['skin'] : '';
		$custom = !empty($_POST['custom']) ? $_POST['custom'] : '';
		echo $this->update($skin, $custom, true);
		if ($thesis->environment == 'ajax') die();
	}

	private function update($skin = false, $custom = false, $editor = false) {
		$skin = $skin ? $skin : '';
		$custom = $custom ? $custom : '';
		$css = $skin . (!empty($custom) ? "\n$custom" : '');
		if (empty($css)) return '';
		$clearfix = array();
		extract($this->packages->css($css));
		$css = $this->vars->css(stripslashes($css));
		if ($editor)
			$css = preg_replace('/url\(\s*(\'|")images\/([\w-\.]+)(\'|")\s*\)/', 'url(${1}'. THESIS_USER_SKIN_URL .'/images/${2}${1})', $css);
		return $css . (!empty($clearfix) ? $this->clearfix($clearfix) : '');
	}

	public function save_package($pkg) {
		return !is_array($pkg) ? false : (is_array($packages = $this->packages->save($pkg)) ? $packages : false);
	}

	public function delete_package($pkg) {
		return !is_array($pkg) ? false : (is_array($packages = $this->packages->delete($pkg)) ? $packages : false);
	}

	public function save_variable($item) {
		return !is_array($item) ? false : (is_array($save = $this->vars->save($item)) ? $save : false);
	}

	public function delete_variable($item) {
		return !is_array($item) ? false : (is_array($save = $this->vars->delete($item)) ? $save : false);
	}

	public function write($skin, $custom) {
		$css = $this->reset() . $this->update(apply_filters('thesis_css', $skin), $custom);
		$css = strip_tags($css);
		if (is_multisite()) {
			update_option('thesis_raw_css', $css);
			wp_cache_flush();
		}
		else {
			$lid = @fopen(THESIS_USER_SKIN . '/css.css', 'w');
			@fwrite($lid, trim($css));
			@fclose($lid);
		}
	}

	private function reset() {
		return
			"* { margin: 0; padding: 0; }\n".
			"h1, h2, h3, h4, h5, h6 { font-weight: normal; }\n".
			"table { border-collapse: collapse; border-spacing: 0; }\n".
			"img, abbr, acronym, fieldset { border: 0; }\n".
			"code { line-height: 1em; }\n".
			"pre { overflow: auto; clear: both; }\n".
			"sub, sup { line-height: 0.5em; }\n".
			".post_image, .thumb { display: block; }\n".
			".alignleft, .left, img[align=\"left\"] { display: block; float: left; }\n".
			".alignright, .right, img[align=\"right\"] { display: block; float: right; }\n".
			".aligncenter, .center, img[align=\"middle\"] { display: block; margin-right: auto; margin-left: auto; float: none; clear: both; }\n".
			".alignnone, .block { display: block; clear: both; }\n";
	}

	private function clearfix($clearfix) {
		if (empty($clearfix) || !is_array($clearfix)) return;
		$clear = array();
		foreach ($clearfix as $selector)
			$clear[] = "$selector:after";
		return "\n" . implode(', ', $clear) . ' { content: "."; display: block; height: 0; clear: both; visibility: hidden; }';
	}
}