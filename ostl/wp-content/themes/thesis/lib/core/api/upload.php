<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_upload {
	private $args = array();

	public function __construct($args = array()) {
		if (empty($args['prefix'])) return false;
		$defaults = array(
			'title' => __('Thesis Uploader', 'thesis'),
			'nonce' => "{$args['prefix']}_upload_nonce",
			'action' => "{$args['prefix']}_upload_action",
			'window_action' => "{$args['prefix']}_window",
			'urlholder' => "{$args['prefix']}_url_holder",
			'file_type' => 'zip', // zip, image, txt
			'folder' => 'box', // skin, box, package
			'post_id' => 0,
			'prefix' => '');
		foreach ($defaults as $key => $value)
			if (isset($args[$key]))
				$defaults[$key] = $args[$key];
		$this->args = $defaults;
		add_action('admin_post_' . $this->args['window_action'], array($this, 'iframe'));
		if (in_array($this->args['file_type'], array('image', 'txt')))
			add_action('admin_post_' . $this->args['action'], array($this, 'save'));
		elseif ($this->args['file_type'] === 'zip')
			add_action('update-custom_' . $this->args['action'], array($this, 'save'));
	}

	public function iframe() {
		global $thesis, $wp_scripts;
		if (!wp_verify_nonce($_GET['window_nonce'], 'thesis_upload_iframe') && current_user_can('upload_files'))
			wp_die(__('You are not allowed to upload files.', 'thesis'));
		$file = in_array($this->args['file_type'], array('image', 'txt')) ? 'admin-post' : 'update';
		$button_text = $this->args['file_type'] == 'zip' ?sprintf(__('Add %s', 'thesis'), ucwords(esc_attr($this->args['folder']))) : ($this->args['file_type'] == 'image' ? __('Add Image', 'thesis') : ($this->args['file_type'] == 'txt' ? __('Import Data', 'thesis') : __('Upload', 'thesis')));
		echo
			"<!DOCTYPE html>\n".
			"<html dir=\"ltr\" lang=\"en-US\">\n".
			"<head>\n".
			"<style type=\"text/css\">\n".
			"* { margin: 0; padding: 0; }\n".
			"h1, h2, h3, h4, h5, h6 { font-weight: normal; }\n".
			"table { border-collapse: collapse; border-spacing: 0; }\n".
			"img, abbr, acronym, fieldset { border: 0; }\n".
			"code { line-height: 1em; }\n".
			"body { font: normal 16px/1.625em \"Lucida Grande\", \"Segoe UI\", Segoe, Tahoma, Geneva, sans-serif; }\n".
			".t_input { font-size: 16px; line-height: 1.25em; padding: 6px 5px; color: #48694a; background: #ffe9cc; border: 1px solid #e6dcd6; border-width: 0 0 1px 0; -webkit-box-shadow: inset 0px 2px 2px rgba(0,0,0,0.25); -moz-box-shadow: inset 0 2px 2px rgba(0,0,0,0.25); box-shadow: inset 0 2px 2px rgba(0,0,0,0.25); -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; }\n".
			".t_button { display: inline-block; font: bold 16px/1em \"Helvetica Neue\", Helvetica, Arial, sans-serif; padding: 8px 10px; cursor: pointer; color: rgba(255,255,255,0.9); text-shadow: -1px -1px 0 rgba(0,0,0,0.2); background-color: #509b26; background-image: url('" . THESIS_IMAGES_URL . "/bg-button.png'); background-repeat: repeat-x; border: 1px solid rgba(0,0,0,0.3); -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px; -webkit-box-shadow: 0 0 5px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.45); -moz-box-shadow: 0 0 5px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.45); box-shadow: 0 0 5px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.45); }\n".
			".t_button:hover { background-color: #5bae2c; }\n".
			".t_button:active { color: #fff; background: #5bae2c; -webkit-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2), inset 0 1px 2px rgba(0,0,0,0.6); -moz-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2), inset 0 1px 2px rgba(0,0,0,0.6); box-shadow: inset 0 1px 0 rgba(255,255,255,0.2), inset 0 1px 2px rgba(0,0,0,0.6); }\n".
			"#t_iframe_submit { position: relative; margin-top: 12px; }\n".
			".t_ajax_alert { position: absolute; top: -3px; z-index: 1000; }\n".
			".t_ajax_alert .t_message { position: relative; }\n".
			".t_ajax_alert p { padding: 8px 12px; color: #fff; text-shadow: 1px 1px 0 rgba(0,0,0,0.95); background: rgba(0,0,0,0.8); -webkit-border-radius: 12px; -moz-border-radius: 12px; border-radius: 12px; }\n".
			".t_ajax_alert p:after { position: absolute; right: 100%; width: 0; height: 0; content: ' '; top: 12px; border: 9px solid transparent; border-right-color: rgba(0,0,0,0.8); }\n".
			"</style>\n";
		do_action($this->args['prefix'] . '_thesis_iframe_head');
		$import = !empty($_GET['import']) && $_GET['import'] == 'true' ? true : false;
		$image = !empty($_GET['height']) && !empty($_GET['width']) && !empty($_GET['url'])? true : false;
		echo ($image && empty($import) ?
			"<script type=\"text/javascript\">\n".
			"var thesis_image_result = { height: ". (int)$_GET['height'] .", width: ". (int)$_GET['width'] .", url: '". esc_url($_GET['url']) ."' };\n".
			"</script>\n" : '').
			"</head>\n";
			do_action("{$this->args['prefix']}_before_thesis_iframe_form");
		echo
			"<body>\n".
			"<form id=\"t_iframe\" method=\"post\" action=\"" . admin_url("$file.php?action=". esc_attr($this->args['action'])) . "\" enctype=\"multipart/form-data\">\n".
			"\t<p>\n".
			"\t\t<input type=\"file\" class=\"t_input\" name=\"thesis_file\" />\n".
			"\t</p>\n".
			"\t<div id=\"t_iframe_submit\">\n".
			"\t\t<input type=\"submit\" id=\"t_upload_button\" class=\"t_button\" value=\"$button_text\" />\n".
			(!! $image ?
			$thesis->api->alert(__('Image uploaded!', 'thesis'), 'image_uploaded', true) : '').
			"\t\t" . wp_nonce_field($this->args['nonce'], 'thesis_form_nonce', false, false). "\n".
			"\t\t" . wp_referer_field(false) . "\n".
			"\t\t<input type=\"hidden\" value=\"". esc_attr($this->args['folder']) ."\" name=\"location\" />\n".
			"\t</div>\n".
			"</form>\n";
		do_action("{$this->args['prefix']}_after_thesis_iframe_form");
		echo "<script type=\"text/javascript\" src=\"" . $wp_scripts->base_url . $wp_scripts->registered['jquery']->src . "\"></script>\n";
		
		?>
			<script type="text/javascript">
				<?php if (!empty($_GET['action']) && $_GET['action'] == 'import_skin_window') {
					echo "jQuery('#t_iframe').submit( function() {
						if (confirm(\"". __('Are you sure you want to do this? If you import from a file, you will lose the current state of your Skin unless you make a backup first.\n\nHit cancel to return to the manager and make a backup, or hit OK to import the Skin options!', 'thesis') ."\"))
							return true;
						else return false;
					});";
				} ?>
				jQuery('td.code textarea').click(function(){
					jQuery(this).select();
				});
			</script>
		<?php
		echo
			(!!$image || !! $import ?
			"<script type=\"text/javascript\">\n".
			($image ? "jQuery(document).ready(function($) {\n".
			"$('#image_uploaded').css({'left': $('#t_upload_button').outerWidth()+11+'px'});\n".
			"$('#image_uploaded').fadeOut(3000, function() { $(this).remove(); });\n".
			"});\n" : '').
			($import ? "parent.window.location.reload();\n" : '' ).
			"</script>\n" : '').
			"</body>\n".
			"</html>\n";
	}

	public function save() {
		global $thesis;
		if ($this->args['file_type'] === 'image') {
			$url = "admin-post.php?action=". $this->args['window_action'] ."&window_nonce=" . wp_create_nonce('thesis_upload_iframe');
			if (is_array($result = $thesis->api->save_image('thesis_file', substr($this->args['folder'], 0, 7), (int) $this->args['post_id'])))
				foreach ($result as $p => $value)
					$url .= "&$p=" . ($p == 'url' ? urlencode(esc_url_raw($value)) : $value);
			wp_redirect(admin_url($url));
			exit;
		}
		elseif ($this->args['file_type'] === 'zip' && in_array($this->args['folder'], array('skin', 'package', 'box'))) {
			// new skin/box/package. Unpack and send to the right directory
			define('IFRAME_REQUEST', true);
			require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
			require_once(THESIS_API . '/upload-ext.php');
			$upload = new File_Upload_Upgrader('thesis_file', 'object');
			add_action('admin_head', array($this, 'admin_css'));
			add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
			require_once(ABSPATH . 'wp-admin/admin-header.php');
			$title = sprintf(__('Installing %s from uploaded file: %s'), ucwords($this->args['folder']), basename($upload->filename));
			$nonce = $this->args['nonce'];
			$url = add_query_arg(array('object' => $upload->id), 'update.php?action='. $this->args['action'] .'');
			$type = 'upload';
			$upgrader = new thesis_uploader(new thesis_upload_skin(compact('type', 'title', 'nonce', 'url')));
			$result = $upgrader->install($upload->package, $this->args, $upload->id);
			if ($result || is_wp_error($result))
				$upload->cleanup();
			include(ABSPATH . 'wp-admin/admin-footer.php');
		}
		elseif ($this->args['file_type'] === 'txt') {
			$url = "admin-post.php?action=". $this->args['window_action'] ."&window_nonce=" . wp_create_nonce('thesis_upload_iframe');
			if ($thesis->skins->import('thesis_file', $this->args['nonce']))
				wp_redirect("$url&import=true");
			else
				wp_redirect("$url&import=false");
			exit;
		}
	}

	public function admin_css() {
		echo
			"<style type=\"text/css\">\n".
			"#adminmenuback, #adminmenuwrap, #wpadminbar, #footer, #icon-update { display:none; }\n".
			"html.wp-toolbar { padding-top:0; }\n".
			"#wpcontent { margin-left:0 !important; }\n".
			"</style>\n";
	}

	public function admin_scripts() {
		wp_enqueue_script('jquery');
	}
}