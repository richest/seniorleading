<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_upload {
	private $args = array();

	public function __construct($args = array()) {
		global $thesis;
		if (empty($args['prefix'])) return false;
		$defaults = array(
			'title' => __('Thesis Uploader', 'thesis'),
			'button' => !empty($args['button']) ? $args['button'] : false,
			'nonce' => "{$args['prefix']}_upload_nonce",
			'action' => "{$args['prefix']}_upload_action",
			'window_action' => "{$args['prefix']}_window",
			'urlholder' => "{$args['prefix']}_url_holder",
			'file_type' => 'zip', // zip, image, txt
			'folder' => 'box', // skin, box, package
			'post_id' => 0,
			'prefix' => '',
			'button_context' => 'save', // save = green, action = blue
			'show_delete' => false,
			'delete_text' => __('Delete Image', 'thesis'),
			'save_callback' => false);
		$this->args = array_merge($defaults, $args);
		if ($thesis->environment != 'admin') return;
		add_action("admin_post_{$this->args['window_action']}", array($this, 'iframe'));
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
		$button_text = !empty($this->args['button']) ?
			esc_attr($this->args['button']) : ($this->args['file_type'] == 'zip' ?
			sprintf(__('Add %s', 'thesis'), ucwords(esc_attr($this->args['folder']))) : ($this->args['file_type'] == 'image' ?
			__('Add Image', 'thesis') : ($this->args['file_type'] == 'txt' ?
			__('Import Data', 'thesis') :
			__('Upload', 'thesis'))));
		$import = !empty($_GET['import']) && $_GET['import'] == 'true' ? true : false;
		$image = !empty($_GET['height']) && !empty($_GET['width']) && !empty($_GET['url']) ? true : false;
		echo
			"<!DOCTYPE html>\n",
			"<html dir=\"ltr\" lang=\"en-US\">\n",
			"<head>\n",
			"<style type=\"text/css\">* { margin: 0; padding: 0; } body #t_canvas { padding: 0; } p.option_field { margin-bottom: 12px; }</style>\n",
			"<link rel=\"stylesheet\" href=\"", THESIS_CSS_URL, "/admin.css\" type=\"text/css\" media=\"all\" />\n",
			"<link rel=\"stylesheet\" href=\"", THESIS_CSS_URL, "/options.css\" type=\"text/css\" media=\"all\" />\n";
		do_action($this->args['prefix'] . '_thesis_iframe_head');
		echo ($image && empty($import) ?
			"<script type=\"text/javascript\">\n".
			"var thesis_image_result = { height: ". (int)$_GET['height'] .", width: ". (int)$_GET['width'] .", url: '". esc_url($_GET['url']) ."' };\n".
			"</script>\n" : ''),
			"</head>\n";
		do_action("{$this->args['prefix']}_before_thesis_iframe_form");
		echo
			"<body>\n",
			"\t<div id=\"t_canvas\">\t",
			"\t\t<form id=\"t_iframe\" method=\"post\" action=\"", admin_url("$file.php?action="), esc_attr($this->args['action']), "\" enctype=\"multipart/form-data\">\n",
			"\t\t\t<p class=\"option_field\">\n",
			"\t\t\t\t<input type=\"file\" data-style=\"input\" name=\"thesis_file\" />\n",
			"\t\t\t</p>\n",
			"\t\t\t<div id=\"t_iframe_submit\">\n",
			"\t\t\t\t<input type=\"submit\" id=\"t_upload_button\" data-style=\"button ", esc_attr($this->args['button_context']), "\" value=\"$button_text\" />\n",
			($this->args['show_delete'] === true ?
			"\t\t\t\t<style type=\"text/css\">#t_canvas #t_delete_button { margin-left: 12px; }</style>\n".
			"\t\t\t\t<button id=\"t_delete_button\" data-style=\"button delete\" name=\"delete_image\" value=\"1\">". esc_attr($this->args['delete_text']). "</button>\n" : ''),
			"\t\t\t\t", wp_nonce_field($this->args['nonce'], 'thesis_form_nonce', false, false), "\n",
			"\t\t\t\t", wp_referer_field(false), "\n",
			"\t\t\t\t<input type=\"hidden\" value=\"", esc_attr($this->args['folder']), "\" name=\"location\" />\n",
			"\t\t\t</div>\n",
			"\t\t</form>\n";
		do_action("{$this->args['prefix']}_after_thesis_iframe_form");
		echo	
			"\t</div>",
			"<script type=\"text/javascript\" src=\"", $wp_scripts->base_url, (!empty($wp_scripts->registered['jquery-core']->src) ? $wp_scripts->registered['jquery-core']->src : $wp_scripts->registered['jquery']->src), "\"></script>\n",
			"<script type=\"text/javascript\">",
			(!empty($_GET['action']) && $_GET['action'] == 'import_skin_window' ?
			"\tjQuery('#t_iframe').submit( function() {\n".
			"\t\tif (confirm(\"". __('Are you sure you want to do this? If you import from a file, you will lose the current state of your Skin unless you make a backup first.\n\nHit cancel to return to the manager and make a backup, or hit OK to import the Skin options!', 'thesis'). "\"))\n".
			"\t\t\treturn true;\n".
			"\t\telse return false;\n".
			"\t});\n" : ''),
			(!! $import ?
			"\tparent.window.location.reload();\n" : ''),
			($this->args['show_delete'] === true && $this->args['file_type'] == 'image' ? 
			"\tjQuery(document).ready(function($){\n".
			"\t\t$('#t_delete_button').on('click', function(){\n".
			"\t\t\tif (confirm('". __('Are you sure you want to remove this image?', 'thesis'). "')) {\n".
			"\t\t\t\t$.each(['url', 'height', 'width'], function(){\n".
			"\t\t\t\t\t$(parent.window.document.getElementById('image_' + this)).val('');\n".
			"\t\t\t\t});\n".
			"\t\t\t\t$('body > img').remove();\n".
			"\t\t\t}\n".
			"\t\t\telse\n".
			"\t\t\t\treturn false;\n".
			"\t\t});\n".
			"\t});\n" : ''),
			"</script>\n";
		do_action("{$this->args['prefix']}_thesis_iframe_body_bottom");
		echo
			"</body>\n",
			"</html>\n";
	}

	public function save() {
		global $thesis;
		if ($this->args['file_type'] === 'image') {
			$url = "admin-post.php?action=". $this->args['window_action'] ."&window_nonce=" . wp_create_nonce('thesis_upload_iframe');
			if (is_array($result = $thesis->api->save_image('thesis_file', substr($this->args['folder'], 0, 7), (int) $this->args['post_id'])))
				foreach ($result as $p => $value)
					$url .= "&$p=" . ($p == 'url' ? urlencode(esc_url_raw($value)) : $value);
			if ($this->args['save_callback'] && is_callable($this->args['save_callback']))
				call_user_func_array($this->args['save_callback'], array($result, 'delete' => !empty($_POST['delete_image']) ? true : false));
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
			"<style type=\"text/css\">\n",
			"#adminmenuback, #adminmenuwrap, #wpadminbar, #footer, #wpfooter, #icon-update { display:none; }\n",
			"html.wp-toolbar { padding-top:0; }\n",
			"#wpcontent { margin-left:0 !important; }\n",
			"</style>\n";
	}

	public function admin_scripts() {
		wp_enqueue_script('jquery');
	}
}