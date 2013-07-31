<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_skin {
	protected $_class; 					// (string) class name of the active Skin
	protected $_skin;					// (array) basic Skin properties
	private $_templates;				// (object) template controller
	private $_user_packages;			// (object) packages added by the user
	private $_name = false;				// (string) Thesis will auto-set this to your Skin name
	private $_menu = false;				// (array) Thesis will auto-set this to an array if your Skin provides admin functionality
	private $_instances = false;		// (array) Thesis will auto-set to an array if your Skin has Box instances that contain options
	public $_boxes;						// (object) box controller
	public $_template = array();		// (array) current template data
	protected $_display = array();		// (array) raw Skin display options
	protected $display = array();		// (array) merged Skin display options, including defaults
	protected $_design = array();		// (array) raw Skin design options
	protected $design = array();		// (array) merged Skin design options, including defaults
	protected $header_image = array();	// (array) will hold all the header image related data
	protected $filters = array();		// (array) functionality overrides

	public function __construct($skin) {
		global $thesis;
		define('THESIS_SKIN', THESIS_CORE. '/skin');
		require_once(THESIS_SKIN. '/boxes.php');
		require_once(THESIS_SKIN. '/skin_boxes.php');
		require_once(THESIS_SKIN. '/templates.php');
		require_once(THESIS_SKIN. '/user_boxes.php');
		require_once(THESIS_SKIN. '/user_packages.php');
		$this->_class = get_class($this);
		$this->_skin = $skin;
		$this->_actions();
		$this->_filters();
		$this->_boxes = new thesis_skin_boxes($thesis->api->get_option("{$this->_class}_boxes"));
		$this->_templates = new thesis_templates($thesis->api->get_option("{$this->_class}_templates"));
		$this->_user_packages = new thesis_user_packages;
		$this->_name = !empty($skin['name']) ? $skin['name'] : false;
		$this->display = $thesis->api->get_options($this->_display(), is_array($this->_display = get_option("{$this->_class}__display")) ? $this->_display : $this->display);
		$this->header_image = $thesis->api->get_option("{$this->_class}__header_image");
		$this->construct();
	}

	protected function construct() {
		// Secondary constructor for skins
	}

	private function _actions() {
		global $thesis;
		if (method_exists($this, 'boxes'))
			add_action('thesis_boxes', array($this, '_add_boxes'));
		add_filter('template_include', array($this, '_skin'));
		if ((!$thesis->environment && !is_admin()) || $thesis->environment == 'canvas') {
			add_action('parse_query', array($this, '_query'));
			if (!$thesis->environment)
				add_action('hook_after_html', array($this, '_editor_launcher'), 2);
		}
		if (empty($thesis->environment)) return;
		add_action('init', array($this, '_css'), 13);
		if ($thesis->environment == 'admin')
			add_action('init', array($this, '_init_wp'));
		else {
			@ini_set('memory_limit', '128M');
			if ($thesis->environment == 'thesis') {
				add_action('init', array($this, '_init_wp'));
				add_action('init', array($this, '_init_admin'));
			}
			if ($thesis->environment == 'editor') {
				remove_action('init', '_wp_admin_bar_init');
				add_action('init', array($this, '_init_editor'), 15);
			}
			elseif ($thesis->environment == 'canvas')
				add_action('init', array($this, '_init_canvas'), 15);
			elseif ($thesis->environment == 'ajax')
				add_action('init', array($this, '_init_ajax'));
		}
	}

	private function _filters() {
		global $thesis;
		add_filter('thesis_html_body_class', array($this, '_body_class'));
		if (method_exists($this, 'meta_viewport'))
			add_filter('thesis_meta_viewport', array($this, 'meta_viewport'));
		if (method_exists($this, 'filter_css') && in_array($thesis->environment, array('canvas', 'admin', 'ajax')))
			add_filter('thesis_css', array($this, 'filter_css'));
		if (method_exists($this, 'font_script'))
			add_filter('thesis_font_script', array($this, 'font_script'));
		if (method_exists($this, 'font_stylesheet'))
			add_filter('thesis_font_stylesheet', array($this, 'font_stylesheet'));
		if (!is_admin()) return;
		add_filter('thesis_post_meta', array($this, '_post_meta'), 11);
		add_filter('thesis_term_options', array($this, '_term_options'), 11);
		if (method_exists($this, 'fonts'))
			add_filter('thesis_fonts', array($this, '_add_fonts'));
	}

	public function _css() {
		global $thesis;
		require_once(THESIS_SKIN. '/css.php');
		$css = array();
		$css['css'] = ($skin = $thesis->api->get_option("{$this->_class}_css")) ? $skin : '';
		$css['custom'] = ($custom = $thesis->api->get_option("{$this->_class}_css_custom")) ? stripslashes($custom) : '';
		$css['packages'] = is_array($packages = $thesis->api->get_option("{$this->_class}_packages")) ? $packages : array();
		$css['user_packages'] = is_array($this->_user_packages->active) ? $this->_user_packages->active : array();
		$css['vars'] = is_array($vars = $thesis->api->get_option("{$this->_class}_vars")) ? $vars : array();
		$css['preprocessor'] = !empty($this->filters['css_preprocessor']) ? $this->filters['css_preprocessor'] : false;
		if (method_exists($this, 'packages')) {
			add_action('thesis_include_packages', array($this, '_include_packages'));
			add_filter('thesis_packages', array($this, '_add_packages'));
		}
		$this->_design_options();
		$this->_css = new thesis_css($css);
	}

	public function _design_options() {
		global $thesis;
		$this->design = $thesis->api->get_options($this->_design(), is_array($this->_design = get_option("{$this->_class}__design")) ? $this->_design : array());
	}

	public function _init_wp() {
		global $thesis;
		if (!empty($_GET['t_quicklaunch_editor'])) {
			wp_redirect(set_url_scheme(home_url('?thesis_editor=1')));
			exit;
		}
		require_once(THESIS_SKIN . '/images.php');
		$this->_images = new thesis_images;
		new thesis_upload(array(
			'title' => sprintf(__('Import %s Data', 'thesis'), $thesis->skins->skin['name']),
			'prefix' => 'import_skin',
			'file_type' => 'txt'));
		add_action('thesis_quicklaunch_menu', array($this, '_quicklaunch'));
		add_action('admin_post_thesis_head', array($this, '_save_head'));
		if (method_exists($this, 'display'))
			add_action("admin_post_{$this->_class}__display", array($this, '_save_display'));
		if (method_exists($this, 'design'))
			add_action("admin_post_{$this->_class}__design", array($this, '_save_design'));
		if (method_exists($this, 'header_image'))
			add_action("admin_post_{$this->_class}__header_image", array($this, '_save_header_image'));
	}

	public function _init_admin() {
		add_filter('thesis_skin_menu', array($this, '_add_menu'));
		add_action('thesis_skin_menu', array($this, '_editor_menu_link'), 100);
		add_action('thesis_current_skin', array($this, '_current'));
		if (!empty($this->_instances) || method_exists($this, 'display'))
			$this->_menu["{$this->_class}__content"] = array(
				'text' => __('Content', 'thesis'),
				'url' => admin_url("admin.php?page=thesis&canvas={$this->_class}__content"));
		if (!empty($_GET['canvas'])) {
			if ($_GET['canvas'] == "{$this->_class}__content") {
				add_action('admin_init', array($this, '_init_content_admin'));
				add_action('thesis_admin_canvas', array($this, '_content_admin'));
			}
		 	elseif ($_GET['canvas'] == 'head') {
				add_action('admin_init', array($this, '_admin_init_head_editor'));
				add_action('thesis_admin_canvas', array($this, '_head_editor'));
			}
		}
		if (method_exists($this, 'design') || method_exists($this, 'design_admin') || !empty($this->filters['design_admin'])) {
			$this->_menu["{$this->_class}__design"] = array(
				'text' => __('Design', 'thesis'),
				'url' => !empty($this->filters['design_url']) ?
					esc_url($this->filters['design_url']) :
					admin_url("admin.php?page=thesis&canvas={$this->_class}__design"));
			if (empty($this->filters['design_url'])) {
				if (!empty($_GET['canvas']) && $_GET['canvas'] == "{$this->_class}__design") {
					add_action('thesis_admin_canvas', array($this, !empty($this->filters['design_admin']) && method_exists($this, $this->filters['design_admin']) ?
						$this->filters['design_admin'] : (method_exists($this, 'design_admin') ?
						'design_admin' :
						'_design_admin')));
					add_action('admin_init', array($this, '_init_design_admin'));
				}
			}
		}
		if (method_exists($this, 'header_image')) {
			$this->_menu["{$this->_class}__header_image"] = array(
				'text' => __('Header Image', 'thesis'),
				'url' => admin_url("admin.php?page=thesis&canvas={$this->_class}__header_image"));
			if (!empty($_GET['canvas']) && $_GET['canvas'] == "{$this->_class}__header_image") {
				add_action('admin_init', array($this, '_init_header_image'));
				add_action('thesis_admin_canvas', array($this, '_header_image'));
			}
		}
	}

	public function _quicklaunch($menu) {
		global $thesis;
		if (is_array($instances = apply_filters('thesis_skin_instances', array())) && !empty($instances))
			$this->_instances = $instances;
		else
			unset($this->_menu["{$this->_class}__content"]);
		if (!empty($this->_instances) || method_exists($this, 'display'))
			$quicklaunch['content'] = array(
				'text' => __('Skin Content', 'thesis'),
				'url' => "admin.php?page=thesis&canvas={$this->_class}__content");
//		else echo "The instances were empty.\n";
		if (method_exists($this, 'design') || method_exists($this, 'design_admin') || (!empty($this->filters['design_admin']) && method_exists($this->filters['design_admin'])))
			$quicklaunch['design'] = array(
				'text' => __('Skin Design', 'thesis'),
				'url' => "admin.php?page=thesis&canvas={$this->_class}__design");
		if (method_exists($this, 'header_image'))
			$quicklaunch['header_image'] = array(
				'text' => __('Header Image', 'thesis'),
				'url' => "admin.php?page=thesis&canvas={$this->_class}__header_image");
		$quicklaunch['custom_css'] = array(
			'text' => sprintf(__('Custom %s', 'thesis'), $thesis->api->base['css']),
			'url' => "admin.php?page=thesis&canvas=custom_css");
		$quicklaunch['editor'] = array(
			'text' => __('Skin Editor', 'thesis'),
			'url' =>  'admin.php?t_quicklaunch_editor=1');
		return !empty($quicklaunch) ? (is_array($menu) ? array_merge($menu, $quicklaunch) : $quicklaunch) : $menu;
	}

	public function _current() {
		global $thesis;
		echo
			"\t\t<div id=\"current_skin\">\n",
			"\t\t\t<h4>", sprintf(__('%s Skin', 'thesis'), wptexturize($this->_name)), "</h4>\n",
			(!empty($thesis->skins->preview) ?
			"\t\t\t<p>". __('You are previewing this Skin in Development Mode. To change this, visit the <strong>Manage Skins</strong> link below.', 'thesis'). "</p>\n" : ''),
			"\t\t\t<ul>\n",
			(!empty($this->_instances) ?
			"\t\t\t\t<li><a href=\"". admin_url("admin.php?page=thesis&canvas={$this->_class}__content"). "\">". __('Content', 'thesis'). "</a></li>\n" : ''),
			(method_exists($this, 'design') ?
			"\t\t\t\t<li><a href=\"". admin_url("admin.php?page=thesis&canvas={$this->_class}__design"). "\">". __('Design', 'thesis'). "</a></li>\n" : ''),
			"\t\t\t\t<li><a href=\"", admin_url('admin.php?page=thesis&canvas=custom_css'), "\">", sprintf(__('Custom %s', 'thesis'), $thesis->api->base['css']), "</a></li>\n",
			"\t\t\t\t<li><a href=\"", admin_url('admin.php?page=thesis&canvas=select_skin'), "\">", __('Manage Skins', 'thesis'), "</a></li>\n",
			"\t\t\t</ul>\n",
			"\t\t</div>\n";
	}

	public function _add_menu($menu) {
		return is_array($this->_menu) ? (is_array($menu) ? array_merge($menu, $this->_menu) : $this->_menu) : $menu;
	}

	public function _add_boxes($boxes) {
		if (file_exists(THESIS_USER_SKIN . '/box.php'))
			include_once(THESIS_USER_SKIN . '/box.php');
		return is_array($add_boxes = $this->boxes()) ? (is_array($boxes) ? array_merge($boxes, $add_boxes) : $add_boxes) : $boxes;
	}

	public function _include_packages() {
		if (file_exists(THESIS_USER_SKIN . '/package.php'))
			include_once(THESIS_USER_SKIN . '/package.php');
	}

	public function _add_packages($packages) {
		return is_array($add_packages = $this->packages()) ? (is_array($packages) ? array_merge($packages, $add_packages) : $add_packages) : $packages;
	}

	public function _add_fonts($fonts) {
		return is_array($add_fonts = $this->fonts()) ? (is_array($fonts) ? array_merge($fonts, $add_fonts) : $add_fonts) : $fonts;
	}

	/*---:[ Skin admin page initializations ]:---*/

	public function _init_content_admin() {
		global $thesis;
		wp_enqueue_style('thesis-skin', THESIS_CSS_URL. '/skin.css', array('thesis-admin'), $thesis->version);
		if (!method_exists($this, 'display')) return;
		wp_enqueue_style('thesis-options');
		wp_enqueue_script('thesis-options');
	}

	public function _display() {
		return method_exists($this, 'display') && is_array($display = apply_filters("{$this->_class}__display", $this->display())) ? $display : array();
	}

	public function _content_admin() {
		global $thesis;
		$li = '';
		$display = array();
		if (!empty($this->_instances))
			foreach ($this->_instances as $name => $link) {
				$this->_instances[$name]['li'] = "\t\t\t\t<li><a href=\"". esc_url($link['url']). "\">{$link['text']}</a></li>\n";
				$instances[$name] = $link['text'];
			}
		unset($instances['skin_thesis_attribution']);
		natcasesort($instances);
		foreach ($instances as $name => $text)
			$li .= $this->_instances[$name]['li'];
		$li .= !empty($this->_instances['skin_thesis_attribution']) ? $this->_instances['skin_thesis_attribution']['li'] : '';
		if (method_exists($this, 'display'))
			$display = $thesis->api->form->fields($this->_display(), $this->_display, "{$this->_class}_", $this->_class, 10, 3);
		echo
			(!empty($_GET['saved']) ? $thesis->api->alert(wptexturize($_GET['saved'] === 'yes' ?
			__('Display options saved!', 'thesis') :
			__('Display options not saved. Please try again.', 'thesis')), 'options_saved', true, false, 3) : ''),
			(is_array($display) && !empty($display) ?
			"\t\t<h3 id=\"display_options\">". sprintf(__('%s Skin Display Options', 'thesis'), $this->_name). "</h3>\n".
			"\t\t<p>". __('You can control the display of some of this Skin&#8217;s content via the options below.', 'thesis'). "</p>\n".
			"\t\t<form class=\"thesis_options_form\" method=\"post\" action=\"". admin_url("admin-post.php?action={$this->_class}__display"). "\" enctype=\"multipart/form-data\">\n".
			$display['output'].
			"\t\t\t<input type=\"submit\" data-style=\"button save\" class=\"t_save\" id=\"save_options\" value=\"". esc_attr(wptexturize(strip_tags(sprintf(__('Save Display Options', 'thesis'), $this->_name)))). "\" />\n".
			"\t\t\t". wp_nonce_field("{$this->_class}_display", "_wpnonce-{$this->_class}_display", true, false). "\n".
			"\t\t</form>\n" : ''),
			"\t\t<h3 id=\"skin_content\">", sprintf(__('%s Skin Content', 'thesis'), $this->_name), "</h3>\n",
			"\t\t<div class=\"skin_content\">\n",
			"\t\t\t<p>", __('You can customize some of this Skin&#8217;s content by editing the following Boxes:', 'thesis'), "</p>\n",
			(!empty($li) ?
			"\t\t\t<ul>\n".
			$li.
			"\t\t\t</ul>\n" : ''),
			"\t\t</div>\n";
	}

	public function _save_display() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST["_wpnonce-{$this->_class}_display"], "{$this->_class}_display");
		$saved = 'no';
		if (!empty($_POST[$this->_class])) {
			$save = $thesis->api->set_options($this->_display(), $_POST[$this->_class]);
			if (empty($save))
				delete_option("{$this->_class}__display");
			else
				update_option("{$this->_class}__display", $save);
			$saved = 'yes';
		}
		wp_redirect("admin.php?page=thesis&canvas={$this->_class}__content&saved=$saved");
		exit;
	}

	public function _init_design_admin() {
		global $thesis;
		wp_enqueue_style('thesis-options');
		wp_enqueue_style('thesis-colors', THESIS_CSS_URL. '/colors.css', array('thesis-options'), $thesis->version);
		wp_enqueue_script('thesis-options');
		wp_enqueue_script('js-color', THESIS_JS_URL. '/jscolor/jscolor.js', array('thesis-options'), $thesis->version);
		wp_enqueue_script('thesis-colors', THESIS_JS_URL. '/colors.js', array('thesis-options', 'js-color'), $thesis->version);
		if (method_exists($this, 'init_design_admin'))
			$this->init_design_admin();
	}

	public function _design() {
		return method_exists($this, 'design') && is_array($design = apply_filters("{$this->_class}__design", $this->design())) ? $design : array();
	}

	public function _design_admin() {
		global $thesis;
		$options = $thesis->api->form->fields($this->_design(), $this->design, "{$this->_class}_", $this->_class, 3, 10);
		echo (!empty($_GET['saved']) ? $thesis->api->alert(wptexturize($_GET['saved'] === 'yes' ?
			sprintf(__('%s saved!', 'thesis'), $this->_name) :
			sprintf(__('%s not saved. Please try again.', 'thesis'), $this->_name)), 'options_saved', true, false, 2) : ''),
			"\t\t<h3>", wptexturize((!empty($this->_name) ? "{$this->_name} " : ''). __('Skin Design Options', 'thesis')), "</h3>\n",
			"\t\t<form class=\"thesis_options_form\" method=\"post\" action=\"", admin_url("admin-post.php?action={$this->_class}__design"), "\" enctype=\"multipart/form-data\">\n",
			"\t\t\t<div id=\"t_skin_options\">\n",
			$options['output'],
			"\t\t\t</div>\n",
			"\t\t\t<input type=\"submit\" data-style=\"button save\" class=\"t_save\" id=\"save_options\" value=\"", esc_attr(wptexturize(strip_tags(sprintf(__('Save Design Options', 'thesis'), $this->_name)))), "\" />\n",
			"\t\t\t", wp_nonce_field("{$this->_class}__design", "_wpnonce-{$this->_class}__design", true, false), "\n",
			"\t\t</form>\n";
	}

	public function _save_design() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST["_wpnonce-{$this->_class}__design"], "{$this->_class}__design");
		$saved = 'no';
		if (!empty($_POST[$this->_class])) {
			$save = $thesis->api->set_options($this->_design(), $_POST[$this->_class]);
			if (empty($save))
				delete_option("{$this->_class}__design");
			else
				update_option("{$this->_class}__design", $save);
			$this->_design_options();
			if (method_exists($this, 'css_variables') && is_array($map = $this->css_variables()) && is_array($vars = $this->_css->update_vars($map)))
				update_option("{$this->_class}_vars", $vars);
			$this->_write_css();
			$saved = 'yes';
		}
		wp_redirect("admin.php?page=thesis&canvas={$this->_class}__design&saved=$saved");
		exit;
	}

	public function _init_header_image() {
		wp_enqueue_style('thesis-options');
		wp_enqueue_media();
		wp_enqueue_script('custom-header');
	}

	public function _header_image() {
		global $thesis;
		$width = is_numeric($w = $this->header_image()) ? strip_tags($w) : false;
		$save_url = esc_url(add_query_arg(array('action' => "{$this->_class}__header_image", '_wpnonce' => wp_create_nonce('thesis-header-image')), admin_url("admin-post.php")));
		$delete_url = esc_url(add_query_arg(array('action' => "{$this->_class}__header_image", '_wpnonce' => wp_create_nonce('thesis-header-image'), 'delete' => 'true'), admin_url("admin-post.php")));
		$data_attributes = "data-style=\"save button\" data-update-link=\"$save_url\" data-choose=\"". __('Select a Header Image', 'thesis'). "\" data-update=\"". __('Set Header Image', 'thesis'). "\"";
		echo
			$width == false ?
			"\t\t<p>". __('You must declare a width for your header image before this functionality can be enabled.', 'thesis'). "</p>\n" :
			"\t\t<h3>". sprintf(__('%s Skin Header Image', 'thesis'), $this->_name). "</h3>\n".
			"\t\t<p class=\"option_item\">". sprintf(__('Based on your current design settings, we recommend a header image that is <strong>%dpx wide</strong>.', 'thesis'), $width). "</p>\n".
			"\t\t<div class=\"option_item\" id=\"t_header_image_container\">\n".
			(!empty($this->header_image) ?
			"\t\t\t<img src=\"". esc_url($this->header_image['src']). "\" height=\"". (int) $this->header_image['height']. "\" width=\"". (int) $this->header_image['width']. "\"/>\n".
			"\t\t\t<p style=\"font-size: 14px; color: #888;\">". sprintf(__('Current image is %1$dpx wide by %2$dpx tall.', 'thesis'), (int) $this->header_image['width'], (int) $this->header_image['height']). "</p>\n" : '').
			"\t\t</div>\n".
			"\t\t<p>\n".
			"\t\t<button id=\"choose-from-library-link\" $data_attributes>". __('Select Header Image', 'thesis'). "</button>\n".
			(!empty($this->header_image) ?
			"\t\t<a id=\"t_delete_header_image\" data-style=\"button delete\" style=\"margin-left: 1em;\" href=\"$delete_url\">". __('Remove Header Image', 'thesis'). "</a>\n" : '').
			"\t\t</p>\n";
	}

	public function _save_header_image() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_GET['_wpnonce'], 'thesis-header-image');
		if (!empty($_GET['delete']) && $_GET['delete'] === 'true') {
			delete_option("{$this->_class}__header_image");
			$this->header_image = array();
		}
		else {
			$id = (int) $_GET['file'];
			$image = wp_get_attachment_metadata($id);
			update_option("{$this->_class}__header_image", $this->header_image = array(
				'src' => esc_url_raw(wp_get_attachment_url($id)),
				'height' => (int) $image['height'],
				'width' => (int) $image['width'],
				'id' => $id));
		}
		wp_cache_flush();
		$this->_write_css();
		wp_redirect(admin_url("admin.php?page=thesis&canvas={$this->_class}__header_image"));
		exit;
	}

	public function _editor_menu_link($menu) {
		$skin['editor'] = array(
			'text' => __('Editor', 'thesis'),
			'url' => set_url_scheme(home_url('?thesis_editor=1')),
			'icon' => '&#59190;');
		return is_array($menu) ? array_merge($menu, $skin) : $skin;
	}

	public function _init_editor() {
		add_action('thesis_editor_head', array($this, '_editor_head'));
		add_action('thesis_editor_scripts', array($this, '_editor_scripts'));
		do_action('thesis_init_editor');
	}

	public function _editor_head() {
		global $thesis;
		$this->_launch_canvas();
		echo
			"<link rel=\"shortcut icon\" href=\"", THESIS_IMAGES_URL, "/favicon.ico\" />\n",
			"<link rel=\"stylesheet\" type=\"text/css\" href=\"", THESIS_CSS_URL, "/editor.css?ver={$thesis->version}\" />\n";
	}

	public function _editor_scripts() {
		global $thesis;
		$wp_scripts = class_exists('WP_Scripts') ? new WP_Scripts : false;
		$scripts = array();
		$includes = array(
			'jquery',
			'jquery-ui-core',
			'jquery-ui-widget',
			'jquery-ui-mouse',
			'jquery-ui-draggable',
			'jquery-ui-droppable',
			'jquery-ui-sortable');
		if (is_object($wp_scripts) && is_array($wp_scripts->registered)) {
			if (empty($wp_scripts->registered['jquery']->src))
				$includes[0] = 'jquery-core';
			foreach ($includes as $script)
				if (is_object($wp_scripts->registered[$script]) && $src = $wp_scripts->registered[$script]->src)
					$scripts[$script] = $wp_scripts->base_url. $src;
		}
		$scripts['editor'] = THESIS_JS_URL. '/editor.js';
		$scripts['options'] = THESIS_JS_URL. '/options.js';
		$scripts['ui'] = THESIS_JS_URL. '/ui.js';
		foreach ($scripts as $script => $src)
			echo "<script src=\"$src?ver={$thesis->version}\"></script>\n";
	}

	public function _init_canvas() {
		add_action('hook_head', array($this, '_canvas_js'), 11);
		do_action('thesis_init_canvas');
	}

	private function _launch_canvas() {
		$real_scheme = defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN === true ? 'https' : 'http';		
		$parsed = parse_url((!empty($_SERVER['HTTP_REFERER']) && strpos(strtolower($_SERVER['HTTP_REFERER']), strtolower(admin_url())) !== 0 && !strpos(strtolower($_SERVER['HTTP_REFERER']), 'wp-login') ? $_SERVER['HTTP_REFERER'] : home_url('', $real_scheme)));
		extract($parsed);
		$query = isset($query) ? str_ireplace('thesis_editor=1', '', $query) : '';
		$url = $real_scheme . '://' . trailingslashit($host) . (!empty($path) && $path != '/' ? trailingslashit($path) : '' ) . '?' . (! empty($query) ? rtrim($query, '&') . '&' : '') . 'thesis_canvas=1&thesis_canvas_nonce=' . wp_create_nonce('thesis-canvas-url') . (!empty($fragment) ? "#$fragment" : '');
		$name = wp_create_nonce('thesis-canvas-name');
		$canvas = wp_create_nonce('thesis-canvas');
		$cookie = explode('|', $_COOKIE[LOGGED_IN_COOKIE]);
		echo
			"<script type=\"text/javascript\">\n",
			"window.name = '$name';\n",
			"var thesis_canvas = {\n",
			"\turl: '", esc_url_raw($url), "',\n",
			"\tname: '$canvas' };\n",
			"var thesis_ajax = { url: '", str_replace('/', '\/', admin_url("admin-ajax.php")), "' },\n",
			"thesis_login = { expire: {$cookie[1]}, url: '", str_replace('/', '\/', wp_login_url(home_url('?thesis_editor=1'))), "' };\n",
			"</script>\n";
	}

	public function _canvas_js() {
		if ($_GET['thesis_canvas'] === '2')
			echo
				"<script type=\"text/javascript\">\n",
				"\t(function(){ window.opener.thesis_custom.update(); window.opener.thesis_custom.onclick_child(); })();\n",
				"</script>\n";
		if ($_GET['thesis_canvas'] !== '1' || !($template = $this->_template['id'])) return;
		echo
			"<script type=\"text/javascript\">\n",
			"var template = '$template';\n",
			"window.opener.thesis_templates.get(template);\n",
			"document.onclick = canvas_control;\n",
			"document.unload = button_control;\n",
			"function canvas_control(e) {\n",
			"\tif (e.target.localName == 'a' && e.target.host == window.location.host) {\n",
			"\t\tvar protocol = host = path = search = hash = thesis_query = url = '';\n",
			"\t\tprotocol = e.target.protocol + '//';\n",
			"\t\thost = e.target.host;\n",
			"\t\tthesis_query = 'thesis_canvas=1&thesis_canvas_nonce=", wp_create_nonce('thesis-canvas-url'), "';\n",
			"\t\tif (typeof e.target.pathname == 'string')\n",
			"\t\t\tpath = (e.target.pathname.charAt(0) != '/' ? '/' :'') + e.target.pathname + (e.target.pathname.charAt(e.target.pathname.length - 1) != '/' ? '/' :'');\n",
			"\t\tif (typeof e.target.search == 'string')\n",
			"\t\t\tsearch = e.target.search.replace(/thesis_canvas=1&thesis_canvas_nonce=\w+/, '');\n",
			"\t\tif (typeof e.target.hash == 'string')\n",
			"\t\t\thash = e.target.hash;\n",
			"\t\turl = protocol + host + path + (search.charAt(0) != '?' || search.length == 0 ? '?' : search + '&') + thesis_query + hash;\n",
			"\t\twindow.opener.thesis_editor.get_canvas(url);\n",
			"\t}\n",
			"\treturn false;\n",
			"}\n",
			"function button_control() {\n",
			"\twindow.opener.thesis_editor.launch_icon();\n",
			"}\n",
			"</script>\n";
	}

	public function _editor_launcher() {
		global $thesis;
		if (!current_user_can('edit_theme_options') || $thesis->wp_customize === true) return;
		$scheme = defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN === true ? 'https' : 'http';
		echo
			"<style type=\"text/css\">\n",
			"#thesis_launcher { position: fixed; bottom: 0; left: 0; font: bold 16px/1em \"Helvetica Neue\", Helvetica, Arial, sans-serif; padding: 12px; text-align: center; color: #fff; background: rgba(0,0,0,0.5); text-shadow: 0 1px 1px rgba(0,0,0,0.75); }\n",
			"#thesis_launcher input { font-size: 16px; margin-top: 6px; }\n",
			"</style>\n",
			"<div id=\"thesis_launcher\">\n",
			"\t<form method=\"post\" action=\"", home_url('?thesis_editor=1', $scheme), "\">\n",
			"\t\t<p>", $thesis->api->esch(ucfirst($this->_template['title'])), "</p>\n",
			"\t\t<p>\n",
			"\t\t\t<input type=\"hidden\" name=\"thesis_template\" value=\"{$this->_template['id']}\" />\n",
			"\t\t\t<input type=\"submit\" name=\"thesis_editor\" value=\"", esc_attr($thesis->api->strings['click_to_edit']), "\" />\n",
			"\t\t</p>\n",
			"\t</form>\n",
			"</div>\n";
	}

	private function _editor() {
		global $thesis;
		$li = '';
		$menu = array(
			'html' => array(
				'text' => __('HTML', 'thesis'),
				'title' => __('Edit HTML Templates', 'thesis')),
			'css' => array(
				'text' => 'CSS',
				'title' => __('Edit CSS', 'thesis')),
			'images' => array(
				'text' => __('Images', 'thesis'),
				'title' => __('Edit Images', 'thesis')),
			'manager' => array(
				'text' => __('Manager', 'thesis'),
				'title' => __('Backup and restore your Skin data', 'thesis')));
		foreach ($menu as $pane => $m)
			$li .= "\t<li><button class=\"t_menu t_pane_switch\" data-pane=\"$pane\" title=\"{$m['title']}\">{$m['text']}</button></li>\n";
		$menu =
			"<ul id=\"t_menu\">\n".
			$li.
			"\t<li><button class=\"t_menu action\" id=\"t_launch_canvas\"><span data-style=\"icon\">&#59212;</span> ". __('Canvas', 'thesis'). "</button></li>\n".
			"\t<li class=\"t_logo t_right\"><a href=\"". esc_url(admin_url('admin.php?page=thesis')). "\" title=\"" . __('return to the Thesis admin page', 'thesis'). "\">Thesis</a></li>\n".
			"\t<li class=\"t_right\"><a class=\"t_menu t_menu_link\" href=\"". esc_url(home_url()). '"><span data-style="icon">&#59392;</span> '. __('View Site', 'thesis'). "</a></li>\n".
			"</ul>\n";
		echo
			"<!DOCTYPE html>\n",
			"<html", $thesis->wp->language_attributes(), ">\n",
			"<head>\n",
			"<title>", __('Thesis Skin Editor', 'thesis'), "</title>\n";
		do_action('thesis_editor_head');
		echo
			"</head>\n",
			"<body>\n",
			$menu,
			"<div id=\"t_editor\" data-style=\"box\">\n",
			"\t<div id=\"t_html\" class=\"t_pane\" data-style=\"box\">\n",
			$this->_templates->editor($this->_template_form()),
			"\t</div>\n",
			"\t<div id=\"t_css\" class=\"t_pane\" data-style=\"box\">\n",
			$this->_css->editor(),
			"\t</div>\n",
			"\t<div id=\"t_images\" class=\"t_pane\" data-style=\"box\">\n",
			$thesis->api->uploader('thesis_images'),
			"\t</div>\n",
			"\t<div id=\"t_manager\" class=\"t_pane\" data-style=\"box\">\n",
			$thesis->skins->manager->editor(),
			"\t</div>\n",
			$thesis->api->popup(array(
				'id' => 'login_notice',
				'title' => __('Login Expiration Notice', 'thesis'),
				'depth' => 1,
				'body' =>	$this->_login_form())),
			"</div>\n";
		do_action('thesis_editor_scripts');
		echo
			"</body>\n",
			"</html>\n";
	}

	private function _template_form() {
		$template = $this->_templates->get_template(!empty($_POST['thesis_template']) ? $_POST['thesis_template'] : 'home');
		$form = $this->_boxes->get_box_form_data($template['boxes']);
		foreach ($form['boxes'] as $id => $box)
			if ($template['type'] && is_array($box->templates) && !in_array($template['type'], $box->templates))
				unset($form['boxes'][$id]);
		foreach ($form['add'] as $class => $box)
			if ($template['type'] && is_array($box->templates) && !in_array($template['type'], $box->templates))
				unset($form['add'][$class]);
		return array(
			'template' => $template,
			'form' => $form);
	}

	public function _admin_init_head_editor() {
		global $thesis;
		wp_enqueue_style('thesis-options'); #wp
		wp_enqueue_style('thesis-popup'); #wp
		wp_enqueue_style('thesis-box-form'); #wp
		wp_enqueue_script('jquery-ui-droppable'); #wp
		wp_enqueue_script('jquery-ui-sortable'); #wp
		wp_enqueue_script('thesis-options');
		wp_enqueue_script('thesis-ui', THESIS_JS_URL . '/ui.js', array('thesis-menu', 'thesis-options'), $thesis->version); #wp
		add_action('admin_head', array($this, '_admin_head_js'));
	}

	public function _admin_head_js() {
		echo
			"<script>\n",
			"var thesis_html_head;\n",
			"(function($) {\n",
			"thesis_html_head = {\n",
			"\tinit: function() {\n",
			"\t\tthesis_ui.box_form.init();\n",
			"\t\t$('#options_saved').css({'right': $('#save_options').outerWidth()+35+'px'}).fadeOut(3000, function() { $(this).remove(); });\n",
			"\t}\n",
			"};\n",
			"$(document).ready(function($){ thesis_html_head.init(); });\n",
			"})(jQuery);\n",
			"</script>\n";
	}

	public function _head_editor() {
		echo $this->_templates->head($this->_boxes->get_box_form_data($this->_templates->head, true));
	}

	public function _init_ajax() {
		add_action('wp_ajax_add_box', array($this, '_add_box'));
		add_action('wp_ajax_save_box', array($this, '_save_box'));
		add_action('wp_ajax_save_template', array($this, '_save_template'));
		add_action('wp_ajax_change_template', array($this, '_change_template'));
		add_action('wp_ajax_create_template', array($this, '_create_template'));
		add_action('wp_ajax_delete_template', array($this, '_delete_template'));
		add_action('wp_ajax_copy_template', array($this, '_copy_template'));
		add_action('wp_ajax_save_css', array($this, '_save_css'));
		add_action('wp_ajax_save_css_package', array($this, '_save_css_package'));
		add_action('wp_ajax_delete_css_package', array($this, '_delete_css_package'));
		add_action('wp_ajax_save_css_variable', array($this, '_save_css_variable'));
		add_action('wp_ajax_delete_css_variable', array($this, '_delete_css_variable'));
		add_action('wp_ajax_color_complement', array($this, '_color_complement'));
		if (method_exists($this, 'admin_ajax'))
			$this->admin_ajax();
	}

	public function _change_template() {
		global $thesis;
		$thesis->wp->nonce($_POST['nonce'], 'thesis-save-template');
		echo $this->_templates->editor($this->_template_form());
		if ($thesis->environment == 'ajax') die();
	}

	public function _create_template() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST['nonce'], 'thesis-save-template');
		if (!is_array($save = $this->_templates->create($_POST['title'])) || empty($save['id']) || empty($save['templates'])) return;
		update_option("{$this->_class}_templates", $save['templates']);
		wp_cache_flush();
		echo $save['id'];
		if ($thesis->environment == 'ajax') die();
	}

	public function _delete_template() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST['nonce'], 'thesis-save-template');
		if (!is_array($templates = $this->_templates->delete($_POST['template'])))
			echo $thesis->api->alert(__('Template not deleted.', 'thesis'), 'template_deleted', true);
		else {
			if (empty($templates))
				delete_option("{$this->_class}_templates");
			else
				update_option("{$this->_class}_templates", $templates);
			wp_cache_flush();
			echo $thesis->api->alert(__('Template deleted!', 'thesis'), 'template_deleted', true);
		}
		if ($thesis->environment == 'ajax') die();
	}

	public function _copy_template() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST['nonce'], 'thesis-save-template');
		if (!is_array($templates = $this->_templates->copy($_POST['to'], $_POST['from'])))
			echo $thesis->api->alert(__('Template not copied.', 'thesis'), 'template_copied', true);
		else {
			update_option("{$this->_class}_templates", $templates); #wp
			wp_cache_flush();
			echo $thesis->api->alert(__('Template copied!', 'thesis'), 'template_copied', true);
		}
		if ($thesis->environment == 'ajax') die();
	}

	public function _save_template() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		parse_str(stripslashes($_POST['form']), $form);
		$thesis->wp->nonce($form['_wpnonce-thesis-ajax'], 'thesis-save-template');
		if (!is_array($save = $this->_templates->save($form)))
			echo $thesis->api->alert(__('Template not saved.', 'thesis'), 'template_saved', true);
		else {
			if (is_array($save['templates']) && empty($save['templates']))
				delete_option("{$this->_class}_templates");
			elseif (is_array($save['templates']))
				update_option("{$this->_class}_templates", $save['templates']); #wp
			$this->_boxes->delete($save['delete_boxes']);
			$boxes = $this->_boxes->save($form);
			if (is_array($boxes) && empty($boxes))
				delete_option("{$this->_class}_boxes");
			elseif (is_array($boxes))
				update_option("{$this->_class}_boxes", $boxes);
			wp_cache_flush();
			echo $thesis->api->alert(__('Template saved!', 'thesis'), 'template_saved', true);
		}
		if ($thesis->environment == 'ajax') die();
	}

	public function _save_box() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		parse_str(stripslashes($_POST['form']), $form);
		if ($thesis->wp->nonce($form['_wpnonce-thesis-save-box'], 'thesis-save-box', true)) {
			$boxes = $this->_boxes->save($form);
			if (is_array($boxes) && empty($boxes))
				delete_option("{$this->_class}_boxes");
			elseif (is_array($boxes))
				update_option("{$this->_class}_boxes", $boxes);
			wp_cache_flush();
			echo $thesis->api->alert(__('Box options saved!', 'thesis'), 'options_saved', true);
		}
		else
			echo $thesis->api->alert(__('Box options not saved.', 'thesis'), 'options_saved', true);
		if ($thesis->environment == 'ajax') die();
	}

	public function _save_head() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST['_wpnonce-thesis-save-head'], 'thesis-save-head');
		$saved = 'no';
		if ($head = $this->_templates->save_head($_POST)) {
			if (is_array($head))
				$this->_boxes->delete($head);
			$this->_boxes->save($_POST, true);
			$saved = 'yes';
		}
		wp_redirect("admin.php?page=thesis&canvas=head&saved=$saved");
		exit;
	}

	public function _add_box() {
		global $thesis;
		$thesis->wp->nonce($_POST['nonce'], 'thesis-add-box');
		if (is_array($boxes = $this->_boxes->add($_POST['id']))) {
			update_option("{$this->_class}_boxes", $boxes);
			wp_cache_flush();
		}
		if ($thesis->environment == 'ajax') die();
	}

	public function _write_css() {
		global $thesis;
		$css = ($skin = get_option("{$this->_class}_css")) ? $skin : '';
		$custom = ($custom = get_option("{$this->_class}_css_custom")) ? $custom : '';
		if (property_exists($this, '_css'))
			$this->_css->write($css, $custom);
	}

	public function _save_css() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST['nonce'], 'thesis-save-css');
		if (isset($_POST['skin']))
			update_option("{$this->_class}_css", trim(strip_tags($_POST['skin'])));
		if (isset($_POST['custom']))
			update_option("{$this->_class}_css_custom", trim(strip_tags($_POST['custom'])));
		wp_cache_flush();
		$this->_write_css();
		echo $thesis->api->alert(__('CSS saved!', 'thesis'), 'css_saved', true);
		if ($thesis->environment == 'ajax') die();
	}

	public function _save_css_package() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		parse_str(stripslashes($_POST['pkg']), $pkg);
		$thesis->wp->nonce($pkg['_wpnonce-thesis-save-package'], 'thesis-save-package');
		if (is_array($packages = $this->_css->save_package($pkg))) {
			update_option("{$this->_class}_packages", $packages);
			echo $thesis->api->alert(__('Package saved!', 'thesis'), 'package_saved', true);
			wp_cache_flush();
		}
		else
			echo $thesis->api->alert(__('Package not saved.', 'thesis'), 'package_saved', true);
		if ($thesis->environment == 'ajax') die();
	}

	public function _delete_css_package() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		parse_str(stripslashes($_POST['pkg']), $pkg);
		$thesis->wp->nonce($pkg['_wpnonce-thesis-save-package'], 'thesis-save-package');
		if (is_array($packages = $this->_css->delete_package($pkg))) {
			if (empty($packages))
				delete_option("{$this->_class}_packages");
			else
				update_option("{$this->_class}_packages", $packages);
			wp_cache_flush();
			echo $thesis->api->alert(__('Package deleted!', 'thesis'), 'package_deleted', true);
		}
		else
			echo $thesis->api->alert(__('Package not deleted.', 'thesis'), 'package_deleted', true);
		if ($thesis->environment == 'ajax') die();
	}

	public function _save_css_variable() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST['nonce'], 'thesis-save-css-variable');
		if (is_array($save = $this->_css->save_variable($_POST['item']))) {
			update_option("{$this->_class}_vars", $save);
			wp_cache_flush();
			echo $thesis->api->alert(__('Variable saved!', 'thesis'), 'var_saved', true);
		}
		else
			echo $thesis->api->alert(__('Variable not saved.', 'thesis'), 'var_saved', true);
		if ($thesis->environment == 'ajax') die();
	}

	public function _delete_css_variable() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST['nonce'], 'thesis-save-css-variable');
		if (is_array($save = $this->_css->delete_variable($_POST['item']))) {
			if (empty($save))
				delete_option("{$this->_class}_vars");
			else
				update_option("{$this->_class}_vars", $save);
			wp_cache_flush();
			echo $thesis->api->alert(__('Variable deleted!', 'thesis'), 'var_deleted', true);
		}
		else
			echo $thesis->api->alert(__('Variable not deleted.', 'thesis'), 'var_deleted', true);
		if ($thesis->environment == 'ajax') die();
	}

	public function _color_complement() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		echo ($complement = $thesis->api->colors->complement($_POST['color'])) ? $complement : "N/A";
		if ($thesis->environment == 'ajax') die();
	}

	public function _login_form() {
		return
			'<p>'. __('Your WordPress login expires in <span id="t_countdown"></span> seconds. Please click the button below to save your skin. You will then be redirected to the WordPress login page.', 'thesis'). "</p>\n".
			'<p><button id="t_login_expiration" data-style="button save">'. __('Save Skin &amp; Login', 'thesis'). "</button></p>\n";
	}

	/*---:[ Front-end Skin output ]:---*/

	public function _skin() {
		global $thesis;
		if ($thesis->environment == 'editor')
			$this->_editor();
		else
			$this->_template();
		exit();
	}

	public function _query($query) {
		global $thesis;
		if (!$query->is_main_query()) return $query;
		$page = $custom = false;
		if ($query->is_page && ($page = !empty($query->queried_object_id) ? $query->queried_object_id : (!empty($query->query_vars['page_id']) ? $query->query_vars['page_id'] : false)) && !empty($page)) {
			$redirect = ($redirect = get_post_meta($page, '_thesis_redirect', true)) ? $redirect : false; #wp
			if (is_array($redirect) && !empty($redirect['url'])) {
				wp_redirect($redirect['url'], 301); #wp
				exit;
			}
			$custom = is_array($post_meta = get_post_meta($page, "_{$this->_class}", true)) ? (!empty($post_meta['template']) ? $post_meta['template'] : false) : false;
		}
		elseif ($query->is_category || $query->is_tax || $query->is_tag) { #wp
			$query->get_queried_object(); #wp
			if (!empty($thesis->wp->terms[$query->queried_object->term_id][$this->_class]['template']) && ($template = $thesis->wp->terms[$query->queried_object->term_id][$this->_class]['template']))
				$custom = !empty($template) ? $template : false;
			do_action('thesis_init_term', $query->queried_object->term_id);
		}
		do_action('thesis_init_template', $this->_template = $this->_templates->get_template($custom));
		return apply_filters('thesis_query', $query);
	}

	private function _template() {
		global $thesis, $wp_query;
		if (!is_array($this->_boxes->active)) return;
		$custom = false;
		if ($wp_query->is_single) {
			$redirect = ($redirect = get_post_meta($wp_query->post->ID, '_thesis_redirect', true)) ? $redirect : false; #wp
			if (is_array($redirect) && !empty($redirect['url'])) wp_redirect($redirect['url'], 301); #wp
			$custom = is_array($post_meta = get_post_meta($wp_query->post->ID, "_{$this->_class}", true)) ? (!empty($post_meta['template']) ? $post_meta['template'] : false) : false;
		}
		if ($wp_query->is_404 || $custom) {
			$this->_template = $this->_templates->get_template($custom);
			do_action('thesis_init_custom_template', $this->_template);
		}
		if ($wp_query->is_singular)
			do_action('thesis_init_post_meta', $wp_query->post->ID);
		if (is_array($preload = apply_filters('thesis_template_preload', array())) && !empty($preload)) {
			$boxes = $this->_preload(array('thesis_html_head', 'thesis_html_body'));
			if (is_array($boxes))
				foreach ($preload as $id)
					if (in_array($id, $boxes) && is_object($this->_boxes->active[$id]) && method_exists($this->_boxes->active[$id], 'preload'))
						$this->_boxes->active[$id]->preload();
		}
		echo
			"<!DOCTYPE html>\n",
			apply_filters('thesis_html_tag', "<html" . apply_filters('thesis_html_attributes', $thesis->wp->language_attributes()) . ">"), "\n";
		if (is_object($this->_boxes->active['thesis_html_head']))
			$this->_boxes->active['thesis_html_head']->html();
		if (is_object($this->_boxes->active['thesis_html_body']))
			$this->_boxes->active['thesis_html_body']->html();
		echo
			"</html>";
	}

	private function _preload($roots) {
		$boxes = array();
		foreach ($roots as $root) {
			if (!in_array($root, $boxes))
				$boxes[] = $root;
			if (!empty($this->_template['boxes'][$root]) && is_array($this->_template['boxes'][$root]))
			 	$boxes = array_merge_recursive($boxes, $this->_preload($this->_template['boxes'][$root]));
		}
		return $boxes;
	}

	public function _body_class($classes) {
		$add = $this->_template['id'];
		if (in_array($this->_template['id'], array_keys($this->_templates->custom_select())))
			$add = 'custom';
		$classes[] = "template-$add";
		return $classes;
	}

	/*---:[ automatic Skin options for WP integration ]:---*/

	public function _post_meta($post_meta) {
		global $thesis;
		$options = array(
			'thesis_redirect' => array(
				'title' => __('301 Redirect', 'thesis'),
				'fields' => array(
					'url' => array(
						'type' => 'text',
						'width' => 'full',
						'code' => true,
						'label' => sprintf(__('Redirect %s', 'thesis'), $thesis->api->base['url']),
						'tooltip' => sprintf(__('Use this handy tool to set up nice-looking affiliate links for your site. If you place a %1$s in this field, users will get redirected to this %1$s whenever they visit the %1$s defined in the <strong>Permalink</strong> above (located beneath the post title field).', 'thesis'), $thesis->api->base['url']),
						'description' => $thesis->api->strings['include_http']))),
			$this->_class => array(
				'title' => __('Thesis Skin Custom Template', 'thesis'),
				'context' => 'side',
				'priority' => 'default',
				'fields' => array(
					'template' => array(
						'type' => 'select',
						'label' => $thesis->api->strings['custom_template'],
						'options' => $this->_templates->custom_select()))));
		return is_array($post_meta) ? array_merge($post_meta, $options) : $options;
	}

	public function _term_options($term_options) {
		global $thesis;
		$options[$this->_class] = array(
			'template' => array(
				'type' => 'select',
				'label' => $thesis->api->strings['custom_template'],
				'options' => $this->_templates->custom_select()));
		return is_array($term_options) ? array_merge($term_options, $options) : $options;
	}

	/*---:[ Skin API valet methods ]:---*/

	public function color_scheme($scheme) {
		global $thesis;
		if (!is_array($scheme)) return;
		$label = !empty($scheme['label']) ? $scheme['label'] : __('Color Scheme', 'thesis');
		$tooltip = !empty($scheme['tooltip']) ? $scheme['tooltip'] : false;
		$scheme['default'] = $options = is_array($scheme['default']) ? $scheme['default'] : array();
		$values = array_merge($scheme['default'], $this->design);
		return array(
			'type' => 'custom',
			'label' => $label,
			'tooltip' => $tooltip,
			'options' => $options,
			'output' => $thesis->api->colors->scheme($scheme, $values, $this->_class));
	}

	public function header_image_html() {
		global $thesis;
		if (empty($this->header_image)) return;
		echo "<a href=\"", esc_url(home_url()), "\"><img id=\"thesis_header_image\" src=\"", esc_url($this->header_image['src']), "\" alt=\"", trim($thesis->api->escht((!empty($thesis->api->options['blogname']) ?
			htmlspecialchars_decode($thesis->api->options['blogname'], ENT_QUOTES). ' ' : ''). __('header image', 'thesis'))), "\" width=\"{$this->header_image['width']}\" height=\"{$this->header_image['height']}\" title=\"", __('click to return home', 'thesis'), "\" /></a>\n";
	}
}