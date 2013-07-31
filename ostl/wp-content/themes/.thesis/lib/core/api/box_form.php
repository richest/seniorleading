<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_box_form extends thesis_form_api {
	private $boxes = array();		// filtered array of box objects applicable to the current form
	private $active = array();		// array of all box ids that will be output in the active area of the current form
	private $add = array();			// array of box objects eligible to be added via the Add Box mechanism
	private $used = array();		// array used in queue output
	private $tabindex = 10;			// tabindex for consistent input tabbing

	public function body($args = array()) {
		if (empty($args)) return;
		extract($args);
		$this->boxes = is_array($boxes) ? $boxes : $this->boxes;			
		$this->active = is_array($active) ? $active : $this->active;		
		$this->add = is_array($add) ? $add : $this->add;			
		$this->tabindex = is_numeric($tabindex) ? $tabindex : $this->tabindex;	
		$root = !empty($root) ? $root : false;		// root box for the current form
		$depth = is_numeric($depth) ? $depth : 0;	// parameter for perfectly indented output
		$tab = str_repeat("\t", $depth);
		return
			"$tab<div id=\"boxes\" data-style=\"box\">\n".
			($root ?
			$this->box($this->boxes[$root], $depth + 1) : '').
			"$tab</div>\n".
			"$tab<div id=\"queues\">\n".
			$this->box_manager($depth + 1).
			$this->delete_boxes($depth + 1).
			"$tab</div>\n";
	}

	public function box($box, $depth) {
		if (!is_object($box) || in_array($box->_id, $this->used) || $box->type === 'false') return;
		$tab = str_repeat("\t", $depth);
		$classes = array();
		$rotator = $sortable = $tray_boxes = $tray = '';
		$root = $box->root ? ' id="box_root" data-root="true"' : '';
		$classes['type'] = $box->type;
		$classes['draggable'] = 'draggable';
		if ($box->_parent) {
			$classes['parent'] = "parent_$box->_parent";
			$classes['child'] = "child child_$box->type";
		}
		if ($box->name)
			$classes['instance'] = 'instance';
		elseif ($box->type == 'box' && !$box->_parent)
			$classes['core'] = 'core_box';
		$classes = !empty($classes) ? implode(' ', $classes) : '';
		$title = ($box->_lineage ? $box->_lineage : ''). ($box->name ? $box->name : $box->title);
		$toggle = $box->type == 'rotator' && !$box->root ?
			"<span class=\"toggle_box". ($box->_switch ? ' toggled' : ''). "\" data-style=\"toggle\" title=\"". __('show/hide box contents', 'thesis'). "\">&#8862;</span>" :
			'';
		$switch = method_exists($box, 'html_options') ||
			(($box->head || (empty($box->head) && apply_filters('thesis_editor_box_options', false))) && (is_array($options = $box->_options()) && !empty($options))) ||
			!empty($box->_uploader) ||
			(is_array($html_admin = $box->_html_admin()) && !empty($html_admin)) ?
			' <span class="switch_options" data-style="switch" title="'. __('show box options', 'thesis'). '">&#9881;</span>' : '';
		if ($box->type == 'rotator') {
			$boxes = !empty($box->_boxes) ?
				$box->_boxes : (!empty($box->_startup) ?
				$box->_startup : array());
			foreach ($boxes as $item => $id)
				$sortable .= $this->box(!empty($this->boxes[$id]) ?
					$this->boxes[$id] : (!empty($box->_add_boxes[$id]) ?
					$box->_add_boxes[$id] : false), $depth + 2);
			if (!empty($box->_dependents)) {
				$children = !empty($boxes) ? array_diff($box->_dependents, $boxes) : $box->_dependents;
				foreach ($children as $child)
					if (!in_array($child, $this->active))
						$tray_boxes .= $this->box(!empty($this->boxes[$child]) ?
							$this->boxes[$child] : (!empty($box->_add_boxes[$child]) ?
							$box->_add_boxes[$child] : false), $depth + 3);
				$tray =
					"$tab\t<div class=\"tray\">\n".
					"$tab\t\t<h5>". __('Drop orange boxes here to hide them in the tray.', 'thesis'). "</h5>\n".
					"$tab\t\t<div class=\"tray_body\">\n".
					"$tab\t\t\t<p class=\"tray_instructions\">". __('Click on a box to add it to the active area above', 'thesis'). "</p>\n".
					"$tab\t\t\t<div class=\"tray_list\">\n".
					$tray_boxes.
					"$tab\t\t\t</div>\n".
					"$tab\t\t</div>\n".
					"$tab\t\t<div class=\"tray_bar\"><span class=\"toggle_tray\" title=\"". __('show/hide tray', 'thesis'). "\">". __('show tray &darr;', ' thesis'). "</span></div>\n".
					"$tab\t</div>\n";
			}
			$rotator =
				"$tab\t<div class=\"sortable\">\n".
				$sortable.
				"$tab\t</div>\n".
				$tray;
		}
		$this->used[] = $box->_id;
		return
			"$tab<div$root data-id=\"$box->_id\" data-class=\"$box->_class\" class=\"$classes\">\n".
			"$tab\t<h4>$toggle<span class=\"box_name\" id=\"$box->_id\">$title</span>$switch</h4>\n".
			$rotator.
			"$tab</div>\n".
			$this->options($box, $depth);
	}

	private function options($box, $depth) {
		global $thesis;
		$tab = str_repeat("\t", $depth);
		$menu = $fields = $panes = array();
		$name = false;
		$type = $box->type == 'box' ? ($box->name ? 'instance' : (!$box->_parent ? 'core_box' : 'box')) : $box->type;
		if ($box->name) {
			$name = array(
				'id' => "{$box->_class}_{$box->_id}_name",
				'name' => "{$box->_class}[$box->_id][_name]",
				'value' => $box->name,
				'tabindex' => !empty($this->tabindex) ? $this->tabindex : false);
			$this->tabindex++;
		}
		if (!empty($box->_uploader)) {
			$menu['uploader'] = __('Uploader', 'thesis');
			$fields['uploader'] = $this->fields($box->_uploader, array(), "{$box->_class}_{$box->_id}_", "{$box->_class}[$box->_id]", !empty($this->tabindex) ? $this->tabindex : false, $depth + 4);
			$this->tabindex = $fields['uploader']['tabindex'];
			$panes['uploader'] = $fields['uploader']['output'];
		}
		if (is_array($html = $box->_html_options()) && !empty($html)) {
			$menu['html'] = $thesis->api->base['html'];
			$fields['html'] = $this->fields($html, $box->options, "{$box->_class}_{$box->_id}_", "{$box->_class}[$box->_id]", !empty($this->tabindex) ? $this->tabindex : false, $depth + 4);
			$this->tabindex = $fields['html']['tabindex'];
			$panes['html'] = $fields['html']['output'];
		}
		if (($box->head || (empty($box->head) && apply_filters('thesis_editor_box_options', false))) && (is_array($options = $box->_options(true)) && !empty($options))) {
			$menu['options'] = __('Options', 'thesis');
			$fields['options'] = $this->fields($options, $box->options, "{$box->_class}_{$box->_id}_", "{$box->_class}[$box->_id]", !empty($this->tabindex) ? $this->tabindex : false, $depth + 4);
			$this->tabindex = $fields['options']['tabindex'];
			$panes['options'] = $fields['options']['output'];
		}
		if (is_array($html_admin = $box->_html_admin()) && !empty($html_admin)) {
			$menu['admin'] = __('Admin', 'thesis');
			$fields['admin'] = $this->fields($html_admin, $box->options, "{$box->_class}_{$box->_id}_", "{$box->_class}[$box->_id]", $this->tabindex, $depth + 4);
			$this->tabindex = $fields['admin']['tabindex'];
			$panes['admin'] = $fields['admin']['output'];
		}
		return $thesis->api->popup(array(
			'id' => $box->_id,
			'type' => $box->_parent ? "{$type}_child" : $type,
			'title' => ($box->_lineage ? $box->_lineage : ''). $box->title,
			'name' => $name,
			'menu' => $menu,
			'panes' => $panes,
			'depth' => $depth));
	}

	private function box_manager($depth) {
		$tab = str_repeat("\t", $depth);
		$classes = array();
		$queue = array(
			'intro' => array(
				'' => __('Select a Box to add:', 'thesis')),
			'unused' => array(),
			'add' => array());
		foreach ($this->boxes as $id => $box)
			if (!in_array($id, $this->used) && !$box->_parent)
				$queue['unused'][$id] = strip_tags(!empty($box->name) ? $box->name : $box->title);
		natcasesort($queue['unused']);
		if (!empty($this->add)) {
			foreach ($this->add as $class => $box)
				$classes[$class] = strip_tags($box->title);
			natcasesort($classes);
			foreach ($classes as $class => $title)
				$queue['add'][$class] = "* $title";
		}
		if (!empty($queue['add']))
			$queue['intro']['*'] = __('* indicates a new Box', 'thesis');
		$queue = array_merge($queue['intro'], $queue['unused'], $queue['add']);
		$add = $this->fields(array(
			'box_id' => array(
				'type' => 'select',
				'options' => $queue)), array(), false, false, $this->tabindex, $depth + 2);
		return
			"$tab<div id=\"box_manager\">\n".
			"$tab\t<p id=\"remove_boxes\" class=\"dropzone\"><kbd>shift</kbd> + ". __('drag boxes here to remove them from the page', 'thesis'). "</p>\n".
			"$tab\t<div id=\"add_box_form\">\n".
			$add['output'].
			"$tab\t\t". wp_nonce_field('thesis-add-box', '_wpnonce-thesis-add-box', true, false). "\n".
			"$tab\t\t<input type=\"submit\" id=\"add_box\" data-style=\"button action\" name=\"add_box\" value=\"". __('Add Box', 'thesis'). "\" />\n".
			"$tab\t</div>\n".
			"$tab\t<div class=\"sortable\">\n".
			"$tab\t</div>\n".
			"$tab</div>\n";
	}

	private function delete_boxes($depth) {
		if (empty($this->add)) return;
		$tab = str_repeat("\t", $depth);
		return
			"$tab<div id=\"delete_boxes\" class=\"rotator visible\">\n".
			"$tab\t<h4 class=\"dropzone\">" . __('<kbd>shift</kbd> + drag blue and white boxes here to delete them on save', 'thesis') . "</h4>\n".
			"$tab\t<div class=\"delete_warning\">\n".
			"$tab\t\t<p>" .  __('<strong>Warning:</strong> Deleted boxes will be removed from ALL templates!', 'thesis') . "</p>\n".
			"$tab\t</div>\n".
			"$tab\t<div class=\"sortable\">\n".
			"$tab\t</div>\n".
			"$tab</div>\n";
	}

	public function save($form) {
		if (!is_array($form)) return false;
		$boxes = array();
		$rotators = isset($form['boxes']) && is_array($form['boxes']) ? $form['boxes'] : array();
		$delete = isset($form['delete_boxes']) && is_array($form['delete_boxes']) ? $form['delete_boxes'] : array();
		foreach ($rotators as $id => $inner_boxes)
			if ($id != 'queue' && !in_array($id, $delete) && is_array($inner_boxes))
				$boxes[$id] = $inner_boxes;
		return array(
			'boxes' => $boxes,
			'delete' => !empty($delete) ? $delete : false);
	}
}