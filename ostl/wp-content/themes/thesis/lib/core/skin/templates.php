<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_templates {
	private $core = array();			// (array) core templates for this skin
	private $post_types = array();		// (array) WP custom post types
	private $queries = array();			// (array) query modification data for active templates
	public $active = array();			// (array) data for all active templates
	public $head = array(				// (array) <head> template
		'thesis_html_head' => array(
			'thesis_meta_charset',
			'thesis_title_tag',
			'thesis_meta_description',
			'thesis_meta_keywords',
			'thesis_meta_robots',
			'thesis_meta_viewport',
			'thesis_stylesheets_link',
			'thesis_favicon',
			'thesis_canonical_link',
			'thesis_feed_link',
			'thesis_pingback_link',
			'thesis_html_head_scripts'));
	// The following properties are intended for use by the template editor ONLY
	private $template = array();		// (array) active template in the template editor
	private $custom = array();			// (array) custom template data from ->get_custom()
	private $options = array();			// (array) template options
	private $tabindex = false;			// (int) tabindex for the template editor

	public function __construct($templates) {
		global $thesis;
		$this->head = is_array($head = $thesis->api->get_option('thesis_head')) ? $head : $this->head;
		$this->active = is_array($templates) ? $templates : $this->active;
		add_action('init', array($this, 'init'));
		add_action('thesis_init_editor', array($this, 'init_editor'));
		add_filter('thesis_site_menu', array($this, 'site_menu'), 1);
	}

	public function site_menu($site) {
		global $thesis;
		$menu['head'] = array(
			'text' => $thesis->api->strings['html_head'],
			'url' => admin_url('admin.php?page=thesis&canvas=head'));
		return is_array($site) ? array_merge($site, $menu) : $menu;
	}

	public function init() {
		global $wp_post_types;
		$this->core = array(
			'home' => array(
				'title' => __('Home', 'thesis')),
			'single' => array(
				'title' => __('Single', 'thesis')),
			'attachment' => array(
				'title' => __('Attachment', 'thesis'),
				'parent' => 'single'),
			'page' => array(
				'title' => __('Page', 'thesis')),
			'front' => array(
				'title' => __('Front Page', 'thesis'),
				'parent' => 'page'),
			'fourohfour' => array(
				'title' => __('404', 'thesis'),
				'parent' => 'page'),
			'archive' => array(
				'title' => __('Archive', 'thesis')),
			'category' => array(
				'title' => __('Category', 'thesis'),
				'parent' => 'archive'),
			'tag' => array(
				'title' => __('Tag', 'thesis'),
				'parent' => 'archive'),
			'tax' => array(
				'title' => __('Taxonomy', 'thesis'),
				'parent' => 'archive'),
			'author' => array(
				'title' => __('Author', 'thesis'),
				'parent' => 'archive'),
			'day' => array(
				'title' => __('Day', 'thesis'),
				'parent' => 'archive'),
			'month' => array(
				'title' => __('Month', 'thesis'),
				'parent' => 'archive'),
			'year' => array(
				'title' => __('Year', 'thesis'),
				'parent' => 'archive'),
			'search' => array(
				'title' => __('Search Results', 'thesis'),
				'parent' => 'archive'));
		$this->core = is_array($core = apply_filters('thesis_templates', $this->core)) ? $core : $this->core;
		if (is_array($wp_post_types))
			$this->post_types = array_keys(array_slice($wp_post_types, 5));
		foreach ($this->post_types as $post_type)
			$this->core[$post_type] = array('title' => $wp_post_types[$post_type]->label, 'parent' => 'single');
	}

	public function get_template($id = false) {
		global $wp_query;
		$id = $id && ((!empty($this->active[$id]) && is_array($this->active[$id])) || (!empty($this->core[$id]) && is_array($this->core[$id]))) ?
			$id : ($wp_query->is_page ? ($wp_query->query_vars['page_id'] > 0 && $wp_query->query_vars['page_id'] == get_option('page_on_front') ?
			'front' :
			'page') : ($wp_query->is_home ?
			'home' : ($wp_query->is_single ? ($wp_query->is_attachment ?
			'attachment' : (!empty($wp_query->query_vars['post_type']) && in_array($wp_query->query_vars['post_type'], $this->post_types) ?
			$wp_query->query_vars['post_type'] :
			'single')) : ($wp_query->is_category ?
			'category' : ($wp_query->is_tag ?
			'tag' : ($wp_query->is_tax ?
			'tax' : ($wp_query->is_archive ? ($wp_query->is_day ?
			'day' : ($wp_query->is_month ?
			'month' : ($wp_query->is_year ?
			'year' : ($wp_query->is_author ?
			'author' :
			'archive')))) : ($wp_query->is_search ?
			'search' : ($wp_query->is_404 ?
			'fourohfour' :
			'home')))))))));
		$template['id'] = $id;
		$template['type'] = !empty($this->core[$id]) && is_array($this->core[$id]) ? (!empty($this->core[$id]['parent']) ? $this->core[$id]['parent'] : $id) : false;
		$template['title'] = !empty($this->active[$id]['title']) ?
			$this->active[$id]['title'] : (!empty($this->core[$id]['title']) ?
			$this->core[$id]['title'] : '');
		$template['options'] = !empty($this->active[$id]['options']) ?
			$this->active[$id]['options'] : (!empty($this->core[$id]['parent']) && !empty($this->active[$this->core[$id]['parent']]['options']) ?
			$this->active[$this->core[$id]['parent']]['options'] : array());
		$template['boxes'] = !empty($this->active[$id]['boxes']) ?
			$this->active[$id]['boxes'] : (!empty($this->core[$id]['parent']) && !empty($this->active[$this->core[$id]['parent']]['boxes']) ?
			$this->active[$this->core[$id]['parent']]['boxes'] : array());
		$template['boxes'] = array_merge($this->head, $template['boxes']);
		return $template;
	}

	private function get_options() {
		return is_array($template_options = apply_filters('thesis_template_options', array())) ? $template_options : array();
	}

	private function get_custom() {
		$templates = $names = $core = array();
		$core = array_keys($this->core);
		foreach ($this->active as $name => $template)
			if (!in_array($name, $core)) {
				$templates[$name] = $template;
				$names[] = $name;
			}
		return array(
			'templates' => $templates,
			'names' => $names);
	}

	public function custom_select() {
		$templates[''] = __('No Custom Template', 'thesis');
		$custom = $this->get_custom();
		if (is_array($custom['templates']))
			foreach ($custom['templates'] as $name => $template)
				$templates[$name] = $template['title'];
		return $templates;
	}

	public function init_editor() {
		add_action('thesis_editor_head', array($this, 'editor_head'));
		add_action('thesis_editor_scripts', array($this, 'editor_scripts'));
	}

	public function editor_head() {
		global $thesis;
		$css = array(
			'box-form' => THESIS_CSS_URL . '/box_form.css',
			'templates' => THESIS_CSS_URL . '/templates.css');
		foreach ($css as $name => $href)
			echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$href?ver={$thesis->version}\" />\n";
	}

	public function editor_scripts() {
		global $thesis;
		echo "<script src=\"" . THESIS_JS_URL . "/templates.js?ver={$thesis->version}\"></script>\n";
	}

	public function editor($data) {
		global $thesis;
		if (!is_array($data) || !is_array($this->template = $data['template']) || !is_array($form = $data['form'])) return;
		$box_form = $thesis->api->get_box_form();
		$tab = str_repeat("\t", $depth = 2);
		$this->tabindex = 10;
		$this->options = $this->get_options();
		$this->custom = $this->get_custom();
		$switch = !empty($this->options) || in_array($this->template['id'], $this->custom['names']) ? ' <span id="switch_template_options" data-style="switch">S</span>' : '';
		return
			"$tab<h3><span id=\"template\" class=\"edit_templates\" title=\"" . __('click to edit other templates or to add a new template', 'thesis') . "\">{$this->template['title']}</span>$switch</h3>\n".
			$this->manager($depth).
			"$tab<form id=\"t_boxes\" method=\"post\" action=\"\" enctype=\"multipart/form-data\">\n".
			"$tab\t<input type=\"hidden\" id=\"current_template\" name=\"template\" value=\"{$this->template['id']}\" />\n".
			$this->options($depth + 1).
			$box_form->body(array_merge($form, array('tabindex' => $this->tabindex, 'depth' => $depth + 1))).
			"$tab\t" . wp_nonce_field('thesis-save-template', '_wpnonce-thesis-ajax', true, false) . "\n".
			"$tab\t<input type=\"submit\" data-style=\"button save\" id=\"save_template\" name=\"save_template\" value=\"" . __('Save Template', 'thesis') . "\" />\n".
			"$tab</form>\n";
	}

	private function manager($depth) {
		$tab = str_repeat("\t", $depth);
		$core = $custom = '';
		$hierarchy = array();
		foreach ($this->core as $name => $template)
			if (!empty($template['parent']))
				$hierarchy[$template['parent']][] = $name;
			else
				$hierarchy[$name] = false;
		foreach ((array) $hierarchy as $name => $templates) {
			$children = '';
			$class = $name == $this->template['id'] ? ' class="current_template"' : '';
			$title = $name == $this->template['id'] ?
				$this->core[$name]['title'] :
				"<a class=\"edit_template\" href=\"\" data-template=\"$name\" title=\"" . __('edit this template', 'thesis') . "\">{$this->core[$name]['title']}</a><a class=\"delete_template\" href=\"\" data-template=\"$name\" title=\"" . __('delete template', 'thesis') . "\">[&times;]</a>";
			if (is_array($templates)) {
				$child_links = '';
				foreach ($templates as $child) {
					$child_class = $child == $this->template['id'] ? ' class="current_template"' : '';
					$child_title = $child == $this->template['id'] ?
						$this->core[$child]['title'] :
						"<a class=\"edit_template\" href=\"\" data-template=\"$child\" title=\"" . __('edit this template', 'thesis') . "\">{$this->core[$child]['title']}</a><a class=\"delete_template\" href=\"\" data-template=\"$child\" title=\"" . __('delete template', 'thesis') . "\">[&times;]</a>";
					$child_links .= "$tab\t\t\t\t\t<li$child_class>$child_title</li>\n";
				}
				$children =
					" <span class=\"toggle_child_templates\" href=\"\">[+]</span>\n".
					"$tab\t\t\t\t<ul class=\"child_templates\">\n".
					$child_links.
					"$tab\t\t\t\t</ul>\n$tab\t\t\t";
			}
			$core .= "$tab\t\t\t<li$class>$title$children</li>\n";
		}
		if (!empty($this->custom['templates'])) {
			$custom_links = '';
			foreach ((array) $this->custom['templates'] as $name => $template) {
				$current = $name == $this->template['id'] ? ' current_template' : '';
				$title = $name == $this->template['id'] ?
					$template['title'] :
					"<a class=\"edit_template\" href=\"\" data-template=\"$name\" title=\"" . __('edit this template', 'thesis') . "\">{$template['title']}</a><a class=\"delete_template\" href=\"\" data-template=\"$name\" title=\"" . __('delete template', 'thesis') . ": {$template['title']}\">[&times;]</a>";
				$custom_links .= "$tab\t\t\t<li class=\"custom_template $name$current\">$title</li>\n";
			}
			$custom =
				"$tab\t\t<ul>\n".
				$custom_links.
				"$tab\t\t</ul>\n";
		}
		return
			"$tab<div id=\"t_template_manager\">\n".
			"$tab\t<div id=\"core_templates\" class=\"template_module\">\n".
			"$tab\t\t<h4 class=\"manager_heading\">" . __('Core Templates', 'thesis') . "</h4>\n".
			"$tab\t\t<ul>\n".
			$core.
			"$tab\t\t</ul>\n".
			"$tab\t</div>\n".
			"$tab\t<div id=\"custom_templates\" class=\"template_module\">\n".
			"$tab\t\t<h4 class=\"manager_heading\">" . __('Custom Templates', 'thesis') . " <span id=\"add_template\" data-style=\"button action\"  title=\"" . __('click to add a new template', 'thesis') . "\">" . __('add new', 'thesis') . "</span></h4>\n".
			$custom.
			"$tab\t</div>\n".
			$this->add_form($depth + 1).
			"$tab\t<div id=\"copy_from_template\" class=\"template_module\">\n".
			"$tab\t\t<h4 class=\"manager_heading\">Copy from Template</h4>\n".
			$this->copy_form($depth + 2).
			"$tab\t</div>\n".
			"$tab</div>\n";
	}

	private function add_form($depth) {
		global $thesis;
		$tab = str_repeat("\t", $depth);
		$fields = $thesis->api->form->fields(array(
			'title' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('New Template Name', 'thesis'),
				'tooltip' => __('The name you specify here will be used to identify this template throughout the WordPress interface.<br /><br />To avoid confusion, always give your templates unique names.', 'thesis'))), array(), 'add_template_', false, $this->tabindex, $depth + 4);
		return $thesis->api->popup(array(
			'id' => 'new_template',
			'type' => 'new_template',
			'title' => __('Create New Template', 'thesis'),
			'depth' => $depth,
			'body' =>
				"$tab\t\t\t<form method=\"post\" action=\"\">\n".
				$fields['output'].
				"$tab\t\t\t\t<p>\n".
				"$tab\t\t\t\t\t<input type=\"submit\" id=\"create_template\" data-style=\"button save\" name=\"add_template\" value=\"" . __('Create Template', 'thesis') . "\" />\n".
				"$tab\t\t\t\t</p>\n".
				"$tab\t\t\t</form>\n"));
	}

	private function copy_form($depth) {
		global $thesis;
		$tab = str_repeat("\t", $depth);
		$options = array();
		$options[''] = __('Select a template:', 'thesis');
		foreach ($this->active as $name => $template)
			if ($name != $this->template['id'])
				$options[$name] = !empty($template['title']) ? $template['title'] : (!empty($this->core[$name]['title']) ? $this->core[$name]['title'] : $name);
		$form = $thesis->api->form->fields(array(
			'copy_from' => array(
				'type' => 'select',
				'options' => $options)), array(), false, false, $this->tabindex, $depth + 1);
	 	return
			"$tab<form method=\"post\" action=\"\">\n".
			$form['output'].
			"$tab\t<p>\n".
			"$tab\t\t<input type=\"hidden\" id=\"copy_to\" name=\"template\" value=\"{$this->template['id']}\" />\n".
			"$tab\t\t<input type=\"submit\" id=\"copy_template\" data-style=\"button save\" name=\"copy_template\" value=\"" . __('Copy Template', 'thesis') . "\" />\n".
			"$tab\t</p>\n".
			"$tab</form>\n";
	}

	private function options($depth) {
		global $thesis;
		$tab = str_repeat("\t", $depth);
		$menu = $panes = array();
		$title = ": {$this->template['title']}";
		$name = false;
		if ($custom = in_array($this->template['id'], $this->custom['names'])) {
			$title = '';
			$name = array(
				'id' => 'template_title',
				'name' => 'title',
				'value' => $this->template['title'],
				'tabindex' => $this->tabindex);
			$this->tabindex++;
		}
		foreach ($this->options as $class => $options)
			if (is_array($options)) {
				$options['exclude'] = !empty($options['exclude']) && is_array($options['exclude']) ? $options['exclude'] : array();
				// what to exclude
				$e = array();
				if (!empty($this->template['id']))
					$e[] = $this->template['id'];
				if (!empty($this->core[$this->template['id']]['parent']))
					$e[] = $this->core[$this->template['id']]['parent'];
				if (!empty($custom))
					$e[] = 'custom';
				if ((!empty($options['exclude']) && !array_intersect($e, $options['exclude'])) || empty($options['exclude'])) {
					$fields = array();
					$fields = $thesis->api->form->fields($options['fields'], (!empty($this->template['options'][$class]) ? $this->template['options'][$class] : false), "template_{$class}_",  "options[$class]", $this->tabindex, $depth + 4);
					$this->tabindex = $fields['tabindex'];
					$menu[$class] = $options['title'];
					$panes[$class] = $fields['output'];
				}
			}
		return $thesis->api->popup(array(
			'id' => 'template',
			'type' => 'template',
			'title' => __('Template', 'thesis') . $title,
			'name' => $name,
			'menu' => $menu,
			'panes' => $panes));
	}

	public function save($form) {
		global $thesis;
		if (!is_array($form) || empty($form['template'])) return false;
		$save = $new = array();
		$box_form = $thesis->api->get_box_form();
		$id = $form['template'];
		$values = is_array($form['options']) ? $form['options'] : array();
		$title = !empty($form['title']) ? $form['title'] : false;
		if (!empty($title))
			$save['title'] = $title;
		elseif (!empty($this->active[$id]['title']))
			$save['title'] = $this->active[$id]['title'];
		foreach ($this->get_options() as $class => $options)
			if (is_array($options) && $new[$class] = $thesis->api->set_options($options['fields'], !empty($values[$class]) ? $values[$class] : false))
				$save['options'][$class] = $new[$class];
		if (is_array($boxes = $box_form->save($form))) {
			if (is_array($boxes['boxes']) && !empty($boxes['boxes']))
				$save['boxes'] = $boxes['boxes'];
			if (is_array($boxes['delete']))
				foreach ($boxes['delete'] as $box)
					$this->remove_box($box);
		}
		if (empty($save))
			unset($this->active[$id]);
		else
			$this->active[$id] = $save;
		return array(
			'templates' => $this->active,
			'delete_boxes' => $boxes['delete']);
	}

	private function remove_box($id) {
		if (!is_array($this->active)) return;
		foreach ($this->active as $name => $template)
			if (is_array($template['boxes']))
				foreach ($template['boxes'] as $rotator => $boxes)
					if ($rotator == $id)
						unset($this->active[$name]['boxes'][$rotator]);
					elseif (is_array($boxes))
						foreach ($boxes as $position => $box)
							if ($box == $id)
								unset($this->active[$name]['boxes'][$rotator][$position]);
	}

	public function create($title) {
		if (empty($title)) return false;
		$id = 'custom_' . time();
		$template[$id] = array('title' => $title);
		$this->active = array_merge($this->active, $template);
		return array(
			'id' => $id,
			'templates' => $this->active);
	}

	public function delete($template) {
		if (empty($template) || empty($this->active[$template])) return false;
		unset($this->active[$template]);
		return $this->active;
	}

	public function copy($to, $from) {
		if (empty($to) || empty($from) || empty($this->active[$from])) return false;
		$this->active[$to]['options'] = $this->active[$from]['options'];
		$this->active[$to]['boxes'] = $this->active[$from]['boxes'];
		return $this->active;
	}

	public function head($form) {
		global $thesis;
		if (!is_array($form)) return;
		$tab = str_repeat("\t", $depth = 2);
		$box_form = $thesis->api->get_box_form();
		return
			"$tab<h3>" . sprintf(__('%1$s %2$s', 'thesis'), $thesis->api->strings['html_head'], $thesis->api->strings['editor']) . "</h3>\n".
			"$tab<form id=\"t_boxes\" method=\"post\" action=\"\" enctype=\"multipart/form-data\">\n".
			$box_form->body(array_merge($form, array('tabindex' => 10, 'depth' => $depth + 1))).
			"$tab\t<input type=\"submit\" data-style=\"button save\" class=\"t_save\" id=\"save_head\" name=\"save_head\" value=\"" . __('Save HTML Head', 'thesis') . "\" />\n".
			"$tab\t\t" . wp_nonce_field('thesis-save-head', '_wpnonce-thesis-ajax', true, false) . "\n".
			"$tab</form>\n";
	}

	public function save_head($form) {
		global $thesis;
		if (!is_array($form)) return false;
		$box_form = $thesis->api->get_box_form();
		if (is_array($head = $box_form->save($form))) {
			if (is_array($head['boxes']))
				if (empty($head['boxes']))
					delete_option('thesis_head');
				else
					update_option('thesis_head', $head['boxes']);
			return is_array($head['delete']) ? $head['delete'] : true;
		}
		else
			return false;
	}
}