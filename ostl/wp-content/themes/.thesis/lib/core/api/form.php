<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_form_api {
	public function fields($fields, $values = array(), $id_prefix = false, $name_prefix = false, $tabindex = 1, $depth = 0) {
		if (!is_array($fields)) return;
		$form_fields = array('output' => '');
		foreach ($fields as $id => $field) {
			$field['id'] = $id;
			$form_field = $field['type'] == 'group' ?
				$this->field_group($field, $values, $id_prefix, $name_prefix, $tabindex, $depth) : ($field['type'] == 'object_set' ?
				$this->field_object_set($field, $values, $id_prefix, $name_prefix, $tabindex, $depth) : ($field['type'] == 'custom' && !empty($field['output']) ?
				$this->field_custom($field, $tabindex) :
				$this->field($field, (!empty($id_prefix) ? $id_prefix. $id : $id), (!empty($name_prefix) ? "{$name_prefix}[{$id}]" : $id), (!empty($values[$id]) ? $values[$id] : false), $tabindex, $depth)));
			$form_fields['output'] .= !empty($form_field['output']) ? $form_field['output'] : '';
			$form_fields['tabindex'] = $form_field['tabindex'];
		}
		return $form_fields;
	}

	public function field_group($field, $values = array(), $id_prefix = false, $name_prefix = false, $tabindex = 1, $depth = 0) {
		if (!is_array($field['fields'])) return;
		$class = 'option_item option_group' . (!empty($field['parent']) ? $this->field_parent($field['parent']) : '');
		$group = $this->fields($field['fields'], $values, $id_prefix, $name_prefix, $tabindex, $depth);
		$group['output'] =
			"<div class=\"$class\">\n".
			(!empty($field['label']) ?
			"\t<label>{$field['label']} <span class=\"toggle_group\" data-style=\"toggle\" title=\"" . __('show/hide options', 'thesis') . "\">&#8862;</span></label>\n".
			"\t<div class=\"group_fields\">\n" : '').
			$group['output'].
			(!empty($field['label']) ?
			"\t</div>\n" : '').
			"</div>\n";
		return $group;
	}

	public function field_object_set($field, $values = array(), $id_prefix = false, $name_prefix = false, $tabindex = 1, $depth = 0) {
		if (!is_array($field['objects'])) return;
		$tab = str_repeat("\t", $depth);
		$class = 'option_item object_set'. (!empty($field['parent']) ? ' '. $this->field_parent($field['parent']) : '');
		$options = $objects = $active = $tips = array();
		$list = $fields = $select = '';
		foreach ($field['objects'] as $id => $object)
			if (is_array($object) && is_array($object['fields'])) {
				$objects[$id] = $this->object(array_merge($object, array('id' => $id)), isset($values[$id]) ? $values[$id] : array(), $id_prefix, $name_prefix, $tabindex, $depth + 2);
				if (empty($objects[$id]['active']))
					$options[$id] = $object['label'];
				else {
					$active[$id] = $object['label'];
					$tips[$id] = $objects[$id]['active'];
				}
				$fields .= $this->popup(array(
					'id' => $id,
					'name' => $object['label'],
					'depth' => $depth + 1,
					'body' => $objects[$id]['fields']));
			}
		if (!empty($field['sort']))
			natcasesort($options);
		foreach (array_merge(array('' => !empty($field['select']) ? $field['select'] : __('Select a new object to edit:', 'thesis')), $options) as $id => $label)
			$select .= "$tab\t\t\t\t<option value=\"$id\">$label</option>\n";
		if (!empty($field['sort']))
			natcasesort($active);
		foreach ($active as $id => $label)
			$list .= "$tab\t\t<li class=\"option_object\" id=\"object_$id\" data-id=\"$id\" data-tooltip=\"". esc_attr($tips[$id]). "\" title=\"". __('click to edit', 'thesis'). "\">$label</li>\n";
		return array(
			'output' =>
				"$tab<div class=\"$class\">\n".
				"$tab\t<div class=\"option_field\">\n".
				(!empty($field['label']) ?
				"$tab\t\t<label for=\"object_select_{$field['id']}\">{$field['label']}</label>\n" : '').
				"$tab\t\t<p>\n".
				"$tab\t\t\t<select id=\"object_select_{$field['id']}\" class=\"object_select\" size=\"1\" tabindex=\"\">\n".
				$select.
				"$tab\t\t\t</select>\n".
				"$tab\t\t</p>\n".
				"$tab\t</div>\n".
				"$tab\t<ul class=\"object_list\">\n".
				$list.
				"$tab\t</ul>\n".
				$fields.
				"$tab</div>\n",
			'tabindex' => $tabindex);
	}

	public function object($object, $values = array(), $id_prefix = false, $name_prefix = false, $tabindex = 1, $depth = 0) {
		if (!is_array($object['fields'])) return;
		$active = array();
		$fields = $this->fields($object['fields'], $values, "$id_prefix{$object['id']}_", "{$name_prefix}[{$object['id']}]", $tabindex, $depth);
		foreach ($object['fields'] as $id => $field)
			if ($field['type'] == 'checkbox') {
				if (is_array($field['options']))
					foreach ($field['options'] as $option => $label)
						if (!empty($values[$id][$option]) || (!isset($values[$id][$option]) && !empty($field['default'][$option])))
							$active[] = esc_attr($label);
			}
			elseif ($field['type'] == 'radio' || $field['type'] == 'select') {
				if (is_array($field['options']))
					foreach ($field['options'] as $value => $label)
						if ((!empty($values[$id]) && $values[$id] == $value)
						|| ($field['type'] == 'radio' && !isset($values[$id]) && empty($value) && empty($field['default']))
						|| (!empty($field['default']) && !isset($values[$id])))
							$active[] = (!empty($field['label']) ? "{$field['label']}: " : ''). $label;
			}
			elseif (!empty($values[$id]))
				$active[] = esc_attr($field['label']). ": ". esc_attr($values[$id]);
			elseif (!empty($field['default']))
				$active[] = esc_attr($field['label']). ": ". esc_attr($field['default']);
		return array(
			'fields' => $fields['output'],
			'active' => !empty($active) ? implode(", ", $active) : false,
			'tabindex' => $fields['tabindex'] + 1);
	}

	public function field_custom($field, $tabindex) {
		if (empty($field['output'])) return;
		$class = 'option_item option_custom'. (!empty($field['parent']) ? $this->field_parent($field['parent']) : '');
		return array(
			'output' =>
				"<div class=\"$class\">\n".
				(!empty($field['label']) ?
				"\t<label>{$field['label']}</label>\n" : '').
				$field['output'].
				"</div>\n",
			'tabindex' => $tabindex + 30);
	}

	public function field_parent($parent) {
		if (!is_array($parent)) return;
		$dependent = ' dependent';
		foreach ($parent as $option => $value) {
			$dependent .= " dependent_$option";
			if (is_array($value))
				foreach ($value as $dependent_value)
					$dependent .= " dependent_{$option}_$dependent_value";
			else
				$dependent .= " dependent_{$option}_$value";
		}
		return $dependent;
	}

	public function field($field, $id, $name, $value = false, $tabindex = 1, $depth = 0) {
		if (!(is_array($field) && $id && $name)) return;
		$tab = str_repeat("\t", $depth);
		$wrapper = $classes = $form_field = array();
		$output = '';
		$wrapper['field'] = 'option_item option_field';
		if (!empty($field['dependents']) && is_array($field['dependents']))
			$wrapper['group'] = 'control_group';
		if (!empty($field['parent']) && $field['parent'])
			$wrapper['dependent'] = trim($this->field_parent($field['parent']));
		if (!empty($field['stack']) && $field['stack'])
			$wrapper['stack'] = 'stack';
		if (!empty($field['clear']) && $field['clear'])
			$wrapper['clear'] = 'clear_stack';
		$wrapper = !empty($wrapper) ? ' class="' . implode(' ', $wrapper) . '"' : '';
		$tooltip = !empty($field['tooltip']) ? "<p class=\"tooltip\">{$field['tooltip']}</p>\n" : false;
		$label = (!empty($field['req']) ? " <span class=\"required\" title=\"". __('required', 'thesis'). "\">{$field['req']}</span>" : '') . (!!$tooltip ? ' <span class="toggle_tooltip">&#59140;</span>' : '');
		$placeholder = !empty($field['placeholder']) ? ' placeholder="'. esc_attr($field['placeholder']). '"' : '';
		$value = !!$value || !!strlen($value) ? $value : (!empty($field['default']) ? $field['default'] : false);
		if ($field['type'] == 'checkbox')
			$classes['checkbox'] = 'checkboxes';
		elseif ($field['type'] == 'radio')
			$classes['radio'] = 'radio';
		if (!empty($field['multiple']))
			$classes['multiple'] = 'select_multiple';
		$classes = !empty($classes) ? ' class="' . implode(' ', $classes) . '"' : '';
		if ($field['type'] == 'text') {
			$class = (!empty($field['width']) ? " {$field['width']}" : ''). (!empty($field['counter']) ? ' count_field' : ''). (!empty($field['code']) ? ' code_input' : '');
			$output =
				"$tab\t<p$classes>\n".
				"$tab\t\t<label for=\"$id\">{$field['label']}$label</label>\n".
				"$tab\t\t<input type=\"text\" class=\"text_input$class\" id=\"$id\" name=\"$name\" value=\"". esc_attr(stripslashes($value)). "\"$placeholder tabindex=\"$tabindex\" />\n".
				(!empty($field['counter']) ?
				"$tab\t\t<input type=\"text\" readonly=\"readonly\" class=\"counter\" size=\"2\" maxlength=\"3\" value=\"0\">\n".
				"$tab\t\t<label class=\"counter_label\">{$field['counter']}</label>\n" : (!empty($field['description']) ?
				"$tab\t\t<span class=\"input_description\">{$field['description']}</span>\n" : '')).
				"$tab\t</p>\n";
		}
		elseif ($field['type'] == 'color') {
			$output =
				"$tab\t<p$classes>\n".
				"$tab\t\t<label for=\"$id\">{$field['label']}$label</label>\n".
				"$tab\t\t<input type=\"text\" class=\"text_input short color {required:false,adjust:false,pickerPosition:'right'}\" id=\"$id\" name=\"$name\" value=\"". esc_attr(stripslashes($value)). "\"$placeholder tabindex=\"$tabindex\" />\n".
				(!empty($field['description']) ?
				"$tab\t\t<span class=\"input_description\">{$field['description']}</span>\n" : '').
				"$tab\t</p>\n";
		}
		elseif ($field['type'] == 'textarea') {
			$class = array();
			if (!empty($field['counter']))
				$class['counter'] = 'count_field';
			if (!empty($field['code']))
				$class['code'] = 'code_input';
			$class = !empty($class) ? ' class="' . implode(' ', $class) . '"' : '';
			$output =
				"$tab\t<p$classes>\n".
				"$tab\t\t<label for=\"$id\">{$field['label']}$label</label>\n".
				"$tab\t\t<textarea id=\"$id\"$class name=\"$name\"". (!empty($field['rows']) && is_numeric($field['rows']) ? " rows=\"{$field['rows']}\"" : ''). " tabindex=\"$tabindex\">". htmlspecialchars(stripslashes($value)). "</textarea>\n".
				(!empty($field['counter']) ?
				"$tab\t\t<input type=\"text\" readonly=\"readonly\" class=\"counter\" size=\"2\" maxlength=\"3\" value=\"0\">\n".
				"$tab\t\t<label class=\"counter_label\">{$field['counter']}</label>\n" : (!empty($field['description']) ?
				"$tab\t\t<span class=\"input_description\">{$field['description']}</span>\n" : '')).
				"$tab\t</p>\n";
		}
		elseif ($field['type'] == 'image_upload') {
			$output =
				"$tab\t<p>\n".
				"$tab\t\t". thesis_api::uploader($field['prefix'], $depth).
				"$tab\t\t<input type=\"hidden\" id=\"{$id}_url\" name=\"{$name}[url]\" value=\"\" />\n".
				"$tab\t\t<input type=\"hidden\" id=\"{$id}_height\" name=\"{$name}[height]\" value=\"\" />\n".
				"$tab\t\t<input type=\"hidden\" id=\"{$id}_width\" name=\"{$name}[width]\" value=\"\" />\n".
				"$tab\t</p>\n";
		}
		elseif ($field['type'] == 'image') {
			$image_url = is_array($value) ? esc_url(stripslashes($value['url'])) : false;
			$upload_label = !empty($field['upload_label']) ? $field['upload_label'] : __('Upload an Image', 'thesis');
			$input_only = !empty($field['input_only']) ? true : false;
			$output =
				"$tab\t<p class=\"upload_field\">\n".
				"$tab\t\t<label class=\"upload_label\" for=\"{$id}_file\">$upload_label$label</label>\n".
				"$tab\t\t<input type=\"file\" class=\"upload\" data-style=\"input\" id=\"{$id}_file\" name=\"$id\" tabindex=\"$tabindex\" />\n".
				"$tab\t\t". wp_nonce_field("thesis-image-$id", "_wpnonce-thesis-image-$id", true, false). "\n".
				"$tab\t</p>\n".
				(!$input_only ? 
					($image_url ?
					"$tab\t<p class=\"current_image\">\n".
					"$tab\t\t<img src=\"$image_url\" alt=\"". __('Uploaded image', 'thesis'). "\" title=\"\" />\n".
					"$tab\t\t<span>". __('<strong>Note:</strong> Image shown here may not be to scale (limited to 460px wide)', 'thesis'). "</span>\n".
					"$tab\t</p>\n" : '').
					"$tab\t<p$classes>\n".
					"$tab\t\t<label for=\"$id\">{$field['label']}</label>\n".
					"$tab\t\t<input type=\"text\" class=\"text_input full\" id=\"$id\" name=\"{$name}[url]\" value=\"$image_url\"$placeholder tabindex=\"". ($tabindex + 1). "\" />\n".
					"$tab\t</p>\n" : '');
			$tabindex++;
		}
		elseif ($field['type'] == 'add_media') {
			$url = is_array($value) && !empty($value['url']) ? esc_url($value['url']) : '';
			$height = is_array($value) && !empty($value['height']) ? absint($value['height']) : '';
			$width = is_array($value) && !empty($value['width']) ? absint($value['width']) : '';
			$attachment_id = is_array($value) && !empty($value['id']) ? absint($value['id']) : '';
			$upload_label = !empty($field['upload_label']) ? $field['upload_label'] : __('Upload an Image', 'thesis');
			$verb = !empty($field['verb']) ? esc_attr($field['verb']) : __('Add Image', 'thesis');
			$output =
				"$tab\t<p class=\"upload_field\">\n".
				"$tab\t\t<label class=\"upload_label\">$upload_label$label</label>\n".
				"$tab\t\t<a href=\"#\" class=\"t_media_upload button\"><span class=\"wp-media-buttons-icon\"></span>$verb</a>\n".
				"$tab\t</p>\n".
				"$tab\t<p class=\"current_image\"". (empty($url) ? ' style="display: none;"' : ''). ">\n".
				"$tab\t\t<img src=\"$url\" alt=\"". __('Uploaded image', 'thesis'). "\" title=\"\" />\n".
				"$tab\t\t<span>". __('<strong>Note:</strong> Image shown here may not be to scale (limited to 460px wide)', 'thesis'). "</span>\n".
				"$tab\t</p>\n".
				"$tab\t<p class=\"upload_field t_add_media\">\n".
				"$tab\t\t<label for=\"$id\">{$field['label']}</label>\n".
				"$tab\t\t<input type=\"text\" name=\"{$name}[url]\" tabindex=\"$tabindex\" value=\"$url\" class=\"text_input full\"/>\n".
				"$tab\t\t<input type=\"hidden\" name=\"{$name}[height]\" value=\"$height\" />\n".
				"$tab\t\t<input type=\"hidden\" name=\"{$name}[width]\" value=\"$width\" />\n".
				"$tab\t\t<input type=\"hidden\" name=\"{$name}[id]\" value=\"$attachment_id\" />\n".
				"$tab\t</p>";
		}
		elseif (is_array($field['options'])) {
			$items = '';
			if ($field['type'] == 'checkbox') {
				$value = is_array($value) ? $value : array();
				$field['default'] = !empty($field['default']) && is_array($field['default']) ? $field['default'] : array();
				foreach ($field['options'] as $option => $option_label) {
					$control = !empty($field['dependents']) && in_array($option, $field['dependents']) ?
						" class=\"control\" data-id=\"{$field['id']}_$option\"" : '';
					$checked = !empty($value[$option]) || (!isset($value[$option]) && !empty($field['default'][$option])) ? ' checked="checked"' : '';
					$items .=
						"$tab\t\t<li>\n".
						"$tab\t\t\t<input type=\"hidden\" name=\"{$name}[$option]\" value=\"0\" />\n".
						"$tab\t\t\t<input type=\"checkbox\" id=\"{$id}_{$option}\"$control name=\"{$name}[$option]\" value=\"1\"$checked tabindex=\"$tabindex\" />\n".
						"$tab\t\t\t<label for=\"{$id}_{$option}\">$option_label</label>\n".
						"$tab\t\t</li>\n";
				}
				$output = (!empty($field['label']) ?
					"$tab\t<label class=\"list_label\">{$field['label']}$label</label>\n" : '').
					"$tab\t<ul$classes>\n".
					$items.
					"$tab\t</ul>\n";
			}
			elseif ($field['type'] == 'radio') {
				foreach ($field['options'] as $option_value => $option_label) {
					$control = !empty($field['dependents']) && in_array($option_value, $field['dependents']) ?
						" class=\"control\" title=\"{$field['id']}_$option_value\"" : '';
					$checked = isset($value) ? ($value == $option_value ? ' checked="checked"' : '') : (!empty($field['default']) && $option_value == $field['default'] ? ' checked="checked"' : '');
					$items .=
						"$tab\t\t<li>\n".
						"$tab\t\t\t<input type=\"radio\" id=\"{$id}_{$option_value}\"$control name=\"$name\" value=\"$option_value\"$checked tabindex=\"$tabindex\" />\n".
						"$tab\t\t\t<label for=\"{$id}_{$option_value}\">$option_label</label>\n".
						"$tab\t\t</li>\n";
				}
				$output = (!empty($field['label']) ?
					"$tab\t<label class=\"list_label\">{$field['label']}$label</label>\n" : '').
					"$tab\t<ul$classes data-id=\"{$field['id']}\">\n".
					$items.
					"$tab\t</ul>\n";
			}
			elseif ($field['type'] == 'select') {
				$multiple = '';
				if (!empty($field['multiple'])) {
					$multiple = ' multiple="multiple"';
					$value = is_array($value) ? $value : array();
					$field['default'] = is_array($field['default']) ? $field['default'] : array();
				}
				foreach ($field['options'] as $option_value => $option_text) {
					$control = !empty($field['dependents']) && in_array($option_value, $field['dependents']) ?
						" class=\"control\" data-id=\"{$field['id']}_$option_value\"" : '';
					$selected = $value == $option_value || (!isset($value) && $option_value == $field['default']) ? ' selected="selected"' : '';
					$items .=
						"$tab\t\t\t<option$control value=\"$option_value\"$selected>$option_text</option>\n";
				}
				$output =
					"$tab\t<p>\n".
					(!empty($field['label']) ?
					"$tab\t\t<label for=\"$id\">{$field['label']}$label</label>\n" : '').
					"$tab\t\t<select id=\"$id\"$classes name=\"$name\" data-id=\"{$field['id']}\" size=\"1\"$multiple tabindex=\"$tabindex\">\n".
					$items.
					"$tab\t\t</select>\n".
					"$tab\t</p>\n";
			}
		}
		$form_field['tabindex'] = $tabindex++;
		$form_field['output'] =
			"$tab<div$wrapper>\n".
			$output.
			$tooltip.
			"$tab</div>\n";
		return $form_field;
	}

	public function popup($args = array()) {
		$id = $name = $body = '';
		$depth = 0;
		extract($args); // array('id' (string), 'name' (string), 'body' (string), 'depth' (int))
		$tab = str_repeat("\t", $depth);
		$name = trim($name);
		return
			"$tab<div id=\"popup_$id\" class=\"popup\">\n".
			"$tab\t<div class=\"popup_html\" data-style=\"box\">\n".
			"$tab\t\t<span class=\"popup_ok\" data-style=\"button action\" title=\"". __('click to close', 'thesis'). "\">OK</span>\n".
			"$tab\t\t<h4 class=\"popup_name\">$name</h4>\n".
			$body.
			"$tab\t</div>\n".
			"$tab</div>\n";
	}

/*	public function field_object($field, $values = array(), $id_prefix = false, $name_prefix = false, $tabindex = 1, $depth = 0) {
		if (!is_array($field['fields'])) return;
		$tab = str_repeat("\t", $depth);
		$object = $this->object($field, $values, $id_prefix, $name_prefix, $tabindex, $depth + 2);
		return array(
			'output' =>
				"$tab<div class=\"option_item". (!empty($field['parent']) ? ' '. $this->field_parent($field['parent']) : ''). "\">\n".
				"$tab\t<span class=\"option_object\" id=\"object_{$field['id']}\" data-id=\"{$field['id']}\" data-tooltip=\"". esc_attr(!empty($object['active']) ? $object['active'] : ''). "\" title=\"". __('click to edit', 'thesis'). "\">{$field['label']}</span>\n".
				$this->popup(array(
					'id' => $field['id'],
					'name' => $field['label'],
					'depth' => $depth + 1,
					'body' => $object['fields'])).
				"$tab</div>\n",
			'tabindex' => $object['tabindex']);
	}*/
}