<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_css {
	private $css;			// (string) skin CSS
	private $custom;		// (string) custom CSS
	private $packages;		// (object) CSS package controller
	private $vars;			// (object) CSS variable controller

	public function __construct($args) {
		global $thesis;
		if (!is_array($args)) return;
		if (!defined('THESIS_CSS'))
			define('THESIS_CSS', THESIS_SKIN. '/css');
		require_once(THESIS_CSS. '/packages.php');
		require_once(THESIS_CSS. '/variables.php');
		extract($args); // $css, $custom, $packages, $user_packages, $vars
		$this->css = !empty($css) ? $css : '';
		$this->custom = !empty($custom) ? $custom : '';
		$this->packages = new thesis_packages(!empty($packages) && is_array($packages) ? $packages : array(), !empty($user_packages) && is_array($user_packages) ? $user_packages : false);
		$this->vars = new thesis_css_variables($vars);
		$this->preprocessor = isset($preprocessor) ? $preprocessor : false;
		if ($thesis->environment == 'thesis') {
			add_filter('thesis_skin_menu', array($this, 'menu'), 11);
			if (!empty($_GET['canvas']) && $_GET['canvas'] === 'custom_css') {
				add_action('admin_init', array($this, 'admin_init'));
				add_action('thesis_admin_canvas', array($this, 'admin'));
				add_action('admin_head', array($this, 'canvas'));
			}
		}
		if ($thesis->environment == 'editor')
			add_action('thesis_init_editor', array($this, 'init_editor'));
		elseif ($thesis->environment == 'canvas')
			add_action('thesis_init_canvas', array($this, 'init_canvas'));
		elseif ($thesis->environment == 'ajax')
			add_action('wp_ajax_live_css', array($this, 'live'));
	}

	public function menu($menu) {
		$add['custom_css'] = array(
			'text' => __('Custom CSS', 'thesis'),
			'url' => admin_url('admin.php?page=thesis&canvas=custom_css'));
		return is_array($menu) ? array_merge($menu, $add) : $add;
	}

	public function admin_init() {
		global $thesis;
		wp_enqueue_style('thesis-options');
		wp_enqueue_style('custom-css', THESIS_CSS_URL . '/custom.css', array('thesis-options'), $thesis->version);
		wp_enqueue_style('codemirror', THESIS_CSS_URL . '/codemirror.css', array('thesis-options'), $thesis->version);
		wp_enqueue_script('custom-css', THESIS_JS_URL . '/custom.js', array('jquery'), $thesis->version);
		wp_enqueue_script('codemirror', THESIS_JS_URL . '/codemirror.js', array(), $thesis->version);
	}

	public function admin() {
		global $thesis;
		$css = strlen(rtrim(htmlspecialchars($this->custom, ENT_QUOTES))) < 1 ? "\r" : rtrim(htmlspecialchars($this->custom, ENT_QUOTES));
		$preprocessor = in_array($this->preprocessor, array(false, 'thesis')) ? __('Thesis', 'thesis') : strtoupper($this->preprocessor);
		echo
			"\t\t<h3 title=\"", sprintf(__("Current Skin: %s\nCSS Preprocessor: %s", 'thesis'), esc_attr($thesis->skins->skin['name']), esc_attr($preprocessor)), "\">", sprintf(__('Custom %s', 'thesis'), $thesis->api->base['css']),
				" <span id=\"t_canvas_launch\" data-style=\"button action\">", __('Live Preview', 'thesis'), "</span></h3>\n",
			"\t\t<form method=\"post\" action=\"", admin_url('admin-post.php?action=thesis_save_custom_css'), "\">\n",
			"\t\t\t<input type=\"submit\" data-style=\"button save\" class=\"t_save\" id=\"t_save_css\" name=\"submit\" value=\"", __('Save Custom CSS', 'thesis'), "\" />\n",
			wp_nonce_field('thesis-save-css', 'nonce', true, false),
			"\t\t</form>\n",
			"\t\t<div class=\"slideout_area\">\n",
			"\t\t\t<textarea id=\"t_css_custom\" class=\"t_css_input language-css\" data-style=\"box\" spellcheck=\"false\">$css</textarea>\n",
			"\t\t\t<span class=\"slideout_toggle\">&#43;</span>\n",
			"\t\t\t<div class=\"slideout\">\n",
			"\t\t\t\t<h4>", __('Variables', 'thesis'), "</h4>\n",
			$this->vars->items(4),
			"\t\t\t</div>\n",
			"\t\t\t<div id=\"t_flyout\" class=\"t_ajax_alert\"><div class=\"t_message\"><p></p></div></div>\n",
			"\t\t</div>\n";
	}

	public function init_editor() {
		add_action('thesis_editor_head', array($this, 'editor_head'));
		add_action('thesis_editor_scripts', array($this, 'editor_scripts'));
	}

	public function editor_head() {
		global $thesis;
		echo
			"<link rel=\"stylesheet\" type=\"text/css\" href=\"", THESIS_CSS_URL, "/css.css?ver={$thesis->version}\" />\n",
			"<link rel=\"stylesheet\" type=\"text/css\" href=\"", THESIS_CSS_URL, "/codemirror.css?ver={$thesis->version}\" />\n";
	}

	public function editor_scripts() {
		global $thesis;
		$scripts = array(
			'css' => THESIS_JS_URL. '/css.js',
			'codemirror' => THESIS_JS_URL. '/codemirror.js',
			'js-color' => THESIS_JS_URL. '/jscolor/jscolor.js');
		foreach ($scripts as $script => $src)
			echo "<script src=\"$src?ver={$thesis->version}\"></script>\n";
	}

	public function editor() {
		global $thesis;
		return
			"\t\t<h3>". sprintf(__('Skin %s', 'thesis'), $thesis->api->base['css']). "</h3>\n".
			"\t\t<div id=\"t_css_area\" data-style=\"box\">\n".
			"\t\t\t\t<textarea id=\"t_css_skin\" class=\"t_css_input css_droppable language-css\" data-style=\"box\" spellcheck=\"false\">".
			(strlen(rtrim(htmlspecialchars($this->css, ENT_QUOTES))) < 1 ?
			"" : rtrim(htmlspecialchars(stripslashes($this->css), ENT_QUOTES))).
			"</textarea>\n".
			"\t\t</div>\n".
			"\t\t<div id=\"t_css_items\" data-style=\"box\">\n".
			"\t\t\t<div id=\"t_vars\" class=\"t_items\" data-style=\"box\">\n".
			"\t\t\t\t<h3>{$thesis->api->strings['variables']} <button id=\"t_create_var\" data-style=\"button action\" data-type=\"var\">". sprintf(__('%1$s %2$s', 'thesis'), $thesis->api->strings['create'], $thesis->api->strings['variable']). "</button></h3>\n".
			$this->vars->items(4).
			"\t\t\t</div>\n".
			(in_array($this->preprocessor, array(false, 'thesis')) ?
			"\t\t\t<div id=\"t_packages\" class=\"t_items\" data-style=\"box\">\n".
			"\t\t\t\t<h3>{$thesis->api->strings['packages']}</h3>\n".
			$this->packages->items(4).
			"\t\t\t</div>\n" : '').
			"\t\t</div>\n".
			"\t\t<div id=\"t_css_popup\" class=\"t_popup force_trigger\">\n".
			"\t\t\t<div class=\"t_popup_html\">\n".
			"\t\t\t</div>\n".
			"\t\t</div>\n".
			"\t\t". wp_nonce_field('thesis-save-css', '_wpnonce-thesis-save-css', true, false). "\n".
			"\t\t<button id=\"t_save_css\" data-style=\"button save\">". sprintf(__('%s CSS', 'thesis'), $thesis->api->strings['save']). "</button>\n";
	}

	public function init_canvas() {
		add_action('hook_head', array($this, 'canvas_head'));
	}

	public function canvas_head() {
		echo
			"<style type=\"text/css\">\n",
			$this->reset(),
			"</style>\n",
			"<style id=\"t_live_css\" type=\"text/css\">\n",
			$this->update(stripslashes($this->css), ($_GET['thesis_canvas'] == '1' ? $this->custom : false), true),
			"\n</style>\n";
	}

	public function live() {
		global $thesis;
		$thesis->wp->nonce($_POST['nonce'], 'thesis-save-css');
		$skin = !empty($_POST['skin']) ? $_POST['skin'] : '';
		$custom = !empty($_POST['custom']) ? $_POST['custom'] : (!empty($skin) ? $this->custom : '');
		echo $this->update(stripslashes($skin), stripslashes($custom), true);
		if ($thesis->environment == 'ajax') die();
	}

	private function update($skin = false, $custom = false, $editor = false) {
		global $thesis;
		$skin = $skin ? $skin : '';
		$custom = $custom ? $custom : '';
		$css = apply_filters('thesis_css', $this->reset(). $skin, $this->reset(), $skin) . (!empty($custom) ? "\n$custom" : '');
		if (empty($css)) return '';
		$return = '';
		$clearfix = array();
		if (in_array($this->preprocessor, array(false, 'thesis')))
			extract($this->packages->css($css));
		$css = $this->vars->css($css);
		if ($editor)
			$css = preg_replace('/url\(\s*(\'|")(images|fonts)\/([\w-\.]+)(\'|")\s*\)/', 'url(${1}'. THESIS_USER_SKIN_URL .'/${2}/${3}${1})', $css);
		$css = $css . (!empty($clearfix) ? $this->clearfix($clearfix) : '');
		if ($this->preprocessor === 'less') {
			require_once(THESIS_CSS . '/lessc.inc.php');
			$less = new lessc;
			$css = $less->compile($css);
		}
		elseif (version_compare(PHP_VERSION, '5.3', '>=') && in_array($this->preprocessor, array('sass', 'scss'))) {
			require_once(THESIS_CSS . '/sass/SassParser.php');
			$options = array(
				'style' => 'nested',
				'cache' => false,
				'syntax' => $this->preprocessor,
				'debug' => false);
			try {
				$sass = new SassParser($options);
				$css = $sass->toCss($css);
			}
			catch (Exception $e) {
				return false;
			}
		}
		return $css;
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
		$css = strip_tags($this->update($skin, !empty($custom) ? "/*---:[ custom CSS ]:---*/\n". $custom : false));
		if (is_multisite()) {
			update_option('thesis_raw_css', $css);
			wp_cache_flush();
		}
		else {
			$lid = @fopen(THESIS_USER_SKIN . '/css.css', 'w');
			@fwrite($lid, trim(stripslashes($css)));
			@fclose($lid);
		}
	}

	private function reset() {
		return apply_filters('thesis_css_reset',
			"/*---:[ Thesis CSS reset ]:---*/\n".
			"* {\n".
			"\tmargin: 0;\n".
			"\tpadding: 0;\n".
			"}\n".
			"h1, h2, h3, h4, h5, h6 {\n".
			"\tfont-weight: normal;\n".
			"}\n".
			"table {\n".
			"\tborder-collapse: collapse;\n".
			"\tborder-spacing: 0;\n".
			"}\n".
			"img, abbr, acronym, fieldset {\n".
			"\tborder: 0;\n".
			"}\n".
			"code {\n".
			"\tline-height: 1em;\n".
			"}\n".
			"pre {\n".
			"\toverflow: auto;\n".
			"\tclear: both;\n".
			"}\n".
			"sub, sup {\n".
			"\tline-height: 0.5em;\n".
			"}\n".
			"img, .wp-caption {\n".
			"\tmax-width: 100%;\n".
			"\theight: auto;\n".
			"}\n".
			"iframe, video, embed, object {\n".
			"\tdisplay: block;\n".
			"\tmax-width: 100%;\n".
			"}\n".
			"img {\n".
			"\tdisplay: block;\n".
			"}\n".
			".left, .alignleft, img[align=\"left\"] {\n".
			"\tdisplay: block;\n".
			"\tfloat: left;\n".
			"}\n".
			".right, .alignright, img[align=\"right\"] {\n".
			"\tdisplay: block;\n".
			"\tfloat: right;\n".
			"}\n".
			".center, .aligncenter, img[align=\"middle\"] {\n".
			"\tdisplay: block;\n".
			"\tmargin-right: auto;\n".
			"\tmargin-left: auto;\n".
			"\ttext-align: center;\n".
			"\tfloat: none;\n".
			"\tclear: both;\n".
			"}\n".
			".block, .alignnone {\n".
			"\tdisplay: block;\n".
			"\tclear: both;\n".
			"}\n".
			".wp-smiley {\n".
			"\tdisplay: inline;\n".
			"}\n");
	}

	private function clearfix($clearfix) {
		if (empty($clearfix) || !is_array($clearfix)) return;
		$clear = array();
		foreach ($clearfix as $selector)
			$clear[] = "$selector:after";
		return "\n" . implode(', ', $clear) . ' { content: "."; display: block; height: 0; clear: both; visibility: hidden; }';
	}

	public function update_vars($vars) {
		return !is_array($vars) || !is_array($update = $this->vars->update($vars)) ? false : $update;
	}

	public function restore_vars($vars) {
		return !is_array($vars) || !is_array($restore = $this->vars->restore($vars)) ? false : $restore;
	}
	
	public function canvas() {
		$url = set_url_scheme(home_url('?thesis_canvas=2'));
		$name = wp_create_nonce('thesis-canvas-name');
		$canvas = wp_create_nonce('thesis-canvas');
		echo
			"<script type=\"text/javascript\">\n",
			"window.name = '$name';\n",
			"var thesis_canvas = {\n",
			"\turl: '", esc_url_raw($url), "',\n",
			"\tname: '$canvas' };\n",
			"var thesis_ajax = { url: '", str_replace('/', '\/', admin_url("admin-ajax.php")), "' };\n",
			"</script>\n";
	}
}