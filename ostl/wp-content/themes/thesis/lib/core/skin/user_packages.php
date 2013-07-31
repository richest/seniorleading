<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_user_packages {
	private $packages = array();	// (array) format: ('package_class' => 'current_folder')
	public $active = array();		// (array) all active user package classes

	public function __construct() {
		global $thesis;
		if ($thesis->environment == 'admin') {
			$args = array(
				'title' => __('Thesis Upload Package', 'thesis'),
				'prefix' => 'thesis_package_uploader',
				'folder' => 'package');
			$this->upload = new thesis_upload($args);
		}
		if (!($thesis->environment == 'editor' || $thesis->environment == 'ajax' || ($thesis->environment == 'admin' && !empty($_GET['canvas']) && $_GET['canvas'] == 'packages'))) return;
		$this->packages = is_array($packages = $thesis->api->get_option('thesis_packages')) ? $packages : $this->packages;
		$this->active = array_keys($this->packages);
		add_action('thesis_include_packages', array($this, 'include_packages'));
		add_action('thesis_admin_canvas', array($this, 'canvas'));
		$this->installed = $this->get_items();
		add_action('init', array($this, 'queue_scripts'));
		if ($thesis->environment == 'ajax') {
			add_action('wp_ajax_save_packages', array($this, 'save')); #wp
		}
	}
	
	public function queue_scripts() {
		global $thesis;
		wp_enqueue_style('thesis-objects', THESIS_CSS_URL . '/objects.css', array('thesis-admin'), $thesis->version); #wp
		wp_enqueue_style('thesis-packages', THESIS_CSS_URL . '/packages.css', array('thesis-objects'), $thesis->version); #wp
		wp_enqueue_script('thesis-packages', THESIS_JS_URL . '/packages.js', array('thesis-menu'), $thesis->version); #wp
	}

	public function include_packages() {
		foreach ($this->packages as $class => $folder)
			if (file_exists(THESIS_USER_PACKAGES . "/$folder/package.php"))
				include_once(THESIS_USER_PACKAGES . "/$folder/package.php");
	}

	public function canvas() {
		global $thesis;
		$tab = str_repeat("\t", $depth = 2);
		$list = '';
		foreach ($this->installed as $class => $package)
			$list .= $this->item_info($package, $depth);
		echo
			"$tab<h3>" . __('Thesis Packages', 'thesis') . " <span id=\"package_upload\" data-style=\"button action\" title=\"" . __('upload a new package', 'thesis') . "\">" . __('Upload Package', 'thesis') . "</span></h3>\n".
			(!empty($this->updates) ? "<p id=\"update_link\"><a onclick=\"if(!thesis_update_message()) return false;\" href=\"". wp_nonce_url(admin_url('update.php?action=thesis_update_objects&update_type=package'), 'thesis-update-objects') ."\">". __('Update Packages', 'thesis') . "</a></p>\n" : '').
			"$tab<p class=\"package_primer\">" . __('<strong>Note:</strong> The packages you select here will be activated and added to the CSS Editor, where you can add them to your CSS workflow.') . "</p>\n".
			"$tab<form id=\"select_packages\" method=\"post\" action=\"\">\n". #wp
			"$tab\t<div class=\"package_list\">\n".
			$list.
			"$tab\t</div>\n".
			"$tab\t" . wp_nonce_field('thesis-update-packages', '_wpnonce-thesis-ajax', true, false) . "\n".
			"$tab\t<input type=\"submit\" data-style=\"button save\" class=\"t_save\" id=\"save_packages\" name=\"save_packages\" value=\"". __('Save Packages', 'thesis') ."\" />\n".
			"$tab</form>\n".
			$thesis->api->popup(array(
				'id' => 'package_uploader',
				'title' => __('Upload a Thesis Package', 'thesis'),
				'body' => $thesis->api->uploader('thesis_package_uploader')));
	}
	
	public function item_info($package, $depth = 0) {
		$active = is_object($this) ? $this->active : (is_array(get_option('thesis_packages')) ? array_keys(get_option('thesis_packages')) : 0);
		if ($active === 0) return '';
		$tab = str_repeat("\t", $depth);
		$checked = is_array($active) && in_array($package['class'], $active) ? ' checked="checked"' : '';
		$is_active = !empty($checked) ? ' active_package' : '';
		$author = !empty($package['author']) ? " <span class=\"package_by\">" . __('by', 'thesis') . "</span> <span class=\"package_author\">". esc_attr($package['author']) ."</span>" : '';
		$out =
			"$tab\t\t<div id=\"package_". esc_attr($package['class']) ."\" class=\"package$is_active\" data-package=\"{$package['class']}\">\n".
			"$tab\t\t\t<h4>". esc_attr($package['name']) . " <span class=\"package_version\">v " . esc_html($package['version']). "</span>$author".
			(is_object($this) && !empty($this->updates[$package['class']]) ? " <span class=\"t_update_available\">" . __('Update Available!', 'thesis') . "</span>" : '').
			"</h4>\n".
			"$tab\t\t\t<p class=\"package_description\">". esc_textarea($package['description']) ."</p>\n".
			"$tab\t\t\t<input type=\"checkbox\" class=\"select_package\" id=\"". esc_attr($package['class']) ."\" name=\"packages[". esc_attr($package['class']) ."]\" value=\"1\"$checked />\n".
			"$tab\t\t\t<a onclick=\"thesis_packages.delete_popup('". esc_attr($package['class']) ."', '". wp_nonce_url(admin_url("update.php?action=thesis_delete_object&thesis_object_type=package&thesis_object_name={$package['class']}"), 'thesis-delete-object') ."')\" data-style=\"button delete\" class=\"delete_package\" data-package=\"". esc_attr($package['class']) ."\">". __('Delete Package', 'thesis') ."</a>\n".
			"$tab\t\t</div>\n";
		return $out;
	}
	
	public function get_items() {
		if (is_object($this)) {
			$nawnce = !empty($_GET['_wpnonce']) ? $_GET['_wpnonce'] : false;
			if (wp_verify_nonce($nawnce, 'thesis_did_update'))
				delete_transient('thesis_packages_update');
			$this->updates = get_transient('thesis_packages_update');
		}
		$packages = array();
		$path = THESIS_USER_PACKAGES;
		$default_headers = array(
			'name' => 'Name',
			'class' => 'Class',
			'author' => 'Author',
			'description' => 'Description',
			'version' => 'Version');
		if (!is_dir($path)) return $packages;
		$dir = scandir($path);
		foreach ($dir as $p) {
			if (in_array($p, array('.', '..')) || strpos($p, '.') === 0 || ! is_dir("$path/$p") || ! file_exists("$path/$p/package.php")) continue;
			$package = get_file_data("$path/$p/package.php", $default_headers); #wp
			if (is_array($package)) {
				$package['folder'] = $p;
				$packages[$package['class']] = $package;
			}
		}
		return $packages;
	}

	public function save() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		parse_str(stripslashes($_POST['form']), $form);
		$thesis->wp->nonce($form['_wpnonce-thesis-ajax'], 'thesis-update-packages');
		if (is_array($form)) {
			$packages = array();
			$installed = $this->get_items();
			if (!empty($form['packages']) && is_array($form['packages']))
				foreach ($form['packages'] as $class => $on)
					if ($on && is_array($installed[$class]) && !empty($installed[$class]['folder']))
						$packages[$class] = $installed[$class]['folder'];
			if (empty($packages))
				delete_option('thesis_packages'); #wp
			else
				update_option('thesis_packages', $packages); #wp
			echo $thesis->api->alert(__('Packages saved!', 'thesis'), 'packages_saved', true);
		}
		else
			echo $thesis->api->alert(__('Packages not saved.', 'thesis'), 'packages_saved', true);
		if ($thesis->environment == 'ajax') die();
	}
}