<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
/**
 * class thesis_skins
 *
 * This class allows users to view the installed skins, switch skins, upload skins, and upgrade skins.
 *
 * @since 2.0
 * @uses $thesis
 */
class thesis_skins {
	public $skin = array();
	public $installed = array(); # skins in wp-content/thesis/skins/ array('class' => array('name' =>, 'author' =>, 'desc' =>, 'path' =>, 'class' =>, 'folder' =>))
	public $active = false;
	public $preview = false;
	public $updates_ready = false;
	public static $headers = array(
		'name' => 'Name',
		'author' => 'Author',
		'description' => 'Description',
		'version' => 'Version',
		'class' => 'Class');

		public function __construct() {
			global $thesis;
			if ($thesis->environment == 'admin' || $thesis->wp_customize) {
				$this->installed = $this->get_items();
				if ($thesis->environment == 'admin') {
					new thesis_upload(array(
						'title' => __('Thesis Upload Skin', 'thesis'),
						'prefix' => 'thesis_skin_uploader',
						'file_type' => 'zip',
						'folder' => 'skin',
						'post_id' => 0));
					add_action('admin_post_thesis_skins', array($this, 'save'));
					add_action('admin_post_export_skin', array($this, 'export'));
					if (!empty($_GET['canvas']) && $_GET['canvas'] == 'skins') {
						$this->check_updates(); // gets updates from transient
						add_action('thesis_admin_canvas', array($this, 'canvas')); #wp
						add_action('init', array($this, 'queue_scripts'));
					}
				}
			}
			$this->skin();
			if ($thesis->environment == 'ajax') {
				add_action('wp_ajax_backup_skin', array($this, 'backup'));
				add_action('wp_ajax_update_backup_skin_table', array($this, 'update_backup'));
				add_action('wp_ajax_restore_skin_backup', array($this, 'restore_backup'));
				add_action('wp_ajax_delete_skin_backup', array($this, 'delete_backup'));
				add_action('wp_ajax_restore_skin_default', array($this, 'restore_default'));
			}
		}

	public function queue_scripts() {
		global $thesis;
		wp_enqueue_style('thesis-objects', THESIS_CSS_URL . '/objects.css', array('thesis-admin'), $thesis->version); #wp
		wp_enqueue_style('thesis-skins', THESIS_CSS_URL . '/skins.css', array('thesis-admin', 'thesis-objects'), $thesis->version); #wp
		wp_enqueue_script('thesis-skins', THESIS_JS_URL . '/skins.js', array('thesis-menu'), $thesis->version); #wp
	}

	private function skin() {
		global $thesis;
		$this->active = $thesis->api->get_option('thesis_skin');
		if (!$this->active && !empty($this->installed)) {
			$skin = $this->installed['thesis_classic'];
			update_option('thesis_skin', $skin);
		}
		else {
			if (is_user_logged_in() && current_user_can('edit_theme_options') && $this->preview = $thesis->api->get_option('thesis_skin_preview'))
				$skin = $this->preview ? $this->preview : $this->active;
			else
				$skin = $this->active;
		}
		if (!empty($skin)) { // will only evaluate to false prior to initial installation
			$this->skin = $skin;
			if (isset($this->skin['directory']) && !isset($this->skin['folder'])) // backwards compat
				$this->skin['folder'] = basename($this->skin['directory']);
			$dir = is_dir(THESIS_USER_SKINS) ? THESIS_USER_SKINS : ($thesis->wp_customize ? THESIS_SKINS : false);
			$url_skins = is_dir(THESIS_USER_SKINS) ? THESIS_USER_SKINS_URL : ($thesis->wp_customize ? THESIS_URL . '/lib/skins' : false);
			$skin_file = "$dir/{$this->skin['folder']}/skin.php";
			if (@file_exists($skin_file)) {
				require_once($skin_file);
				define('THESIS_USER_SKIN', dirname($skin_file));
				define('THESIS_USER_SKIN_IMAGES', THESIS_USER_SKIN . '/images');
				define('THESIS_USER_SKIN_URL', $url_skins . '/' . $this->skin['folder']);
				define('THESIS_USER_SKIN_IMAGES_URL', THESIS_USER_SKIN_URL . '/images');
				$this->manager = new thesis_skin_manager($this->skin);
			}
		}
	}

	public function canvas() {
		global $thesis;
		$tab = str_repeat("\t", $depth = 2);
		$current = $preview = $installed = '';
		if (is_array($this->installed) && !empty($this->installed))
			foreach ($this->installed as $class => $skin)
				if ($class == $this->active['class'])
					$current = $this->item_info($skin, $depth + 1);
				elseif ($class == $this->preview['class'])
					$preview = $this->item_info($skin, $depth + 1);
				else
					$installed .= $this->item_info($skin, $depth);
		echo
			(!empty($_GET['changed']) && $_GET['changed'] == 'true' ?
			$thesis->api->alert(__('Success! You just changed your Thesis skin.', 'thesis'), false, false, $depth) :
			(!empty($_GET['preview']) && $_GET['preview'] == 'true' ?
			$thesis->api->alert(__('You are now previewing a skin in development mode. As an administrator, you can edit the Preview Skin, but visitors to your site will continue to see the Current Skin.', 'thesis'), false, false, $depth) :
			(!empty($_GET['stopped']) && $_GET['stopped'] == 'true' ?
			$thesis->api->alert(__('You are no longer previewing a skin in development mode.', 'thesis'), false, false, $depth) :
			!empty($_GET['deleted']) && ($_GET['deleted'] == 'true' ?
			$thesis->api->alert(__('Skin deleted.', 'thesis'), false, false, $depth) :
			(!empty($preview) ?
			$thesis->api->alert(__('You are currently previewing a skin in development mode. Visitors to your site will still see the Current Skin shown below, and you can develop the Preview Skin without fear of messing up your site for existing visitors!', 'thesis'), 'warning', false, $depth) : ''))))).
			"$tab<span id=\"skin_upload\" data-style=\"button action\">" . __('Upload Skin', 'thesis') . "</span>\n".
			(!empty($this->updates) ? "<a class=\"thesis_update_button\" onclick=\"if(!thesis_update_message()) return false;\" style=\"float:right;margin-right:12px;\" data-style=\"button save\" href=\"". wp_nonce_url(admin_url('update.php?action=thesis_update_objects&update_type=skin'), 'thesis-update-objects') ."\">". __('Update Skins', 'thesis') . "</a>\n" : '').
			(!empty($preview) ?
			"$tab<h3 id=\"preview_skin\">" . __('Preview Skin', 'thesis') . "</h3>\n".
			"$tab<div class=\"active_skin\">\n".
			$preview.
			"$tab</div>\n" : '').
			"$tab<h3 id=\"current_skin\">" . __('Current Skin', 'thesis') . "</h3>\n".
			"$tab<div class=\"active_skin\">\n".
			$current.
			"$tab</div>\n".
			"$tab<h3 id=\"installed_skins\">" . __('Installed Skins', 'thesis') . "</h3>\n".
			$installed.
			$thesis->api->popup(array(
				'id' => 'skin_uploader',
				'title' => __('Upload a Thesis Skin', 'thesis'),
				'body' => $thesis->api->uploader('thesis_skin_uploader')));
	}

	public function item_info($skin, $depth = false) {
		global $thesis;
		if (!is_array($skin)) return;
		extract($skin); # name, author, description, version, class, folder
		if (empty($class) || empty($folder)) return;
		$tab = str_repeat("\t", (is_numeric($depth) ? $depth : 2));
		$preview = is_object($this) && !empty($this->preview) ? $this->preview : get_option('thesis_skin_preview');
		$active = is_object($this) && !empty($this->active) ? $this->active : get_option('thesis_skin');
		$zip = ((!empty($preview['class']) && $class === $preview['class']) || $class === $active['class']) && apply_filters('thesis_skin_create_zip', false) ?
			"$tab\t\t\t<a data-style=\"button action\" href=\"" . wp_nonce_url(admin_url("update.php?action=thesis_generate_skin&skin=" . esc_attr($class)), 'thesis-generate-skin') ."\">" . __('Create Zip File', 'thesis') . "</a>\n" : false;
		return
			"$tab<div id=\"skin_" . esc_attr($class) . "\" class=\"skin_info\">\n".
			"$tab\t<form method=\"post\" action=\"" . admin_url('admin-post.php?action=thesis_skins') . "\">\n".
			(file_exists(trailingslashit(THESIS_USER_SKINS) . "$folder/screenshot.png") ?
			"$tab\t\t<img class=\"skin_screenshot\" src=\"" . trailingslashit(THESIS_USER_SKINS_URL) . "$folder/screenshot.png\" alt=\"" . esc_attr($name) . " screenshot\" width=\"300\" height=\"225\" />\n" : '<div style="width:300px;height:225px;" class="skin_screenshot"></div>').
			"$tab\t\t<h4>" . esc_html($name) . " <span class=\"skin_version\">v " . esc_html($version) . "</span> <span class=\"skin_by\">" . __('by', 'thesis') . "</span> <span class=\"skin_author\">" . esc_html($author) . "</span>".
			(is_object($this) && !empty($this->updates[$class]) && version_compare($this->updates[$class]['version'], $version, '>') ?
			" <span class=\"t_update_available\">" . __('Update available!', 'thesis') . "</span>\n" : '') . "</h4>\n".
			"$tab\t\t<p>" . esc_html($description) . "</p>\n".
			((!empty($preview['class']) && $class === $preview['class']) || ($class === $active['class'] && !empty($zip)) || ($class !== $preview['class'] && $class !== $active['class']) ?
			"$tab\t\t<p>\n" . ($class !== $preview['class'] && $class !== $active['class'] ?
			"$tab\t\t\t<input type=\"submit\" data-style=\"button action\" name=\"preview_skin\" value=\"" . __('Preview Skin in Development Mode', 'thesis') . "\" />\n" : ($class === $preview['class'] ?
			"$tab\t\t\t<input type=\"submit\" class=\"stop_preview\" data-style=\"button action\" name=\"stop_preview\" value=\"" . __('Stop Previewing Skin', 'thesis') . "\" />\n" : '')).
			(!empty($zip) ? $zip : '').
			"$tab\t\t</p>\n" : '').
			($class !== $active['class'] ?
			"$tab\t\t<p>\n" .
			"$tab\t\t\t<input type=\"hidden\" name=\"skin\" value=\"". esc_attr($class) ."\" />\n".
			($class !== $preview['class'] ?
			"$tab\t\t\t<a onclick=\"thesis_skins.delete_popup('". esc_attr($class) ."', '". wp_nonce_url(admin_url("update.php?action=thesis_delete_object&thesis_object_type=skin&thesis_object_name=". esc_attr($class)), 'thesis-delete-object')."')\" class=\"skin_delete\" data-style=\"button delete\">" . __('Delete Skin', 'thesis') ."</a>\n" : '').
			"$tab\t\t\t<input type=\"submit\" data-style=\"button save\" name=\"activate_skin\" value=\"" . __('Activate Skin', 'thesis') . "\" />\n".
			"$tab\t\t</p>\n" : '').
			"$tab\t\t" . wp_nonce_field('thesis-skins', '_wpnonce-thesis-skins', true, false) . "\n". #wp
			"$tab\t</form>\n".
			"$tab</div>\n";
	}

	public function save() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		check_admin_referer('thesis-skins', '_wpnonce-thesis-skins'); #wp
		if (!($class = $_POST['skin'])) {
			wp_redirect(admin_url('admin.php?page=thesis&canvas=skins&update=false')); #wp
			exit;
		}
		$this->get_items();
		if (!isset($this->installed[$class])) {
			wp_redirect(admin_url('admin.php?page=thesis&canvas=skins&update=false')); #wp
			exit;
		}
		if (@file_exists(THESIS_USER_SKINS . '/' . $this->installed[$class]['folder'] ."/skin.php")) {
			if (!empty($_POST['preview_skin'])) {
				update_option('thesis_skin_preview', $this->installed[$class]); #wp
				wp_cache_flush(); #wp
				wp_redirect(admin_url('admin.php?page=thesis&canvas=skins&preview=true')); #wp
			}
			elseif (!empty($_POST['activate_skin'])) {
				delete_option('thesis_skin_preview');
				update_option('thesis_skin', $this->installed[$class]); #wp
				wp_cache_flush(); #wp
				wp_redirect(admin_url('admin.php?page=thesis&canvas=skins&changed=true')); #wp
			}
			elseif (!empty($_POST['stop_preview'])) {
				delete_option('thesis_skin_preview');
				wp_cache_flush(); #wp
				wp_redirect(admin_url('admin.php?page=thesis&canvas=skins&stopped=true')); #wp
			}
		}
		else
			wp_redirect(admin_url('admin.php?page=thesis&canvas=skins&update=false')); #wp
	}

	public function get_items() {
		$customize = !empty($GLOBALS['thesis']) && is_object($GLOBALS['thesis']) && $GLOBALS['thesis']->wp_customize ? true : false;
		$dir = is_dir(THESIS_USER_SKINS) ? THESIS_USER_SKINS : ($customize ? THESIS_SKINS : false);		
		$skins = @scandir($dir);
		if (!is_array($skins)) # if this is happening, the pooch has been completely sodomized
			return false;
		$installed = array();
		foreach ($skins as $skin) {
			$skin_file = "$dir/$skin/skin.php";
			if ($skin == '.' || $skin == '..' || !@file_exists($skin_file)) continue;
			$file_data = get_file_data($skin_file, self::$headers); # skin.php is present
			$installed[$file_data['class']] = $file_data;
			$installed[$file_data['class']]['folder'] = $skin;
		}
		return $installed;
	}

	public function check_updates($installed = array()) {
		global $thesis;
		if ((is_object($this) && empty($this->installed)) || (!is_object($this) && empty($installed))) 
			return false;
		$installed = is_object($this) ? $this->installed : $installed;
		$nawnce = !empty($_GET['_wpnonce']) ? $_GET['_wpnonce'] : '';
		if (wp_verify_nonce($nawnce, 'thesis_did_update'))
			delete_transient('thesis_skins_update');
		$updates = get_transient('thesis_skins_update');
		if (is_object($this));
			$this->updates = $updates;
		return $updates;
	}

	public function backup() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST['nonce'], 'thesis-skin-manager');
		echo $thesis->api->alert($this->manager->add($_POST['note']) === false ? __('Backup failed.', 'thesis') : __('Backup complete!', 'thesis'), 'manager_saved', true);
		if ($thesis->environment == 'ajax') die();
	}

	public function update_backup() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST['nonce'], 'thesis-skin-manager');
		echo $this->manager->backup_table();
		if ($thesis->environment == 'ajax') die();
	}

	public function restore_backup() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST['nonce'], 'thesis-skin-manager');
		if (($restore = $this->manager->restore((int) $_POST['id'])) && !empty($restore)) {
			$css = array();
			$css['css'] = ($skin = get_option("{$this->skin['class']}_css")) ? stripslashes($skin) : '';
			$css['custom'] = ($custom = get_option("{$this->skin['class']}_css_custom")) ? stripslashes($custom) : '';
			$css['packages'] = is_array($packages = get_option("{$this->skin['class']}_packages")) ? $packages : array();
			$css['vars'] = is_array($vars = get_option("{$this->skin['class']}_vars")) ? $vars : array();
			$kew = new thesis_css($css);
			$kew->write($css['css'], $css['custom']);
		}
		if ($thesis->environment == 'ajax') die();
	}

	public function delete_backup() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST['nonce'], 'thesis-skin-manager');
		echo $thesis->api->alert($this->manager->delete((int) $_POST['id']) === false ? __('Deletion failed.', 'thesis') : __('Backup deleted!', 'thesis'), 'manager_saved', true);
		if ($thesis->environment == 'ajax') die();
	}

	public function export() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST['_wpnonce-thesis-skin-export'], 'thesis-skin-export');
		if (is_array($_POST['export'])) {
			$export = array_filter($_POST['export']);
			if (!empty($export))
				$this->manager->export($export);
		}
	}

	public function import($files, $action) {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		check_admin_referer($action, 'thesis_form_nonce');
		if (($imported = $this->manager->import($files)) && !empty($imported)) {
			require_once(THESIS_CORE . '/skin/css.php');
			$css = array();
			$css['css'] = ($skin = get_option("{$this->skin['class']}_css")) ? stripslashes($skin) : '';
			$css['custom'] = ($custom = get_option("{$this->skin['class']}_css_custom")) ? stripslashes($custom) : '';
			$css['packages'] = is_array($packages = get_option("{$this->skin['class']}_packages")) ? $packages : array();
			$css['vars'] = is_array($vars = get_option("{$this->skin['class']}_vars")) ? $vars : array();
			$kew = new thesis_css($css);
			$kew->write($css['css'], $css['custom']);
			return true;
		}
		return false;
	}

	public function restore_default() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST['nonce'], 'thesis-skin-manager');
		if ($this->manager->defaults() === true) {
			$css = array();
			$css['css'] = ($skin = get_option("{$this->skin['class']}_css")) ? stripslashes($skin) : '';
			$css['custom'] = ($custom = get_option("{$this->skin['class']}_css_custom")) ? stripslashes($custom) : '';
			$css['packages'] = is_array($packages = get_option("{$this->skin['class']}_packages")) ? $packages : array();
			$css['vars'] = is_array($vars = get_option("{$this->skin['class']}_vars")) ? $vars : array();
			$kew = new thesis_css($css);
			$kew->write($css['css'], $css['custom']);
			echo 'true';
		}
		else
			echo $thesis->api->alert(__('Skin default not restored.', 'thesis'), 'manager_saved', true);
		if ($thesis->environment == 'ajax') die();
	}
}