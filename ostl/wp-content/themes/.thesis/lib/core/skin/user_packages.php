<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_user_packages {
	private $packages = array();	// (array) format: ('package_class' => 'current_folder')
	public $active = array();		// (array) all active user package classes

	public function __construct() {
		global $thesis;
		if (!($thesis->environment == 'admin' || $thesis->environment == 'thesis' || $thesis->environment == 'editor' || $thesis->environment == 'ajax')) return;
		$this->packages = is_array($packages = $thesis->api->get_option('thesis_packages')) ? $packages : $this->packages;
		$this->active = array_keys($this->packages);
		add_action('thesis_include_packages', array($this, 'include_packages'));
		if ($thesis->environment == 'admin') {
			$this->upload = new thesis_upload(array(
				'title' => __('Thesis Upload Package', 'thesis'),
				'prefix' => 'thesis_package_uploader',
				'folder' => 'package'));
			add_action('admin_post_save_packages', array($this, 'save'));
		}
		if ($thesis->environment == 'thesis')
			add_action('admin_init', array($this, 'admin_init'));
		if ($thesis->environment == 'ajax')
			add_action('wp_ajax_delete_package', array($this, 'delete'));
	}

	public function include_packages() {
		foreach ($this->packages as $class => $folder)
			if (file_exists(THESIS_USER_PACKAGES . "/$folder/package.php"))
				include_once(THESIS_USER_PACKAGES . "/$folder/package.php");
	}

	public function admin_init() {
		add_action('thesis_packages_menu', array($this, 'menu'), 1);
		if (!empty($_GET['canvas']) && $_GET['canvas'] == 'packages') {
			wp_enqueue_style('thesis-objects'); #wp
			wp_enqueue_script('thesis-objects'); #wp
			add_action('thesis_admin_canvas', array($this, 'canvas'));
		}
	}

	public function menu($menu) {
		$add['packages'] = array(
			'text' => __('Select Packages', 'thesis'),
			'url' => admin_url('admin.php?page=thesis&canvas=packages'));
		return is_array($menu) ? array_merge($menu, $add) : $add;
	}

	public function get_items() {
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

	public function canvas() {
		global $thesis;
		$tab = str_repeat("\t", $depth = 2);
		$updates = get_transient('thesis_packages_update');
		$packages = $this->get_items();
		$sort = array();
		$list = '';
		foreach ($packages as $class => $package)
			$sort[$class] = $package['name'];
		natcasesort($sort);
		foreach ($sort as $class => $name)
			$list .= $this->item_info($packages[$class], $updates, $depth);
		echo (!empty($_GET['saved']) ? $thesis->api->alert(($_GET['saved'] === 'yes' ?
			__('Packages saved!', 'thesis') :
			__('Packages not saved. Please try again.', 'thesis')), 'objects_saved', true, '', $depth) : ''),
			"$tab<h3>", __('Thesis Packages', 'thesis'), " <span id=\"object_upload\" data-style=\"button action\" title=\"", __('upload a new package', 'thesis'), "\">", __('Upload Package', 'thesis'), "</span></h3>\n",
			"$tab<p class=\"object_primer\">", sprintf(__('The packages you select here will be activated and added to the <a href="%1$s">Skin %2$s Editor</a>, where you can add them to your %2$s workflow.'), set_url_scheme(home_url('?thesis_editor=1')), $thesis->api->base['css']), "</p>\n",
			"$tab<p class=\"object_alert\">", __('<strong>Attention!</strong> As of Thesis 2.1, Packages are considered deprecated, and we no longer recommend installing or using them. New Skin design options have rendered Packages obsolete, and the current plan is to phase them out of Thesis by version 2.2.', 'thesis'), "</p>\n",
			"$tab<form id=\"select_objects\" method=\"post\" action=\"", admin_url('admin-post.php?action=save_packages'), "\">\n", #wp
			"$tab\t<div class=\"object_list\">\n",
			$list,
			"$tab\t</div>\n",
			"$tab\t", wp_nonce_field('thesis-update-packages', '_wpnonce-thesis-update-packages', true, false), "\n",
			"$tab\t<input type=\"submit\" data-style=\"button save\" class=\"t_save\" id=\"save_objects\" name=\"save_packages\" value=\"", __('Save Packages', 'thesis'), "\" />\n",
			"$tab</form>\n",
			$thesis->api->popup(array(
				'id' => 'object_uploader',
				'title' => __('Upload a Thesis Package', 'thesis'),
				'body' => $thesis->api->uploader('thesis_package_uploader')));
	}

	public function item_info($package, $updates = array(), $depth = 0) {
		global $thesis;
		$tab = str_repeat("\t", $depth);
		$id = esc_attr($package['class']);
		$active = is_object($this) && !empty($this->active) ? $this->active : array_keys(get_option('thesis_packages', array()));
		$checked = is_array($active) && in_array($package['class'], $active) ? ' checked="checked"' : '';
		$author = !empty($package['author']) ? " <span class=\"object_by\">". __('by', 'thesis'). "</span> <span class=\"object_author\">". esc_attr($package['author']). "</span>" : '';
		return
			"$tab\t\t<div id=\"package_$id\" class=\"object". (!empty($checked) ? ' active_object' : ''). "\">\n".
			"$tab\t\t\t<label for=\"$id\">". $thesis->api->escht($package['name']). " <span class=\"object_version\">v ". esc_html($package['version']). "</span>$author".
			(!empty($updates[$package['class']]) ? " <span class=\"t_update_available\">". __('Update Available!', 'thesis'). "</span>".
			"<a onclick=\"if(!thesis_update_message()) return false;\" href=\"". wp_nonce_url(admin_url('update.php?action=thesis_update_objects&update_type=package&class='. $id), 'thesis-update-objects'). "\">". sprintf(__('Update %s', 'thesis'), esc_attr($package['name'])). "</a>" : ''). "</label>\n".
			"$tab\t\t\t<p class=\"object_description\">". wptexturize(wp_kses($package['description'], array('a' => array('href' => array(), 'title' => array(), 'target' => array()), 'code' => array(), 'em' => array(), 'strong' => array()))). "</p>\n".
			"$tab\t\t\t<input type=\"checkbox\" class=\"select_object\" id=\"$id\" name=\"packages[$id]\" value=\"1\"$checked />\n".
			"$tab\t\t\t<button data-style=\"button delete\" class=\"delete_object\" data-type=\"package\" data-class=\"$id\" data-url=\"". wp_nonce_url(admin_url("update.php?action=thesis_delete_object&thesis_object_type=package&thesis_object_name=$id"), 'thesis-delete-object'). "\">". __('Delete Package', 'thesis'). "</button>\n".
			"$tab\t\t</div>\n";
	}

	public function save() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST['_wpnonce-thesis-update-packages'], 'thesis-update-packages');
		$saved = 'no';
		if (is_array($form = $_POST)) {
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
			$saved = 'yes';
		}
		wp_redirect("admin.php?page=thesis&canvas=packages&saved=$saved");
		exit;
	}

	public function delete() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		if (empty($_POST['class']) || empty($_POST['url'])) return;
		echo $thesis->api->popup(array(
			'id' => 'delete_'. esc_attr($_POST['class']),
			'title' => __('Delete Package', 'thesis'),
			'body' =>
				"<iframe style=\"width:100%; height:100%;\" frameborder=\"0\" src=\"". esc_url($_POST['url']). "\" id=\"thesis_delete_". esc_attr($_POST['class']). "\"></iframe>\n"));
		if ($thesis->environment == 'ajax') die();
	}
}