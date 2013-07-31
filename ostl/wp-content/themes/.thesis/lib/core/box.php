<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_box {
	// optional properties for box extensions (to be defined by developer as necessary)
	public $type = 'box';					// (string) possible types: box, rotator, false (false is like a plugin)
	public $title = false;					// (string) required for type 'box' and 'rotator'; must be defined in translate() for translation
	public $name = false;					// (string) For multi-instance boxes, supply a name; must be defined in translate() for translation
	public $root = false;					// (bool) Currently, only <head> and <body> should be considered roots
	public $head = false;					// (bool) True = box goes in the <head>; false = <body>.
	public $dependents = false;				// (array) class names of dependent boxes
	public $children = false;				// (array) class names of dependent boxes that are active when the parent is added
	public $switch = false;					// (bool) Set to true if the box contents should be visible on admin page load (rotators only).
	public $templates = array(				// (array) Top-level, core templates for which this box is valid
		'home',
		'single',
		'page',
		'archive');
	protected $filters = array();			// (array) API for certain box overrides
	// critical reserved properties set by the box constructor
	public $_class;							// (string) quick reference for this box's class name
	public $_id;							// (string) unique identifier for this box
	// reserved properties set by the box constructor and NOT intended for use by box extensions
	public $_parent = false;				// (string) unique ID of parent box
	public $_lineage = false;				// (string) breadcrumb-style ID showing parent/child box relationships
	public $_switch = false;				// (bool) toggle the rotator by default on the template editor?
	public $_uploader = array();			// (array) contains uploader options
	public $_menu = false;					// (array) admin menu properties, if applicable
	public $_link = false;					// (array) skin admin link to instance-based options page
	// reserved properties set by the constructor and intended for use in box extensions
	public $options = array();				// (array) options for this instance (in the context of the current Skin)
	public $class_options = array();		// (array) class-specific options for this box
	public $post_meta = array();			// (array) this box's post meta data for the current page, if it exists
	public $term_options = array();			// (array) this box's term options data for the current term, if it exists
	public $template_options = array();		// (array) this box's template options data for the current template, if it exists

	public function __construct($box = array()) {
		global $thesis;
		extract($box); // $id, $options, $parent, $lineage, $check
		$this->_class = strtolower(get_class($this));
		$this->_id = !empty($id) ? $id : $this->_class;
		if (method_exists($this, 'translate'))
			$this->translate();
		if (!empty($parent)) {
			$this->_id = !empty($id) ? $this->_id : "{$parent}_$this->_id";
			$this->_parent = $parent;
			$this->_lineage = !empty($lineage) ? $lineage : $this->_lineage;
		}
		$this->class_options = method_exists($this, 'class_options') && is_array($class_options = $thesis->api->get_option($this->_class)) ? $class_options : $this->class_options;
		$this->options = array_merge($this->class_options, !empty($options) && is_array($options) ? $options : $this->options);
		$this->name = !empty($this->options['_name']) ? stripslashes($this->options['_name']) : $this->name;
		$this->_switch = isset($this->options['_admin']['open']) ? (bool) $this->options['_admin']['open'] : $this->switch;
		$this->_uploader = method_exists($this, 'uploader') && is_array($uploader = $this->uploader()) ? $uploader : $this->_uploader;
		if (!empty($this->dependents) && is_array($this->dependents))
			$this->dependents = is_array($dependents = apply_filters("{$this->_class}_dependents", $this->dependents)) ? $dependents : $this->dependents;
		if (!empty($this->children) && is_array($this->children))
			$this->children = is_array($children = apply_filters("{$this->_class}_children", $this->children)) ? $children : $this->children;
		if (method_exists($this, 'post_meta')) {
			add_filter('thesis_post_meta', array($this, '_add_post_meta'));
			add_action('thesis_init_post_meta', array($this, '_get_post_meta'));
		}
		if (method_exists($this, 'term_options')) {
			add_filter('thesis_term_options', array($this, '_add_term_options'));
			add_action('thesis_init_term', array($this, '_get_term_options'));
		}
		if (method_exists($this, 'template_options')) {
			add_filter('thesis_template_options', array($this, '_add_template_options'));
			add_action('thesis_init_template', array($this, '_get_template_options'));
			add_action('thesis_init_custom_template', array($this, '_get_template_options'));
		}
		if (method_exists($this, 'preload'))
			add_filter('thesis_template_preload', array($this, '_preload'));
		$this->construct();
		if ($thesis->environment == 'admin' || $thesis->environment == 'thesis')
			$this->_box_admin();
		if ($thesis->environment == 'ajax' && method_exists($this, 'admin_ajax'))
			$this->admin_ajax();
		if (in_array($thesis->environment, array('canvas', 'ajax', 'admin')) && method_exists($this, 'filter_css'))
			add_filter('thesis_css', array($this, 'filter_css'), 10, 3);
	}

	protected function _display() {
		global $thesis;
		return !apply_filters("{$this->_class}_". trim($thesis->api->esc(
			(!empty($this->name) || !empty($this->_parent))
			&& !$this->root
			&& !empty($this->options['_id']) ?
				"{$this->options['_id']}_" : '')). "show", true) ? false : true;
	}

	public function _class_options() {
		return method_exists($this, 'class_options') && is_array($options = apply_filters("{$this->_class}_class_options", $this->class_options())) ? $options : array();
	}

	public function _options($ignore_display = false) {
		return
			method_exists($this, 'options')
			&& is_array($options = apply_filters("{$this->_class}_options", $this->options()))
			&& ($this->_display() || !empty($ignore_display)) ?
				$options : array();
	}

	public function _html_options() {
		return method_exists($this, 'html_options') && is_array($options = apply_filters("{$this->_class}_html_options", $this->html_options())) ? $options : array();
	}

	public function _add_post_meta($post_meta) {
		return
			is_array($options[$this->_class] = apply_filters("{$this->_class}_post_meta", $this->post_meta()))
			&& $this->_display() ?
				(is_array($post_meta) ?
					array_merge($post_meta, $options) :
					$options) :
				$post_meta;
	}

	public function _add_term_options($term_options) {
		return is_array($options[$this->_class] = apply_filters("{$this->_class}_term_options", $this->term_options())) ? (is_array($term_options) ? array_merge($term_options, $options) : $options) : $term_options;
	}

	public function _add_template_options($template_options) {
		return is_array($options[$this->_class] = apply_filters("{$this->_class}_template_options", $this->template_options())) ? (is_array($template_options) ? array_merge($template_options, $options) : $options) : $template_options;
	}

	public function _get_post_meta($post_id) {
		$this->post_meta = !is_numeric($post_id) || !is_array($post_meta = get_post_meta($post_id, "_{$this->_class}", true)) ? array() : $post_meta;
	}

	public function _get_term_options($term_id) {
		global $thesis;
		if (!is_numeric($term_id) || empty($thesis->wp->terms[$term_id][$this->_class]) || (!empty($thesis->wp->terms[$term_id][$this->_class]) && !is_array($thesis->wp->terms[$term_id][$this->_class]))) return;
		$this->term_options = $thesis->wp->terms[$term_id][$this->_class];
	}

	public function _get_template_options($template) {
		if (!is_array($template) || (!empty($template['options'][$this->_class]) && !is_array($template['options'][$this->_class]))) return;
		$this->template_options = !empty($template['options'][$this->_class]) ? $template['options'][$this->_class] : false;
	}

	public function _preload($boxes) {
		$boxes[] = $this->_id;
		return $boxes;
	}

	public function _add_menu($menu) {
		return is_array($this->_menu) ? (is_array($menu) ? array_merge($menu, $this->_menu) : $this->_menu) : $menu;
	}

	private function _box_admin() {
		global $thesis;
		if (($instance = (empty($this->head) && is_array($options = $this->_options()) && !empty($options) && ((!empty($this->name) && $this->_id != $this->_class) || empty($this->name) && $this->_id == $this->_class)))) {
			$id = (empty($this->name) ? 'skin_' : ''). $this->_id;
			$this->_instance[$id] = array(
				'text' => (!empty($this->_lineage) ? $this->_lineage : ''). (!empty($this->name) ? $this->name : $this->title),
				'url' => admin_url("admin.php?page=thesis&canvas=$id"));
			add_filter('thesis_skin_instances', array($this, '_add_instance'));
		}
		if ($thesis->environment == 'admin') {
			if (method_exists($this, 'class_options') && empty($this->filters['url']))
				add_action("admin_post_$this->_class", array($this, '_save_admin'));
		}
		if ($thesis->environment == 'thesis') { // This needs to be re-worked to initiate instances on WP pages as well
			$this->_class_admin();
			if (!empty($instance) && !empty($_GET['canvas']) && $_GET['canvas'] == $id) {
				add_action('admin_init', array($this, '_admin_init'));
				add_action('thesis_admin_canvas', array($this, '_instance_canvas'));
			}
		}
	}

	public function _add_instance($instances) {
		return is_array($this->_instance) ? (is_array($instances) ? array_merge($instances, $this->_instance) : $this->_instance) : $instances;
	}

	public function _html_admin() {
		global $thesis;
		$html = array();
		if ($this->root) return $html;
		$html['_admin'] = $this->type == 'rotator' ? array(
			'type' => 'checkbox',
			'label' => __('Admin Visibility', 'thesis'),
			'options' => array(
				'open' => __('Box is open by default (template editor only)', 'thesis')),
			'default' => array(
				'open' => (bool) $this->switch)) : false;
		$html['_id'] = !empty($this->name) || !empty($this->_parent) ? array_merge(array(
			'type' => 'text',
			'width' => 'medium',
			'code' => true), $this->type == 'rotator' ? array(
			'label' => __('Hook Name', 'thesis'),
			'tooltip' => sprintf(__('Specify a hook name here, and you&#8217;ll be able to target this Box in your %s. Use this to add additional flexibility to your Skin.', 'thesis'), $thesis->api->base['php'])) : array(
			'label' => __('Programmatic ID', 'thesis'),
			'tooltip' => sprintf(__('Specify a programmatic ID here, and you&#8217;ll be able to control this Box with <a href="%s">Skin display options</a>. Use this to add additional flexibility to your Skin.', 'thesis'), 'http://diythemes.com/thesis/rtfm/api/skin/#section-display'))) : false;
		return array_filter($html);
	}

	private function _class_admin() {
		global $thesis;
		if (!((method_exists($this, 'class_options') || method_exists($this, 'admin') || !empty($this->filters['admin'])) && !in_array($this->_class, $thesis->box_admin))) return;
		$this->_menu[$this->_class] = array(
			'text' => !empty($this->filters['text']) ? $this->filters['text'] : $this->title,
			'url' => !empty($this->filters['url']) ? esc_url($this->filters['url']) : admin_url("admin.php?page=thesis&canvas=$this->_class"));
		add_filter(!empty($this->filters['menu']) ? ($this->filters['menu'] == 'site' ?
			'thesis_site_menu' : ($this->filters['menu'] == 'skin' || $this->filters['menu'] == 'skins' ?
			'thesis_skin_menu' : 'thesis_boxes_menu')) :
			'thesis_boxes_menu', array($this, '_add_menu'), !empty($this->filters['priority']) && is_numeric($this->filters['priority']) ? $this->filters['priority'] : 10);
		if (empty($this->filters['url'])) {
			if (!empty($_GET['canvas']) && $_GET['canvas'] == $this->_class) {
				add_action('thesis_admin_canvas', array($this, !empty($this->filters['admin']) && method_exists($this, $this->filters['admin']) ?
					$this->filters['admin'] : (method_exists($this, 'admin') ?
					'admin' :
					'_class_canvas')));
				add_action('admin_init', array($this, '_admin_init'));
				if (method_exists($this, 'admin_init'))
					add_action('admin_init', array($this, 'admin_init'));
			}
		}
		$thesis->box_admin[] = $this->_class;
	}

	public function _admin_init() {
		wp_enqueue_style('thesis-options');
		wp_enqueue_script('thesis-options');
	}

	public function _class_canvas() {
		global $thesis;
		$options = $thesis->api->form->fields($this->_class_options(), $this->class_options, '', $this->_class, 3, 10);
		echo
			(!empty($_GET['saved']) ? $thesis->api->alert(wptexturize($_GET['saved'] === 'yes' ?
			sprintf(__('%s saved!', 'thesis'), $this->title) :
			sprintf(__('%s not saved. Please try again.', 'thesis'), $this->title)), 'options_saved', true, false, 2) : ''),
			"\t\t<h3>", wptexturize($this->title), "</h3>\n",
			"\t\t<form class=\"thesis_options_form", (!empty($this->filters['canvas_left']) ? ' t_canvas_left' : ''), "\" method=\"post\" action=\"", admin_url("admin-post.php?action=$this->_class"), "\" enctype=\"multipart/form-data\">\n",
			$options['output'],
			"\t\t\t<input type=\"submit\" data-style=\"button save\" class=\"t_save\" id=\"save_options\" value=\"", esc_attr(wptexturize(strip_tags(sprintf(__('Save %s', 'thesis'), $this->title)))), "\" />\n",
			"\t\t\t", wp_nonce_field($this->_class, "_wpnonce-$this->_class", true, false), "\n",
			"\t\t</form>\n";
	}

	public function _save_admin() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST["_wpnonce-$this->_class"], $this->_class);
		$saved = 'no';
		if (!empty($_POST[$this->_class])) {
			$this->class_options = $save = $thesis->api->set_options($this->_class_options(), $_POST[$this->_class]);
			if (empty($save))
				delete_option($this->_class);
			else
				update_option($this->_class, $save);
			if (method_exists($this, 'save_admin'))
				$this->save_admin($save);
			if (method_exists($this, 'filter_css'))
				$thesis->skin->_write_css();
			$saved = 'yes';
		}
		if ($saved == 'yes')
			wp_cache_flush();
		wp_redirect("admin.php?page=thesis&canvas=$this->_class&saved=$saved");
		exit;
	}

	public function _instance_canvas() {
		global $thesis;
		$options = $thesis->api->form->fields($this->_options(), $this->options, '', "{$this->_class}[$this->_id]", 3, 10);
		echo
			"\t\t<h3>", (!empty($this->_lineage) ? $this->_lineage : ''), (!empty($this->name) ? $this->name : $this->title), " ", __('Options', 'thesis'), "</h3>\n",
			"\t\t<form id=\"box_options_$this->_id\" class=\"thesis_options_form", (!empty($this->filters['instance_canvas_left']) ? ' t_canvas_left' : ''), "\" method=\"post\" action=\"\" enctype=\"multipart/form-data\">\n",
			$options['output'],
			"\t\t\t<input type=\"submit\" data-style=\"button save\" class=\"t_save thesis_save_ajax\" id=\"save_options\" data-type=\"box\" name=\"save_options\" value=\"", __('Save Options', 'thesis'), "\" />\n",
			"\t\t\t", wp_nonce_field('thesis-save-box', '_wpnonce-thesis-save-box', true, false), "\n",
			"\t\t</form>\n";
	}

	public function _save($form) {
		global $thesis;
		if (empty($form) || !is_array($form) || empty($form[$this->_class][$this->_id]) || !is_array($values = $form[$this->_class][$this->_id])) return false;
		$box = array();
		if (is_array($options = $thesis->api->set_options(array_merge($this->_options(), $this->_html_options(), $this->_html_admin(), $this->_uploader), array_merge($this->options, $values))))
			$box = $options;
		if ($this->name)
			$box['_name'] = !empty($values['_name']) ? $values['_name'] : $this->name;
		if (empty($box)) return 'delete';
		if ($this->_parent)
			$box['_parent'] = $this->_parent;
		return $box;
	}

	protected function construct() {
		// secondary constructor for boxes that need to initiate things before the page loads
	}

	protected function rotator($args = array()) {
		global $thesis;
		if (!empty($thesis->skin->_template['boxes'][$this->_id]) && is_array($thesis->skin->_template['boxes'][$this->_id]))
			foreach ($thesis->skin->_template['boxes'][$this->_id] as $box)
				if (!empty($thesis->skin->_boxes->active[$box])
				&& is_object($thesis->skin->_boxes->active[$box])
				&& !empty($thesis->skin->_boxes->active[$box]->type)
				&& method_exists($thesis->skin->_boxes->active[$box], 'html')
				&& $thesis->skin->_boxes->active[$box]->_display()) {
					$args = func_get_args();
					call_user_func_array(array($thesis->skin->_boxes->active[$box], 'html'), $args);
				}
	}

	public function html() {
		// This method determines the box's HTML output and should be overwritten by box extensions.
	}
}