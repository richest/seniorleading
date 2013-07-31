<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
/**
 * class thesis_terms (formerly thesis_term_options)
 *
 * @package Thesis
 * @since 1.8
 */
class thesis_terms {
	public $terms = array();		// (array) all saved term data
	private $options = array();		// (array) term options

	public function __construct() {
		global $thesis;
		$this->terms = is_array($terms = $thesis->api->get_option('thesis_terms')) ? $terms : $this->terms;
		if (!is_admin()) return;
		add_action('init', array($this, 'init_options'), 11);
		add_action('create_term', array($this, 'create_term')); #wp
		add_action('edit_terms', array($this, 'edit_term')); #wp
		add_action('delete_term', array($this, 'delete_term')); #wp
	}

	public function init_options() {
		global $pagenow;
		$this->options = is_array($options = apply_filters('thesis_term_options', array())) ? $options : array();
		$taxonomy = !empty($_GET['taxonomy']) ? $_GET['taxonomy'] : ''; #wp
		if (!($pagenow == 'edit-tags.php' && $taxonomy != 'link_category')) return;
		add_action("{$taxonomy}_add_form_fields", array($this, 'add')); #wp
		add_action("{$taxonomy}_edit_form_fields", array($this, 'edit')); #wp
		wp_enqueue_style('thesis-terms', THESIS_CSS_URL . '/terms.css'); #wp
		wp_enqueue_script('thesis-terms', THESIS_JS_URL . '/terms.js'); #wp
	}

	public function add() {
		$taxonomy = $_GET['taxonomy']; #wp
		$output = false;
		foreach ($this->options as $class => $options)
			if (is_array($options))
				foreach ($options as $id => $option)
					if (is_array($option)) {
						$field = '';
						if ($option['type'] == 'text' || $option['type'] == 'textarea') {
							$classes = array();
							if (!empty($option['counter']))
								$classes['counter'] = 'count_field';
							if ($option['type'] == 'text' && !empty($option['width']))
								$classes['width'] = $option['width'];
							$classes = !empty($classes) ? ' class="' . implode(' ', $classes) . '"' : '';
							$counter = !empty($option['counter']) ?
								"<input type=\"text\" readonly=\"readonly\" class=\"counter\" size=\"2\" maxlength=\"3\" value=\"0\">\n".
								"\t\t\t<label>{$option['counter']}</label>" : false;
							$description = !empty($option['description']) ?
								"<p class=\"description\">{$option['description']}</p>" : false;
							$value = !empty($option['default']) ? $option['default'] : '';
							$input = $option['type'] == 'text' ?
								"<input type=\"text\"$classes id=\"{$class}_$id\" name=\"{$class}[$id]\" value=\"$value\" />" :
								"<textarea$classes id=\"{$class}_$id\" name=\"{$class}[$id]\"" . (is_numeric($option['rows']) ? " rows=\"{$option['rows']}\"" : '') . ">$value</textarea>";
							$field =
								"\t\t\t$input\n".
								(!empty($counter) ?
								"\t\t\t$counter\n" : '').
								(!empty($description) ?
								"\t\t\t$description\n" : '');
						}
						elseif ($option['type'] == 'checkbox') {
							$list = '';
							$default = apply_filters("thesis_term_option_{$class}_$id", false, $taxonomy);
							foreach ($option['options'] as $name => $label) {
								$checked = !empty($default) && is_array($default) ? (!empty($default[$name]) ? 'checked="checked" ' : '') : '';
								$list .=
									"\t\t\t\t<li>\n".
									"\t\t\t\t\t<input type=\"hidden\" name=\"{$class}[$id][$name]\" value=\"0\" />\n".
									"\t\t\t\t\t<input type=\"checkbox\" id=\"{$class}_{$id}_$name\" name=\"{$class}[$id][$name]\" value=\"1\" $checked/>\n".
									"\t\t\t\t\t<label for=\"{$class}_{$id}_$name\">$label</label>\n".
									"\t\t\t\t</li>\n";
							}
							$field =
								"\t\t\t<ul class=\"form-list\">\n".
								$list.
								"\t\t\t</ul>\n";
						}
						elseif ($option['type'] == 'select') {
							$items = '';
							foreach ($option['options'] as $value => $label)
								$items .= "\t\t\t\t<option value=\"$value\">$label</option>\n";
							$field =
								"\t\t\t<select id=\"{$class}_$id\" name=\"{$class}[$id]\" size=\"1\">\n".
								$items.
								"\t\t\t</select>\n";
						}
						if (!empty($field))
							$output .=
								"\t\t<div class=\"form-field\">\n".
								"\t\t\t<label for=\"{$class}_$id\">{$option['label']}</label>\n".
								$field.
								"\t\t</div>\n";
					}
		if ($output)
			echo $output;
	}

	public function edit() {
		if (!($_GET['action'] == 'edit')) return; #wp
		$taxonomy = $_GET['taxonomy']; #wp
		$output = false;
		$values = !empty($this->terms[$_GET['tag_ID']]) ? $this->terms[$_GET['tag_ID']] : array(); #wp
		foreach ($this->options as $class => $options)
			if (is_array($options))
				foreach ($options as $id => $option)
					if (is_array($option)) {
						$cell = '';
						if ($option['type'] == 'text' || $option['type'] == 'textarea') {
							$classes = array();
							if (!empty($option['counter']))
								$classes['counter'] = 'count_field';
							if ($option['type'] == 'text' && !empty($option['width']))
								$classes['width'] = $option['width'];
							$classes = !empty($classes) ? ' class="' . implode(' ', $classes) . '"' : '';
							$counter = !empty($option['counter']) ?
								"<input type=\"text\" readonly=\"readonly\" class=\"counter\" size=\"2\" maxlength=\"3\" value=\"0\">\n".
								"\t\t\t<label class=\"counter_label\">{$option['counter']}</label>" : false;
							$description = !empty($option['description']) ?
								"<p class=\"description\">{$option['description']}</p>" : false;
							$value = !empty($values[$class][$id]) ? stripslashes($values[$class][$id]) : (!empty($option['default']) ? $option['default'] : '');
							$input = $option['type'] == 'text' ?
								"<input type=\"text\"$classes id=\"{$class}_$id\" name=\"{$class}[$id]\" value=\"" . esc_attr($value) . '" />' :
								"<textarea$classes id=\"{$class}_$id\" name=\"{$class}[$id]\"" . (is_numeric($option['rows']) ? " rows=\"{$option['rows']}\"" : '') . ">$value</textarea>";
							$cell =
								"\t\t\t<td>\n".
								"\t\t\t\t$input\n".
								($counter ?
								"\t\t\t\t$counter\n" : '').
								($description ?
								"\t\t\t\t$description\n" : '').
								"\t\t\t</td>\n";
						}
						elseif ($option['type'] == 'checkbox') {
							$list = '';
							if (is_array($default = apply_filters("thesis_term_option_{$class}_$id", false, $taxonomy)))
								$values[$class][$id] = is_array($values[$class][$id]) ? array_merge($default, $values[$class][$id]) : $default;
							foreach ($option['options'] as $name => $label) {
								$checked = !empty($values[$class][$id][$name]) ? 'checked="checked" ' : '';
								$list .=
									"\t\t\t\t\t<li>\n".
									"\t\t\t\t\t\t<input type=\"hidden\" name=\"{$class}[$id][$name]\" value=\"0\" />\n".
									"\t\t\t\t\t\t<input type=\"checkbox\" id=\"{$class}_{$id}_$name\" name=\"{$class}[$id][$name]\" value=\"1\" $checked/>\n".
									"\t\t\t\t\t\t<label for=\"{$class}_{$id}_$name\">$label</label>\n".
									"\t\t\t\t\t</li>\n";
							}
							$cell =
								"\t\t\t<td>\n".
								"\t\t\t\t<ul class=\"form-list\">\n".
								$list.
								"\t\t\t\t</ul>\n".
								"\t\t\t</td>\n";
						}
						elseif ($option['type'] == 'select') {
							$items = '';
							foreach ($option['options'] as $value => $label) {
								$selected = !empty($values[$class]) && $values[$class][$id] == $value || (!isset($values[$class][$id]) && !empty($option['default']) && $value == $option['default']) ? ' selected="selected"' : '';
								$items .=
									"\t\t\t\t\t<option value=\"$value\"$selected>$label</option>\n";
							}
							$cell =
								"\t\t\t<td>\n".
								"\t\t\t\t<select id=\"{$class}_$id\" name=\"{$class}[$id]\" size=\"1\">\n".
								$items.
								"\t\t\t\t</select>\n".
								"\t\t\t</td>\n";
						}
						if (!empty($cell))
							$output .=
								"\t\t<tr class=\"form-field\">\n".
								"\t\t\t<th scope=\"row\" valign=\"top\"><label for=\"{$class}_$id\">{$option['label']}</label></th>\n".
								$cell.
								"\t\t</tr>\n";
					}
		if ($output)
			echo $output;
	}

	public function create_term($term_id) {
		global $thesis;
		if (!($taxonomy = !empty($_POST['taxonomy']) ? $_POST['taxonomy'] : false)) return;
		$new = $save = array();
		foreach ($this->options as $class => $options)
			if (is_array($options)) {
				foreach ($options as $id => $option)
					if (is_array($option))
						$option['default'] = apply_filters("thesis_term_option_{$class}_$id", (!empty($option['default']) ? $option['default'] : ''), $taxonomy);
				if ($new[$class] = $thesis->api->set_options($options, $_POST[$class]))
					$save[$class] = $new[$class];
			}
		if (empty($save)) return;
		$this->terms[$term_id] = $save;
		update_option('thesis_terms', $this->terms);
	}

	public function edit_term() {
		global $thesis;
		if (!($taxonomy = !empty($_POST['taxonomy']) ? $_POST['taxonomy'] : false) || !($term = $_POST['tag_ID'])) return;
		$new = $save = array();
		foreach ($this->options as $class => $options)
			if (is_array($options)) {
				foreach ($options as $id => $option)
					if (is_array($option))
						$option['default'] = apply_filters("thesis_term_option_{$class}_$id", !empty($option['default']) ? $option['default'] : false, $taxonomy);
				if ($new[$class] = $thesis->api->set_options($options, $_POST[$class]))
					$save[$class] = $new[$class];
			}
		if (empty($save))
			unset($this->terms[$term]);
		else
			$this->terms[$term] = $save;
		if (empty($this->terms))
			delete_option('thesis_terms');
		else
			update_option('thesis_terms', $this->terms);
	}

	public function delete_term($term) {
		if (!is_numeric($term)) return;
		unset($this->terms[$term]);
		if (empty($this->terms))
			delete_option('thesis_terms');
		else
			update_option('thesis_terms', $this->terms);
	}
}