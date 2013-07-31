<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_post_meta {
	private $id;			// (string) unique reference for the current post meta box (usually a class extension name)
	private $post_meta;		// (array) title and fields that define this post meta box
	private $tabindex;		// (int) tabindex to use for the current post meta box
	private $saved = false;

	public function __construct($id, $options, $tabindex) {
		if (!is_admin() || !is_array($options)) return;
		$this->id = $id;
		$this->post_meta = $options;
		$this->post_meta['exclude_from'] = !empty($this->post_meta['exclude_from']) && is_array($this->post_meta['exclude_from']) ? $this->post_meta['exclude_from'] : array();
		$this->tabindex = $tabindex;
		add_action('admin_menu', array($this, 'add')); #wp
		add_action('save_post', array($this, 'save')); #wp
	}

	public function add() {
		foreach (get_post_types() as $post_type) #wp
			if (!in_array($post_type, $this->post_meta['exclude_from'])) {
				$context = !empty($this->post_meta['context']) ? $this->post_meta['context'] : 'normal';
				$priority = !empty($this->post_meta['priority']) ? $this->post_meta['priority'] : 'high';
				add_meta_box($this->id, $this->post_meta['title'], array($this, 'form'), $post_type, $context, $priority); #wp
			}
	}

	public function form() {
		global $thesis, $post; #wp
		$fields = $thesis->api->form->fields($this->post_meta['fields'], get_post_meta($post->ID, "_{$this->id}", true), "{$this->id}_", "{$this->id}", $this->tabindex, 1);
		$this->tabindex = $fields['tabindex'];
		echo
			"<div class=\"t_meta\">\n".
			$fields['output'].
			"\t<input type=\"hidden\" name=\"{$this->id}_noncename\" id=\"{$this->id}_noncename\" value=\"" . wp_create_nonce(plugin_basename(__FILE__)) . "\" />\n".
			"</div>\n";
	}

	public function save() {
		global $thesis, $post; #wp
		if ($this->saved || !wp_verify_nonce((!empty($_POST[$this->id . '_noncename']) ? $_POST[$this->id . '_noncename'] : false), plugin_basename(__FILE__))) return; #wp
		if ($_POST['post_type'] == 'page') {
			if (!current_user_can('edit_page', $post->ID)) return; #wp
		}
		else
			if (!current_user_can('edit_post', $post->ID)) return; #wp
		if (wp_is_post_revision($post->ID)) return; #wp
		if (is_array($this->post_meta['fields']))
			foreach ($this->post_meta['fields'] as $id => $option) {
				if (is_array($option)) {
					if ($option['type'] === 'image') {
						$sent_url = !empty($_POST[$this->id][$id]['url']) ? trim($_POST[$this->id][$id]['url']) : false;
						$old_url = get_post_meta($post->ID, "_{$this->id}", true);
						if (!empty($sent_url) && !empty($old_url['image']['url']) && (trim($old_url['image']['url']) == $sent_url))
							$_POST[$this->id][$id] = $old_url['image'];
					}
					$this->post_meta['fields'][$id]['default'] = apply_filters("thesis_post_meta_{$this->id}_$id",(!empty($option['default']) ? $option['default'] : ''));
				}
			}
		if (is_array($save = $thesis->api->set_options($this->post_meta['fields'], $_POST[$this->id], "{$this->id}_", 'default', $post->ID)))
			update_post_meta($post->ID, "_{$this->id}", $save); #wp
		else
			delete_post_meta($post->ID, "_{$this->id}"); #wp
		$this->saved = true;
	}
}