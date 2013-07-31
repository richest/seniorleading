<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_packages {
	private $packages = array();		// array of saved package data relative to the current skin
	private $active = array();			// all active package objects
	private $available = array();		// all available package objects
	private $core = array(				// core package classes
		'thesis_package_basic',
		'thesis_package_links',
		'thesis_package_wp_nav',
		'thesis_package_post_format',
		'thesis_package_columns',
		'thesis_package_wp_comments',
		'thesis_package_input',
		'thesis_package_wp_widgets');

	public function __construct($packages, $user_packages = false) {
		global $thesis;
		require_once(THESIS_CSS . '/package.php');
		do_action('thesis_include_packages');
		$this->packages = is_array($packages) ? $packages : $this->packages;
		$this->active();
		$this->available($user_packages);
		if ($thesis->environment == 'ajax')
			add_action('wp_ajax_edit_package', array($this, 'edit'));
	}

	private function active() {
		foreach ($this->packages as $class => $pkgs)
			if (class_exists($class) && is_subclass_of($class, 'thesis_package') && is_array($pkgs))
				foreach ($pkgs as $id => $options)
					if (is_array($options)) {
						$pkg = new $class($id, $options);
						$this->active[$pkg->_id] = $pkg;
					}
	}

	private function available($user) {
		$core = is_array($classes = apply_filters('thesis_packages', $this->core)) ? $classes : $this->core;
		$user = is_array($user) ? $user : array();
		foreach (array_merge($core, $user) as $class)
			if (class_exists($class) && is_subclass_of($class, 'thesis_package'))
				$this->available[$class] = new $class;
	}

	public function items($depth) {
		global $thesis;
		$tab = str_repeat("\t", $depth);
		$available = $active = array();
		$packages = '';
		foreach ($this->available as $class => $pkg)
			$available[$class] = strip_tags($pkg->title);
		natcasesort($available);
		$options = array('' => __('Select package to add:', 'thesis')) + $available;
		$select = $thesis->api->form->fields(array(
			't_select_package' => array(
				'type' => 'select',
				'options' => $options)), array(), false, false, 4, $depth + 1);
		foreach ($this->active as $id => $pkg)
			$active[$id] = $pkg->options['_name'];
		natcasesort($active);
		foreach ($active as $id => $name) {
			$pkg = $this->active[$id];
			$packages .=
				"$tab\t<li>".
				"<code class=\"t_edit_item css_draggable\" data-type=\"pkg\" data-class=\"$pkg->_class\" data-id=\"$id\" data-value=\"&amp;{$pkg->options['_ref']}\" data-tooltip=\"". esc_attr($pkg->options['_name']). ' ('. esc_attr($pkg->title). ")\" title=\"". esc_attr($thesis->api->strings['click_to_edit']). "\">&amp;". $thesis->api->esch($pkg->options['_ref']). "</code>".
				"</li>\n";
		}
		return
			"$tab<form method=\"post\" action=\"\">\n".
			$select['output'].
			"$tab\t<input type=\"submit\" data-style=\"button action\" id=\"t_add_package\" name=\"add_package\" value=\"". __('Add Package', 'thesis'). "\" />\n".
			"$tab</form>\n".
			"$tab<ul class=\"t_item_list\">\n".
			$packages.
			"$tab</ul>\n";
	}

	public function edit() {
		global $thesis;
		$thesis->wp->nonce($_POST['nonce'], 'thesis-save-css');
		$pkg = $_POST['pkg'];
		if (empty($pkg) || empty($pkg['id']) || empty($pkg['class'])) die();
		$package = false;
		if ($pkg['id'] == 'new_package' && is_object($this->available[$pkg['class']])) {
			$package = $this->available[$pkg['class']];
			$package->_id = "{$package->_class}_" . time();
		}
		elseif (!empty($this->active[$pkg['id']]) && is_object($this->active[$pkg['id']]))
			$package = $this->active[$pkg['id']];
		if (!is_object($package)) die();
		echo $package->_edit();
		if ($thesis->environment == 'ajax') die();
	}

	public function save($pkg) {
		if (!is_array($pkg) || empty($pkg['id']) || empty($pkg['class'])) return false;
		$save = !empty($this->active[$pkg['id']]) && is_object($this->active[$pkg['id']]) ?
			$this->active[$pkg['id']]->_save($pkg) : (is_object($this->available[$pkg['class']]) ?
			$this->available[$pkg['class']]->_save($pkg) : false);
		if (is_array($save)) {
			$this->packages[$pkg['class']][$pkg['id']] = $save;
			return $this->packages;
		}
		return false;
	}

	public function delete($pkg) {
		if (!is_array($pkg) || empty($pkg['id']) || !is_object($delete = $this->active[$pkg['id']])) return false;
		unset($this->packages[$delete->_class][$delete->_id]);
		if (empty($this->packages[$delete->_class]))
			unset($this->packages[$delete->_class]);
		return $this->packages;
	}

	public function css($css) {
		if (empty($css)) return array();
		$clearfix = array();
		foreach ($this->active as $id => $pkg) {
			$css = preg_replace('/&' . $pkg->options['_ref'] . '((?=[\s\r\n])|\Z)/i', str_replace('$', '\$', $pkg->css()) . (!empty($pkg->options['_css']) ? "\n" . trim(str_replace('$', '\$', $pkg->options['_css'])) : ''), $css, 1);
			if (is_array($pkg->clearfix))
				$clearfix = array_merge($clearfix, $pkg->clearfix);
		}
		return array(
			'css' => $css,
			'clearfix' => $clearfix);
	}
}