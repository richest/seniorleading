<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_skin_boxes {
	public $active = array();		// (array) all active Box objects
	public $add = array();			// (array) instance-based Boxes that can be added via the Box form
	private $head = array();		// (array) <head> Box data
	private $skin = array();		// (array) <body> (skin) Box data
	private $instances = array();	// (array) data for all Box instances (<head> + <body>)
	private $core = array(			// (array) list of core Box classes intended for HTML output
		'thesis_html_head',
		'thesis_title_tag',
		'thesis_meta_description',
		'thesis_meta_keywords',
		'thesis_meta_robots',
		'thesis_stylesheets_link',
		'thesis_favicon',
		'thesis_canonical_link',
		'thesis_html_head_scripts',
		'thesis_html_body',
		'thesis_html_container',
		'thesis_site_title',
		'thesis_site_tagline',
		'thesis_wp_nav_menu',
		'thesis_wp_loop',
		'thesis_post_box',
		'thesis_post_list',
		'thesis_comments_intro',
		'thesis_comments_nav',
		'thesis_comments',
		'thesis_comment_form',
		'thesis_trackbacks',
		'thesis_previous_post_link',
		'thesis_next_post_link',
		'thesis_previous_posts_link',
		'thesis_next_posts_link',
		'thesis_archive_title',
		'thesis_archive_content',
		'thesis_query_box',
		'thesis_wp_widgets',
		'thesis_text_box',
		'thesis_attribution',
		'thesis_wp_admin',
		'thesis_js');

	public function __construct($skin) {
		global $thesis;
		$this->head = is_array($head = $thesis->api->get_option('thesis_head_boxes')) ? $head : $this->head;
		$this->skin = is_array($skin) ? $skin : $this->skin;
		add_action('init', array($this, 'init'));
	}

	public function init() {
		$user = new thesis_user_boxes;
		$this->instances = array_merge($this->head, $this->skin);
		$core = is_array($boxes = apply_filters('thesis_boxes', $this->core)) ? $boxes : $this->core;
		$this->create(is_array($user->active) ? array_merge($core, $user->active) : $core);
	}

	private function create($classes, $parent = false) {
		foreach ((array) $classes as $class) {
			$activated = false;
			$lineage = !empty($parent) ? (($this->active[$parent]->_lineage ? $this->active[$parent]->_lineage : '') . ($this->active[$parent]->name ? $this->active[$parent]->name : $this->active[$parent]->title) . " &rarr; ") : false;
			if (!empty($this->instances[$class]) && is_array($this->instances[$class]))
				foreach ($this->instances[$class] as $id => $options)
					if (class_exists($class) && is_subclass_of($class, 'thesis_box') && (!$parent || ($parent && !empty($options['_parent']) && $options['_parent'] == $parent))) {
						$box = new $class(array('id' => $id, 'options' => $options, 'parent' => $parent, 'lineage' => $lineage));
						if ($box->name)
							$this->add[$class] = $box;
						$this->assign($box);
						$activated = true;
					}
			if (!$activated && class_exists($class)) {
				$box = new $class(array('parent' => $parent, 'lineage' => $lineage));
				($box->name && empty($box->_parent) ? $this->add[$class] = $box : $this->assign($box));
			}
		}
	}

	private function assign($box) {
		$this->active[$box->_id] = $box;
		if ($box->_parent) {
			$this->active[$box->_parent]->_dependents[] = $box->_id;
			if (is_array($this->active[$box->_parent]->children) && in_array($box->_class, $this->active[$box->_parent]->children))
				$this->active[$box->_parent]->_startup[] = $box->_id;
		}
		if (is_array($box->dependents))
			$this->create($box->dependents, $box->_id);
	}

	public function get_box_form_data($boxes = array(), $head = false) {
		$form = array(
			'boxes' => array(),
			'active' => array(),
			'add' => array(),
			'root' => false);
		foreach ($this->active as $id => $box)
			if (!empty($box->type) && (($head && $box->head) || (!$head && !$box->head))) {
				$form['boxes'][$id] = $box;
				if ($box->root)
					$form['root'] = $id;
			}
		if (is_array($this->add))
			foreach ($this->add as $class => $box)
				if (($head && $box->head) || (!$head && !$box->head))
					$form['add'][$class] = $box;
		if (is_array($boxes))
			foreach ($boxes as $id => $sortable) {
				if (!in_array($id, $form['active']))
					$form['active'][] = $id;
				if (is_array($sortable)) {
					if (isset($form['boxes'][$id]))
						$form['boxes'][$id]->_boxes = $sortable;
					foreach ($sortable as $box_id)
						if (!in_array($box_id, $form['active']))
							$form['active'][] = $box_id;
				}
			}
		return $form;
	}

	public function save($form, $head = false) {
		if (!is_array($this->active) || !is_array($form)) return false;
		$boxes = $head ? $this->head : $this->skin;
		foreach ($this->active as $id => $box)
			if (((!$head && !$box->head) || ($head && $box->head)) && method_exists($box, '_save'))
				if (is_array($save = $box->_save($form)) && !empty($save))
					$boxes[$box->_class][$box->_id] = $save;
				elseif ($save == 'delete') {
					unset($boxes[$box->_class][$box->_id]);
					if (empty($boxes[$box->_class]))
						unset($boxes[$box->_class]);
				}
		if ($head) {
			if (is_array($boxes))
				if (empty($boxes))
					delete_option('thesis_head_boxes');
				else
					update_option('thesis_head_boxes', $boxes);
		}
		else
			return is_array($boxes) ? $boxes : false;
	}

	public function add($id) {
		global $thesis;
		if (empty($id)) return;
		$save = false;
		if (in_array($id, array_keys($this->active)))
			$box = $this->active[$id];
		elseif (class_exists($id)) {
			$box = new $id(array('id' => "{$id}_" . time()));
			$save = true;
		}
		else
			return;
		echo $thesis->api->get_box_form()->box($box->dependents ? $this->add_dependents($box) : $box, 4);
		if (!empty($save))
			if ($box->head) {
				$this->head[$box->_class][$box->_id] = array();
				update_option('thesis_head_boxes', $this->head);
			}
			else {
				$this->skin[$box->_class][$box->_id] = array();
				return $this->skin;
			}
	}

	public function add_dependents($box) {
		if (empty($box) || !is_object($box)) return;
		foreach ($box->dependents as $class) {
			$new_box = !empty($this->active["{$box->_id}_$class"]) ?
				$this->active["{$box->_id}_$class"] :
				new $class(array('id' => "{$box->_id}_$class", 'parent' => $box->_id, 'lineage' => (!empty($box->_lineage) ? $box->_lineage : ''). ($box->name ? $box->name : $box->title). ' &rarr; '));
			if ($new_box->dependents)
				$new_box = $this->add_dependents($new_box);
			$box->_add_boxes[$new_box->_id] = $new_box->dependents ? $this->add_dependents($new_box) : $new_box;
			$box->_dependents[] = $new_box->_id;
			if (is_array($box->children) && in_array($class, $box->children))
				$box->_startup[] = $new_box->_id;
		}
		return $box;
	}

	public function delete($delete) {
		if (!is_array($delete) || empty($delete)) return;
		foreach ($delete as $id)
			$this->delete_box($id);
	}

	private function delete_box($id) {
		if (!is_array($this->active) || !is_object($box = $this->active[$id])) return;
		if (!empty($box->_dependents))
			foreach ($box->_dependents as $dependent)
				$this->delete_box($dependent);
		unset($this->active[$id]);
		if ($box->head) {
			unset($this->head[$box->_class][$id]);
			if (empty($this->head[$box->_class]))
				unset($this->head[$box->_class]);
		}
		else {
			unset($this->skin[$box->_class][$id]);
			if (empty($this->skin[$box->_class]))
				unset($this->skin[$box->_class]);
		}
	}
}