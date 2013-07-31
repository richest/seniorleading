<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_uploader extends WP_Upgrader {
	public $default_headers = array(
		'name' => 'Name',
		'author' => 'Author',
		'description' => 'Description',
		'version' => 'Version',
		'class' => 'Class');
	public $custom_file_contents = "<?php\n/*\n\tThis file is for skin specific customizations. Be careful not to change your skin's skin.php file as that will be upgraded in the future and your work will be lost.\n\tIf you are more comfortable with PHP, we recommend using the super powerful Thesis Box system to create elements that you can interact with in the Thesis HTML Editor.\n*/";
	
	private $destination;
	
	public function install($package, $args, $upload_id) {
		// $type = skin, package or box
		if (! in_array($args['folder'], array('skin', 'package', 'box')))
			return new WP_Error('wrong_type', __('You have not given a recognized upload type.', 'thesis'));
		$this->strings = array(
			'unpack_package' => "Unpacking {$args['folder']}&#8230;",
			'process_success' => ucfirst($args['folder']) . " successfully installed&#8230;",
			'not_installed' => "{$args['folder']} was not installed&#8230;",
			'process_failed' => ucfirst($args['folder']) . " was not installed&#8230;"
		);
		$this->args_t = $args;
		$this->upload_id = $upload_id;
		$this->init();
		
		// skin, box or package?
		$this->destination = $args['folder'] === 'skin' ? THESIS_USER_SKINS : ($args['folder'] === 'box' ? THESIS_USER_BOXES : ($args['folder'] === 'package' ? THESIS_USER_PACKAGES : false));
		
		// full path to asset
		$this->asset = $this->destination . '/' . basename($package, '.zip');
		
		// file type
		$this->type = !!$this->destination ? $args['folder'] : false;
		
		$run = array(
			'package' => $package,
			'destination' => $this->asset,
			'clear_destination' => false,
			'clear_working' => true
		);
			
		add_filter('upgrader_post_install', array($this, 'my_validate'), 10, 3);
		$this->run($run);
		remove_filter('upgrader_post_install', array($this, 'my_validate'), 10, 3);
		if (!$this->result || is_wp_error($this->result))
			return $this->result;
		return true;
	}
	
	public function my_validate($true, $hook_extra, $result) {
		global $wp_filesystem;

		// $result['remote_destination'] is the fs path to the installed folder and is trailingslashed
		$asset = untrailingslashit($result['remote_destination']);
		
		if (! $wp_filesystem->exists("$asset/{$this->type}.php"))
			return new WP_Error("no_{$this->type}", __("Could not find the {$this->type}.php file.", 'thesis'));
		
		// look for skin/box/package.php
		$file = "$asset/{$this->type}.php";

		// list the skin contents
		if (! ($contents = $wp_filesystem->dirlist($asset)))
			$this->skin->feedback(__('The filesystem is currently unavailable.', 'thesis'));


		// get the headers
		$headers = get_file_data("{$result['local_destination']}/{$this->type}.php", $this->default_headers);
		
		// are we missing any crucial headers?
		if (empty($headers['class']) || empty($headers['version']) || empty($headers['name']))
			return new WP_Error('headers', sprintf(__('This %1$s has incomplete file headers. Please contact the author.', 'thesis'), $this->type));
			
		$this->item_headers = $headers;
		$this->item_headers['folder'] = basename($result['local_destination']);
		add_action('admin_footer', array($this, 'admin_footer'), 100);

		if ($this->type === 'skin') {
			
			// do we have an images folder? if not, try to make it.
			if (! isset($f['images']) ) // effectively checking wp_fs->exists without a func call
				if (! $wp_filesystem->is_dir("$asset/images") && ! $wp_filesystem->mkdir("$asset/images"))
					$this->skin->feedback(__('Could not make images folder.', 'thesis'));

			// do we have a custom file? Doubtful. Let's make one.		
			if (! isset($f['custom.php']) )
				if (! $wp_filesystem->put_contents("$asset/custom.php", $this->custom_file_contents)) // custom functions
					$this->skin->feedback(__('Could not make custom.php file.', 'thesis'));
		}

		return true;
	}
	
	public function admin_footer() {
		global $thesis;
		
		if ($this->args_t['folder'] == 'skin') {
			$item = thesis_skins::item_info($this->item_headers);
			$js = 'skins';
			$selector = '#installed_skins';
		}
		elseif ($this->args_t['folder'] == 'package') {
			$item = thesis_user_packages::item_info($this->item_headers);
			$js = 'packages';
			$selector = '.package_list';
		}
		elseif ($this->args_t['folder'] == 'box') {
			$item = thesis_user_boxes::item_info($this->item_headers);
			$js = 'boxes';
			$selector = '.box_list';
		}

		echo "<div style=\"display:none;\">$item</div>";
		
		// need to check for asset type (package, box, skin)
		echo "<script type=\"text/javascript\">		
			(function(){
				parent.thesis_$js.add_item('#thesis_upload_iframe_{$this->args_t['prefix']}', '#{$this->args_t['folder']}_{$this->item_headers['class']}', '$selector', '" . admin_url("admin-post.php?action={$this->args_t['prefix']}_window&window_nonce=" . wp_create_nonce('thesis_upload_iframe')) . "');
			})();
		</script>";
	}
	
	function fs_connect() {
		global $wp_filesystem;
		
		$fs = create_function('$url, $e, $con', 'return request_filesystem_credentials($url, "", $e, $con);');
		$c = $fs($this->skin->options['url'], false, $this->skin->options['context']);
		$f = WP_Filesystem($c);

		if (! $c)
			return false;
		elseif (! $f) {
			$e = true;
			if (is_object($wp_filesystem) && $wp_filesystem->errors->get_error_code())
				$e = $wp_filesystem->errors;
			$fs($this->skin->options['url'], $e, $this->skin->options['context']); //Failed to connect, Error and request again
			return false;
		}
		elseif (! is_object($wp_filesystem))
			return new WP_Error('fs_unavailable', __('The filesystem is presently unavailable.', 'thesis'));

		// do we have a wp-content dir? if not, DIEEEEE	
		if (! $wp_filesystem->wp_content_dir())
			return new WP_Error('fs_no_content_dir', __('We could not find your wp-content directory.', 'thesis'));

		// END wp_filesystem. Now, we work on more specific tasks.
		
		// folder in relation to wp_fs
		$asset_fs = trailingslashit($wp_filesystem->find_folder(dirname($this->asset))) . trailingslashit(basename($this->asset));

		// list of installed assets we have by folder name.
		$installed = array_keys($wp_filesystem->dirlist($wp_filesystem->find_folder($this->destination), false));
		
		// attachment id of uploaded zip
		$id = !empty($this->upload_id) ? $this->upload_id : (!empty($_GET['object']) ? $_GET['object'] : false);
		
		// if the skin we are trying to upload exists, we lose and it's time to bail.
		// do I need to delete anything (attachment, etc) here? I think so.
		if (in_array(basename($this->asset), $installed)) {
			if (! wp_delete_attachment(absint($id)))
				$this->skin->feedback('not_installed', __("We were unable to delete the zip file from your server.", 'thesis'));
			return new WP_Error('skin_exists', sprintf( __('This %1$s already exists.', 'thesis'), esc_attr($this->args_t['folder'])));
		}
		
		// create the file destination we are uploading. Kinda dumb that I have to do this here.
		if (! $wp_filesystem->mkdir($asset_fs)) {
			if (! wp_delete_attachment(absint($id)))
				$this->skin->feedback('not_installed', __("We were unable to delete the zip file from your server.", 'thesis'));
			return new WP_Error('mkdir_failure', __("Unable to make ". basename($this->asset) .".", 'thesis'));
		}

		return true;
	}
}

class thesis_upload_skin extends WP_Upgrader_Skin {

}