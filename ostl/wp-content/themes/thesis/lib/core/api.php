<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_api {
	public function __construct() {
		global $thesis;
		define('THESIS_API', THESIS_CORE . '/api');
		require_once(THESIS_API . '/form_api.php');
		require_once(THESIS_API . '/upload.php');
		$this->options = wp_load_alloptions();
		$this->strings = $this->strings();
		$this->schema = $this->schema();
		$this->form = new thesis_form_api;
	}

	public function get_option($option) {
		return isset($this->options[$option]) ? maybe_unserialize($this->options[$option]) : false;
	}

	public function esc($value) {
		return esc_attr(stripslashes($value));
	}

	public function esch($value) {
		return esc_html(stripslashes($value));
	}
	
	public function escht($value, $strip = false) {
		return $strip ?
			esc_html(wptexturize(stripslashes($value))) :
			esc_html(wptexturize($value));
	}

	public function alert($message, $id = false, $ajax = false, $status = false, $depth = false) {
		if (empty($message)) return;
		$id = $id ? " id=\"$id\"" : '';
		$ajax = $ajax ? '_ajax' : '';
		$status = $status == 'good' ? ' t_good' : ($status == 'bad' ? ' t_bad' : ($status == 'warning' ? ' t_warning' : ''));
		$tab = str_repeat("\t", (is_numeric($depth) ? $depth : 2));
		return
			"$tab<div$id class=\"t{$ajax}_alert$status\">\n".
			"$tab\t<div class=\"t_message\">\n".
			"$tab\t\t<p>$message</p>\n".
			"$tab\t</div>\n".
			"$tab</div>\n";
	}

	public function popup($args = array()) {
		$id = $title = $body = $type = '';
		$name = $menu = $panes = array();
		$depth = 0;
		extract($args); // array('id' (string), 'title' (string), 'name' (array), 'menu' (array), 'panes' (array), 'body' (string), 'depth' (int))
		$tab = str_repeat("\t", $depth);
		$li = false;
		$type = $type ? " type_$type" : '';
		$title = trim($title);
		$name = !empty($name) && is_array($name) ?
			": <input type=\"text\" data-style=\"input\" id=\"{$name['id']}\" data-id=\"$id\" class=\"t_popup_name\" name=\"{$name['name']}\" value=\"" . esc_attr($name['value']) . '"' . (is_numeric($name['tabindex']) ? " tabindex=\"{$name['tabindex']}\"" : '') . " />" : (!empty($name) ? ": $name" : '');
		if (is_array($menu))
			foreach ($menu as $pane => $text)
				$li[$pane] = "<li data-pane=\"$pane\">$text</li>";
		if (is_array($panes))
			foreach ($panes as $pane => $options)
				$body .=
					"$tab\t\t\t<div class=\"pane pane_$pane\">\n".
					$options.
					"$tab\t\t\t</div>\n";
		return
			"$tab<div id=\"popup_$id\" class=\"t_popup\">\n".
			"$tab\t<div class=\"t_popup_html$type\">\n".
			"$tab\t\t<div class=\"t_popup_head\" data-style=\"box\">\n".
			"$tab\t\t\t<span class=\"t_popup_close\" data-style=\"close\" title=\"" . __('click to close', 'thesis') . "\">'</span>\n".
			"$tab\t\t\t<h4>$title$name</h4>\n".
			(is_array($li) ?
			"$tab\t\t\t<ul class=\"t_popup_menu\">\n".
			"$tab\t\t\t\t" . implode("\n$tab\t\t\t\t", $li) . "\n".
			"$tab\t\t\t</ul>\n" : '').
			"$tab\t\t</div>\n".
			"$tab\t\t<div class=\"t_popup_body\">\n".
			$body.
			"$tab\t\t</div>\n".
			"$tab\t</div>\n".
			"$tab</div>\n";
	}

	public function uploader($name, $depth = false) {
		$tab = str_repeat("\t", is_numeric($depth) ? $depth : 0);
		return
			"$tab<iframe style=\"width:90%;height:100%;". (stripos($_SERVER['HTTP_USER_AGENT'], 'mozilla') >= 0 && $name == 'thesis_images' ? "position:absolute;" : '') ."\" frameborder=\"0\" src=\"" . admin_url("admin-post.php?action={$name}_window&window_nonce=" . wp_create_nonce('thesis_upload_iframe')) . "\" id=\"thesis_upload_iframe_$name\">\n".
			"$tab</iframe>\n";
	}

	public function get_box_form() {
		require_once(THESIS_API . '/box_form.php');
		return new thesis_box_form;
	}

	public function css() {
		require_once(THESIS_API . '/css_api.php');
		require_once(THESIS_API . '/typography.php');
		$this->css = new thesis_css_api;
		$this->typography = new thesis_typography_api;
	}

	public function set_options($fields, $values, $reference = '', $upload_type = 'default', $post_id = 0) {
		if (!is_array($fields)) return false;
		$save = array();
		foreach ($fields as $id => $field) {
			if (is_array($field)) {
				if ($field['type'] == 'group') {
					if (is_array($field['fields']))
						if ($group = $this->set_options($field['fields'], $values))
							foreach($group as $item_id => $val)
								$save[$item_id] = $val;
				}
				else {
					if ($field['type'] == 'image') {
						$value = !empty($values[$id]) ? $values[$id] : false;
						if (!empty($_FILES["{$reference}$id"]['name'])) {
							$new_image = $this->save_image("{$reference}$id", $upload_type, $post_id);
							$diff = array_diff(array('url', 'width', 'height', 'id'), array_keys(array_filter($new_image)));
							if ((in_array($upload_type, array('default', 'box')) && empty($diff))
								|| ($upload_type == 'skin' && count($diff) === 3)) {
								$value['url'] = esc_url_raw($new_image['url']);
								$value['width'] = absint($new_image['width']);
								$value['height'] = absint($new_image['height']);
								if (isset($new_image['id']))
									$value['id'] = (int) $new_image['id'];
							}
						}
					}
					else {
						$value = !empty($values[$id]) ? $values[$id] : false;
						if ($field['type'] == 'checkbox' && is_array($field['options'])) {
							$checkbox = array();
							$value = is_array($value) ? $value : array();
							foreach ($field['options'] as $option => $label)
								if ($value[$option] && empty($field['default'][$option]))
									$checkbox[$option] = true;
								elseif (empty($value[$option]) && !empty($field['default'][$option]))
									$checkbox[$option] = false;
							if (!empty($checkbox))
								$value = $checkbox;
							else
								unset($value);
						}
						elseif ((!empty($field['type']) && $field['type'] == 'text' || $field['type'] == 'color' || $field['type'] == 'textarea' || $field['type'] == 'radio' || ($field['type'] == 'select' && empty($field['multiple']))) && (isset($field['default']) && $value == $field['default']))
							unset($value);
						elseif (!empty($field['type']) && $field['type'] == 'select' && !empty($field['multiple']) && !empty($field['options']) && is_array($field['options'])) {
							
						}
						elseif ($field['type'] == 'image_upload')
							$value = array_filter(array(
							 	'url' => !empty($value['url']) ? esc_url_raw($value['url']) : false,
								'width' => !empty($value['width']) ? (int) $value['width'] : false,
								'height' => !empty($value['height']) ? (int) $value['height'] : false));
					}
					if (!empty($value))
						$save[$id] = $value;
				}
			}
		}
		return !empty($save) ? $save : false;
	}

	// $location is the location in $_FILES
	// $type = default means handle like normak upload
	// $type = skin means send to active skin's images folder
	public function save_image($location, $type = 'default', $post_id = 0) {
		if (empty($_FILES[$location]) || !current_user_can('upload_files'))
			return false;
		$url = $width = $height = $id = false;
		// plain old upload
		if ($type === 'default' || $type === 'box') {
			$post_id = (int) abs($post_id);
			// returns the attachment id.
			$id = media_handle_upload($location, $post_id);
			$id = (int) $id;
			$post = get_post($id);
			if (empty($post->guid))
				return false;
			$url = $post->guid;
			if (empty($url)) return false;
			$metadata = wp_get_attachment_metadata($id);
			if (empty($metadata)) {
				$wp_upload = wp_upload_dir(); // path
				$image_data = @getimagesize("{$wp_upload['path']}/" . basename($post->guid));
			}
			$height = !empty($metadata['height']) ? $metadata['height'] : (!empty($image_data[1]) ? $image_data[1] : false);
			$width = !empty($metadata['width']) ? $metadata['width'] : (!empty($image_data[0]) ? $image_data[0] : false);
		}
		elseif ($type === 'skin') {
			$upload = $_FILES[$location];
			if (! @is_uploaded_file($upload['tmp_name']) || ! ($upload_data = @getimagesize($upload['tmp_name'])) || $upload['error'] > 0 ||
				! defined('THESIS_USER_SKIN_IMAGES'))
				return false;
			if (! @is_dir(THESIS_USER_SKIN_IMAGES) && get_filesystem_method() === 'direct') {
				include_once(ABSPATH . 'wp-admin/includes/file.php');
				WP_Filesystem();
				if (!$GLOBALS['wp_filesystem']->mkdir(THESIS_USER_SKIN_IMAGES))
					return false;
			}
			$ext = explode('/', $upload_data['mime']);
			$ext = strtolower($ext[1]) == 'jpeg' ? 'jpg' : (strtolower($ext[1]) == 'tiff' ? 'tif' : strtolower($ext[1]));
			if (! stristr($upload['name'], ".$ext")) {
				$a = explode('.', $upload['name']);
				array_pop($a);
				array_push($a, $ext);
				$upload['name'] = implode('.', $a);
			}
			// make a unique file name
			$upload['name'] = wp_unique_filename(THESIS_USER_SKIN_IMAGES, $upload['name']);
			$path = untrailingslashit(THESIS_USER_SKIN_IMAGES) . "/{$upload['name']}";
			if (@move_uploaded_file($upload['tmp_name'], $path) === false)
				return false;
			$url = untrailingslashit(THESIS_USER_SKIN_IMAGES_URL) . "/{$upload['name']}";
			$height = $upload_data[0];
			$width = $upload_data[1];
		}
		$return = array_filter(array(
			'url' => esc_url_raw($url),
			'width' => !empty($width) ? (int) $width : false,
			'height' => !empty($height) ? (int) $height : false,
			'id' => !empty($id) ? $id : false));
		return !empty($return) ? $return : false;
	}

	public function get_options($fields, $values) { // Returns options + defaults (defaults are not saved to the db)
		if (!is_array($fields)) return array();
		$values = is_array($values) ? $values : array();
		$options = array();
		foreach ($fields as $id => $field)
			if (is_array($field)) {
				if ($field['type'] == 'group') {
					if (is_array($field['fields']))
						$options = is_array($group = $this->get_options($field['fields'], $values)) ? array_merge($options, $group) : $options;
				}
				else {
					if ($field['type'] == 'checkbox' && is_array($field['options']))
						foreach ($field['options'] as $option => $option_value) {
							$options[$id][$option] = isset($values[$id][$option]) ? (bool) $values[$id][$option] : (!empty($field['default'][$option]) ? $field['default'][$option] : false);
							if (empty($options[$id][$option]))
								unset($options[$id][$option]);
						}
					else
						$options[$id] = !empty($values[$id]) ? $values[$id] : (!empty($field['default']) ? $field['default'] : false);
				}
				if (empty($options[$id]))
					unset($options[$id]);
			}
		return $options;
	}

	public function verify_class_name($class) {
		return preg_match('/\A[a-zA-Z_]\w*\Z/', $class) ? $class : false;
	}

	public function verify_data_file($file, $class, $type = 'skin') {
		$string = is_string($file);
		$array = is_array($file);
		if (($array && !file_exists($file['tmp_name'])) || ($string && !file_exists($file)) || empty($class))
			return false;
		$name = $string ? basename($file) : ($array ? $file['name'] : false);
		$location = $string ? $file : ($array ? $file['tmp_name'] : false);
		if (!$name || !$location)
			return false;
		if (!(preg_match('/^[a-z0-9-]+\.txt$/', strtolower($name)))		// first, read the file and check the checksum
		|| !($contents = file_get_contents($location))
		|| !is_serialized($contents)
		|| !($unserialize = unserialize($contents))
		|| empty($unserialize['checksum']) || empty($unserialize['data'])
		|| $unserialize['checksum'] !== md5(serialize($unserialize['data']))
		|| empty($unserialize['data']['class'])
		|| $unserialize['data']['class'] !== $class)
			return false;
		$options = $type == 'skin' ? array('boxes', 'templates', 'packages', 'vars', 'css', 'css_custom') : array();
		$real = array_intersect($options, array_keys($unserialize['data']));
		foreach ($real as $send)
			$data[$send] = $type == 'skin' && in_array($send, array('css', 'css_custom')) ? strip_tags(stripslashes($unserialize['data'][$send])) : stripslashes_deep($unserialize['data'][$send]);
		return $data;
	}

	public function html_options($tags = false, $default = false, $group = false) {
		global $thesis;
		$options['html'] = !empty($tags) && is_array($tags) ? array_filter(array(
			'type' => 'select',
			'label' => $thesis->api->strings['html_tag'],
			'options' => $tags,
			'default' => $default)) : false;
		$options = array_filter(array_merge($options, array(
			'id' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'label' => $thesis->api->strings['html_id'],
				'tooltip' => $thesis->api->strings['id_tooltip']),
			'class' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'label' => $thesis->api->strings['html_class'],
				'tooltip' => $thesis->api->strings['class_tooltip'] . $thesis->api->strings['class_note']))));
		return !empty($group) ? array(
			'html' => array(
				'type' => 'group',
				'label' => sprintf(__('%s Options', 'thesis'), $thesis->api->base['html']),
				'fields' => $options)) : $options;
	}

	private function schema() {
		return array(
			'schema' => __('Schema', 'thesis'),
			'tooltip' => sprintf(__('Enrich your pages by adding a <a href="%s" target="_blank">markup schema</a> that is universally recognized by search engines.', 'thesis'), 'http://schema.org/'),
			'options' => array(
				'' => __('no schema', 'thesis'),
				'article' => __('Article', 'thesis'),
				'creativework' => __('CreativeWork', 'thesis'),
				'recipe' => __('Recipe', 'thesis'),
				'review' => __('Review', 'thesis')),
			'itemtype' => array(
				'article' => 'http://schema.org/Article',
				'creativework' => 'http://schema.org/CreativeWork',
				'recipe' => 'http://schema.org/Recipe',
				'review' => 'http://schema.org/Review'));
	}

	private function strings() {
		global $thesis;
		$this->base = array(
			'html' => '<abbr title="HyperText Markup Language">HTML</abbr>',
			'css' => '<abbr title="Cascading Style Sheet">CSS</abbr>',
			'url' => '<abbr title="Uniform Resource Locator">URL</abbr>',
			'seo' => '<abbr title="Search Engine Optimization">SEO</abbr>',
			'php' => '<abbr title="Recursive acronym for Hypertext Preprocessor">PHP</abbr>',
			'rss' => '<abbr title="Really Simple Syndication">RSS</abbr>',
			'ssl' => '<abbr title="Secure Socket Layer">SSL</abbr>',
			'wp' => '<abbr title="WordPress">WP</abbr>',
			'api' => '<abbr title="Application Programming Interface">API</abbr>',
			'id' => '<code>id</code>',
			'class' => '<code>class</code>');
	 	return array(
			'page' => __('Page', 'thesis'),
			'pages' => __('Pages', 'thesis'),
			'search' => __('Search', 'thesis'),
			'edit' => __('Edit', 'thesis'),
			'name' => __('Name', 'thesis'),
			'email' => __('Email', 'thesis'),
			'website' => __('Website', 'thesis'),
			'required' => __('Required', 'thesis'),
			'comment' => __('Comment', 'thesis'),
			'submit' => __('Submit', 'thesis'),
			'click_to_edit' => __('click to edit', 'thesis'),
			'click_to_read' => __('click to read', 'thesis'),
			'comment_singular' => __('comment', 'thesis'),
			'comment_plural' => __('comments', 'thesis'),
			'comment_permalink' => __('permalink to this comment', 'thesis'), // End front-end strings
			'save' => __('Save', 'thesis'),
			'cancel' => __('Cancel', 'thesis'),
			'delete' => __('Delete', 'thesis'),
			'create' => __('Create', 'thesis'),
			'select' => __('Select', 'thesis'),
			'site' => __('Site', 'thesis'),
			'skin' => __('Skin', 'thesis'),
			'custom' => __('Custom', 'thesis'),
			'editor' => __('Editor', 'thesis'),
			'package' => __('Package', 'thesis'),
			'packages' => __('Packages', 'thesis'),
			'variable' => __('Variable', 'thesis'),
			'variables' => __('Variables', 'thesis'),
			'override' => __('Override', 'thesis'),
			'reference' => __('Reference', 'thesis'),
			'comments' => __('Comments', 'thesis'),
			'home_page' => __('Home Page', 'thesis'),
			'title_tag' => __('Title Tag', 'thesis'),
			'meta_description' => __('Meta Description', 'thesis'),
			'meta_keywords' => __('Meta Keywords', 'thesis'),
			'meta_robots' => __('Meta Robots', 'thesis'),
			'custom_template' => __('Custom Template', 'thesis'),
			'html_head' => sprintf(__('%s Head', 'thesis'), $this->base['html']),
			'title_counter' => __('Search engines allow a maximum of 70 characters for the title.', 'thesis'),
			'description_counter' => __('Search engines allow a maximum of roughly 150 characters for the description.', 'thesis'),
			'html_tag' => sprintf(__('%s Tag', 'thesis'), $this->base['html']),
			'html_id' => sprintf(__('%1$s %2$s', 'thesis'), $this->base['html'], $this->base['id']),
			'html_class' => sprintf(__('%1$s %2$s', 'thesis'), $this->base['html'], $this->base['class']),
			'id_tooltip' => sprintf(__('If you need to target this box individually with %1$s or JavaScript, you can enter an %2$s here.<br /><br /><strong>Note:</strong> %2$ss cannot begin with numbers, and only one %2$s is valid per box!', 'thesis'), $this->base['css'], $this->base['id']),
			'class_tooltip' => sprintf(__('If you want to target this box with %1$s or JavaScript, you should enter a %2$s name here.', 'thesis'), $this->base['css'], $this->base['class']),
			'class_note' => sprintf(__('<br /><br /><strong>Note:</strong> %1$s names cannot begin with numbers! Separate multiple %1$ses with spaces.', 'thesis'), $this->base['class']),
			'hook_label' => __('Unique Hook Name', 'thesis'),
			'hook_tooltip_1' => __('If you want to access this box programmatically, you should supply a unique hook name here. Your hook references will then become:', 'thesis'),
			'hook_tooltip_2' => __('&hellip;where <code>{name}</code> is equal to the value you enter here.', 'thesis'),
			'posts_to_show' => __('Number of Posts to Show', 'thesis'),
			'avatar_size' => __('Avatar Size', 'thesis'),
			'comment_term_singular' => __('Comment Term Singular', 'thesis'),
			'comment_term_plural' => __('Comment Term Plural', 'thesis'),
			'character_separator' => __('Character Separator', 'thesis'),
			'alt_tooltip' => sprintf(__('Adding <code>alt</code> text will help you derive the maximum %s benefit from your image. Be concise and descriptive!', 'thesis'), $this->base['seo']),
			'caption_tooltip' => __('After headlines, sub-headings and image captions are the most commonly read items on web pages. Don&#8217;t miss this opportunity to engage your readers&#8212;add a caption to your image!', 'thesis'),
			'frame_label' => __('Frame This Image?', 'thesis'),
			'frame_tooltip' => sprintf(__('If you set this option to true, then an %s class of <code>frame</code> will be added to your image. Please note that your active skin may not support image framing.', 'thesis'), $this->base['html']),
			'frame_option' => __('add a frame to this image', 'thesis'),
			'alignment' => __('Alignment', 'thesis'),
			'alignment_tooltip' => sprintf(__('If you select an alignment, a corresponding %1$s %2$s will be added to your image. Please note that your active skin may not support image alignment.', 'thesis'), $this->base['html'], $this->base['class']),
			'alignleft' => __('left with text wrap', 'thesis'),
			'alignright' => __('right with text wrap', 'thesis'),
			'aligncenter' => __('centered (no wrap)', 'thesis'),
			'alignnone' => __('left with no text wrap', 'thesis'),
			'skin_default' => __('use skin default (recommended)', 'thesis'),
			'display_options' => __('Display Options', 'thesis'),
			'date_tooltip' => sprintf(__('This field accepts a <a href="%1$s" target="_blank">%2$s date format</a>.', 'thesis'), esc_url('http://us.php.net/manual/en/function.date.php'), $this->base['php']),
			'show_label' => __('show input label', 'thesis'),
			'placeholder' => __('Placeholder Text', 'thesis'),
			'placeholder_tooltip' => sprintf(__('By providing %s5 placeholder text, you can give users an example of the info they should enter into this form field.', 'thesis'), $this->base['html']),
			'submit_button_text' => __('Submit Button Text', 'thesis'),
			'intro_text' => __('Intro Text', 'thesis'),
			'link_text' => __('Link Text', 'thesis'),
			'use_post_title' => __('use post title (recommended)', 'thesis'),
			'use_custom_text' => __('use custom text', 'thesis'),
			'custom_link_text' => __('Custom Link Text', 'thesis'),
			'no_html' => sprintf(__('no %s tags allowed', 'thesis'), $this->base['html']),
			'include_http' => __('(including <code>http://</code> or <code>https://</code>)', 'thesis'),
			'this_page' => __('this page', 'thesis'),
			'not_recommended' => __('(not recommended)', 'thesis'),
			'tracking_scripts' => __('Tracking Scripts', 'thesis'),
			'saved' => __('saved', 'thesis'),
			'not_saved' => __('not saved', 'thesis'),
			'auto_wp_label' => __('Automatic WordPress Post Classes', 'thesis'),
			'auto_wp_tooltip' => __('WordPress can output post classes that allow you to target specific types of posts more easily. Target by post type, category, tag, taxonomy, author, and more.', 'thesis'),
			'auto_wp_option' => __('Use automatically-generated WordPress post classes', 'thesis'));
	}
}