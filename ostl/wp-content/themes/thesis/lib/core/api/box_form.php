<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
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
			$this->queue($depth + 1).
			$this->add_boxes($depth + 1).
			$this->delete_boxes($depth + 1).
			"$tab</div>\n";
	}

	private function box($box, $depth) {
		if (!is_object($box) || in_array($box->_id, $this->used)) return;
		$tab = str_repeat("\t", $depth);
		$classes = array();
		$rotator = $sortable = $tray_boxes = $tray_output = '';
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
		$title = ($box->_lineage ? $box->_lineage : '') . ($box->name ? $box->name : $box->title);
		$toggle = $box->type == 'rotator' && !$box->root ?
			"<span class=\"toggle_box" . ($box->_switch ? ' toggled' : '') . "\" data-style=\"toggle\" title=\"" . __('show/hide box contents', 'thesis') . "\">&nbsp;</span>" :
			'';
		$switch = method_exists($box, 'options') || !empty($box->_uploader) || !empty($box->_admin) ? ' <span class="switch_options" data-style="switch" title="' . __('show/hide box options', 'thesis') . '">S</span>' : '';
		if ($box->type == 'rotator') {
			$boxes = property_exists($box, '_boxes') && is_array($box->_boxes) ? $box->_boxes : (property_exists($box, '_startup') && is_array($box->_startup) ? $box->_startup : array());
			foreach ($boxes as $item => $id)
				if (!empty($this->boxes[$id]))
					$sortable .= $this->box($this->boxes[$id], $depth + 2);
			if (property_exists($box, '_children') && is_array($box->_children)) {
				$children = !empty($boxes) ? array_diff($box->_children, $boxes) : $box->_children;
				foreach ($children as $child)
					$tray_boxes .= $this->box($this->boxes[$child], $depth + 3);
				$tray_output =
					"$tab\t<div class=\"tray\">\n".
					"$tab\t\t<h5>" . __('Drop green boxes here to hide them in the tray.', 'thesis') . "</h5>\n".
					"$tab\t\t<div class=\"tray_body\">\n".
					"$tab\t\t\t<p class=\"tray_instructions\">" . __('Click on a box to add it to the active area above', 'thesis') . "</p>\n".
					"$tab\t\t\t<div class=\"tray_list\">\n".
					$tray_boxes.
					"$tab\t\t\t</div>\n".
					"$tab\t\t</div>\n".
					"$tab\t\t<div class=\"tray_bar\"><span class=\"toggle_tray\" title=\"" . __('show/hide tray', 'thesis') . "\">" . __('show tray &darr;', ' thesis') . "</span></div>\n".
					"$tab\t</div>\n";
			}
			$rotator =
				"$tab\t<div class=\"sortable\">\n".
				$sortable.
				"$tab\t</div>\n".
				$tray_output;
		}
		$this->used[] = $box->_id;
		return
			"$tab<div$root data-id=\"$box->_id\" data-class=\"$box->_class\" class=\"$classes\">\n".
			"$tab\t<h4>$toggle<span id=\"$box->_id\">$title</span>$switch</h4>\n".
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
		if (is_array($options = $box->_get_options()) && !empty($options)) {
			$menu['options'] = __('Options', 'thesis');
			$fields['options'] = $this->fields($options, $box->options, "{$box->_class}_{$box->_id}_", "{$box->_class}[$box->_id]", !empty($this->tabindex) ? $this->tabindex : false, $depth + 4);
			$this->tabindex = $fields['options']['tabindex'];
			$panes['options'] = $fields['options']['output'];
		}
		if (!empty($box->_admin)) {
			$menu['admin'] = __('Admin', 'thesis');
			$fields['admin'] = $this->fields($box->_admin, $box->options, "{$box->_class}_{$box->_id}_", "{$box->_class}[$box->_id]", $this->tabindex, $depth + 4);
			$this->tabindex = $fields['admin']['tabindex'];
			$panes['admin'] = $fields['admin']['output'];
		}
		return $thesis->api->popup(array(
			'id' => $box->_id,
			'type' => $box->_parent ? "{$type}_child" : $type,
			'title' => $box->title,
			'name' => $name,
			'menu' => $menu,
			'panes' => $panes,
			'depth' => $depth));
	}

	private function queue($depth) {
		$tab = str_repeat("\t", $depth);
		$queue = '';
		foreach ($this->boxes as $id => $box)
			if (!in_array($id, $this->used) && !$box->_parent)
				$queue .= $this->box($box, $depth + 3);
		return
			"$tab<div data-id=\"queue\" id=\"box_queue\" class=\"rotator visible\" data-style=\"box\">\n".
			"$tab\t<h4>" . __('<kbd>shift</kbd> + drag boxes here to remove them from the page', 'thesis') . "</h4>\n".
			"$tab\t<div class=\"sortable\">\n".
			$queue.
			"$tab\t</div>\n".
			"$tab</div>\n";
	}

	private function add_boxes($depth) {
		if (empty($this->add)) return;
		$tab = str_repeat("\t", $depth);
		$boxes = array('' => __('Type of box to add:', 'thesis'));
		foreach ($this->add as $class => $box)
			$boxes[$class] = $box->title;
		$fields = array(
			'box_class' => array(
				'type' => 'select',
				'options' => $boxes),
			'box_name' => array(
				'type' => 'text',
				'label' => __('New Box Name <span class="optional">optional</span>', 'thesis')));
		$fields = $this->fields($fields, false, false, false, $this->tabindex, $depth + 1);
		return
			"$tab<div id=\"add_boxes\" class=\"rotator visible\">\n".
			"$tab\t<h4>Add Boxes <span>(drag me around!)</span></h4>\n".
			$fields['output'].
			"$tab\t" . wp_nonce_field('thesis-add-box', '_wpnonce-thesis-add-box', true, false) . "\n".
			"$tab\t<input type=\"submit\" id=\"add_box\" data-style=\"button action\" name=\"add_box\" value=\"" . __('Add Box', 'thesis') . "\" />\n".
			"$tab\t<p class=\"add_box_instructions\"><kbd>shift</kbd> + drag boxes to move them!</p>\n".
			"$tab\t<div class=\"sortable\">\n".
			"$tab\t</div>\n".
			"$tab</div>\n";
	}

	private function delete_boxes($depth) {
		if (empty($this->add)) return;
		$tab = str_repeat("\t", $depth);
		return
			"$tab<div id=\"delete_boxes\" class=\"rotator visible\">\n".
			"$tab\t<h4>" . __('<kbd>shift</kbd> + drag blue and white boxes here to delete them on save', 'thesis') . "</h4>\n".
			"$tab\t<div class=\"delete_warning\">\n".
			"$tab\t\t<p>" .  __('<strong>Warning:</strong> Deleted boxes will be removed from ALL templates!', 'thesis') . "</p>\n".
			"$tab\t</div>\n".
			"$tab\t<div class=\"sortable\">\n".
			"$tab\t</div>\n".
			"$tab</div>\n";
	}

	public function add_box($box) {
		if (!is_object($box)) return;
		$this->boxes[$box->_id] = $box;
		if ($box->dependents)
			foreach ($box->dependents as $class)
				$this->add_dependent($class, $box->_id);
		echo $this->box($box, 4);
	}

	private function add_dependent($class, $parent) {
		$lineage = $parent ? (($this->boxes[$parent]->_lineage ? $this->boxes[$parent]->_lineage : '') . ($this->boxes[$parent]->name ? $this->boxes[$parent]->name : $this->boxes[$parent]->title) . " &rarr; ") : false;
		$box = new $class(array('parent' => $parent, 'lineage' => $lineage));
		$this->boxes[$box->_id] = $box;
		if ($box->_parent) {
			$this->boxes[$parent]->_children[] = $box->_id;
			if (is_array($this->boxes[$box->_parent]->children) && in_array($class, $this->boxes[$box->_parent]->children))
				$this->boxes[$box->_parent]->_startup[] = $box->_id;
		}
		if (is_array($box->dependents))
			foreach ($box->dependents as $dependent_class)
				$this->add_dependent($dependent_class, $box->_id);
		if (!empty($this->boxes[$parent]->_startup) && is_array($this->boxes[$parent]->_startup))
			$this->active[$parent] = $this->boxes[$parent]->_startup;
	}

	public function save($form) {
		if (!is_array($form)) return false;
		$boxes = array();
		$rotators = is_array($form['boxes']) ? $form['boxes'] : array();
		$delete = isset($form['delete_boxes']) && is_array($form['delete_boxes']) ? $form['delete_boxes'] : array();
		foreach ($rotators as $id => $inner_boxes)
			if ($id != 'queue' && !in_array($id, $delete) && is_array($inner_boxes))
				$boxes[$id] = $inner_boxes;
		return array(
			'boxes' => $boxes,
			'delete' => !empty($delete) ? $delete : false);
	}
}