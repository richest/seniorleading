<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
new thesis_asset_handler;
class thesis_asset_handler {
	
	public function __construct() {
		global $pagenow;
		
		if ($pagenow == 'update.php') {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			
			// updates
			add_action('update-custom_thesis_update_objects', array($this, 'update'));
			
			// initial install
			add_action('update-custom_thesis-install-components', array($this, 'install'));
			
			// delete stuff
			add_action("update-custom_thesis_delete_object", array($this, 'delete'));
			
			// generate zip
			add_action('update-custom_thesis_generate_skin', array($this, 'generate'));
		}
		add_filter('update_theme_complete_actions', array($this, 'core_message'), 10, 2);
		add_action('admin_notices', array($this, 'sitewide_nag'));
		if (is_dir(WP_CONTENT_DIR . '/thesis'))
			add_action('admin_init', array($this, 'get_all_updates'), 100);
		add_filter('site_transient_update_themes', array($this, 'append_thesis'));
		add_filter('transient_update_themes', array($this, 'append_thesis'));
		add_action('after_switch_theme', array($this, 'install_branch'));
	}
	
	public function install_branch() {
		include_once(ABSPATH . '/wp-admin/includes/file.php');
		if (get_filesystem_method() === 'direct' && !is_dir(WP_CONTENT_DIR . '/thesis') && is_dir(THESIS_SKINS)) {
			// first, set up wp_filesystem
			
			WP_Filesystem();
			$f = $GLOBALS['wp_filesystem'];
			
			// directories
			$directories = array(
				'thesis/', 'thesis/boxes/', 'thesis/packages/', 'thesis/skins/'
			);
			foreach ($directories as $dir)
				$f->mkdir($f->wp_content_dir() . $dir);
			
			// master.php
			$f->put_contents($f->wp_content_dir() . 'thesis/master.php', "<?php\n// This is the Thesis master.php file.\n// Use this file to affect every site on your network.\n// Note: this is the last file included in Thesis!\n");
			
			// move skins
			$from = trailingslashit($f->find_folder(THESIS_SKINS));
			$to = $f->wp_content_dir() . 'thesis/skins/';
			$skins = array_keys($f->dirlist($from));
			
			foreach ($skins as $skin) {
				$f->move($from . $skin, $to . $skin);
				if (!$f->exists($to.$skin.'/images'))
					$f->mkdir($to.$skin.'/images');
				if (!$f->exists($to.$skin.'/custom.php'))
					$f->put_contents($to.$skin.'/custom.php', "<?php\n/*\n\tThis file is for skin specific customizations. Be careful not to change your skin's skin.php file as that will be upgraded in the future and your work will be lost.\n\tIf you are more comfortable with PHP, we recommend using the super powerful Thesis Box system to create elements that you can interact with in the Thesis HTML Editor.\n*/");	
			}
			
			// clean up
			if (($lib = array_keys($f->dirlist(THESIS_SKINS))) && empty($lib))
				$f->delete(THESIS_SKINS);
		}
	}
	
	public function core_message($update_actions, $theme) {
		if ($theme == 'thesis') {
			$update_actions = '<a href="'. wp_nonce_url(admin_url('admin.php?page=thesis'), 'thesis_did_update') .'">'. __('Return to Thesis!', 'thesis') .'</a>';
		}
		return $update_actions;
	}
	
	public function sitewide_nag() {
		global $thesis;
		if (($data = get_transient('thesis_core_update')) && is_super_admin() && version_compare($thesis->version, $data['new_version'], '<') && $GLOBALS['pagenow'] !== 'update.php') {
			$url = wp_nonce_url('update.php?action=upgrade-theme&amp;theme=thesis', 'upgrade-theme_thesis');
			$html = "<div id=\"update-nag\">\n".
					"\t". sprintf(__('Thesis %s is available! %sUpdate Now!</a>', 'thesis'), esc_attr($data['new_version']), '<a onclick="if(!thesis_update_message()) return false;" id="thesis-update-link" href="'. esc_url($url) .'">') . "\n".
					"</div>\n";
			echo $html;
		}
	}
	
	public function update() {
		global $thesis;
		if (! current_user_can('install_themes') || !wp_verify_nonce($_REQUEST['_wpnonce'], 'thesis-update-objects'))
			wp_die(__("You are not allowed to perform this action.", 'thesis'));
		
		$plural = array(
			'skin' => "Skins",
			'box' => 'Boxes',
			'package' => 'Packages'
		);
		
		$t = sprintf(__('Updating %s', 'thesis'), $plural[$_GET['update_type']]);
		$n = "thesis-update-objects";
		$u = "update.php?action=thesis_update_objects&update_type=" . esc_attr($_GET['update_type']);
		$v = compact('t', 'n', 'u');
				
		require_once(ABSPATH . 'wp-admin/admin-header.php');
		
		$update = new thesis_update_objects(new thesis_update_objects_skin($v));
		$update->update();
		
		include(ABSPATH . 'wp-admin/admin-footer.php');
	}
	
	public function install() {
		if (! current_user_can('install_themes') || !wp_verify_nonce($_REQUEST['_wpnonce'], 'thesis-install'))
			wp_die(__("You are not allowed to perform this action.", 'thesis'));
		
		$t = __('Installing Thesis', 'thesis');
		$n = 'thesis-install';
		$u = "update.php?action=thesis-install-components";
		$c = compact('t', 'n', 'u');
		
		require_once(ABSPATH . 'wp-admin/admin-header.php');
		
		$install = new thesis_install(new WP_Upgrader_Skin($c));
		$install->run();
		
		include(ABSPATH . 'wp-admin/admin-footer.php');
	}
	
	public function delete() {
		if (! current_user_can('delete_themes'))
			wp_die(__("You are not allowed to perform this action.", 'thesis'));
		check_admin_referer('thesis-delete-object');
		
		if (empty($_GET['thesis_object_name']) || empty($_GET['thesis_object_type']) || ! in_array($_GET['thesis_object_type'], array('skin', 'box', 'package')))
			wp_die(__('An error was encountered.', 'thesis'));
		
		
		$t = "Deleting ". esc_attr($_GET['thesis_object_type']);
		$n = 'thesis-delete-object';
		$u = "update.php?action=thesis_delete_object&thesis_object_type={$_GET['thesis_object_type']}&thesis_object_name={$_GET['thesis_object_name']}";
		$c = compact($t, $n, $u);
		add_action('admin_head', array('thesis_upload', 'admin_css'));
		require_once(ABSPATH . 'wp-admin/admin-header.php');
		
		$delete = new thesis_delete(new thesis_delete_skin($c));
		
		$delete->delete_object($_GET['thesis_object_type'], $_GET['thesis_object_name']);
		
		include(ABSPATH . 'wp-admin/admin-footer.php');
	}
	
	public function generate() {
		if (! current_user_can('install_themes'))
			wp_die(__('You are not allowed to perform this action.', 'thesis'));
		check_admin_referer('thesis-generate-skin');
		
		if (empty($_GET['skin']))
			wp_die(__('The skin class was passed as empty.', 'thesis'));
		
		$t = "Creating skin zip file";
		$n = "thesis-generate-skin";
		$u = "update.php?action=thesis_generate_skin&skin=" . urlencode($_GET['skin']);
		$c = compact($t, $n, $u);
		
		require_once(ABSPATH . 'wp-admin/admin-header.php');
		
		$generate = new thesis_generate(new thesis_generate_skin($c));
		$generate->generate();
		
		include(ABSPATH . 'wp-admin/admin-footer.php');
	}
	
	public function append_thesis($updates) {
		// if there is an update, it should have been grabbed before we reach this point
		
		$core = get_transient('thesis_core_update');
		if (!! $core)
			$updates->response['thesis'] = $core;
		return $updates;
	}
	
	public function get_all_updates() {
		global $thesis;
		
		if (get_transient('thesis_callout'))
			return;
		
		set_transient('thesis_callout', time(), 60*60*24);
		
		$objects = array(
			'skins' => thesis_skins::get_items(),
			'boxes' => thesis_user_boxes::get_items(),
			'packages' => thesis_user_packages::get_items()
		);
		
		$transients = array(
			'skins' => 'thesis_skins_update',
			'boxes' => 'thesis_boxes_update',
			'packages' => 'thesis_packages_update',
			'thesis' => 'thesis_core_update'
		);
		
		$all = array();
		
		foreach ($objects as $object => $array)
			if (is_array($array) && !empty($array))
				foreach ($array as $class => $data)
					$all[$object][$class] = $data['version'];
		
		$all['thesis'] = $thesis->version;
		
		foreach ($transients as $key => $transient)
			if (get_transient($transient))
				unset($all[$key]);
		
		if (empty($all))
			return;
		
		$from = 'http://thesisapi.com/update.php';
		$post_args = array(
			'body' => array(
				'data' => serialize($all),
				'wp' => $GLOBALS['wp_version'],
				'php' => phpversion(),
				'user-agent' => "WordPress/{$GLOBALS['wp_version']};" . home_url()
			)
		);
		
		$post = wp_remote_post($from, $post_args);

		if (is_wp_error($post) || empty($post['body']))
			return;
		
		$returned = @unserialize($post['body']);

		if (!is_array($returned))
			return;

		foreach ($returned as $type => $data) // will only return the data that we need to update
			if (in_array("thesis_{$type}_update", $transients))
				set_transient("thesis_{$type}_update", $returned[$type], 60*60*24);
	}
}
/*

For skins/pckgs/boxes:
	- handle an upload (either through the interface or when presented a URL)
	- handle an upgrade via URL ONLY
	- handle bulk upgrade? Maybe, but not for now?
	- handle deletion? Not sure if I like this idea, but it would be consistent with the WP experience.

*/
if (class_exists('WP_Upgrader')): // wp-admin/includes/class-wp-upgrade.php has been included. safe to do things :)
	
	class thesis_update_objects extends WP_Upgrader {
		public $updates;
		public $object_type;
		public $path_to_download;
		
		// these are strings used throughout the process by various parts of the routine. We must provide relevant messages as WordPress does not.
		function upgrade_strings() {
			$strings = array(
				'up_to_date' => sprintf(__('Congrats! Your %s is up to date.', 'thesis'), $_GET['update_type']),
				'no_package' => sprintf(__('The %s update is not currently available.', 'thesis'), $_GET['update_type']),
				'downloading_package' => sprintf(__('Fetching the updated %s&#8230;', 'thesis'), $_GET['update_type']),
				'unpack_package' => sprintf(__('Decompressing the downloaded %s&#8230;', 'thesis'), $_GET['update_type']),
				'remove_old'=> sprintf(__('Deleting the old %s&#8230;', 'thesis'), $_GET['update_type']),
				'remove_old_failed' => sprintf(__('We couldn\'t remove the old %s&#8230;', 'thesis'), $_GET['update_type']),
				'process_failed' => sprintf(__('Dang! The %s update failed. We\'ll try again later&#8230;', 'thesis'), $_GET['update_type']),
				'process_success' => sprintf(__('Yes! The %s update was a ravishing success!', 'thesis'), $_GET['update_type'])
			);
			$this->strings = $strings + $this->strings;
		}

		public function update() {
			global $wp_filesystem;
			// initialize wp_upgrader
			$this->init();
			// strings
			$this->upgrade_strings();
			// do fs biznass
			$this->fs_connect();
			// call skin header
			$this->skin->header();
			
			// plural
			$this->plural_name = $_GET['update_type'] . ($_GET['update_type'] == 'skin' || $_GET['update_type'] == 'packages' ? "s" : "es");
			
			// if we are connected to the fs, do the upgrade(s)
			if (is_object($wp_filesystem))
				$this->do_updates();

			// call the footer
			$this->skin->footer();
		}

		public function do_updates() {
			// valid types
			$types = array(
				'box', 'package', 'skin'
			);

			// decide the type. if it is invalid, bail
			if (empty($_GET['update_type']) || ! in_array($_GET['update_type'], $types))
				$this->skin->feedback(__('Invalid update type was passed.', 'thesis'));
			else {
				// we have a valid type
				$this->all_items = $_GET['update_type'] == 'skin' ? thesis_skins::get_items() : ($_GET['update_type'] == 'box' ? thesis_user_boxes::get_items() : ($_GET['update_type'] == 'package' ? thesis_user_packages::get_items() : false));

				// the updates we have in the db
				$this->updates = get_transient("thesis_{$this->plural_name}_update");

				// box or package update
				$dirs = array(
					'box' => THESIS_USER_BOXES,
					'package' => THESIS_USER_PACKAGES,
					'skin' => THESIS_USER_SKINS
				);

				add_filter('upgrader_source_selection', array($this, 'prepare_object'));
				foreach ($this->updates as $class => $data) {
					if (empty($this->all_items[$class])) continue;
					$this->raw_path = $dirs[$_GET['update_type']] . (!empty($this->all_items[$class]['folder']) ? "/{$this->all_items[$class]['folder']}" : '');
					if ($this->raw_path === $dirs[$_GET['update_type']]) continue; // bail if no folder given
					$run = array(
						'package' => $this->updates[$class]['url'], // this will ONLY be a URL
						'destination' => $this->raw_path, // will be deleted then remade
						'clear_destination' => true, // should fire AFTER we have processed the skin with upgrader_source_selection
						'clear_working' => true,
						'is_multi' => true); // prevents header and footer from being called over and over
					$this->current_class = $class;
					$break = $this->run($run);
				}
				remove_filter('upgrader_source_selection', array($this, 'prepare_object'));
			}
		}

		public function prepare_object($source) {
			global $wp_filesystem;
			// check that a box/package.php file exists
			// check that the reported classes are the same

			// $source is the downloaded folder
			$source = untrailingslashit($source);
			
			// rebuilt path to source (ie, the unzipped package)
			$this->path_to_download = '/' . implode('/', array_diff(explode('/', ABSPATH), explode('/', $source))) . (strpos($source, '/') === 0 ? '' : '/') . $source;
						
			// does the box/package.php file exist?
			if (! file_exists("{$this->path_to_download}/{$_GET['update_type']}.php"))
				return new WP_Error('no_php', sprintf(__('There is no %s.php file.', 'thesis'), $_GET['update_type']));

			$this->file_data = get_file_data("{$this->path_to_download}/{$_GET['update_type']}.php", 	array('name' => 'Name', 'class' => 'Class', 'author' => 'Author', 'description' => 'Description', 'version' => 'Version'));

			if (! $this->file_data['class'] == $this->current_class)
				return new WP_Error('class_not_same', sprintf(__('The class for your %s does not match the class of the update.', 'thesis'), $_GET['update_type']));

			if ($_GET['update_type'] == 'skin')
				$this->prepare_skin();
			
			// allow objects to override this or add things
			if (file_exists($this->path_to_download . '/upgrade.php'))
				include_once($this->path_to_download . '/upgrade.php');
			
			// heh
			$this->complete_success = apply_filters('thesis_install_object', true, $this->path_to_download, $this->raw_path, $wp_filesystem);

			// checked and ready to roll
			return $this->complete_success === true ? trailingslashit($source) : false;
		}

		public function prepare_skin() {
			global $wp_filesystem, $thesis;

			$source = $wp_filesystem->find_folder($this->path_to_download);
			$skin = $wp_filesystem->find_folder($this->raw_path);
			
			// if both have an images folder
			if ($wp_filesystem->is_dir("$source/images") && $wp_filesystem->is_dir("$skin/images")) {
				// 1 get images from download and skin
				$images = $wp_filesystem->dirlist("$source/images"); // images in download
				$installed_images = $wp_filesystem->dirlist("$skin/images"); // images currently in skin/images

				// 2 see what the installed skin has that the download doesn't
				$images_to_move = array_diff_key($installed_images, $images);

				// 3 move the installed skin images that aren't present in the download to the download
				foreach ($images_to_move as $image_name => $image_data)
					// move the images from the install to the download
					if (!$wp_filesystem->move("$skin/images/$image_name", "$source/images/$image_name"))
						$this->skin->feedback(sprintf('Could not move %s.', $image_name));
			}
			// if there is not an images folder, attempt to make one
			elseif (!$wp_filesystem->is_dir("$source/images" && !$wp_filesystem->mkdir("$source/images")))
				$this->skin->feedback('Could not make images folder.');
				
			if ($wp_filesystem->exists("$skin/css.css"))
				if (!$wp_filesystem->move("$skin/css.css", "$source/css.css", true))
					$this->skin->feedback("Could not move css file. <strong>You will need to save your CSS options in the editor.</strong>.");

			// if the skin was shipped with a custom file, and we have an installed one, delete the shipped
			if ($wp_filesystem->exists("$source/custom.php") && $wp_filesystem->exists("$skin/custom.php")) {
				if (! $wp_filesystem->delete("$source/custom.php", true)) {
					$this->skin->feedback('Could not delete custom.php');
					// kill the script. not writing over that junk.
					die();
				}
			}
			
			// attempt to move the custom folder
			if ($wp_filesystem->exists("$skin/custom.php")) {
				if (! $wp_filesystem->move("$skin/custom.php", "$source/custom.php")) {
					$this->skin->feedback('Could not move custom.php');
					die();
				}
			}
			else
				$wp_filesystem->put_contents("$source/custom.php", "<?php\n/*\n\tThis file is for skin specific customizations. Be careful not to change your skin's skin.php file as that will be upgraded in the future and your work will be lost.\n\tIf you are more comfortable with PHP, we recommend using the super powerful Thesis Box system to create elements that you can interact with in the Thesis HTML Editor.\n*/");
		}
	}

	class thesis_update_objects_skin extends WP_Upgrader_Skin {
		public function footer() {
			if ($this->upgrader->complete_success)
				echo "<p><a href=\"". wp_nonce_url(admin_url("admin.php?page=thesis&canvas={$this->upgrader->plural_name}"), 'thesis_did_update') ."\">". __('Click here to finish!', 'thesis') . "</a></p>";
			echo "</div>";
		}
	}

class thesis_install extends WP_Upgrader {
	public $custom_functions = "<?php\n/*\n\tThis file is for skin specific customizations. Be careful not to change your skin's skin.php file as that will be upgraded in the future and your work will be lost.\n\tIf you are more comfortable with PHP, we recommend using the super powerful Thesis Box system to create elements that you can interact with in the Thesis HTML Editor.\n*/";
	
	public function run() {
		global $wp_filesystem;
		$this->init();
		$this->fs_connect();
		$this->skin->header();
		
		if (is_object($wp_filesystem)) {
			$this->skin->feedback("Beginning installation.");
			if (! $this->directories() || ! $this->move_skins() || ! $this->make_master()) {
				$fail = true;
				$this->skin->feedback("Installation failed.");
			}
			$this->clean_skins();
			if (empty($fail))
				$this->skin->feedback("Installation was a success!");
			$this->skin->feedback("<a href=\"" . admin_url("admin.php?page=thesis") . "\">" . __('Return to Thesis', 'thesis') . "</a>");
		}
		
		$this->skin->footer();
		
	}
	
	public function make_master() {
		global $wp_filesystem;
		$file = trailingslashit($wp_filesystem->wp_content_dir()) . 'thesis/master.php';
		if (!$wp_filesystem->exists($file) && ! $wp_filesystem->put_contents($file, "<?php\n// This is the Thesis master.php file.\n// Use this file to affect every site on your network.\n// Note: this is the last file included in Thesis!\n"))
			return false;
		else return true;
	}
	
	public function clean_skins() {
		global $wp_filesystem;
		$s = $wp_filesystem->find_folder(THESIS_SKINS);
		$d = $wp_filesystem->dirlist($s);
		if ($wp_filesystem->is_dir($s) && empty($d))
			$wp_filesystem->delete($s);
	}
	
	public function directories() {
		global $wp_filesystem;
		
		$directories = array(
			'thesis/', 'thesis/boxes/', 'thesis/packages/', 'thesis/skins/'
		);
		$this->skin->feedback('Making primary folder structure.');
		foreach ($directories as $d) {
			$location = $wp_filesystem->wp_content_dir() . "$d";
			$this->skin->feedback("Making wp-content/$d");
			if (! $wp_filesystem->mkdir($location)){
				$this->skin->feedback("Unable to make wp-content/$d");
				$this->skin->feedback("Install halted. Please check your file permissions for wp-content.");
				return false;
			}
		}
		return true;
	}
	
	
	
	public function move_skins() {
		global $wp_filesystem;
		$wp_skins = untrailingslashit($wp_filesystem->wp_content_dir()) . "/thesis/skins";
		
		if (! $wp_filesystem->is_dir($wp_skins))
			return false;
			
		$lib_skins = untrailingslashit($wp_filesystem->find_folder(THESIS_SKINS));
		$lib_skins_content = $wp_filesystem->dirlist($lib_skins);
		
		$this->skin->feedback('Preparing default skins to move.');
		foreach ($lib_skins_content as $skin => $data) {
			if (! $wp_filesystem->exists("$lib_skins/$skin/custom.php")) {
				if (! $wp_filesystem->put_contents("$lib_skins/$skin/custom.php", $this->custom_functions)) {
					$this->skin->feedback("Could not make custom.php file. Please check your file permissions.");
					return false;
				}
			}
			if (! $wp_filesystem->exists("$lib_skins/$skin/images")) {
				if (! $wp_filesystem->mkdir("$lib_skins/$skin/images")) {
					$this->skin->feedback("Unable to make images folder. Please check file permissions.");
					return false;
				}
			}
			$this->skin->feedback("Moving $skin.");
			if (! $wp_filesystem->move("$lib_skins/$skin", "$wp_skins/$skin")) {
				$this->skin->feedback("Unable to move $skin");
				return false;
			}
			$this->skin->feedback("$skin successfully installed.");
		}
		$this->skin->feedback("Default skins installed successfully.");
		return true;
	}
}

class thesis_delete extends WP_Upgrader {
	public $done = false;
	public function delete_object($type, $class) {
		global $wp_filesystem, $thesis;
		$this->init();
		$this->fs_connect();
		$this->skin->header();

		if (! in_array($type, array('box', 'skin', 'package')))
			return new WP_Error('wrong_type', __('Type not recognized.', 'thesis'));
		if (empty($class))
			wp_die(__('Object class not passed.', 'thesis'));
		$this->thesis_type = $type;
		$this->thesis_class = $class;
		$items = false;
		$delete = false;
		if ($type == 'skin') {
			$items = thesis_skins::get_items();
			$delete = THESIS_USER_SKINS . "/{$items[$class]['folder']}";
		}
		elseif ($type == 'package') {
			$items = thesis_user_packages::get_items();
			$delete = THESIS_USER_PACKAGES . "/{$items[$class]['folder']}";
		}
		elseif ($type == 'box') {
			$items = thesis_user_boxes::get_items();
			$delete = THESIS_USER_BOXES . "/{$items[$class]['folder']}";
		}
		if (is_object($wp_filesystem)) {
			$delete = $wp_filesystem->find_folder($delete);
			if (! !!$items || ! !!$delete)
				$this->skin->feedback(__('Could not find the requested object.', 'thesis'));
			elseif (!$wp_filesystem->delete($delete, true))
				$this->skin->feedback('Could not delete ' . esc_attr($items[$class]['name']));
			else {
				$this->skin->feedback(esc_attr($items[$class]['name']) . ' has been deleted.');
				$this->done = true;
			}
		}
		$this->skin->footer($type);
	}
}

class thesis_delete_skin extends WP_Upgrader_Skin {
	public function footer($type = '') {
		if ($this->upgrader->done === true) {
			$canvas = $type == 'skin' ? 'skins' : ($type == 'box' ? 'boxes' : ($type == 'package' ? 'packages' : ''));
			if ($canvas !== 'skins')
				$options = get_option("thesis_$canvas");
			else
				$options = array();
			if (isset($options[$this->upgrader->thesis_class])) {
				unset($options[$this->upgrader->thesis_class]);
				update_option("thesis_$canvas", $options);
				wp_cache_flush();
			}
			echo "<script type=\"text/javascript\">parent.jQuery('#". esc_attr($this->upgrader->thesis_type) ."_". esc_attr($this->upgrader->thesis_class) ."').remove();</script>";
		}
		echo "</div>";
	}
}

class thesis_generate extends WP_Upgrader {
	public $zip_url = false;
	public $class = false;
	public $skin_data = array();
	
	public function generate() {
		global $wp_filesystem;
		
		$this->init();
		$this->fs_connect();
		$this->skin->header();
		
		if (is_object($wp_filesystem))
			$this->begin();
		
		$this->skin->footer();
	}
	
	private function begin() {
		if (! empty($_GET['skin']))
			$this->class = urldecode($_GET['skin']);
		else
			wp_die(__('The skin class was not found.', 'thesis'));
		
		// fills skin data with info
		$this->setup_skin_data();
		
		// returns true if seed file created
		$seed = $this->create_seed();
		
		if (!! $seed)
			$this->start_zip();
		else
			wp_die(__('Unable to create options file.', 'thesis'));
		
		
	}
	
	private function setup_skin_data() {
		// skin list and check if class exists
		$skins = thesis_skins::get_items();
		if (! isset($skins[$this->class]) && ! empty($skins[$this->class]['folder']))
			wp_die(__('Could not find the specified skin.', 'thesis'));
		else
			$this->skin->feedback(__('Setting up skin.', 'thesis'));
		$this->skin_data = $skins[$this->class];
	}
	
	private function create_seed() {
		global $wp_filesystem;
		include_once(THESIS_CORE . '/manager.php');
		$manager = new thesis_skin_manager(array('class' => $this->class));
		$data = $manager->export(false, true);
		$skin_dir = trailingslashit($wp_filesystem->find_folder(THESIS_USER_SKINS . '/' . $this->skin_data['folder']));
		if (! !!$skin_dir)
			wp_die(__('Skin not found on filesystem.', 'thesis'));
			
		$entries = array('css', 'boxes', 'packages', 'vars', 'templates');
		$build = array();
		foreach ($entries as $entry)
			if ($option = get_option($this->class . '_' . $entry))
				$build[$this->class . '_' . $entry] = $option;
				
		
		$code = "<?php\n\n".
				"function " . $this->class . "_defaults() {\n".
				"\t\$all = ". var_export($build, true) .";\n".
				"\tforeach (\$all as \$key => \$data)\n".
				"\t\tupdate_option(\$key, (strpos(\$key, 'css') ? strip_tags(\$data) : \$data));\n".
				"}\n".
				"wp_cache_flush();";
		
		if (! $wp_filesystem->put_contents($skin_dir . 'seed.php', $code))
			wp_die(__('Seed file not made.', 'thesis'));
		$this->skin->feedback(__('Seed file created.', 'thesis'));
		return true;
	}
	
	private function start_zip() {
		if (file_exists(ABSPATH . 'wp-admin/includes/class-pclzip.php'))
			require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
		if (! class_exists('PclZip'))
			wp_die(__('Unable to load the PclZip class that is normally packaged with WordPress. Please contact your server administrator.', 'thesis'));
		
		$this->skin->feedback(__('Creating zip file in ' . THESIS_USER_SKINS, 'thesis'));
		
		$zip_name = THESIS_USER_SKINS . '/' . $this->skin_data['folder'] . '.zip';

		$a = new PclZip($zip_name);
		
		$add = $a->create(THESIS_USER_SKINS . '/' . $this->skin_data['folder'], PCLZIP_OPT_REMOVE_PATH, THESIS_USER_SKINS);
		
		if ($add === 0)
			wp_die(sprintf(__('Unspecified error encountered creating zip file. Please contact your server administrator and reference %s', 'thesis'), ABSPATH . 'wp-admin/includes/class-pclzip.php'));
		else $this->skin->feedback(sprintf(__('Successfully created zip file for %s.', 'thesis'), esc_attr($this->skin_data['name'])));
		
		$this->zip_url = THESIS_USER_SKINS_URL . "/" . basename($zip_name);
	}
}

class thesis_generate_skin extends WP_Upgrader_Skin {
	public function footer() {
		if ($this->upgrader->zip_url !== false) {
			echo "<p><a href=\"". esc_url($this->upgrader->zip_url) ."\">". __('Click here to download zip file.', 'thesis') ."</a></p>";
		}
		echo "</div>";
	}
}
endif;