<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_form_api {
	public function fields($fields, $values = array(), $id_prefix = false, $name_prefix = false, $tabindex = 1, $depth = 0) {
		if (!is_array($fields)) return;
		$form_fields = array('output' => '');
		foreach ($fields as $id => $field) {
			$field['id'] = $id;
			$form_field = $field['type'] == 'group' ?
				$this->field_group($field, $values, $id_prefix, $name_prefix, $tabindex, $depth) :
				$this->field($field, (!empty($id_prefix) ? $id_prefix . $id : $id), (!empty($name_prefix) ? "{$name_prefix}[{$id}]" : $id), (!empty($values[$id]) ? $values[$id] : false), $tabindex, $depth);
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
			"\t<label>{$field['label']} <span class=\"toggle_group\" title=\"" . __('show/hide options', 'thesis') . "\">&nbsp;</span></label>\n".
			"\t<div class=\"group_fields\">\n" : '').
			$group['output'].
			(!empty($field['label']) ?
			"\t</div>\n" : '').
			"</div>\n";
		return $group;
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
		# wrapper
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
		# field
		$tooltip = !empty($field['tooltip']) ? "<p class=\"tooltip\">{$field['tooltip']}</p>\n" : false;
		$label = (!empty($field['req']) ? " <span class=\"required\" title=\"" . __('required', 'thesis') . "\">{$field['req']}</span>" : '') . (!!$tooltip ? ' <span class="toggle_tooltip">[?]</span>' : '');
		$placeholder = !empty($field['placeholder']) ? ' placeholder="' . esc_attr($field['placeholder']) . '"' : '';
		$value = !!$value || !!strlen($value) ? $value : (!empty($field['default']) ? $field['default'] : false);
		if ($field['type'] == 'checkbox')
			$classes['checkbox'] = 'checkboxes';
		elseif ($field['type'] == 'radio')
			$classes['radio'] = 'radio';
		if (!empty($field['multiple']))
			$classes['multiple'] = 'select_multiple';
		$classes = !empty($classes) ? ' class="' . implode(' ', $classes) . '"' : '';
		if ($field['type'] == 'text') {
			$class = (!empty($field['width']) ? " {$field['width']}" : '') . (!empty($field['counter']) ? ' count_field' : '') . (!empty($field['code']) ? ' code_input' : '');
			$output =
				"$tab\t<p$classes>\n".
				"$tab\t\t<label for=\"$id\">{$field['label']}$label</label>\n".
				"$tab\t\t<input type=\"text\" class=\"text_input$class\" id=\"$id\" name=\"$name\" value=\"" . esc_attr(stripslashes($value)) . "\"$placeholder tabindex=\"$tabindex\" />\n".
				(!empty($field['counter']) ?
				"$tab\t\t<input type=\"text\" readonly=\"readonly\" class=\"counter\" size=\"2\" maxlength=\"3\" value=\"0\">\n".
				"$tab\t\t<label class=\"counter_label\">{$field['counter']}</label>\n" : '').
				(!empty($field['description']) ?
				"$tab\t\t<span class=\"input_description\">{$field['description']}</span>\n" : '').
				"$tab\t</p>\n";
		}
		elseif ($field['type'] == 'color') {
			$output =
				"$tab\t<p$classes>\n".
				"$tab\t\t<label for=\"$id\">{$field['label']}$label</label>\n".
				"$tab\t\t<input type=\"text\" class=\"text_input short color {required:false,adjust:false,pickerPosition:'right'}\" id=\"$id\" name=\"$name\" value=\"" . esc_attr(stripslashes($value)) . "\"$placeholder tabindex=\"$tabindex\" />\n".
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
				"$tab\t\t<textarea id=\"$id\"$class name=\"$name\"" . (!empty($field['rows']) && is_numeric($field['rows']) ? " rows=\"{$field['rows']}\"" : '') . " tabindex=\"$tabindex\">" . stripslashes($value) . "</textarea>\n".
				(!empty($field['counter']) ?
				"$tab\t\t<input type=\"text\" readonly=\"readonly\" class=\"counter\" size=\"2\" maxlength=\"3\" value=\"0\">\n".
				"$tab\t\t<label class=\"counter_label\">{$field['counter']}</label>\n" : '').
				(!empty($field['description']) ?
				"$tab\t\t<span class=\"input_description\">{$field['description']}</span>\n" : '').
				"$tab\t</p>\n";
		}
		elseif ($field['type'] == 'image_upload') {
			$output =
				"$tab\t<p>\n\t\t". 
				thesis_api::uploader($field['prefix'], $depth) .
				"$tab\t\t<input type=\"hidden\" id=\"{$id}_url\" name=\"{$name}[url]\" value=\"\" />\n".
				"$tab\t\t<input type=\"hidden\" id=\"{$id}_height\" name=\"{$name}[height]\" value=\"\" />\n".
				"$tab\t\t<input type=\"hidden\" id=\"{$id}_width\" name=\"{$name}[width]\" value=\"\" />\n".
				"$tab\t</p>\n";
		}
		elseif ($field['type'] == 'image') {
			$image_url = is_array($value) ? esc_url(stripslashes($value['url'])) : false;
			$upload_label = !empty($field['upload_label']) ? $field['upload_label'] : __('Upload an Image', 'thesis');
			$output =
				"$tab\t<p class=\"upload_field\">\n".
				"$tab\t\t<label class=\"upload_label\" for=\"{$id}_file\">$upload_label$label</label>\n".
				"$tab\t\t<input type=\"file\" class=\"upload\" id=\"{$id}_file\" name=\"$id\" tabindex=\"$tabindex\" />\n".
				"$tab\t\t" . wp_nonce_field("thesis-image-$id", "_wpnonce-thesis-image-$id", true, false) . "\n".
				"$tab\t</p>\n".
				($image_url ?
				"$tab\t<p class=\"current_image\">\n".
				"$tab\t\t<img src=\"$image_url\" alt=\"" . __('Uploaded image', 'thesis') . "\" title=\"\" />\n".
				"$tab\t\t<span>" . __('<strong>Note:</strong> Image shown here may not be to scale (limited to 460px wide)', 'thesis') . "</span>\n".
				"$tab\t</p>\n" : '').
				"$tab\t<p$classes>\n".
				"$tab\t\t<label for=\"$id\">{$field['label']}</label>\n".
				"$tab\t\t<input type=\"text\" class=\"text_input full\" id=\"$id\" name=\"{$name}[url]\" value=\"$image_url\"$placeholder tabindex=\"" . ($tabindex + 1) . "\" />\n".
				"$tab\t</p>\n";
			$tabindex++;
		}
		elseif (is_array($field['options'])) {
			$items = '';
			if ($field['type'] == 'checkbox') {
				$value = is_array($value) ? $value : array();
				$field['default'] = !empty($field['default']) && is_array($field['default']) ? $field['default'] : array();
				foreach ($field['options'] as $option => $option_label) {
					$control = !empty($field['dependents'][$option]) && !is_array($field['dependents'][$option]) ? " class=\"control\" title=\"{$field['id']}_$option\"" : '';
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
					$control = !empty($field['dependents']) && !empty($field['dependents'][$option_value]) && !is_array($field['dependents'][$option_value]) ? " class=\"control\" title=\"{$field['id']}_$option_value\"" : '';
					$checked = isset($value) ? ($value == $option_value ? ' checked="checked"' : '') : (!empty($field['default']) && $option_value == $field['default'] ? ' checked="checked"' : '');
					$items .=
						"$tab\t\t<li>\n".
						"$tab\t\t\t<input type=\"radio\" id=\"{$id}_{$option_value}\"$control name=\"$name\" value=\"$option_value\"$checked tabindex=\"$tabindex\" />\n".
						"$tab\t\t\t<label for=\"{$id}_{$option_value}\">$option_label</label>\n".
						"$tab\t\t</li>\n";
				}
				$output = (!empty($field['label']) ?
					"$tab\t<label class=\"list_label\">{$field['label']}$label</label>\n" : '').
					"$tab\t<ul$classes title=\"{$field['id']}\">\n".
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
				$title = !empty($field['dependents']) && is_array($field['dependents']) ? " title=\"{$field['id']}\"" : '';
				foreach ($field['options'] as $option_value => $option_text) {
					$control = !empty($field['dependents']) && !empty($field['dependents'][$option_value]) && !is_array($field['dependents'][$option_value]) ? " class=\"control\" title=\"{$field['id']}_$option_value\"" : '';
					$selected = $value == $option_value || (!isset($value) && $option_value == $field['default']) ? ' selected="selected"' : '';
					$items .=
						"$tab\t\t\t<option$control value=\"$option_value\"$selected>$option_text</option>\n";
				}
				$output =
					"$tab\t<p>\n".
					(!empty($field['label']) ?
					"$tab\t\t<label>{$field['label']}$label</label>\n" : '').
					"$tab\t\t<select id=\"$id\"$classes name=\"$name\"$title size=\"1\"$multiple tabindex=\"$tabindex\">\n".
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
}