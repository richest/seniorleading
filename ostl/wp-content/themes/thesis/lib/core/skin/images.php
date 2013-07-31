<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_images {
	public function __construct() {
		$args = array(
			'title' => __('Thesis Upload Image', 'thesis'),
			'prefix' => 'thesis_images',
			'file_type' => 'image',
			'folder' => 'skin');
		$this->upload = new thesis_upload($args);
		add_action("{$args['prefix']}_after_thesis_iframe_form", array($this, 'get_images'));
		add_action("{$args['prefix']}_thesis_iframe_head", array($this, 'css'));
		add_action('admin_post_thesis_delete_image', array($this, 'delete_image'));
	}

	public function css() {
		echo
			"<style type=\"text/css\">\n".
			"body { padding: 0 12px; }\n".
			"#images { margin-top: 24px; padding: 13px 24px; background: #fff; -webkit-box-shadow: inset 0 0 8px rgba(0,0,0,0.5); -moz-box-shadow: inset 0 0 8px rgba(0,0,0,0.5); box-shadow: inset 0 0 8px rgba(0,0,0,0.5); float:left; clear: both; }\n".
			"th { font: bold 18px/26px \"Helvetica Neue\", Helvetica, Arial, sans-serif; }\n".
			"th, td { padding: 6px 12px; text-align: center; }\n".
			"tr th:first-child, tr td:first-child { padding-left: 0; }\n".
			"tr th:last-child, tr td:last-child { padding-right: 0; }\n".
			".code { font-family: Consolas, Monaco, Menlo, Verdana, sans-serif; font-size: 14px; }\n".
			"tr:hover { background: #fffdcc; }\n".
			"img { display: block; max-width: 200px; max-height: 150px; }\n".
			"[data-style~=\"button\"] { display: inline-block; font: bold 16px/1em \"Helvetica Neue\", Helvetica, Arial, sans-serif; padding: 8px 10px; cursor: pointer; color: rgba(255,255,255,0.9); text-shadow: -1px -1px 0 rgba(0,0,0,0.2); background-color: #888; border: 1px solid rgba(0,0,0,0.3); -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px; -webkit-box-shadow: 0 0 5px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.45); -moz-box-shadow: 0 0 5px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.45); box-shadow: 0 0 5px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.45); }\n".
			"[data-style~=\"button\"]:hover { background-color: #9f9f9f; }\n".
			"[data-style~=\"button\"]:active { color: #fff; background: #9f9f9f; -webkit-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2), inset 0 1px 2px rgba(0,0,0,0.6); -moz-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2), inset 0 1px 2px rgba(0,0,0,0.6); box-shadow: inset 0 1px 0 rgba(255,255,255,0.2), inset 0 1px 2px rgba(0,0,0,0.6); }\n".
			"[data-style~=\"delete\"] { background-color: #bb0303; text-decoration: none; }\n".
			"[data-style~=\"delete\"]:hover { background-color: #d50b0b; }\n".
			"[data-style~=\"delete\"]:active { background: #d50b0b; }\n".
			"td textarea { resize: none; border: none; outline: none; width: auto; background: transparent; font-size: 14px; text-align: center; }\n".
			"td.code textarea { width: 300px;}\n".
			"</style>\n";
	}

	public function get_images() {
		global $thesis;
		if (!defined('THESIS_USER_SKIN_IMAGES') || !defined('THESIS_USER_SKIN_IMAGES_URL')) return false;
		$img_dir = THESIS_USER_SKIN_IMAGES;
		$img_url = THESIS_USER_SKIN_IMAGES_URL;
		$files = @scandir($img_dir);
		$images = '';
		if (!$files === false) {
			foreach ($files as $file)
				if (!in_array($file, array('.', '..'))) {
					$image_data = @getimagesize("$img_dir/$file");
					if ($image_data === false)
						continue;
					$image_url = trailingslashit($img_url) . $file;
					$images .= 
						"\t\t\t<tr>\n".
						"\t\t\t\t<td><img src=\"" . esc_url($image_url) . "\" /></td>\n".
						"\t\t\t\t<td class=\"code\"><textarea rows=\"1\" readonly=\"readonly\">images/$file</textarea></td>\n".
						"\t\t\t\t<td class=\"number\">{$image_data[0]}</td>\n".
						"\t\t\t\t<td class=\"number\">{$image_data[1]}</td>\n".
						"\t\t\t\t<td><a onclick=\"if (!confirm('". __('Are you sure you want to delete this image?') ."')) return false\" data-style=\"button delete\" href=\"". esc_url(wp_nonce_url(admin_url('admin-post.php?action=thesis_delete_image&image=' . urlencode($file)), 'thesis-delete-image')) ."\">{$thesis->api->strings['delete']}</a></td>\n".
						"\t\t\t</tr>\n";
				}
		}
		else
			return false;
		echo
			"<div id=\"images\">\n".
			"\t<table>\n".
			"\t\t<thead>\n".
			"\t\t\t<tr class=\"highlight\">\n".
			"\t\t\t\t<th>" . __('Image', 'thesis') . "</th>\n".
			"\t\t\t\t<th>" . sprintf(__('%s Reference', 'thesis'), $thesis->api->base['css']) . "</th>\n".
			"\t\t\t\t<th class=\"number\">" . __('Width (px)', 'thesis') . "</th>\n".
			"\t\t\t\t<th class=\"number\">" . __('Height (px)', 'thesis') . "</th>\n".
			"\t\t\t</tr>\n".
			"\t\t</thead>\n".
			"\t\t<tbody>\n".
			$images.
			"\t\t</tbody>\n".
			"\t</table>\n".
			"</div>\n";
	}
	
	public function delete_image() {
		if (!current_user_can('install_themes') && !wp_verify_nonce($_REQUEST['_wpnonce'], 'thesis-delete-image'))
			wp_die(__('You cannot perform this action.', 'thesis'));
		$file = THESIS_USER_SKIN_IMAGES . '/' . urldecode($_GET['image']);
		
		if (! file_exists($file) || getimagesize($file) === false)
			wp_die(__('You cannot perform this action.', 'thesis'));
		
		@unlink($file);
		
		wp_redirect(admin_url("admin-post.php?action=thesis_images_window&window_nonce=" . wp_create_nonce('thesis_upload_iframe')));
		exit;
	}
}