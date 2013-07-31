<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_user_boxes {
	private $boxes = array();	// (array) format: ('box_class' => 'current_folder')
	public $active = array();	// (array) all active user box classes

	public function __construct() {
		global $thesis;
		$this->boxes = is_array($boxes = $thesis->api->get_option('thesis_boxes')) ? $boxes : $this->boxes;
		$this->active = array_keys($this->boxes);
		foreach ($this->boxes as $class => $folder)
			if (file_exists(THESIS_USER_BOXES . "/$folder/box.php"))
				include_once(THESIS_USER_BOXES . "/$folder/box.php");
		if ($thesis->environment == 'admin') {
			new thesis_upload(array(
				'title' => __('Thesis Upload Box', 'thesis'),
				'prefix' => 'thesis_box_uploader',
				'file_type' => 'zip',
				'folder' => 'box'));
			$this->installed = $this->get_items();
			if (!empty($_GET['canvas']) && $_GET['canvas'] == 'boxes') {
				add_action('thesis_admin_canvas', array($this, 'canvas')); #wp
				wp_enqueue_style('thesis-objects', THESIS_CSS_URL . '/objects.css', array('thesis-admin'), $thesis->version); #wp
				wp_enqueue_style('thesis-boxes', THESIS_CSS_URL . '/boxes.css', array('thesis-objects'), $thesis->version); #wp
				wp_enqueue_script('thesis-boxes', THESIS_JS_URL . '/boxes.js', array('thesis-menu'), $thesis->version); #wp
			}
		}
		if ($thesis->environment == 'ajax') {
			add_action('wp_ajax_save_boxes', array($this, 'save')); #wp
		}
	}

	public function get_items() {
		if (is_object($this)) {
			$nawnce = !empty($_GET['_wpnonce']) ? $_GET['_wpnonce'] : false;
			if (wp_verify_nonce($nawnce, 'thesis_did_update'))
				delete_transient('thesis_boxes_update');
			$this->updates = get_transient('thesis_boxes_update');
		}
		$boxes = array();
		$path = THESIS_USER_BOXES;
		$default_headers = array(
			'name' => 'Name',
			'class' => 'Class',
			'author' => 'Author',
			'description' => 'Description',
			'version' => 'Version');
		$directory = scandir($path);
		foreach ($directory as $dir) {
			if (in_array($dir, array('.', '..')) || strpos($dir, '.') === 0 || ! is_dir("$path/$dir") || ! @file_exists("$path/$dir/box.php")) continue;
			$box = get_file_data("$path/$dir/box.php", $default_headers);
			$box['folder'] = $dir;
			$boxes[$box['class']] = $box;
		}
		return $boxes;
	}

	public function canvas() {
		global $thesis;
		$tab = str_repeat("\t", $depth = 2);
		$boxes = $this->installed;
		$list = '';
		foreach ($boxes as $class => $box)
			$list .= $this->item_info($box, 2);
		echo
			"$tab<h3>" . __('Thesis Boxes', 'thesis') . " <span id=\"box_upload\" data-style=\"button action\" title=\"" . __('upload a new box', 'thesis') . "\">" . __('Upload Box', 'thesis') . "</span>".
			(!empty($this->updates) ? "<a onclick=\"if(!thesis_update_message()) return false;\" style=\"margin-left:12px;vertical-align:10%;\" data-style=\"button save\" href=\"". wp_nonce_url(admin_url('update.php?action=thesis_update_objects&update_type=box'), 'thesis-update-objects') ."\">". __('Update Boxes', 'thesis') . "</a>\n" : '').
			"</h3>\n".
			"$tab<p class=\"box_primer\">" . sprintf(__('<strong>Note:</strong> The boxes you select here will be activated and added to the Template Editor, where you can add them to your templates. If your box is designed for use in the document <code>&lt;head&gt;</code>, it will be added to the <a href="%1$s">Document Head editor</a>.', 'thesis'), admin_url('admin.php?page=thesis&canvas=head')) . "</p>\n".
			"$tab<form id=\"select_boxes\" method=\"post\" action=\"\">\n". #wp
			"$tab\t<div class=\"box_list\">\n".
			$list.
			"$tab\t</div>\n".
			"$tab\t" . wp_nonce_field('thesis-update-boxes', '_wpnonce-thesis-ajax', true, false) . "\n".
			"$tab\t<input type=\"submit\" data-style=\"button save\" class=\"t_save\" id=\"save_boxes\" name=\"save_boxes\" value=\"" . __('Save Boxes', 'thesis') . "\" />\n".
			"$tab</form>\n".
			$thesis->api->popup(array(
				'id' => 'box_uploader',
				'title' => __('Upload a Thesis Box', 'thesis'),
				'body' => $thesis->api->uploader('thesis_box_uploader')));
	}

	public function item_info($box, $depth = 0) {
		$tab = str_repeat("\t", $depth);
		$active_boxes = is_object($this) && property_exists($this, 'active') ? $this->active : array_keys(get_option('thesis_boxes', array()));
		$checked = in_array($box['class'], $active_boxes) ? ' checked="checked"' : '';
		$active = !empty($checked) ? ' active_box' : '';
		$author = !empty($box['author']) ? " <span class=\"box_by\">" . __('by', 'thesis') . "</span> <span class=\"box_author\">". esc_attr($box['author']) ."</span>" : '';
		return
			"$tab\t\t<div id=\"box_". esc_attr($box['class']) ."\" class=\"box$active\" data-box=\"". esc_attr($box['class']) ."\">\n".
			"$tab\t\t\t<h4>{$box['name']} <span class=\"box_version\">v " . esc_attr($box['version']) . "</span>$author" . (is_object($this) && !empty($this->updates[$box['class']]) ? " <span class=\"t_update_available\">" . __('Update Available!', 'thesis') . "</span>" : '') . "</h4>\n".
			"$tab\t\t\t<p class=\"box_description\">". esc_textarea($box['description']) ."</p>\n".
			"$tab\t\t\t<input type=\"checkbox\" class=\"select_box\" id=\"". esc_attr($box['class']) ."\" name=\"boxes[". esc_attr($box['class']) ."]\" value=\"1\"$checked />\n".
			"$tab\t\t\t<a onclick=\"thesis_boxes.delete_popup('". esc_attr($box['class']) ."', '". wp_nonce_url(admin_url("update.php?action=thesis_delete_object&thesis_object_type=box&thesis_object_name={$box['class']}"), 'thesis-delete-object') ."')\" data-style=\"button delete\" class=\"delete_box\" data-box=\"". esc_attr($box['class']) ."\">". __('Delete Box', 'thesis') ."</a>\n".
			"$tab\t\t</div>\n";
	}

	public function save() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		parse_str(stripslashes($_POST['form']), $form);
		$thesis->wp->nonce($form['_wpnonce-thesis-ajax'], 'thesis-update-boxes');
		if (is_array($form)) {
			$boxes = array();
			$installed = $this->get_items();
			if (!empty($form['boxes']) && is_array($form['boxes']))
				foreach ($form['boxes'] as $class => $on)
					if ($on && is_array($installed[$class]) && !empty($installed[$class]['folder']))
						$boxes[$class] = $installed[$class]['folder'];
			if (empty($boxes))
				delete_option('thesis_boxes'); #wp
			else
				update_option('thesis_boxes', $boxes); #wp
			echo $thesis->api->alert(__('Boxes saved!', 'thesis'), 'boxes_saved', true);
		}
		else
			echo $thesis->api->alert(__('Boxes not saved.', 'thesis'), 'boxes_saved', true);
		if ($thesis->environment == 'ajax') die();
	}
}