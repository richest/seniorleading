<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_skin {
	private $_class; 					// (string) class name of the active skin
	private $_templates;				// (object) template controller
	private $_user_packages;			// (object) packages added by the user
	private $_menu = false;				// (array) Thesis will auto-set this to an array if you provide an admin page
	public $_boxes;						// (object) box controller
	public $_template = array();		// (array) current template data

	public function __construct() {
		global $thesis;
		define('THESIS_SKIN', THESIS_CORE . '/skin');
		require_once(THESIS_SKIN . '/box.php');
		require_once(THESIS_SKIN . '/boxes.php');
		require_once(THESIS_SKIN . '/templates.php');
		require_once(THESIS_SKIN . '/user_boxes.php');
		require_once(THESIS_SKIN . '/user_packages.php');
		$this->_class = get_class($this);
		$this->_boxes = new thesis_boxes($thesis->api->get_option("{$this->_class}_boxes"));
		$this->_templates = new thesis_templates($thesis->api->get_option("{$this->_class}_templates"));
		$this->_user_packages = new thesis_user_packages;
		$this->_actions();
		$this->_filters();
		$this->construct();
	}

	protected function construct() {
		// Secondary constructor for skins
	}

	private function _actions() {
		global $thesis;
		if (method_exists($this, 'boxes'))
			add_action('thesis_boxes', array($this, '_add_boxes'));
		add_action('template_redirect', array($this, '_redirect'));
		if ((!$thesis->environment && !is_admin()) || $thesis->environment == 'canvas') {
			add_action('parse_query', array($this, '_query'));
			if (!$thesis->environment)
				add_action('_thesis_editor_launcher', array($this, '_editor_launcher'));
		}
		if (!$thesis->environment) return;
		elseif ($thesis->environment == 'admin') {
			require_once(THESIS_SKIN . '/images.php');
			$this->_images = new thesis_images;
			add_action('init', array($this, '_init_admin'));
		}
		else {
			@ini_set('memory_limit', '128M');
			add_action('init', array($this, '_css'));
			if ($thesis->environment == 'editor') {
				remove_action('init', '_wp_admin_bar_init');
				add_action('init', array($this, '_init_editor'));
			}
			elseif ($thesis->environment == 'canvas')
				add_action('init', array($this, '_init_canvas'));
			elseif ($thesis->environment == 'ajax')
				add_action('init', array($this, '_init_ajax'));
		}
	}

	private function _filters() {
		if (!is_admin()) return;
		add_filter('thesis_post_meta', array($this, '_post_meta'), 11);
		add_filter('thesis_term_options', array($this, '_term_options'), 11);
	}

	public function _add_boxes($boxes) {
		if (file_exists(THESIS_USER_SKIN . '/box.php'))
			include_once(THESIS_USER_SKIN . '/box.php');
		return is_array($add_boxes = $this->boxes()) ? (is_array($boxes) ? array_merge($boxes, $add_boxes) : $add_boxes) : $boxes;
	}

	public function _init_admin() {
		global $thesis;
		new thesis_upload(array(
			'title' => sprintf(__('Import %s Data', 'thesis'), $thesis->skins->skin['name']),
			'prefix' => 'import_skin',
			'file_type' => 'txt'));
		if (!empty($_GET['canvas']) && $_GET['canvas'] == 'head') {
			wp_enqueue_style('thesis-objects', THESIS_CSS_URL . '/objects.css', array('thesis-admin'), $thesis->version); #wp
			wp_enqueue_style('thesis-box-form', THESIS_CSS_URL . '/box_form.css', array('thesis-objects'), $thesis->version); #wp
			wp_enqueue_script('jquery-ui-droppable'); #wp
			wp_enqueue_script('jquery-ui-sortable'); #wp
			wp_enqueue_script('thesis-ui', THESIS_JS_URL . '/ui.js', array('thesis-menu'), $thesis->version); #wp
			wp_enqueue_script('thesis-head', THESIS_JS_URL . '/head.js', array('thesis-ui'), $thesis->version); #wp
			add_action('thesis_admin_canvas', array($this, '_head_editor'));
		}
		if (method_exists($this, 'admin') && is_array($admin = $this->admin()) && !empty($admin['page'])) {
			$this->_menu[$this->_class] = array(
				'text' => !empty($admin['text']) ? $admin['text'] : sprintf(__('%s Options', 'thesis'), $thesis->skins->skin['name']),
				'url' => admin_url("admin.php?page=thesis&canvas=$this->_class"));
			add_filter(!empty($admin['menu']) && $admin['menu'] == 'site' ? 'thesis_site_menu' : 'thesis_skins_menu', array($this, '_add_menu'));
			if (!empty($_GET['canvas']) && $_GET['canvas'] == $this->_class && method_exists($this, $admin['page'])) {
				add_action('thesis_admin_canvas', array($this, $admin['page']));
				if (method_exists($this, 'admin_init'))
					$this->admin_init();
			}
		}
	}

	public function _add_menu($menu) {
		return is_array($this->_menu) ? (is_array($menu) ? array_merge($menu, $this->_menu) : $this->_menu) : $menu;
	}

	public function _css() {
		global $thesis;
		require_once(THESIS_SKIN . '/css.php');
		$css = array();
		$css['css'] = ($skin = $thesis->api->get_option("{$this->_class}_css")) ? stripslashes($skin) : '';
		$css['custom'] = ($custom = $thesis->api->get_option("{$this->_class}_css_custom")) ? stripslashes($custom) : '';
		$css['packages'] = is_array($packages = $thesis->api->get_option("{$this->_class}_packages")) ? $packages : array();
		$css['user_packages'] = is_array($this->_user_packages->active) ? $this->_user_packages->active : array();
		$css['vars'] = is_array($vars = $thesis->api->get_option("{$this->_class}_vars")) ? $vars : array();
		if (method_exists($this, 'packages')) {
			add_action('thesis_include_packages', array($this, '_include_packages'));
			add_filter('thesis_packages', array($this, '_add_packages'));
		}
		if (method_exists($this, 'fonts'))
			add_filter('thesis_fonts', array($this, '_add_fonts'));
		$this->_css = new thesis_css($css);
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

	public function _init_editor() {
		add_action('thesis_editor_head', array($this, '_editor_head'));
		add_action('thesis_editor_scripts', array($this, '_editor_scripts'));
		do_action('thesis_init_editor');
	}

	public function _editor_head() {
		global $thesis;
		$this->_launch_canvas();
		echo
			"<link rel=\"shortcut icon\" href=\"" . THESIS_IMAGES_URL . "/icon-swatch.png\" />\n".
			"<link rel=\"stylesheet\" type=\"text/css\" href=\"" . THESIS_CSS_URL . "/editor.css?ver={$thesis->version}\" />\n";
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
		if (is_object($wp_scripts) && is_array($wp_scripts->registered))
			foreach ($includes as $script)
				if (is_object($wp_scripts->registered[$script]) && $src = $wp_scripts->registered[$script]->src)
					$scripts[$script] = $wp_scripts->base_url . $src;
		$scripts['editor'] = THESIS_JS_URL . '/editor.js';
		$scripts['ui'] = THESIS_JS_URL . '/ui.js';
		foreach ($scripts as $script => $src)
			echo "<script src=\"$src?ver={$thesis->version}\"></script>\n";
	}

	public function _init_canvas() {
		add_action('thesis_hook_head', array($this, '_canvas_js'));
		do_action('thesis_init_canvas');
	}

	public function _init_ajax() {
		add_action('wp_ajax_add_box', array($this, '_add_box'));
		add_action('wp_ajax_save_head', array($this, '_save_head'));
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
		if (method_exists($this, 'admin_ajax'))
			$this->admin_ajax();
	}

	private function _launch_canvas() {
		$real_scheme = defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN === true ? 'https' : 'http';		
		$parsed = parse_url((!empty($_SERVER['HTTP_REFERER']) && strpos(strtolower($_SERVER['HTTP_REFERER']), strtolower(admin_url())) !== 0 ? $_SERVER['HTTP_REFERER'] : home_url('', $real_scheme)));
		extract($parsed);
		$query = isset($query) ? str_ireplace('thesis_editor=1', '', $query) : '';
		$url = $real_scheme . '://' . trailingslashit($host) . (!empty($path) && $path != '/' ? trailingslashit($path) : '' ) . '?' . (! empty($query) ? rtrim($query, '&') . '&' : '') . 'thesis_canvas=1&thesis_canvas_nonce=' . wp_create_nonce('thesis-canvas-url') . (!empty($fragment) ? "#$fragment" : '');
		$name = wp_create_nonce('thesis-canvas-name');
		$canvas = wp_create_nonce('thesis-canvas');
		echo
			"<script type=\"text/javascript\">\n".
			"window.name = '$name';\n".
			"var thesis_canvas = {\n".
			"\turl: '" . esc_url_raw($url) . "',\n".
			"\tname: '$canvas' };\n".
			"var thesis_ajax = { url: '" . str_replace('/', '\/', admin_url("admin-ajax.php")) . "' };\n".
			"</script>\n";
	}

	public function _canvas_js() {
		if (!($template = $this->_template['id'])) return;
?>
<script type="text/javascript">	
	var template = '<?php echo $template; ?>';
	window.opener.thesis_templates.get(template);
	document.onclick = canvas_control;
	function canvas_control(e) {
		if (e.target.localName == 'a' && e.target.host == window.location.host) {
			var protocol = host = path = search = hash = thesis_query = url = '';
			protocol = e.target.protocol + '//';
			host = e.target.host;
			thesis_query = 'thesis_canvas=1&thesis_canvas_nonce=<?php echo wp_create_nonce('thesis-canvas-url'); ?>';
			if (typeof e.target.pathname == 'string')
				path = (e.target.pathname.charAt(0) != '/' ? '/' :'') + e.target.pathname + (e.target.pathname.charAt(e.target.pathname.length - 1) != '/' ? '/' :'');
			if (typeof e.target.search == 'string')
				search = e.target.search.replace(/thesis_canvas=1&thesis_canvas_nonce=\w+/, '');
			if (typeof e.target.hash == 'string')
				hash = e.target.hash;
			url = protocol + host + path + (search.charAt(0) != '?' || search.length == 0 ? '?' : search + '&') + thesis_query + hash;
			window.opener.thesis_editor.get_canvas(url);
		}
		return false;
	}
</script>
<?php
	}

	public function _editor_launcher() {
		global $thesis;
		if (!current_user_can('edit_theme_options') || $thesis->wp_customize === true) return;
		$scheme = defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN === true ? 'https' : 'http';
		echo
			"<style type=\"text/css\">\n".
			"#thesis_launcher { position: fixed; bottom: 0; left: 0; font: bold 16px/1em \"Helvetica Neue\", Helvetica, Arial, sans-serif; padding: 12px; text-align: center; color: #fff; background: rgba(0,0,0,0.5); text-shadow: 0 1px 1px rgba(0,0,0,0.75); }\n".
			"#thesis_launcher input { font-size: 16px; margin-top: 6px; }\n".
			"</style>\n".
			"<div id=\"thesis_launcher\">\n".
			"\t<form method=\"post\" action=\"" . home_url('?thesis_editor=1', $scheme) . "\">\n".
			"\t\t<p>" . $thesis->api->esch(ucfirst($this->_template['title'])) . "</p>\n".
			"\t\t<p>\n".
			"\t\t\t<input type=\"hidden\" name=\"thesis_template\" value=\"{$this->_template['id']}\" />\n".
			"\t\t\t<input type=\"submit\" name=\"thesis_editor\" value=\"" . esc_attr($thesis->api->strings['click_to_edit']) . "\" />\n".
			"\t\t</p>\n".
			"\t</form>\n".
			"</div>\n";
	}

	public function _query($query) {
		global $thesis;
		if (!$query->is_main_query()) return $query;
		$page = $custom = false;
		if ($query->is_page && ($page = !empty($query->queried_object_id) ? $query->queried_object_id : (!empty($query->query_vars['page_id']) ? $query->query_vars['page_id'] : false)) && !empty($page)) {
			$redirect = ($redirect = get_post_meta($page, '_thesis_redirect', true)) ? $redirect : false; #wp
			if (is_array($redirect) && !empty($redirect['url'])) wp_redirect($redirect['url'], 301); #wp
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

	public function _redirect() {
		global $thesis;
		if ($thesis->environment == 'editor')
			$this->_editor();
		elseif (is_feed())
			do_feed();
		else
			$this->_template();
		exit();
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
			"<!DOCTYPE html>\n".
			"<html" . apply_filters('thesis_html_attributes', $thesis->wp->language_attributes()) . ">\n";
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
			$li .= "\t<li><button class=\"t_pane_switch\" data-pane=\"$pane\" title=\"{$m['title']}\">{$m['text']}</button></li>\n";
		$menu =
			"<ul id=\"t_menu\">\n".
			$li.
			"\t<li class=\"t_logo t_right\"><a href=\"" . esc_url(admin_url('admin.php?page=thesis')) . "\" title=\"" . __('return to the Thesis admin page', 'thesis') . "\">Thesis</a></li>\n".
			"\t<li class=\"t_right\"><a class=\"t_menu_link\" href=\"" . esc_url(home_url()) . '">' . __('View Site', 'thesis') . "</a></li>\n".
			"</ul>\n";
		echo
			"<!DOCTYPE html>\n".
			"<html" . $thesis->wp->language_attributes() . ">\n".
			"<head>\n".
			"<title>" . __('Thesis Skin Editor', 'thesis') . "</title>\n";
		do_action('thesis_editor_head');
		echo
			"</head>\n".
			"<body>\n".
			$menu.
			"<div id=\"t_editor\" data-style=\"box\">\n".
			"\t<div id=\"t_html\" class=\"t_pane\" data-style=\"box\">\n".
			$this->_templates->editor($this->_template_form()).
			"\t</div>\n".
			"\t<div id=\"t_css\" class=\"t_pane\" data-style=\"box\">\n".
			$this->_css->editor().
			"\t</div>\n".
			"\t<div id=\"t_images\" class=\"t_pane\" data-style=\"box\">\n".
			$thesis->api->uploader('thesis_images').
			"\t</div>\n".
			"\t<div id=\"t_manager\" class=\"t_pane\" data-style=\"box\">\n".
			$thesis->skins->manager->editor().
			"\t</div>\n".
			"</div>\n";
		do_action('thesis_editor_scripts');
		echo
			"</body>\n".
			"</html>\n";
	}

	public function _head_editor() {
		echo $this->_templates->head($this->_boxes->get_box_form_data($this->_templates->head, true));
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

	public function _save_head() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		parse_str(stripslashes($_POST['form']), $form);
		$thesis->wp->nonce($form['_wpnonce-thesis-ajax'], 'thesis-save-head');
		if ($head = $this->_templates->save_head($form)) {
			if (is_array($head))
				$this->_boxes->delete($head);
			$this->_boxes->save($form, true);
			echo $thesis->api->alert(__('Head saved!', 'thesis'), 'head_saved', true);
		}
		else
			echo $thesis->api->alert(__('Head not saved.', 'thesis'), 'head_saved', true);
		if ($thesis->environment == 'ajax') die();
	}

	public function _add_box() {
		global $thesis;
		$thesis->wp->nonce($_POST['nonce'], 'thesis-add-box');
		if (is_array($boxes = $this->_boxes->add($_POST['box']))) {
			update_option("{$this->_class}_boxes", $boxes);
			wp_cache_flush();
		}
		if ($thesis->environment == 'ajax') die();
	}

	public function _save_css() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST['nonce'], 'thesis-save-css');
		update_option("{$this->_class}_css", strip_tags($_POST['skin']));
		update_option("{$this->_class}_css_custom", strip_tags($_POST['custom']));
		wp_cache_flush();
		$this->_css->write(strip_tags($_POST['skin']), strip_tags($_POST['custom']));
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
						'description' => $thesis->api->strings['include_http'],
						'legacy' => 'thesis_redirect'))),
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
}