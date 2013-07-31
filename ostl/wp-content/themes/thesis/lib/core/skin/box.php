<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_box {
	// optional properties for box extensions (to be defined by developer as necessary)
	public $type = 'box';					// (string) possible types: box, rotator, false (false is like a plugin)
	public $title = false;					// (string) required for extensions of type 'box' and 'rotator'; must be defined in translate() for translation
	public $name = false;					// (string) Is this a multiple instance box? If so, give it a name; must be defined in translate() for translation
	public $root = false;					// (bool) Is this box a root box? Currently, only <head> and <body> should be considered roots
	public $head = false;					// (bool) True = box goes in the <head>; false = <body>.
	public $dependents = false;				// (array) class names of dependent boxes
	public $children = false;				// (array) class names of dependent boxes that are active inside the parent when it is first added to the interface
	public $switch = false;					// (bool) Set to true if the box contents should be visible on admin page load (rotators only).
	public $templates = array(				// (array) Top-level, core templates for which this box is valid. Boxes are visible on all templates unless you specify otherwise.
		'home',
		'single',
		'page',
		'archive');
	// critical reserved properties set by the box constructor
	public $_class;							// (string) quick reference for this box's class name
	public $_id;							// (string) unique identifier for this box
	// reserved properties set by the box constructor and NOT intended for use by box extensions
	public $_parent = false;				// (string) unique ID of parent box
	public $_lineage = false;				// (string) breadcrumb-style ID showing parent/child box relationships
	public $_admin = array();				// (array) contains admin option fields, where applicable (rotators only)
	public $_uploader = array();			// (array) contains uploader options
	public $_switch = false;				// (bool) toggle the rotator by default on the template editor?
	public $_menu = false;					// (array) admin page/menu properties, if applicable
	// reserved properties set by the constructor and intended for use in box extensions
	public $options = array();				// (array) actively-selected options for this box
	public $post_meta = array();			// (array) this box's post meta data for the current page, if it exists
	public $term_options = array();			// (array) this box's term options data for the current term, if it exists
	public $template_options = array();		// (array) this box's template options data for the current template, if it exists

	public function __construct($box = array()) {
		global $thesis;
		extract($box); // $id, $options, $parent, $lineage, $check
		$this->_class = strtolower(get_class($this));
		$this->_id = !empty($id) ? $id : $this->_class;
		if (method_exists($this, 'translate'))
			$this->translate();
		if (!empty($parent)) {
			$this->_id = !empty($id) ? $this->_id : "{$parent}_$this->_id";
			$this->_parent = $parent;
			$this->_lineage = !empty($lineage) ? $lineage : $this->_lineage;
		}
		$this->options = !empty($options) && is_array($options) ? $options : $this->options;
		$this->name = !empty($this->options['_name']) ? stripslashes($this->options['_name']) : $this->name;
		$this->_admin = is_array($admin_options = $this->_admin()) ? $admin_options : $this->_admin;
		$this->_uploader = method_exists($this, 'uploader') && is_array($uploader = $this->uploader()) ? $uploader : $this->_uploader;
		$this->_switch = isset($this->options['_admin']['open']) ? (bool) $this->options['_admin']['open'] : $this->switch;
		if (!empty($this->dependents) && is_array($this->dependents))
			$this->dependents = is_array($dependents = apply_filters("{$this->_class}_dependents", $this->dependents)) ? $dependents : $this->dependents;
		if (!empty($this->children) && is_array($this->children))
			$this->children = is_array($children = apply_filters("{$this->_class}_children", $this->children)) ? $children : $this->children;
		if (method_exists($this, 'post_meta')) {
			add_filter('thesis_post_meta', array($this, '_add_post_meta'));
			add_action('thesis_init_post_meta', array($this, '_get_post_meta'));
		}
		if (method_exists($this, 'term_options')) {
			add_filter('thesis_term_options', array($this, '_add_term_options'));
			add_action('thesis_init_term', array($this, '_get_term_options'));
		}
		if (method_exists($this, 'template_options')) {
			add_filter('thesis_template_options', array($this, '_add_template_options'));
			add_action('thesis_init_template', array($this, '_get_template_options'));
			add_action('thesis_init_custom_template', array($this, '_get_template_options'));
		}
		if (method_exists($this, 'preload'))
			add_filter('thesis_template_preload', array($this, '_preload'));
		if ($thesis->environment == 'admin' && method_exists($this, 'admin') && is_array($admin = $this->admin()) && !empty($admin['page'])) {
			$this->_menu[$this->_class] = array(
				'text' => !empty($admin['text']) ? $admin['text'] : $this->title,
				'url' => admin_url("admin.php?page=thesis&canvas=$this->_class"));
			if (!in_array($this->_class, $thesis->box_admin)) {
				add_filter(!empty($admin['menu']) ? ($admin['menu'] == 'site' ?
					'thesis_site_menu' : ($admin['menu'] == 'skins' ?
					'thesis_skins_menu' : 'thesis_boxes_menu')) :
					'thesis_boxes_menu', array($this, '_add_menu'));
				if (!empty($_GET['canvas']) && $_GET['canvas'] == $this->_class && method_exists($this, $admin['page'])) {
					add_action('thesis_admin_canvas', array($this, $admin['page']));
					if (method_exists($this, 'admin_init'))
						$this->admin_init();
				}
				$thesis->box_admin[] = $this->_class;
			}
		}
		if ($thesis->environment == 'ajax' && method_exists($this, 'admin_ajax'))
			$this->admin_ajax();
		$this->construct();
	}

	private function _admin() {
		return $this->type == 'rotator' && !$this->root ? array(
			'_admin' => array(
				'type' => 'checkbox',
				'label' => __('Admin Visibility', 'thesis'),
				'options' => array(
					'open' => __('Box is open by default (template editor only)', 'thesis')),
				'default' => array(
					'open' => (bool) $this->switch))) : false;
	}

	public function _get_options() {
		return method_exists($this, 'options') && is_array($options = apply_filters("{$this->_class}_options", $this->options())) ? $options : array();
	}

	public function _add_post_meta($post_meta) {
		return is_array($options[$this->_class] = apply_filters("{$this->_class}_post_meta", $this->post_meta())) ? (is_array($post_meta) ? array_merge($post_meta, $options) : $options) : $post_meta;
	}

	public function _add_term_options($term_options) {
		return is_array($options[$this->_class] = apply_filters("{$this->_class}_term_options", $this->term_options())) ? (is_array($term_options) ? array_merge($term_options, $options) : $options) : $term_options;
	}

	public function _add_template_options($template_options) {
		return is_array($options[$this->_class] = apply_filters("{$this->_class}_template_options", $this->template_options())) ? (is_array($template_options) ? array_merge($template_options, $options) : $options) : $template_options;
	}

	public function _get_post_meta($post_id) {
		$this->post_meta = !is_numeric($post_id) || !is_array($post_meta = get_post_meta($post_id, "_{$this->_class}", true)) ? array() : $post_meta;
	}

	public function _get_term_options($term_id) {
		global $thesis;
		if (!is_numeric($term_id) || empty($thesis->wp->terms[$term_id][$this->_class]) || (!empty($thesis->wp->terms[$term_id][$this->_class]) && !is_array($thesis->wp->terms[$term_id][$this->_class]))) return;
		$this->term_options = $thesis->wp->terms[$term_id][$this->_class];
	}

	public function _get_template_options($template) {
		if (!is_array($template) || (!empty($template['options'][$this->_class]) && !is_array($template['options'][$this->_class]))) return;
		$this->template_options = !empty($template['options'][$this->_class]) ? $template['options'][$this->_class] : false;
	}

	public function _preload($boxes) {
		$boxes[] = $this->_id;
		return $boxes;
	}

	public function _add_menu($menu) {
		return is_array($this->_menu) ? (is_array($menu) ? array_merge($menu, $this->_menu) : $this->_menu) : $menu;
	}

	public function _save($form) {
		global $thesis;
		if (empty($form) || !is_array($form) || empty($form[$this->_class][$this->_id]) || !is_array($values = $form[$this->_class][$this->_id])) return false;
		$box = array();
		if (is_array($options = $thesis->api->set_options(array_merge($this->_get_options(), $this->_admin, $this->_uploader), !empty($values) ? $values : array())))
			$box = array_merge($box, $options);
		if ($this->name)
			$box['_name'] = !empty($values['_name']) ? $values['_name'] : $this->name;
		if (empty($box)) return 'delete';
		if ($this->_parent)
			$box['_parent'] = $this->_parent;
		return $box;
	}

	protected function construct() {
		// secondary constructor for boxes that need to initiate things before the page loads
	}

	protected function rotator($args = false) {
		global $thesis;
		if (!empty($thesis->skin->_template['boxes'][$this->_id]) && is_array($thesis->skin->_template['boxes'][$this->_id]))
			foreach ($thesis->skin->_template['boxes'][$this->_id] as $box)
				if (!empty($thesis->skin->_boxes->active[$box]) && is_object($thesis->skin->_boxes->active[$box]) && method_exists($thesis->skin->_boxes->active[$box], 'html')) {
					$args = func_get_args();
					call_user_func_array(array($thesis->skin->_boxes->active[$box], 'html'), $args);
				}
	}

	public function html() {
		// This method determines the box's HTML output and should be overwritten by box extensions.
	}
}

class thesis_html_head extends thesis_box {
	public $type = 'rotator';
	public $root = true;
	public $head = true;

	protected function translate() {
		$this->title = __('Head', 'thesis');
	}

	public function html() {
		global $thesis;
		$attributes = apply_filters('thesis_head_attributes', '');
		$attributes = !empty($attributes) ? " $attributes" : '';
		echo "<head$attributes>\n";
		$this->rotator();
		do_action('_thesis_head_scripts');
		do_action('thesis_hook_head'); #wp
		echo "</head>\n";
	}
}

class thesis_title_tag extends thesis_box {
	public $head = true;
	private $separator = '&#8212;';

	protected function translate() {
		global $thesis;
		$this->title = $thesis->api->strings['title_tag'];
	}

	protected function options() {
		global $thesis;
		return array(
			'branded' => array(
				'type' => 'checkbox',
				'label' => sprintf(__('%s Branding', 'thesis'), $this->title),
				'options' => array(
					'on' => sprintf(__('Append site name to <code>&lt;title&gt;</code> tags %s', 'thesis'), $thesis->api->strings['not_recommended']))),
			'separator' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => $thesis->api->strings['character_separator'],
				'tooltip' => __('This character will appear between the title and site name (where appropriate).', 'thesis'),
				'placeholder' => $this->separator));
	}

	protected function post_meta() {
		global $thesis;
		return array(
			'title' => $this->title,
			'fields' => array(
				'title' => array(
					'type' => 'text',
					'width' => 'full',
					'label' => sprintf(__('Custom %s', 'thesis'), $this->title),
					'tooltip' => sprintf(__('By default, Thesis uses the title of your post as the contents of the %1$s tag. You can override this and further extend your on-page %2$s by entering your own %1$s tag here.', 'thesis'), '<code>&lt;title&gt;</code>', $thesis->api->base['seo']),
					'counter' => $thesis->api->strings['title_counter'],
					'legacy' => 'thesis_title')));
	}

	protected function term_options() {
		global $thesis;
		return array(
			'title' => array(
				'type' => 'text',
				'label' => $this->title,
				'counter' => $thesis->api->strings['title_counter']));
	}

	public function html() {
		global $thesis, $wp_query; #wp
		$site = get_bloginfo('name'); #wp
		$separator = !empty($this->options['separator']) ? trim($this->options['separator']) : $this->separator;
		$title = !empty($this->post_meta['title']) ?
			$this->post_meta['title'] : (!empty($this->term_options['title']) ?
			$this->term_options['title'] : (!!$wp_query->is_home || is_front_page() ? (!empty($thesis->wp->home->seo['title']) ?
			$thesis->wp->home->seo['title'] : (($tagline = get_bloginfo('description')) ?
			"$site $separator $tagline" :
			$site)) : (!!$wp_query->is_search ?
			$thesis->api->strings['search'] . ': ' . esc_html($wp_query->query_vars['s']) :
			wp_title('', false))));
		$title .= ($wp_query->query_vars['paged'] > 1 ?
			" $separator {$thesis->api->strings['page']} {$wp_query->query_vars['paged']}" : '').
			(!empty($this->options['branded']['on']) && !$wp_query->is_home ?
			" $separator $site" : '');
		echo '<title>' . trim($thesis->api->escht(apply_filters($this->_class, stripslashes($title), stripslashes($separator)))) . "</title>\n";
	}
}

class thesis_meta_description extends thesis_box {
	public $head = true;

	protected function translate() {
		global $thesis;
		$this->title = $thesis->api->strings['meta_description'];
	}

	protected function post_meta() {
		global $thesis;
		return array(
			'title' => $this->title,
			'fields' => array(
				'description' => array(
					'type' => 'textarea',
					'rows' => 2,
					'label' => $this->title,
					'tooltip' => sprintf(__('Entering a %1$s description is just one more thing you can do to seize an on-page %2$s opportunity. Keep in mind that a good %1$s description is both informative and concise.', 'thesis'), '<code>&lt;meta&gt;</code>', $thesis->api->base['seo']),
					'counter' => $thesis->api->strings['description_counter'],
					'legacy' => 'thesis_description')));
	}

	protected function term_options() {
		global $thesis;
		return array(
			'description' => array(
				'type' => 'textarea',
				'rows' => 2,
				'label' => $this->title,
				'counter' => $thesis->api->strings['description_counter']));
	}

	public function html() {
		global $thesis, $wp_query;
		$description = !empty($this->post_meta['description']) ?
			$this->post_meta['description'] : (!empty($this->term_options['description']) ?
			$this->term_options['description'] : (!!$wp_query->is_home ? (!empty($thesis->wp->home->seo['description']) ?
			$thesis->wp->home->seo['description'] :
			get_bloginfo('description')) : false));
		$description = apply_filters($this->_class, stripslashes($description));
		if (!empty($description))
			echo "<meta name=\"description\" content=\"" . trim($thesis->api->escht($description)) . "\" />\n";
	}
}

class thesis_meta_keywords extends thesis_box {
	public $head = true;

	protected function translate() {
		global $thesis;
		$this->title = $thesis->api->strings['meta_keywords'];
	}

	protected function options() {
		global $thesis;
		return array(
			'tags' => array(
				'type' => 'checkbox',
				'options' => array(
					'on' => sprintf(__('Automatically use tags as keywords on posts %s', 'thesis'), $thesis->api->strings['not_recommended']))));
	}

	protected function post_meta() {
		global $thesis;
		return array(
			'title' => $this->title,
			'fields' => array(
				'keywords' => array(
					'type' => 'text',
					'width' => 'full',
					'label' => $this->title,
					'tooltip' => sprintf(__('Like the %1$s description, %1$s keywords are yet another on-page %2$s opportunity. Enter a few keywords that are relevant to your article, but don&#8217;t go crazy here&#8212;just a few should suffice.', 'thesis'), '<code>&lt;meta&gt;</code>', $thesis->api->base['seo']),
					'legacy' => 'thesis_keywords')));
	}

	protected function term_options() {
		return array(
			'keywords' => array(
				'type' => 'text',
				'label' => $this->title));
	}

	public function html() {
		global $thesis, $wp_query;
		$keywords = !empty($this->post_meta['keywords']) ?
			$this->post_meta['keywords'] : (!empty($this->term_options['keywords']) ?
			$this->term_options['keywords'] : (!!$wp_query->is_home && !empty($thesis->wp->home->seo['keywords']) ?
			$thesis->wp->home->seo['keywords'] : false));
		if (empty($keywords) && $wp_query->is_single && !empty($this->options['tags']['on'])) {
			$tags = array();
			if (is_array($post_tags = get_the_tags())) #wp
				foreach ($post_tags as $tag)
					$tags[] = $tag->name;
			if (!empty($tags))
				$keywords = implode(', ', $tags);
		}
		$keywords = apply_filters($this->_class, stripslashes($keywords));
		if (!empty($keywords))
			echo "<meta name=\"keywords\" content=\"" . trim($thesis->api->escht($keywords)) . "\" />\n";
	}
}

class thesis_meta_robots extends thesis_box {
	public $head = true;

	protected function translate() {
		global $thesis;
		$this->title = $thesis->api->strings['meta_robots'];
	}

	protected function construct() {
		add_filter("thesis_term_option_{$this->_class}_robots", array($this, 'get_term_defaults'), 10, 2);
	}

	protected function options() {
		return array(
			'category' => array(
				'type' => 'group',
				'label' => __('Category Pages', 'thesis'),
				'fields' => array(
					'category' => array(
						'type' => 'checkbox',
						'options' => array(
							'noindex' => '<code>noindex</code>',
							'nofollow' => '<code>nofollow</code>',
							'noarchive' => '<code>noarchive</code>')))),
			'tag' => array(
				'type' => 'group',
				'label' => __('Tag Pages', 'thesis'),
				'fields' => array(
					'post_tag' => array(
						'type' => 'checkbox',
						'options' => array(
							'noindex' => '<code>noindex</code>',
							'nofollow' => '<code>nofollow</code>',
							'noarchive' => '<code>noarchive</code>'),
						'default' => array(
							'noindex' => true)))),
			'tax' => array(
				'type' => 'group',
				'label' => __('Custom Taxonomy Pages', 'thesis'),
				'fields' => array(
					'tax' => array(
						'type' => 'checkbox',
						'options' => array(
							'noindex' => '<code>noindex</code>',
							'nofollow' => '<code>nofollow</code>',
							'noarchive' => '<code>noarchive</code>')))),
			'author' => array(
				'type' => 'group',
				'label' => __('Author Pages', 'thesis'),
				'fields' => array(
					'author' => array(
						'type' => 'checkbox',
						'options' => array(
							'noindex' => '<code>noindex</code>',
							'nofollow' => '<code>nofollow</code>',
							'noarchive' => '<code>noarchive</code>'),
						'default' => array(
							'noindex' => true)))),
			'day' => array(
				'type' => 'group',
				'label' => __('Daily Archive Pages', 'thesis'),
				'fields' => array(
					'day' => array(
						'type' => 'checkbox',
						'options' => array(
							'noindex' => '<code>noindex</code>',
							'nofollow' => '<code>nofollow</code>',
							'noarchive' => '<code>noarchive</code>'),
						'default' => array(
							'noindex' => true)))),
			'month' => array(
				'type' => 'group',
				'label' => __('Monthly Archive Pages', 'thesis'),
				'fields' => array(
					'month' => array(
						'type' => 'checkbox',
						'options' => array(
							'noindex' => '<code>noindex</code>',
							'nofollow' => '<code>nofollow</code>',
							'noarchive' => '<code>noarchive</code>'),
						'default' => array(
							'noindex' => true)))),
			'year' => array(
				'type' => 'group',
				'label' => __('Yearly Archive Pages', 'thesis'),
				'fields' => array(
					'year' => array(
						'type' => 'checkbox',
						'options' => array(
							'noindex' => '<code>noindex</code>',
							'nofollow' => '<code>nofollow</code>',
							'noarchive' => '<code>noarchive</code>'),
						'default' => array(
							'noindex' => true)))),
			'sub' => array(
				'type' => 'group',
				'label' => __('Blog Sub-pages', 'thesis'),
				'fields' => array(
					'sub' => array(
						'type' => 'checkbox',
						'options' => array(
							'noindex' => '<code>noindex</code>',
							'nofollow' => '<code>nofollow</code>',
							'noarchive' => '<code>noarchive</code>'),
						'default' => array(
							'noindex' => true)))),
			'directory' => array(
				'type' => 'group',
				'label' => __('Directory Tags (Sitewide)', 'thesis'),
				'fields' => array(
					'directory' => array(
						'type' => 'checkbox',
						'options' => array(
							'noodp' => '<code>noodp</code>',
							'noydir' => '<code>noydir</code>'),
						'default' => array(
							'noodp' => true,
							'noydir' => true)))));
	}

	protected function post_meta() {
		global $thesis;
		return array(
			'title' => $this->title,
			'fields' => array(
				'robots' => array(
					'type' => 'checkbox',
					'label' => $this->title,
					'tooltip' => sprintf(__('Fine-tune the %1$s on every page of your site with these handy robots meta tag selectors.', 'thesis'), $thesis->api->base['seo']),
					'options' => array(
						'noindex' => sprintf(__('<code>noindex</code> %s', 'thesis'), $thesis->api->strings['this_page']),
						'nofollow' => sprintf(__('<code>nofollow</code> %s', 'thesis'), $thesis->api->strings['this_page']),
						'noarchive' => sprintf(__('<code>noarchive</code> %s', 'thesis'), $thesis->api->strings['this_page'])),
					'legacy' => 'thesis_robots')));
	}

	protected function term_options() {
		global $thesis;
		return array(
			'robots' => array(
				'type' => 'checkbox',
				'label' => $this->title,
				'options' => array(
					'noindex' => sprintf(__('<code>noindex</code> %s', 'thesis'), $thesis->api->strings['this_page']),
					'nofollow' => sprintf(__('<code>nofollow</code> %s', 'thesis'), $thesis->api->strings['this_page']),
					'noarchive' => sprintf(__('<code>noarchive</code> %s', 'thesis'), $thesis->api->strings['this_page']))));
	}

	public function get_term_defaults($default, $taxonomy) {
		if (empty($taxonomy)) return $default;
		$taxonomy = $taxonomy != 'category' && $taxonomy != 'post_tag' ? 'tax' : $taxonomy;
		return !empty($this->options[$taxonomy]) && is_array($this->options[$taxonomy]) ? $this->options[$taxonomy] : $default;
	}

	public function html() {
		global $thesis, $wp_query;
		if (get_option('blog_public') == 0) return;
		$content = array();
		$options = $thesis->api->get_options($this->_get_options(), $this->options);
		$page_type = $wp_query->is_home && $wp_query->query_vars['paged'] > 1 ?
			'sub' : ($wp_query->is_archive ? ($wp_query->is_category ?
			'category' : ($wp_query->is_tag ?
			'post_tag' : ($wp_query->is_tax ?
			'tax' : ($wp_query->is_author ?
			'author' : ($wp_query->is_day ?
			'day' : ($wp_query->is_month ?
			'month' : ($wp_query->is_year ?
			'year' : false))))))) : false);
		$robots = !empty($this->post_meta['robots']) ?
			$this->post_meta['robots'] : (!empty($this->term_options['robots']) ?
			$this->term_options['robots'] : ($wp_query->is_home && empty($page_type) && !empty($thesis->wp->home->seo['robots']) &&  is_array($thesis->wp->home->seo['robots']) ?
			$thesis->wp->home->seo['robots'] : ($wp_query->is_search || $wp_query->is_404 ?
			array('noindex' => true, 'nofollow' => true, 'noarchive' => true) : (!empty($page_type) && !empty($options[$page_type]) ?
			$options[$page_type] : false))));
		if (!empty($options['directory']['noodp']))
			$robots['noodp'] = true;
		if (!empty($options['directory']['noydir']))
			$robots['noydir'] = true;
		if (!empty($robots) && is_array($robots))
			foreach ($robots as $tag => $value)
				if ($value)
					$content[] = $tag;
		if (!empty($content))
			echo '<meta name="robots" content="' . apply_filters($this->_class, implode(', ', $content)) . "\" />\n";
	}
}

class thesis_meta_charset extends thesis_box {
	public $head = true;

	protected function translate() {
		$this->title = __('Meta Character Encoding', 'thesis');
	}

	public function html() {
		global $thesis;
		echo "<meta charset=\"" . (!empty($thesis->api->options['blog_charset']) ? strtolower($thesis->api->options['blog_charset']) : 'utf-8') . "\" />\n";
	}
}

class thesis_meta_viewport extends thesis_box {
	public $head = true;

	protected function translate() {
		$this->title = __('Meta Viewport', 'thesis');
	}

	public function html() {
		echo "<meta name=\"viewport\" content=\"width=device-width\" />\n";
	}
}

class thesis_meta_verify extends thesis_box {
	public $head = true;

	protected function translate() {
		$this->title = __('Site Verification', 'thesis');
	}

	protected function options() {
		$tooltip = __('For optimal search engine performance, we recommend verifying your site with', 'thesis');
		return array(
			'google' => array(
				'type' => 'text',
				'width' => 'long',
				'label' => __('Google Site Verification', 'thesis'),
				'tooltip' => sprintf(__('%1$s <a href="%2$s" target="_blank">Google Webmaster Tools</a>.', 'thesis'), $tooltip, 'https://www.google.com/webmasters/tools/')),
			'bing' => array(
				'type' => 'text',
				'width' => 'long',
				'label' => __('Bing Site Verification', 'thesis'),
				'tooltip' => sprintf(__('%1$s <a href="%2$s" target="_blank">Bing Webmaster Tools</a>.', 'thesis'), $tooltip, 'http://www.bing.com/toolbox/webmasters/')));
	}

	public function html() {
		global $thesis;
		if (!is_front_page()) return;
		$meta = array();
		if (!empty($this->options['google']))
			$meta['google'] = "<meta name=\"google-site-verification\" content=\"" . trim($thesis->api->esc($this->options['google'])) . "\" />";
		if (!empty($this->options['bing']))
			$meta['bing'] = "<meta name=\"msvalidate.01\" content=\"" . trim($thesis->api->esc($this->options['bing'])) . "\" />";
		if (!empty($meta))
			echo implode("\n", $meta) . "\n";
	}
}

class thesis_stylesheets_link extends thesis_box {
	public $head = true;

	protected function translate() {
		$this->title = __('Stylesheets', 'thesis');
	}

	public function html() {
		$styles = $links = array();
		$styles['layout'] = array(
			'url' => !is_multisite() ? THESIS_USER_SKIN_URL . '/css.css' : home_url("?thesis_do=css&ref=". THESIS_MS_CSS_VAL . time()),
			'media' => 'screen, projection');
		if ($ie_stylesheet = apply_filters('thesis_ie_stylesheet', false)) $styles['ie'] = $ie_stylesheet;
		foreach ($styles as $type => $style)
			$links[$type] = $type == 'ie' ? $style : sprintf('<link rel="stylesheet" href="%1$s" type="text/css" media="%2$s" />', $style['url'], $style['media']);
		if (!empty($links) && !((is_user_logged_in() && current_user_can('edit_theme_options')) && (!empty($_GET['thesis_editor']) && $_GET['thesis_editor'] === '1' || !empty($_GET['thesis_canvas']) && $_GET['thesis_canvas'] === '1')))
			echo implode("\n", $links) . "\n";
	}
}

class thesis_image_uploader_box extends thesis_box {
	protected function translate() {
		$this->title = __('Image Uploader', 'thesis');
	}

	protected function construct() {
		new thesis_upload(array(
			'title' => __('Upload Image', 'thesis'),
			'prefix' => $this->_class,
			'file_type' => 'image',
			'folder' => 'box'));
		add_action("{$this->_class}_before_thesis_iframe_form", array($this, '_script'));
	}

	protected function uploader() {
		return array(
			'image' => array(
				'type' => 'image_upload',
				'label' => $this->title,
				'upload_label' => __('Upload Image', 'thesis'),
				'prefix' => $this->_class));
	}

	protected function options() {
		global $thesis;
		return array(
			'class' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'label' => $thesis->api->strings['html_class'],
				'tooltip' => $thesis->api->strings['class_tooltip'] . $thesis->api->strings['class_note']));
	}

	public function _script() {
		$url = !empty($_GET['url']) ? esc_url(urldecode($_GET['url'])) : (!empty($this->options['image']['url']) ? esc_url($this->options['image']['url']) : false);
		$height = !empty($_GET['height']) ? (int)$_GET['height'] : (!empty($this->options['image']['height']) ? (int)$this->options['image']['height'] : false);
		$width = !empty($_GET['width']) ? (int)$_GET['width'] : (!empty($this->options['image']['width']) ? (int)$this->options['image']['width'] : false);
		if (!!$url)
			echo "<img style=\"max-width: 90%;\" id=\"". esc_attr($this->_id) ."_box_image\" src=\"$url\" />\n";
		if ($url && $height && $width)
			echo
				"<script type=\"text/javascript\">".
					"function fill_inputs(){".
						"parent.document.getElementById('". $this->_class. '_' .$this->_id ."_image_url').value = '$url';".
						"parent.document.getElementById('". $this->_class. '_' .$this->_id ."_image_height').value = '$height';".
						"parent.document.getElementById('". $this->_class. '_' .$this->_id ."_image_width').value = '$width';".
					"}".
					"fill_inputs();".
				"</script>";
	}

	public function html() {
		global $thesis;
		$url = !empty($this->options['image']) && !empty($this->options['image']['url']) ?
			esc_url(stripslashes($this->options['image']['url'])) : false;
		if (!empty($url)) {
			$image['class'] = !empty($this->options['class']) ? 'class="' . $thesis->api->esc($this->class) . '"' : false;
			$image['src'] = "src=\"$url\"";
			$image['width'] = !empty($this->options['image']['width']) ? "width=\"{$this->options['image']['width']}\"" : false;
			$image['height'] = !empty($this->options['image']['height']) ? "height=\"{$this->options['image']['height']}\"" : false;
			echo '<img ' . implode(' ', array_filter($image)) . " />\n";
		}
	}
}

class thesis_favicon extends thesis_image_uploader_box {
	public $head = true;

	protected function translate() {
		$this->title = __('Favicon', 'thesis');
	}

	protected function options() {
		return false;
	}

	public function html() {
		$url = esc_url(empty($this->options['image']) || empty($this->options['image']['url']) ?
			THESIS_IMAGES_URL . '/icon-swatch.png' :
			stripslashes($this->options['image']['url']));
		echo "<link rel=\"shortcut icon\" href=\"$url\" />\n";
	}
}

class thesis_canonical_link extends thesis_box {
	public $head = true;

	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('Canonical %s', 'thesis'), $thesis->api->base['url']);
	}

	protected function post_meta() {
		global $thesis;
		return array(
			'title' => $this->title,
			'fields' => array(
				'url' => array(
					'type' => 'text',
					'width' => 'full',
					'code' => true,
					'label' => sprintf(__('%1$s %2$s', 'thesis'), $this->title, $thesis->api->strings['override']),
					'tooltip' => sprintf(__('Although Thesis auto-generates proper canonical %1$ss for every page of your site, there are certain situations where you may wish to supply your own canonical %1$s for a given page.<br /><br />For example, you may want to run a checkout page with %2$s, and because of this, you may only want this page to be accessible with the %3$s protocol. In this case, you&#8217;d want to supply your own canonical %1$s, which would include %3$s.', 'thesis'), $thesis->api->base['url'], $thesis->api->base['ssl'], '<code>https</code>'),
					'description' => $thesis->api->strings['include_http'],
					'legacy' => 'thesis_canonical')));
	}

	protected function term_options() {
		global $thesis;
		return array(
			'url' => array(
				'type' => 'text',
				'code' => true,
				'label' => sprintf(__('%1$s %2$s', 'thesis'), $this->title, $thesis->api->strings['override']),
				'description' => sprintf(__('Only use this if you need a canonical %s that is different from the Thesis default for this page!', 'thesis'), $thesis->api->base['url'])));
	}

	public function html() {
		global $thesis, $wp_query; #wp
		$url = !empty($this->post_meta['url']) ?
			$this->post_meta['url'] : (!empty($this->term_options['url']) ?
			$this->term_options['url'] : ($wp_query->is_home ? ($wp_query->is_posts_page ?
			get_permalink($wp_query->queried_object->ID) :
			home_url()) : ($wp_query->is_singular ?
			get_permalink() : ($wp_query->is_archive ? ($wp_query->is_category || $wp_query->is_tax || $wp_query->is_tag ?
			get_term_link($wp_query->queried_object, $wp_query->queried_object->taxonomy) : ($wp_query->is_author ?
			get_author_posts_url($wp_query->query_vars['author'], $thesis->wp->author($wp_query->query_vars['author'], 'user_nicename')) : ($wp_query->is_day ?
			get_day_link($wp_query->query_vars['year'], $wp_query->query_vars['monthnum'], $wp_query->query_vars['day']) : $wp_query->is_month ?
			get_month_link($wp_query->query_vars['year'], $wp_query->query_vars['monthnum']) : ($wp_query->is_year ?
			get_year_link($wp_query->query_vars['year']) : false)))) : false))));
		if (!empty($url))
			echo "<link rel=\"canonical\" href=\"" . esc_url(apply_filters($this->_class, stripslashes($url))) . "\" />\n";
	}
}

class thesis_rel_author_link extends thesis_box {
	public $head = true;

	protected function translate() {
		global $thesis;
		$this->title = __('Google Rel Author', 'thesis');
		$this->label = __('Google+ Number', 'thesis');
	}

	protected function construct() {
		add_filter('user_contactmethods', array($this, 'add_gplus'));
	}

	protected function options() {
		return array(
			'gplus' => array(
				'type' => 'text',
				'width' => 'long',
				'label' => $this->label,
				'tooltip' => sprintf(__('If you want the <code>rel="author"</code> tag to show on every page of your site, enter your %1$s here. If you run a multi-author website, be sure to enter each author&#8217;s %1$s on their user profile page.', 'thesis'), $this->label)));
	}

	public function html() {
		global $thesis, $wp_query;
		if (!$this->options['gplus'] && !$wp_query->is_singular) return;
		if ($wp_query->is_singular)
			$gplus = get_user_option('gplus', get_the_author_meta('ID'));
		if (empty($gplus) && $this->options['gplus'])
			$gplus = $this->options['gplus'];
		if (!empty($gplus))
			echo '<link rel="author" href="https://plus.google.com/' . trim($thesis->api->esc($gplus)) . "/\" />\n";
	}

	public function add_gplus($contacts) {
		$contacts['gplus'] = $this->label;
		return $contacts;
	}
}

class thesis_feed_link extends thesis_box {
	public $head = true;

	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('%s Feed', 'thesis'), $thesis->api->base['rss']);
	}

	protected function options() {
		global $thesis;
		return array(
			'url' => array(
				'type' => 'text',
				'width' => 'long',
				'code' => true,
				'label' => sprintf(__('%1$s %2$s', 'thesis'), $this->title, $thesis->api->base['url']),
				'tooltip' => sprintf(__('If you don&#8217;t enter anything in this field, Thesis will use your default WordPress feed, <code>%1$s</code>. If you&#8217;d like to use any other feed, please enter the feed %2$s here.', 'thesis'), esc_url(get_bloginfo(get_default_feed() . '_url')), $thesis->api->base['url'])));
	}

	public function html() {
		echo '<link rel="alternate" type="application/rss+xml" title="' . trim(esc_attr(get_bloginfo('name') . ' ' . __('feed', 'thesis'))) . '" href="' . esc_url(apply_filters($this->_class, !empty($this->options['url']) ? stripslashes($this->options['url']) : get_bloginfo(get_default_feed() . '_url'))) . "\" />\n"; #wp
	}
}

class thesis_pingback_link extends thesis_box {
	public $head = true;

	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('Pingback %s', 'thesis'), $thesis->api->base['url']);
	}

	public function html() {
		echo '<link rel="pingback" href="' . esc_url(get_bloginfo('pingback_url')) . "\" />\n"; #wp
	}
}

class thesis_html_head_scripts extends thesis_box {
	public $head = true;

	protected function translate() {
		$this->title = __('Head Scripts', 'thesis');
	}

	protected function options() {
		return array(
			'scripts' => array(
				'type' => 'textarea',
				'rows' => 4,
				'code' => true,
				'label' => __('Scripts', 'thesis'),
				'tooltip' => __('If you wish to add scripts that will only function properly when placed in the document <code>&lt;head&gt;</code>, you should add them here.<br /><br /><strong>Note:</strong> Only do this if you have no other option. Scripts placed in the <code>&lt;head&gt;</code> can have a negative impact on site performance.', 'thesis'),
				'description' => __('include <code>&lt;script&gt;</code> and other tags as necessary', 'thesis')));
	}

	public function html() {
		if (empty($this->options['scripts'])) return;
		echo trim(stripslashes($this->options['scripts'])) . "\n";
	}
}

class thesis_html_body extends thesis_box {
	public $type = 'rotator';
	public $root = true;
	public $switch = true;

	protected function translate() {
		$this->title = __('Body', 'thesis');
	}

	protected function options() {
		global $thesis;
		return array(
			'class' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'label' => $thesis->api->strings['html_class'],
				'tooltip' => $thesis->api->strings['class_tooltip'] . $thesis->api->strings['class_note']),
			'wp' => array(
				'type' => 'checkbox',
				'label' => __('Automatic WordPress Body Classes', 'thesis'),
				'tooltip' => sprintf(__('WordPress can output body classes that allow you to target specific types of posts and content more easily. You may experience a %1$s naming conflict if you use this option (and most of the output adds unnecessary weight to the %2$s), so we do not recommend it.', 'thesis'), $thesis->api->base['class'], $thesis->api->base['html']),
				'options' => array(
					'auto' => __('Use automatically-generated WordPress body classes', 'thesis'))));
	}

	protected function post_meta() {
		global $thesis;
		return array(
			'title' => __('Custom Body Class', 'thesis'),
			'fields' => array(
				'class' => array(
					'type' => 'text',
					'width' => 'medium',
					'code' => true,
					'label' => $thesis->api->strings['html_class'],
					'tooltip' => sprintf(__('If you want to style this post individually, you should enter a %1$s name here. Anything you enter here will appear on this page&#8217;s <code>&lt;body&gt;</code> tag. Separate multiple classes with spaces.<br /></br /><strong>Note:</strong> %1$s names cannot begin with numbers!', 'thesis'), $thesis->api->base['class']),
					'legacy' => 'thesis_slug')));
	}

	protected function template_options() {
		global $thesis;
		return array(
			'title' => __('Body Class', 'thesis'),
			'fields' => array(
				'class' => array(
					'type' => 'text',
					'width' => 'medium',
					'code' => true,
					'label' => __('Template Body Class', 'thesis'),
					'tooltip' => sprintf(__('If you wish to provide a custom %1$s for this template, you can do that here. Please note that a naming conflict could cause unintended results, so be careful when choosing a %1$s name.', 'thesis'), $thesis->api->base['class']))));
	}

	public function html() {
		global $thesis;
		echo "<body" . $this->classes() . ">\n";
		do_action('_thesis_analytics');
		do_action('thesis_hook_before_html');
		do_action('thesis_hook_body_top');
		$this->rotator();
		do_action('thesis_hook_body_bottom');
		do_action('_thesis_editor_launcher');
		do_action('_thesis_tracking_scripts');
		do_action('_thesis_skin_scripts');
		do_action('thesis_hook_after_html');
		echo "\n</body>\n";
	}

	private function classes() {
		$classes = array();
		if (!empty($this->post_meta['class']))
			$classes[] = trim(stripslashes($this->post_meta['class']));
		if (!empty($this->template_options['class']))
			$classes[] = trim(stripslashes($this->template_options['class']));
		if (!empty($this->options['class']))
			$classes[] = trim(stripslashes($this->options['class']));
		if (!empty($this->options['wp']['auto']))
			$classes = is_array($wp = get_body_class()) ? $classes + $wp : $classes;
		return is_array($filtered = apply_filters("{$this->_class}_class", $classes)) && !empty($filtered) ? ' class="' . trim(esc_attr(implode(' ', $filtered))) . '"' : '';
	}
}

class thesis_html_container extends thesis_box {
	public $type = 'rotator';

	protected function translate() {
		$this->title = $this->name = __('Container', 'thesis');
	}

	protected function options() {
		global $thesis;
		return array_merge($thesis->api->html_options(array(
			'div' => 'div',
			'p' => 'p',
			'section' => 'section',
			'article' => 'article',
			'hgroup' => 'hgroup',
			'header' => 'header',
			'footer' => 'footer',
			'aside' => 'aside',
			'nav' => 'nav',
			'span' => 'span'), 'div'), array(
			'hook' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'label' => $thesis->api->strings['hook_label'],
				'tooltip' => $thesis->api->strings['hook_tooltip_1'] . '<br /><br /><code>thesis_hook_before_container_{name}</code><br /><code>thesis_hook_container_{name}_top</code><br /><code>thesis_hook_container_{name}_bottom</code><br /><code>thesis_hook_after_container_{name}</code><br /><br />' . $thesis->api->strings['hook_tooltip_2'])));
	}

	public function html($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", $depth = !empty($depth) ? $depth : 0);
		$html = !empty($this->options['html']) ? $this->options['html'] : 'div';
		$hook = !empty($this->options['hook']) ? 'container_' . trim($thesis->api->esc($this->options['hook'])) : $this->_id;
		do_action("thesis_hook_before_$hook");
		echo
			"$tab<$html". (!empty($this->options['id']) ? ' id="' . trim($thesis->api->esc($this->options['id'])) . '"' : '').
			(!empty($this->options['class']) ? ' class="' . trim($thesis->api->esc($this->options['class'])) . '"' : '') . ">\n";
		do_action("thesis_hook_{$hook}_top");
		$this->rotator(array_merge($args, array('depth' => $depth + 1)));
		do_action("thesis_hook_{$hook}_bottom");
		echo
			"$tab</$html>\n";
		do_action("thesis_hook_after_$hook");
	}
}

class thesis_site_title extends thesis_box {
	protected function translate() {
		$this->title = __('Site Title', 'thesis');
	}

	protected function options() {
		global $thesis;
		return array(
			'class' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'label' => $thesis->api->strings['html_class'],
				'tooltip' => $thesis->api->strings['class_tooltip'] . $thesis->api->strings['class_note']));
	}

	public function html($args = false) {
		global $thesis, $wp_query; #wp
		extract($args = is_array($args) ? $args : array());
		$html = apply_filters("{$this->_class}_html", $wp_query->is_home || is_front_page() ? 'h1' : 'p'); #wp
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0).
			"<$html id=\"site_title\"" . (!empty($this->options['class']) ? ' class="' . trim($thesis->api->esc($this->options['class'])) . '"' : '') . '>'.
			"<a href=\"" . esc_url(home_url()) . "\">". #wp
			trim($thesis->api->escht(apply_filters($this->_class, get_bloginfo('name')))). #wp
			"</a></$html>\n";
	}
}

class thesis_site_tagline extends thesis_box {
	protected function translate() {
		$this->title = __('Site Tagline', 'thesis');
	}

	public function html($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$html = apply_filters("{$this->_class}_html", 'p');
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0).
			"<$html id=\"site_tagline\">".
			trim($thesis->api->escht(apply_filters($this->_class, get_bloginfo('description')))).
			"</$html>\n";
	}
}

class thesis_wp_nav_menu extends thesis_box {
	protected function translate() {
		global $thesis;
		$this->name = __('Nav Menu', 'thesis');
		$this->title = sprintf(__('%1$s %2$s', 'thesis'), $thesis->api->base['wp'], $this->name);
	}

	protected function options() {
		global $thesis;
		$menus = array();
		foreach (wp_get_nav_menus() as $menu)
			$menus[(int) $menu->term_id] = esc_attr($menu->name);
		return array(
			'menu' => array(
				'type' => 'select',
				'label' => __('Menu To Display', 'thesis'),
				'tooltip' => sprintf(__('Select a WordPress nav menu for this box to display. To edit your menus, visit the <a href="%s" target="_blank">WordPress nav menu editor</a>.', 'thesis'), admin_url('nav-menus.php')),
				'options' => $menus),
			'menu_id' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'label' => $thesis->api->strings['html_id'],
				'tooltip' => $thesis->api->strings['id_tooltip']),
			'menu_class' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'label' => $thesis->api->strings['html_class'],
				'tooltip' => sprintf(__('By default, this menu will render with a %1$s of <code>menu</code>, but if you&#8217;d prefer to use a different %1$s, you can supply one here.%2$s', 'thesis'), $thesis->api->base['class'], $thesis->api->strings['class_note']),
				'placeholder' => 'menu'));
	}

	public function html($args = false) {
		extract($args = is_array($args) ? $args : array());
		echo str_repeat("\t", !empty($depth) ? $depth : 0) . wp_nav_menu(array_merge($this->options, array('container' => false, 'echo' => false))) . "\n"; #wp
	}
}

class thesis_wp_loop extends thesis_box {
	public $type = 'rotator';
	public $switch = true;

	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('%s Loop', 'thesis'), $thesis->api->base['wp']);
	}

	protected function construct() {
		add_filter('thesis_query', array($this, 'query'));
	}

	protected function term_options() {
		global $thesis;
		return array(
			'posts_per_page' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => $thesis->api->strings['posts_to_show'],
				'default' => get_option('posts_per_page')));
	}

	protected function template_options() {
		global $thesis;
		return array(
			'title' => $this->title,
			'exclude' => array('single', 'page'),
			'fields' => array(
				'posts_per_page' => array(
					'type' => 'text',
					'width' => 'tiny',
					'label' => $thesis->api->strings['posts_to_show'],
					'default' => get_option('posts_per_page'))));
	}

	public function query($query) {
		$posts_per_page = !empty($this->term_options['posts_per_page']) && is_numeric($this->term_options['posts_per_page']) ?
			$this->term_options['posts_per_page'] : (!empty($this->template_options['posts_per_page']) && is_numeric($this->template_options['posts_per_page']) ?
			$this->template_options['posts_per_page'] : false);
		if ($posts_per_page)
			$query->query_vars['posts_per_page'] = $posts_per_page;
		return $query;
	}

	public function html($args = false) {
		global $thesis, $wp_query, $post;
		extract($args = is_array($args) ? $args : array());
		$post_count = 1;
		if (!have_posts() && $wp_query->is_404)
			$wp_query = apply_filters('thesis_404', $wp_query);
		if (have_posts())
			while (have_posts()) {
				the_post();
				if (!$wp_query->is_singular)
					do_action('thesis_init_post_meta', $post->ID);
				$this->rotator(array_merge($args, array('post_count' => $post_count)));
				$post_count++;
			}
		elseif (!$wp_query->is_404)
			do_action('thesis_empty_loop');
	}
}

class thesis_post_box extends thesis_box {
	public $type = 'rotator';
	public $dependents = array(
		'thesis_post_headline',
		'thesis_post_date',
		'thesis_post_author',
		'thesis_post_author_avatar',
		'thesis_post_author_description',
		'thesis_post_edit',
		'thesis_post_content',
		'thesis_post_excerpt',
		'thesis_post_num_comments',
		'thesis_post_categories',
		'thesis_post_tags',
		'thesis_post_image',
		'thesis_post_thumbnail',
		'thesis_wp_featured_image');
	public $children = array(
		'thesis_post_headline',
		'thesis_post_author',
		'thesis_post_edit',
		'thesis_post_content');

	protected function translate() {
		$this->title = $this->name = __('Post Box', 'thesis');
	}

	protected function options() {
		global $thesis;
		$options = $thesis->api->html_options(array(
			'div' => 'div',
			'section' => 'section',
			'article' => 'article'), 'div');
		unset($options['id']);
		return array_merge($options, array(
			'wp' => array(
				'type' => 'checkbox',
				'label' => $thesis->api->strings['auto_wp_label'],
				'tooltip' => $thesis->api->strings['auto_wp_tooltip'],
				'options' => array(
					'auto' => $thesis->api->strings['auto_wp_option']),
				'default' => array(
					'auto' => true)),
			'schema' => array(
				'type' => 'select',
				'label' => $thesis->api->schema['schema'],
				'tooltip' => $thesis->api->schema['tooltip'],
				'options' => $thesis->api->schema['options']),
			'hook' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'label' => $thesis->api->strings['hook_label'],
				'tooltip' => $thesis->api->strings['hook_tooltip_1'] . '<br /><br /><code>thesis_hook_before_post_box_{name}</code><br /><code>thesis_hook_post_box_{name}_top</code><br /><code>thesis_hook_post_box_{name}_bottom</code><br /><code>thesis_hook_after_post_box_{name}</code><br /><br />' . $thesis->api->strings['hook_tooltip_2'])));
	}

	public function html($args = false) {
		global $thesis, $wp_query, $post; #wp
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", $depth = !empty($depth) ? $depth : 0);
		$post_count = !empty($post_count) ? $post_count : false;
		$html = !empty($this->options['html']) ? $this->options['html'] : 'div';
		$classes[] = !empty($this->options['class']) ? trim(stripslashes($this->options['class'])) : 'post_box';
		if (empty($post_count) || $post_count == 1)
			$classes[] = 'top';
		if (!isset($this->options['wp']['auto']))
			$classes = is_array($wp = get_post_class()) ? $classes + $wp : $classes;
		$schema = !empty($this->options['schema']) ? $this->options['schema'] : false;
		$hook = !empty($this->options['hook']) ? 'post_box_' . trim($thesis->api->esc($this->options['hook'])) : $this->_id;
		do_action("thesis_hook_before_$hook", $post_count);
		echo "$tab<$html" . ($wp_query->is_404 ? '' : " id=\"post-$post->ID\"") . ' class="' . trim(esc_attr(implode(' ', $classes))) . '"' . ($schema ? ' itemscope itemtype="' . esc_url($thesis->api->schema['itemtype'][$schema]) . '"' : '') . ">\n"; #wp
		do_action("thesis_hook_{$hook}_top", $post_count);
		$this->rotator(array_merge($args, array('depth' => $depth + 1, 'schema' => $schema)));
		do_action("thesis_hook_{$hook}_bottom", $post_count);
		echo "$tab</$html>\n";
		do_action("thesis_hook_after_$hook", $post_count);
	}
}

class thesis_post_headline extends thesis_box {
	protected function translate() {
		$this->title = __('Headline', 'thesis');
	}

	protected function options() {
		global $thesis;
		$options = $thesis->api->html_options(array(
			'h1' => 'h1',
			'h2' => 'h2',
			'h3' => 'h3',
			'h4' => 'h4',
			'p' => 'p',
			'span' => 'span'), 'h1');
		$options['class']['tooltip'] = sprintf(__('This box already contains a %1$s called <code>headline</code>. If you wish to add an additional %1$s, you can do that here. Separate multiple %1$ses with spaces.%2$s', 'thesis'), $thesis->api->base['class'], $thesis->api->strings['class_note']);
		unset($options['id']);
		return array_merge($options, array(
			'link' => array(
				'type' => 'checkbox',
				'options' => array(
					'on' => __('Link headline to article page', 'thesis')))));
	}

	public function html($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$html = !empty($this->options['html']) ? $this->options['html'] : 'h1';
		$class = !empty($this->options['class']) ? " {$thesis->api->esc($this->options['class'])}" : '';
	 	echo
			str_repeat("\t", !empty($depth) ? $depth : 0).
			"<$html class=\"headline$class\"" . (!empty($schema) ? ' itemprop="name"' : '') . '>'.
			(!empty($this->options['link']['on']) ? #wp
			'<a href="' . get_permalink() . '" rel="bookmark">' . get_the_title() . '</a>' : #wp
			get_the_title()). #wp
			"</$html>\n";
	}
}

class thesis_post_author extends thesis_box {
	protected function translate() {
		$this->title = __('Author', 'thesis');
	}

	protected function options() {
		global $thesis;
		return array(
			'intro' => array(
				'type' => 'text',
				'width' => 'short',
				'label' => __('Author Intro Text', 'thesis'),
				'tooltip' => sprintf(__('Any text you supply here will be wrapped in %s, like so:<br /><code>&lt;span class="author_by"&gt</code>your text<code>&lt;/span&gt;</code>.', 'thesis'), $thesis->api->base['html'])),
			'class' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'label' => $thesis->api->strings['html_class'],
				'tooltip' => sprintf(__('This box already contains a %1$s of <code>post_author</code>. If you&#8217;d like to supply another %1$s, you can do that here.%2$s', 'thesis'), $thesis->api->base['class'], $thesis->api->strings['class_note'])),
			'link' => array(
				'type' => 'checkbox',
				'options' => array(
					'on' => __('Link author names to archives', 'thesis')),
				'dependents' => array(
					'on' => 'true')),
			'nofollow' => array(
				'type' => 'checkbox',
				'options' => array(
					'nofollow' => __('Add <code>nofollow</code> to author link', 'thesis')),
				'parent' => array(
					'link' => 'on')));
	}

	public function html($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$author = !empty($this->options['link']['on']) ?
			'<a href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '"' . (!empty($this->options['link']['nofollow']) ? ' rel="nofollow"' : '') . '>' . get_the_author() . '</a>' :
			get_the_author(); #wp
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0) . (!empty($this->options['intro']) ?
			'<span class="author_by">' . $thesis->api->esch($this->options['intro']) . '</span> ' : '') . apply_filters($this->_class,
			'<span class="post_author' . (!empty($this->options['class']) ? ' ' . $thesis->api->esc($this->options['class']) : '') . '"' . (!empty($schema) ? ' itemprop="author"' : '') . ">{$author}</span>") . "\n";
	}
}

class thesis_post_author_avatar extends thesis_box {
	protected function translate() {
		$this->title = __('Author Avatar', 'thesis');
	}

	protected function options() {
		global $thesis;
		return array(
			'size' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => $thesis->api->strings['avatar_size'],
				'tooltip' => __('Your author avatars will display at the size you enter here. If you enter nothing, your avatars will be 96px square. Please note that avatars will always be returned as square images (eg. 96&times;96 pixels).', 'thesis'),
				'description' => 'px'),
/*			'default_avatar' => array(
				'type' => 'image',
				'upload_label' => __('Upload a Default Avatar Image', 'thesis'),
				'tooltip' => __('All avatars are output with square dimensions, so in order to ensure proper display, please upload an image with square dimensions.', 'thesis'),
				'label' => __('Default Avatar Image URL', 'thesis'))*/);
	}

	public function html($args = false) {
		global $post;
		extract($args = is_array($args) ? $args : array());
		echo str_repeat("\t", !empty($depth) ? $depth : 0) . get_avatar(
			$post->post_author,
			!empty($this->options['size']) && is_numeric($this->options['size']) ? $this->options['size'] : false,
			!empty($this->options['default_avatar']) && is_array($default = $this->options['default_avatar']) ? $default['url'] : false) . "\n";
	}
}

class thesis_post_author_description extends thesis_box {
	protected function translate() {
		$this->title = __('Author Description', 'thesis');
	}

	protected function construct() {
		global $thesis;
		$thesis->wp->filter($this->_class, array(
			'wptexturize' => false,
			'convert_smilies' => false,
			'convert_chars' => false,
			'wpautop' => false,
			'shortcode_unautop' => false,
			'do_shortcode' => false));
	}

	protected function options() {
		return array(
			'intro' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('Description Intro Text', 'thesis'),
				'placeholder' => __('About the author:', 'thesis')));
	}

	public function html($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0).
			'<p class="author_description">'.
			(!empty($this->options['intro']) ?
			'<span class="author_intro">' . trim($thesis->api->escht($this->options['intro'], true)) . '</span> ' : '').
			trim(apply_filters($this->_class, get_the_author_meta('user_description', get_the_author_meta('ID')))).
			"</p>\n";
	}
}

class thesis_post_date extends thesis_box {
	protected function translate() {
		$this->title = __('Date', 'thesis');
	}

	protected function options() {
		global $thesis;
		return array(
			'intro' => array(
				'type' => 'text',
				'width' => 'short',
				'label' => __('Date Intro Text', 'thesis'),
				'tooltip' => sprintf(__('Any text you supply here will be wrapped in %s, like so:<br /><code>&lt;span class="date_on"&gt</code>your text<code>&lt;/span&gt;</code>.', 'thesis'), $thesis->api->base['html']),
				'placeholder' => __('on', 'thesis')),
			'class' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'label' => $thesis->api->strings['html_class'],
				'tooltip' => sprintf(__('This box already contains a %1$s of <code>post_date</code>. If you&#8217;d like to supply another %1$s, you can do that here.%2$s', 'thesis'), $thesis->api->base['class'], $thesis->api->strings['class_note'])),
			'schema' => array(
				'type' => 'checkbox',
				'label' => __('If a Markup Schema Is Present&hellip;', 'thesis'),
				'tooltip' => __('If a markup schema is present, this box will output the date <code>&lt;meta&gt;</code> automatically. This option is only intended to control whether or not the date actually displays on the page when a schema is present.', 'thesis'),
				'options' => array(
					'only' => sprintf(__('do not show the date, but include the date <code>&lt;meta&gt;</code> in the %s', 'thesis'), $thesis->api->base['html']))));
	}

	public function html($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$time = get_the_time('Y-m-d');
		echo
			(!empty($schema) ?
			"$tab<meta itemprop=\"datePublished\" content=\"$time\" />\n".
			"$tab<meta itemprop=\"dateModified\" content=\"" . get_the_modified_date('Y-m-d') . "\" />\n" : '').
			(empty($schema) || (!empty($schema) && !isset($this->options['schema']['only'])) ?
			$tab . (!empty($this->options['intro']) ?
			'<span class="date_on">' . $thesis->api->esch($this->options['intro']) . '</span> ' : '').
			"<span class=\"post_date" . (!empty($this->options['class']) ? ' ' . $thesis->api->esc($this->options['class']) : '') . "\" title=\"$time\">".
			get_the_time(apply_filters("{$this->_class}_date_format", get_option('date_format'))).
			"</span>\n" : '');
	}
}

class thesis_post_edit extends thesis_box {
	protected function translate() {
		global $thesis;
		$this->title = __('Edit Link', 'thesis');
		$this->edit = strtolower($thesis->api->strings['edit']);
	}

	protected function options() {
		global $thesis;
		return array(
			'text' => array(
				'type' => 'text',
				'label' => sprintf(__('%s Text', 'thesis'), $this->title),
				'tooltip' => sprintf(__('The default edit link text is &lsquo;%s&rsquo;, but you can change that by entering your own text here.', 'thesis'), $this->edit),
				'placeholder' => $this->edit));
	}

	public function html($args = false) {
		global $thesis;
		if (!is_user_logged_in()) return;
		extract($args = is_array($args) ? $args : array());
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0).
			"<a class=\"post_edit\" href=\"" . get_edit_post_link() . "\" title=\"{$thesis->api->strings['click_to_edit']}\" rel=\"nofollow\">".
			trim(apply_filters($this->_class, !empty($this->options['text']) ? $thesis->api->esch($this->options['text']) : $this->edit)).
			"</a>\n";
	}
}

class thesis_post_content extends thesis_box {
	protected function translate() {
		$this->title = __('Content', 'thesis');
		$this->custom = __('Custom &ldquo;Read More&rdquo; Text', 'thesis');
		$this->read_more = __('[click to continue&hellip;]', 'thesis');
	}

	protected function options() {
		global $thesis;
		$options = $thesis->api->html_options();
		unset($options['id']);
		$options['class']['tooltip'] = sprintf(__('This box already contains a %1$s of <code>post_content</code>. If you&#8217;d like to supply another %1$s, you can do that here.%2$s', 'thesis'), $thesis->api->base['class'], $thesis->api->strings['class_note']);
		return array_merge($options, array(
			'read_more' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('&ldquo;Read More&rdquo; Text', 'thesis'),
				'tooltip' => sprintf(__('If you use <code>&lt;!--more--&gt;</code> within your post, the text you enter here will be shown to your visitors to encourage them to click through.<br/><br/>You can override this text on any post or page by filling out the <strong>%s</strong> field on the post editing screen.', 'thesis'), $this->custom),
				'placeholder' => $this->read_more)));
	}

	protected function post_meta() {
		return array(
			'title' => $this->custom,
			'fields' => array(
				'read_more' => array(
					'type' => 'text',
					'width' => 'medium',
					'label' => $this->custom,
					'tooltip' => __('If you use <code>&lt;!--more--&gt;</code> within your post, you can specify custom &ldquo;Read More&rdquo; text here. If you don&#8217;t specify anything, Thesis will use the default text for this box.', 'thesis'),
					'legacy' => 'thesis_readmore')));
	}

	public function html($args = false) {
		global $thesis, $wp_query;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$schema = !empty($schema) ? ' itemprop="' . ($schema == 'article' ? 'articleBody' : 'text') . '"' : '';
		echo "$tab<div class=\"post_content" . (!empty($this->options['class']) ? ' ' . trim($thesis->api->esc($this->options['class'])) : '') . "\"$schema>\n";
		do_action('thesis_hook_before_post');
		the_content(trim($thesis->api->escht(!empty($this->post_meta['read_more']) ? #wp
			$this->post_meta['read_more'] : (!empty($this->options['read_more']) ?
			$this->options['read_more'] :
			$this->read_more), true)));
		if ($wp_query->is_singular) wp_link_pages("<p><strong>{$thesis->api->strings['pages']}:</strong> ", '</p>', 'number'); #wp
		do_action('thesis_hook_after_post');
		echo "$tab</div>\n";
	}
}

class thesis_post_excerpt extends thesis_box {
	protected function translate() {
		$this->title = __('Excerpt', 'thesis');
	}

	protected function options() {
		global $thesis;
		$options = $thesis->api->html_options();
		unset($options['id']);
		return $options;
	}

	public function html($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		echo
			"$tab<div class=\"post_excerpt" . (!empty($this->options['class']) ? ' ' . trim($thesis->api->esc($this->options['class'])) : '') . '"' . (!empty($schema) ? ' itemprop="description"' : '') . ">\n".
			apply_filters($this->_class, get_the_excerpt()) . "\n". #wp
			"$tab</div>\n";
	}
}

class thesis_post_num_comments extends thesis_box {
	protected function translate() {
		$this->title = __('Number of Comments', 'thesis');
	}

	protected function options() {
		global $thesis;
		return array(
			'display' => array(
				'type' => 'checkbox',
				'label' => $thesis->api->strings['display_options'],
				'options' => array(
					'link' => __('Link to comments section', 'thesis'),
					'term' => __('Show term with number (ex: &#8220;5 comments&#8221; instead of &#8220;5&#8221;)', 'thesis'),
					'closed' => __('Display even if comments are closed', 'thesis')),
				'default' => array(
					'link' => true,
					'term' => true,
					'closed' => true),
				'dependents' => array(
					'term' => true)),
			'singular' => array(
				'type' => 'text',
				'label' => $thesis->api->strings['comment_term_singular'],
				'placeholder' => $thesis->api->strings['comment_singular'],
				'parent' => array(
					'display' => 'term')),
			'plural' => array(
				'type' => 'text',
				'label' => $thesis->api->strings['comment_term_plural'],
				'placeholder' => $thesis->api->strings['comment_plural'],
				'parent' => array(
					'display' => 'term')));
	}

	public function html($args = false) {
		global $thesis;
		$options = $thesis->api->get_options($this->_get_options(), $this->options);
		if (!(comments_open() || (!comments_open() && !empty($options['display']['closed'])))) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$number = get_comments_number(); #wp
		echo (!empty($schema) ?
			"$tab<meta itemprop=\"interactionCount\" content=\"UserComments:$number\" />\n" : '').
			$tab . apply_filters($this->_class, (!empty($options['display']['link']) ?
				'<a class="num_comments_link" href="' . get_permalink() . "#comments\" rel=\"nofollow\">" : ''). #wp
				"<span class=\"num_comments\">$number</span>".
				(!empty($options['display']['term']) ?
			 	' ' . trim($thesis->api->esch($number == 1 ? (!empty($options['singular']) ?
				$options['singular'] : $thesis->api->strings['comment_singular']) : (!empty($options['plural']) ?
				$options['plural'] : $thesis->api->strings['comment_plural']))) : '').
				(!empty($options['display']['link']) ?
				'</a>' : '')) . "\n";
	}
}

class thesis_post_categories extends thesis_box {
	protected function translate() {
		$this->title = __('Categories', 'thesis');
	}

	protected function options() {
		global $thesis;
		$options = $thesis->api->html_options(array(
			'p' => 'p',
			'div' => 'div',
			'span' => 'span'), 'p');
		unset($options['id'], $options['class']);
		return array_merge($options, array(
			'intro' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['intro_text'],
				'tooltip' => __('Any intro text you provide will precede the post category output, and it will be wrapped inside <code>&lt;span class="post_cats_intro"&gt;</code>.', 'thesis')),
			'separator' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => $thesis->api->strings['character_separator'],
				'tooltip' => __('If you&#8217;d like to separate your categories with a particular character (a comma, for instance), you can do that here.', 'thesis')),
			'nofollow' => array(
				'type' => 'checkbox',
				'options' => array(
					'on' => __('Add <code>nofollow</code> to category links', 'thesis')))));
	}

	public function html($args = false) {
		global $thesis;
		if (!is_array($categories = get_the_category())) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$cats = array();
		$html = apply_filters("{$this->_class}_html", !empty($this->options['html']) ? $this->options['html'] : 'p');
		$nofollow = !empty($this->options['nofollow']['on']) ? ' nofollow' : '';
		foreach ($categories as $cat)
			$cats[] = "<a href=\"" . esc_url(get_category_link($cat->term_id)) . "\" rel=\"category tag$nofollow\">$cat->name</a>"; #wp
		echo
			"$tab<$html class=\"post_cats\"" . (!empty($schema) ? ' itemprop="keywords"' : '') . ">\n".
			(!empty($this->options['intro']) ?
			"$tab\t<span class=\"post_cats_intro\">" . trim($thesis->api->escht($this->options['intro'], true)) . "</span>\n" : '').
			"$tab\t" . implode((!empty($this->options['separator']) ? trim($thesis->api->esch($this->options['separator'])) : '') . "\n$tab\t", $cats) . "\n".
			"$tab</$html>\n"; #wp
	}
}

class thesis_post_tags extends thesis_box {
	protected function translate() {
		$this->title = __('Tags', 'thesis');
	}

	protected function options() {
		global $thesis;
		$options = $thesis->api->html_options(array(
			'p' => 'p',
			'div' => 'div',
			'span' => 'span'), 'p');
		unset($options['id'], $options['class']);
		return array_merge($options, array(
			'intro' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['intro_text'],
				'tooltip' => __('Any intro text you provide will precede the post tag output, and it will be wrapped inside <code>&lt;span class="post_tags_intro"&gt;</code>.', 'thesis')),
			'separator' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => $thesis->api->strings['character_separator'],
				'tooltip' => __('If you&#8217;d like to separate your tags with a particular character (a comma, for instance), you can do that here.', 'thesis')),
			'nofollow' => array(
				'type' => 'checkbox',
				'options' => array(
					'on' => __('Add <code>nofollow</code> to tag links', 'thesis')),
				'default' => array(
					'on' => true))));
	}

	public function html($args = false) {
		global $thesis;
		if (!is_array($post_tags = get_the_tags())) return; #wp
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$tags = array();
		$html = apply_filters("{$this->_class}_html", !empty($this->options['html']) ? $this->options['html'] : 'p');
		$nofollow = isset($this->options['nofollow']['on']) ? '' : ' nofollow';
		foreach ($post_tags as $tag)
			$tags[] = "<a href=\"" . esc_url(get_tag_link($tag->term_id)) . "\" rel=\"tag$nofollow\">$tag->name</a>"; #wp
		echo
			"$tab<$html class=\"post_tags\"" . (!empty($schema) ? ' itemprop="keywords"' : '') . ">\n".
			(!empty($this->options['intro']) ?
			"$tab\t<span class=\"post_tags_intro\">" . trim($thesis->api->escht($this->options['intro'], true)) . "</span>\n" : '').
			"$tab\t" . implode((!empty($this->options['separator']) ? trim($thesis->api->esch($this->options['separator'])) : '') . "\n$tab\t", $tags) . "\n".
			"$tab</$html>\n";
	}
}

class thesis_post_image extends thesis_box {
	private $allowed = array(
		'a' => array(
			'class' => array(),
			'href' => array(),
			'rel' => array(),
			'title' => array(),
			'target' => array()),
		'span' => array(
			'class' => array(),
			'title' => array()),
		'em' => array(),
		'strong' => array(),
		'u' => array(),
		'code' => array(),
		'sup' => array(),
		'sub' => array(),
		'cite' => array(),
		'strike' => array(),
		'br' => array());

	protected function translate() {
		$this->image_type = __('Post Image', 'thesis');
		$this->title = sprintf(__('Thesis %s', 'thesis'), $this->image_type);
	}

	protected function construct() {
		global $thesis;
		if (empty($thesis->_did_rss)) {
			add_filter('the_content', array($this, 'add_image_to_feed'));
			$thesis->_did_rss = true;
		}
	}

	protected function post_meta() {
		global $thesis;
		return array(
			'title' => $this->title,
			'fields' => array(
				'image' => array(
					'type' => 'image',
					'upload_label' => sprintf(__('Upload a %s', 'thesis'), $this->image_type),
					'tooltip' => sprintf(__('Upload a %1$s here, or else input the %2$s of an image you&#8217;d like to use in the <strong>%3$s %2$s</strong> field below.', 'thesis'), strtolower($this->image_type), $thesis->api->base['url'], $this->image_type),
					'label' => "$this->image_type {$thesis->api->base['url']}",
					'legacy' => 'thesis_post_image'),
				'alt' => array(
					'type' => 'text',
					'width' => 'full',
					'label' => sprintf(__('%s <code>alt</code> Text', 'thesis'), $this->image_type),
					'tooltip' => $thesis->api->strings['alt_tooltip'],
					'legacy' => 'thesis_post_image_alt'),
				'caption' => array(
					'type' => 'text',
					'width' => 'full',
					'label' => sprintf(__('%s Caption', 'thesis'), $this->image_type),
					'tooltip' => $thesis->api->strings['caption_tooltip']),
				'frame' => array(
					'type' => 'checkbox',
					'label' => $thesis->api->strings['frame_label'],
					'tooltip' => $thesis->api->strings['frame_tooltip'],
					'options' => array(
						'on' => $thesis->api->strings['frame_option']),
					'legacy' => 'thesis_post_image_frame'),
				'alignment' => array(
					'type' => 'radio',
					'label' => $thesis->api->strings['alignment'],
					'tooltip' => $thesis->api->strings['alignment_tooltip'],
					'options' => array(
						'' => $thesis->api->strings['skin_default'],
						'left' => $thesis->api->strings['alignleft'],
						'right' => $thesis->api->strings['alignright'],
						'center' => $thesis->api->strings['aligncenter'],
						'flush' => $thesis->api->strings['alignnone']),
					'legacy' => 'thesis_post_image_horizontal')));
	}

	public function html($args = false) {
		global $thesis, $wp_query; #wp
		if (empty($this->post_meta['image']) || !is_array($this->post_meta['image'])) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$attachment = !empty($this->post_meta['image']['id']) ? get_post($this->post_meta['image']['id']) : false;
		$alt = !empty($this->post_meta['alt']) ?
			$this->post_meta['alt'] : (!empty($this->post_meta['image']['id']) && ($wp_alt = get_post_meta($this->post_meta['image']['id'], '_wp_attachment_image_alt', true)) ?
			$wp_alt : get_the_title() . ' ' . strtolower($this->image_type));
		$caption = !empty($this->post_meta['caption']) ?
			$this->post_meta['caption'] : (is_object($attachment) && $attachment->post_excerpt ?
			$attachment->post_excerpt : false);
		$alignment = !empty($this->post_meta['alignment']) ? ' ' . ($this->post_meta['alignment'] == 'left' ?
			'alignleft' : ($this->post_meta['alignment'] == 'right' ?
			'alignright' : ($this->post_meta['alignment'] == 'center' ?
			'aligncenter' : 'alignnone'))) : '';
		$frame = !empty($this->post_meta['frame']) ? ' frame' : '';
		$dimensions = !empty($this->post_meta['image']['width']) && !empty($this->post_meta['image']['height']) ?
			" width=\"{$this->post_meta['image']['width']}\" height=\"{$this->post_meta['image']['height']}\"" : '';
		$img = '';
		if (!empty($this->post_meta['image']['url']))	
			$img = "<img class=\"post_image$alignment$frame\" src=\"". esc_url($this->post_meta['image']['url']) ."\"$dimensions alt=\"" . trim($thesis->api->escht($alt, true)) . "\"" . (!empty($schema) ? ' itemprop="image"' : '') . " />";
		if (!($wp_query->is_single || $wp_query->is_page) && !empty($img))
			$img = "<a class=\"post_image_link\" href=\"" . get_permalink() . "\" title=\"". esc_attr($thesis->api->strings['click_to_read']) ."\">$img</a>"; #wp
		echo $caption ?
			"$tab<div class=\"post_image_box wp-caption$alignment\"" . (!empty($this->post_meta['image']['width']) ? " style=\"width: {$this->post_meta['image']['width']}px\"" : '') . ">\n".
			"$tab\t$img\n".
			"$tab\t<p class=\"wp-caption-text\">" . trim(wptexturize(wp_kses(stripslashes($caption), $this->allowed))) . "</p>\n".
			"$tab</div>\n" : "$tab$img\n";
	}

	public function add_image_to_feed($content) {
		global $thesis, $post;
		if (!is_feed()) return $content;
		$image = get_post_meta($post->ID, "_{$this->_class}", true);
		if (empty($image['image']['url'])) return $content;
		$attachment = !empty($image['image']['id']) ? get_post($image['image']['id']) : false;
		$alt = !empty($image['alt']) ?
			$image['alt'] : (!empty($image['image']['id']) && ($wp_alt = get_post_meta($image['image']['id'], '_wp_attachment_image_alt', true)) ?
			$wp_alt : get_the_title() . ' ' . strtolower($this->image_type));
		$caption = !empty($image['caption']) ?
			$image['caption'] : (is_object($attachment) && $attachment->post_excerpt ?
			$attachment->post_excerpt : false);
		$dimensions = !empty($image['image']['width']) && !empty($image['image']['height']) ?
			" width=\"{$image['image']['width']}\" height=\"{$image['image']['height']}\"" : '';
		return
			"<p><a href=\"" . get_permalink() . "\" title=\"{$thesis->api->strings['click_to_read']}\"><img class=\"post_image\" src=\"{$image['image']['url']}\"$dimensions alt=\"" . trim($thesis->api->escht($alt, true)) . "\" /></a></p>\n".
			($caption ?
			"<p class=\"caption\">" . trim(wptexturize(wp_kses(stripslashes($caption), $this->allowed))) . "</p>\n" : '').
			$content;
	}
}

class thesis_post_thumbnail extends thesis_box {
	private $allowed = array(
		'a' => array(
			'class' => array(),
			'href' => array(),
			'rel' => array(),
			'title' => array(),
			'target' => array()),
		'span' => array(
			'class' => array(),
			'title' => array()),
		'em' => array(),
		'strong' => array(),
		'u' => array(),
		'code' => array(),
		'sup' => array(),
		'sub' => array(),
		'cite' => array(),
		'strike' => array(),
		'br' => array());

	protected function translate() {
		$this->image_type = __('Thumbnail', 'thesis');
		$this->title = "Thesis $this->image_type";
	}

	protected function post_meta() {
		global $thesis;
		return array(
			'title' => $this->title,
			'fields' => array(
				'image' => array(
					'type' => 'image',
					'upload_label' => sprintf(__('Upload a %s', 'thesis'), $this->image_type),
					'tooltip' => sprintf(__('Upload a %1$s here, or else input the %2$s of an image you&#8217;d like to use in the <strong>%3$s %2$s</strong> field below.', 'thesis'), strtolower($this->image_type), $thesis->api->base['url'], $this->image_type),
					'label' => "$this->image_type {$thesis->api->base['url']}",
					'legacy' => 'thesis_thumb'),
				'alt' => array(
					'type' => 'text',
					'width' => 'full',
					'label' => sprintf(__('%s <code>alt</code> Text', 'thesis'), $this->image_type),
					'tooltip' => $thesis->api->strings['alt_tooltip'],
					'legacy' => 'thesis_thumb_alt'),
				'caption' => array(
					'type' => 'text',
					'width' => 'full',
					'label' => sprintf(__('%s Caption', 'thesis'), $this->image_type),
					'tooltip' => $thesis->api->strings['caption_tooltip']),
				'frame' => array(
					'type' => 'checkbox',
					'label' => $thesis->api->strings['frame_label'],
					'tooltip' => $thesis->api->strings['frame_tooltip'],
					'options' => array(
						'on' => $thesis->api->strings['frame_option']),
					'legacy' => 'thesis_thumb_frame'),
				'alignment' => array(
					'type' => 'radio',
					'label' => $thesis->api->strings['alignment'],
					'tooltip' => $thesis->api->strings['alignment_tooltip'],
					'options' => array(
						'' => $thesis->api->strings['skin_default'],
						'left' => $thesis->api->strings['alignleft'],
						'right' => $thesis->api->strings['alignright'],
						'center' => $thesis->api->strings['aligncenter'],
						'flush' => $thesis->api->strings['alignnone']),
					'legacy' => 'thesis_thumb_horizontal')));
	}

	public function html($args = false) {
		global $thesis, $wp_query; #wp
		if (empty($this->post_meta['image']) || !is_array($this->post_meta['image'])) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$attachment = !empty($this->post_meta['image']['id']) ? get_post($this->post_meta['image']['id']) : false;
		$alt = !empty($this->post_meta['alt']) ?
			$this->post_meta['alt'] : (!empty($this->post_meta['image']['id']) && ($wp_alt = get_post_meta($this->post_meta['image']['id'], '_wp_attachment_image_alt', true)) ?
			$wp_alt : get_the_title() . ' ' . strtolower($this->image_type));
		$caption = !empty($this->post_meta['caption']) ?
			$this->post_meta['caption'] : (is_object($attachment) && $attachment->post_excerpt ?
			$attachment->post_excerpt : false);
		$alignment = !empty($this->post_meta['alignment']) ? ' ' . ($this->post_meta['alignment'] == 'left' ?
			'alignleft' : ($this->post_meta['alignment'] == 'right' ?
			'alignright' : ($this->post_meta['alignment'] == 'center' ?
			'aligncenter' : 'alignnone'))) : '';
		$frame = !empty($this->post_meta['frame']) ? ' frame' : '';
		$dimensions = !empty($this->post_meta['image']['width']) && !empty($this->post_meta['image']['height']) ?
			" width=\"". (int)$this->post_meta['image']['width'] ."\" height=\"". (int)$this->post_meta['image']['height'] ."\"" : '';
		$img = '';
		if (!empty($this->post_meta['image']['url']))	
			$img = "<img class=\"thumb$alignment$frame\" src=\"". esc_url($this->post_meta['image']['url']) ."\"$dimensions alt=\"" . trim($thesis->api->escht($alt, true)) . '"' . (!empty($schema) ? ' itemprop="thumbnailUrl"' : '') . " />";
		if (!($wp_query->is_single || $wp_query->is_page))
			$img = "<a class=\"thumb_link\" href=\"" . get_permalink() . "\" title=\"{$thesis->api->strings['click_to_read']}\">$img</a>"; #wp
		echo $caption ?
			"$tab<div class=\"thumb_box wp-caption$alignment\"" . (!empty($this->post_meta['image']['width']) ? " style=\"width: {$this->post_meta['image']['width']}px\"" : '') . ">\n".
			"$tab\t$img\n".
			"$tab\t<p class=\"wp-caption-text\">" . trim(wptexturize(wp_kses(stripslashes($caption), $this->allowed))) . "</p>\n".
			"$tab</div>\n" : "$tab$img\n";
	}
}

class thesis_wp_featured_image extends thesis_box {
	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('%s Featured Image', 'thesis'), $thesis->api->base['wp']);
	}

	protected function construct() {
		add_theme_support('post-thumbnails');
	}

	public function html($args = false) {
		extract($args = is_array($args) ? $args : array());
		echo str_repeat("\t", !empty($depth) ? $depth : 0) . get_the_post_thumbnail();
	}
}

class thesis_comments_intro extends thesis_box {
	public $templates = array('single', 'page');

	protected function translate() {
		$this->title = __('Comments Intro', 'thesis');
	}

	protected function options() {
		global $thesis;
		return array(
			'singular' => array(
				'type' => 'text',
				'label' => $thesis->api->strings['comment_term_singular'],
				'placeholder' => $thesis->api->strings['comment_singular']),
			'plural' => array(
				'type' => 'text',
				'label' => $thesis->api->strings['comment_term_plural'],
				'placeholder' => $thesis->api->strings['comment_plural']));
	}

	public function html($args = false) {
		global $thesis, $wp_query;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$number = (int) count($wp_query->comments_by_type['comment']);
		if (comments_open())
			echo
				$tab.
				"<p class=\"comments_intro\">".
				apply_filters($this->_class,
				"<span class=\"num_comments\">" . count($wp_query->comments_by_type['comment']) . "</span> ".
				($number == 1 ? (!empty($this->options['singular']) ?
				$thesis->api->esch($this->options['singular']) : $thesis->api->strings['comment_singular']) : (!empty($this->options['plural']) ?
				$thesis->api->esch($this->options['plural']) : $thesis->api->strings['comment_plural'])).
				"&#8230; <a href=\"#commentform\" rel=\"nofollow\">" . trim(__(apply_filters("{$this->_class}_add", 'add one'), 'thesis')) . "</a>").
				"</p>\n";
		else
			echo "$tab<p class=\"comments_closed\">" . trim(esc_html(__(apply_filters("{$this->_class}_closed", 'Comments on this entry are closed.'), 'thesis'))) . "</p>\n";
	}
}

class thesis_comments_nav extends thesis_box {
	public $templates = array('single', 'page');

	protected function translate() {
		$this->title = __('Comments Navigation', 'thesis');
		$this->previous = __('Previous Comments', 'thesis');
		$this->next = __('Next Comments', 'thesis');
	}

	protected function options() {
		return array(
			'previous' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('Previous Comments Link Text', 'thesis'),
				'placeholder' => $this->previous),
			'next' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('Next Comments Link Text', 'thesis'),
				'placeholder' => $this->next));
	}

	public function html($args = false) {
		global $thesis;
		if (!get_option('page_comments')) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$previous_link = get_previous_comments_link(trim($thesis->api->escht(apply_filters("{$this->_class}_previous", !empty($this->options['previous']) ? stripslashes($this->options['previous']) : $this->previous))));
		$next_link = get_next_comments_link(trim($thesis->api->escht(apply_filters("{$this->_class}_next", !empty($this->options['next']) ? stripslashes($this->options['next']) : $this->next))));
		if (empty($previous_link) && empty($next_link)) return;
		echo
			"$tab<ul id=\"comment_nav\">\n".
			(!empty($previous_link) ?
			"$tab\t<li class=\"previous_comments\">$previous_link</li>\n" : '').
			(!empty($next_link) ?
			"$tab\t<li class=\"next_comments\">$next_link</li>\n" : '').
			"$tab</ul>\n";
	}
}

class thesis_comments extends thesis_box {
	public $type = 'rotator';
	public $dependents = array(
		'thesis_comment_author',
		'thesis_comment_avatar',
		'thesis_comment_date',
		'thesis_comment_number',
		'thesis_comment_edit',
		'thesis_comment_text',
		'thesis_comment_reply');
	public $children = array(
		'thesis_comment_author',
		'thesis_comment_date',
		'thesis_comment_edit',
		'thesis_comment_text',
		'thesis_comment_reply');
	public $abort = false;

	protected function translate() {
		$this->title = $this->name = __('Comments', 'thesis');
	}

	protected function options() {
		global $thesis;
		$options = $thesis->api->html_options(array(
			'ul' => 'ul',
			'ol' => 'ol',
			'div' => 'div'), 'ul');
		$options['id']['tooltip'] = __('The default ID for the comment wrapper is <code>comments</code>. If you&#8217;d like to use a different ID, you can supply it here.', 'thesis');
		return array_merge($options, array(
			'per_page' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => __('Comments Per Page', 'thesis'),
				'tooltip' => __('Number of comments per page. The default is set in the WordPress General &rarr; Discussion options.', 'thesis'),
				'placeholder' => get_option('comments_per_page'),
				'default' => get_option('comments_per_page'))));
	}

	public function preload() {
		add_filter('comments_template', array($this, 'return_our_path'));
		if (!class_exists('thesis_comments_dummy'))
			comments_template('/comments.php', true);
		if (!empty($GLOBALS['wp_query']->comments_by_type['comment']) && !(bool)get_option('thread_comments')) {
			$GLOBALS['t_comment_counter'] = array();
			foreach ($GLOBALS['wp_query']->comments_by_type['comment'] as $number => $comment)
				$GLOBALS['t_comment_counter'][$comment->comment_ID] = $number + 1;
		}
		wp_enqueue_script('comment-reply'); #wp
	}
	
	public function return_our_path($path) {
		if ($path !== TEMPLATEPATH . '/comments.php')
			$this->abort = $path;
		return TEMPLATEPATH . '/comments.php';
	}
	
	public function html($args = false) {
		global $thesis, $wp_query;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", ($this->tab_depth = !empty($depth) ? $depth : 0));
		if ($this->abort === false) {
			if (post_password_required()){
				echo "$tab\t<p>" . __('This post is password protected. Enter the password to view comments.', 'thesis') . "</p>\n";
				return;
			}
			$is_it = apply_filters('comments_template', false);
			$html = !empty($this->options['html']) ? $this->options['html'] : 'ul';
			$this->child_html = in_array($html, array('ul', 'ol')) ? 'li' : 'div';
			if (!empty($wp_query->comments)) {
				echo "$tab<$html id=\"" . (!empty($this->options['id']) ? trim($thesis->api->esc($this->options['id'])) : 'comments') . '"' . (!empty($this->options['class']) ? ' class="' . trim($thesis->api->esc($this->options['class'])) . '"' : '') . ">\n";
				$args = array(
					'walker' => new thesis_comment_walker,
					'callback' => array($this, 'start'),
					'type' => 'comment',
					'style' => $html);
				if ((bool) get_option('page_comments'))
					$args['per_page'] = (int) !empty($this->options['per_page']) ? $this->options['per_page'] : get_option('comments_per_page');
				wp_list_comments($args, $wp_query->comments_by_type['comment']);
				echo "$tab</$html>\n";
			}
		}
		else
			include_once($this->abort);
	}

	public function start($comment, $args, $depth) {
		global $thesis;
		$GLOBALS['comment'] = $comment;
		echo
			str_repeat("\t", $this->tab_depth + 1).
			"<$this->child_html class=\"" . esc_attr(implode(' ', get_comment_class())) . "\" id=\"comment-" . get_comment_ID() . "\">\n";
		$this->rotator(array('depth' => $this->tab_depth + 2));
	}
}

class thesis_comment_walker extends Walker_Comment {
	public function start_lvl(&$out, $depth = 0, $args = array()) {
		if (in_array($args['style'], array('ul', 'ol', 'div')))
			echo "<" . esc_attr(strtolower($args['style'])) . " class=\"children\">\n";
	}
	
	public function end_lvl(&$out, $depth = 0, $args = array()) {
		if (in_array($args['style'], array('ul', 'ol', 'div')))
			echo "</" . esc_attr(strtolower($args['style'])) . ">\n";
	}
}

class thesis_comment_author extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Author', 'thesis');
	}

	protected function options() {
		return array(
			'author' => array(
				'type' => 'checkbox',
				'options' => array(
					'link' => __('Link comment author name', 'thesis')),
				'default' => array(
					'link' => true)));
	}

	public function html($args = false) {
		extract($args = is_array($args) ? $args : array());
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0).
			"<span class=\"comment_author\">" . (isset($this->options['author']['link']) ? get_comment_author() : get_comment_author_link()) . "</span>\n";
	}
}

class thesis_comment_avatar extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Avatar', 'thesis');
	}

	protected function options() {
		global $thesis;
		return array(
			'size' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => $thesis->api->strings['avatar_size'],
				'description' => 'px',
				'default' => 88));
	}

	public function html($args = false) {
		extract($args = is_array($args) ? $args : array());
		$avatar = get_avatar(get_comment_author_email(), !empty($this->options['size']) && is_numeric($this->options['size']) ? $this->options['size'] : 88);
		$author_url = get_comment_author_url();
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0).
			"<span class=\"avatar\">".
			apply_filters($this->_class, empty($author_url) || $author_url == 'http://' ? $avatar : "<a href=\"$author_url\" rel=\"nofollow\">$avatar</a>").
			"</span>\n";
	}
}

class thesis_comment_date extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Date', 'thesis');
	}

	protected function options() {
		global $thesis;
		return array(
			'format' => array(
				'type' => 'text',
				'width' => 'short',
				'code' => true,
				'label' => __('Date Format', 'thesis'),
				'tooltip' => $thesis->api->strings['date_tooltip'],
				'default' => 'F j, Y'),
			'display' => array(
				'type' => 'checkbox',
				'label' => $thesis->api->strings['display_options'],
				'options' => array(
					'link' => __('add comment permalink to date', 'thesis'),
					'time' => __('display comment time', 'thesis')),
				'default' => array(
					'link' => true,
					'time' => true),
				'dependents' => array(
					'time' => true)),
			'separator' => array(
				'type' => 'text',
				'width' => 'short',
				'label' => __('Date/Time Separator', 'thesis'),
				'placeholder' => __('at', 'thesis'),
				'parent' => array(
					'display' => 'time')),
			'time_format' => array(
				'type' => 'text',
				'width' => 'short',
				'code' => true,
				'label' => __('Time Format', 'thesis'),
				'tooltip' => $thesis->api->strings['date_tooltip'],
				'default' => 'g:i a',
				'parent' => array(
					'display' => 'time')),
			'class' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'label' => $thesis->api->strings['html_class'],
				'tooltip' => sprintf(__('This box already contains a %1$as of <code>comment_date</code>. If you&#8217;d like to supply another %1$s, you can do that here.%2$s', 'thesis'), $thesis->api->base['class'], $thesis->api->strings['class_note'])));
	}

	public function html($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$options = $thesis->api->get_options($this->_get_options(), $this->options);
		$date = get_comment_date(stripslashes($options['format'])) . (!empty($options['display']['time']) ? ' ' . (!empty($options['separator']) ? trim($thesis->api->esch($options['separator'])) . ' ' : '') . get_comment_time(stripslashes($options['time_format'])) : '');
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0).
			'<span class="comment_date' . (!empty($options['class']) ? ' ' . $thesis->api->esc($options['class']) : '') . '">'.
			apply_filters($this->_class, (!empty($options['display']['link']) ?
			'<a href="#comment-' . get_comment_ID() . "\" title=\"{$thesis->api->strings['comment_permalink']}\" rel=\"nofollow\">$date</a>" : $date)).
			"</span>\n";
	}
}

class thesis_comment_number extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Number', 'thesis');
	}

	public function html($args = false) {
		global $thesis;
		if ((bool) get_option('thread_comments')) return;
		extract($args = is_array($args) ? $args : array());
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0).
			"<a class=\"comment_number\" href=\"#comment-" . get_comment_ID() . "\" title=\"{$thesis->api->strings['comment_permalink']}\" rel=\"nofollow\">" . (int) $GLOBALS['t_comment_counter'][get_comment_ID()] . "</a>\n";
	}
}

class thesis_comment_edit extends thesis_box {
	protected function translate() {
		$this->title = __('Edit Comment Link', 'thesis');
	}

	public function html($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0).
			"<a class=\"comment_edit\" href=\"" . get_edit_comment_link() . "\" rel=\"nofollow\">" . trim(esc_html(apply_filters($this->_class, strtolower($thesis->api->strings['edit'])))) . "</a>\n";
	}
}

class thesis_comment_text extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Text', 'thesis');
	}

	protected function construct() {
		global $thesis;
		$thesis->wp->filter($this->_class, array(
			'wptexturize' => false,
			'convert_chars' => false,
			'make_clickable' => 9,
			'force_balance_tags' => 25,
			'convert_smilies' => 20,
			'wpautop' => 30));
	}

	protected function options() {
		global $thesis;
		$options = $thesis->api->html_options();
		unset($options['id']);
		return $options;
	}

	public function html($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		echo $GLOBALS['comment']->comment_approved == '0' ?
			"$tab<p class=\"comment_moderated\">" . __('Your comment is awaiting moderation.', 'thesis') . "</p>\n" :
			"$tab<div class=\"comment_text" . (!empty($this->options['class']) ? ' ' . trim($thesis->api->esc($this->options['class'])) : '') . "\" id=\"comment-body-" . get_comment_ID() . "\">".
			apply_filters($this->_class, get_comment_text()).
			"$tab</div>\n";
	}
}

class thesis_comment_reply extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Reply Link', 'thesis');
		$this->text = __('Reply', 'thesis');
	}

	protected function options() {
		return array(
			'text' => array(
				'type' => 'text',
				'width' => 'short',
				'label' => __('Reply Link Text', 'thesis'),
				'placeholder' => $this->text));
	}

	public function html($args = false) {
		if (!get_option('thread_comments')) return;
		extract($args = is_array($args) ? $args : array());
		echo str_repeat("\t", !empty($depth) ? $depth : 0) . get_comment_reply_link(array(
			'add_below' => 'comment-body',
			'respond_id' => 'commentform',
			'reply_text' => trim(esc_html(!empty($this->options['text']) ? stripslashes($this->options['text']) : $this->text)),
			'login_text' => __('Log in to reply', 'thesis'),
			'depth' => $GLOBALS['comment_depth'],
			'before' => apply_filters("{$this->_class}_before", ''),
			'after' => apply_filters("{$this->_class}_after", ''),
			'max_depth' => (int) get_option('thread_comments_depth'))) . "\n";
	}
}

class thesis_comment_form extends thesis_box {
	public $type = 'rotator';
	public $dependents = array(
		'thesis_comment_form_title',
		'thesis_comment_form_cancel',
		'thesis_comment_form_name',
		'thesis_comment_form_email',
		'thesis_comment_form_url',
		'thesis_comment_form_comment',
		'thesis_comment_form_submit');
	public $children = array(
		'thesis_comment_form_title',
		'thesis_comment_form_cancel',
		'thesis_comment_form_name',
		'thesis_comment_form_email',
		'thesis_comment_form_url',
		'thesis_comment_form_comment',
		'thesis_comment_form_submit');

	protected function translate() {
		$this->title = $this->name = __('Comment Form', 'thesis');
	}

	public function html($args = false) {
		global $user_ID, $post; #wp
		if (!comments_open()) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", $depth = !empty($depth) ? $depth : 0);
		if (get_option('comment_registration') && !!!$user_ID) #wp
			echo "$tab<p class=\"login_alert\">" . __('You must log in to post a comment.', 'thesis') . " <a href=\"" . wp_login_url(get_permalink()) . "\" rel=\"nofollow\">" . __('Log in now.', 'thesis') . "</a></p>\n";
		else {
			echo "$tab<form id=\"commentform\" method=\"post\" action=\"" . site_url('wp-comments-post.php') . "\">\n"; #wp
			do_action('thesis_hook_comment_form_top');
			$this->rotator(array_merge($args, array('depth' => $depth + 1, 'req' => get_option('require_name_email'))));
			do_action('thesis_hook_comment_form_bottom');
			do_action('comment_form', $post->ID); #wp
			comment_id_fields(); #wp
			echo "$tab</form>\n";
		}
	}
}

class thesis_comment_form_title extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Form Title', 'thesis');
		$this->leave = __('Leave a Comment', 'thesis');
	}

	protected function options() {
		return array(
			'title' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $this->title,
				'placeholder' => $this->leave));
	}

	public function html($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0).
			"<p id=\"comment_form_title\">".
			trim($thesis->api->escht(apply_filters($this->_class, !empty($this->options['title']) ? stripslashes($this->options['title']) : $this->leave))).
			"</p>\n";
	}
}

class thesis_comment_form_name extends thesis_box {
	protected function translate() {
		$this->title = __('Name Input', 'thesis');
	}

	protected function options() {
		global $thesis;
		return array(
			'label' => array(
				'type' => 'checkbox',
				'options' => array(
					'show' => $thesis->api->strings['show_label']),
				'default' => array(
					'show' => true)),
			'placeholder' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['placeholder'],
				'tooltip' => $thesis->api->strings['placeholder_tooltip']));
	}

	public function html($args = false) {
		global $thesis, $user_ID, $user_identity, $commenter;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		if (!!$user_ID) // This should probably be moved to the comment form box to safeguard against unwanted display outcomes
			echo
				"$tab<p>" . __('Logged in as', 'thesis') . ' <a href="' . admin_url('profile.php') . "\" rel=\"nofollow\">$user_identity</a>. ".
				'<a href="' . wp_logout_url(get_permalink()) . '" rel="nofollow">' . __('Log out &rarr;', 'thesis') . "</a></p>\n";
		else
			echo
				"$tab<p id=\"comment_form_name\">\n".
				(isset($this->options['label']['show']) ? '' :
				"$tab\t<label for=\"author\">{$thesis->api->strings['name']}" . (!!$req ? " <span class=\"required\" title=\"{$thesis->api->strings['required']}\">*</span>" : '') . "</label>\n").
				"$tab\t<input type=\"text\" id=\"author\" class=\"input_text\" name=\"author\" value=\"" . esc_attr($commenter['comment_author']) . '" '.
				(!empty($this->options['placeholder']) ?
				'placeholder="' . trim($thesis->api->esc($this->options['placeholder'])) . '" ' : '').
				'tabindex="1"' . ($req ? ' aria-required="true"' : '') . " />\n".
				"$tab</p>\n";
	}
}

class thesis_comment_form_email extends thesis_box {
	protected function translate() {
		$this->title = __('Email Input', 'thesis');
	}

	protected function options() {
		global $thesis;
		return array(
			'label' => array(
				'type' => 'checkbox',
				'options' => array(
					'show' => $thesis->api->strings['show_label']),
				'default' => array(
					'show' => true)),
			'placeholder' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['placeholder'],
				'placeholder' => $thesis->api->strings['placeholder_tooltip']));
	}

	public function html($args = false) {
		global $thesis, $user_ID, $commenter;
		if (!!$user_ID) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		echo
			"$tab<p id=\"comment_form_email\">\n".
			(isset($this->options['label']['show']) ? '' :
			"$tab\t<label for=\"email\">{$thesis->api->strings['email']}" . (!!$req ? " <span class=\"required\" title=\"" . esc_attr($thesis->api->strings['required']) . '">*</span>' : '') . "</label>\n").
			"$tab\t<input type=\"text\" id=\"email\" class=\"input_text\" name=\"email\" value=\"" . esc_attr($commenter['comment_author_email']) . '" '.
			(!empty($this->options['placeholder']) ?
			'placeholder="' . trim($thesis->api->esc($this->options['placeholder'])) . '" ' : '').
			'tabindex="2"' . (!!$req ? ' aria-required="true"' : '') . " />\n".
			"$tab</p>\n";
	}
}

class thesis_comment_form_url extends thesis_box {
	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('%s Input', 'thesis'), $thesis->api->base['url']);
	}

	protected function options() {
		global $thesis;
		return array(
			'label' => array(
				'type' => 'checkbox',
				'options' => array(
					'show' => $thesis->api->strings['show_label']),
				'default' => array(
					'show' => true)),
			'placeholder' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['placeholder'],
				'tooltip' => $thesis->api->strings['placeholder_tooltip']));
	}

	public function html($args = false) {
		global $thesis, $user_ID, $commenter;
		if (!!$user_ID) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		echo
			"$tab<p id=\"comment_form_url\">\n".
			(isset($this->options['label']['show']) ? '' :
			"$tab\t<label for=\"url\">{$thesis->api->strings['website']}</label>\n").
			"$tab\t<input type=\"text\" id=\"url\" class=\"input_text\" name=\"url\" value=\"" . esc_attr($commenter['comment_author_url']) . '" '.
			(!empty($this->options['placeholder']) ?
			'placeholder="' . trim($thesis->api->esc($this->options['placeholder'])) . '" ' : '').
			"tabindex=\"3\" />\n".
			"$tab</p>\n";
	}
}

class thesis_comment_form_comment extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Input', 'thesis');
	}

	protected function options() {
		global $thesis;
		return array(
			'label' => array(
				'type' => 'checkbox',
				'options' => array(
					'show' => $thesis->api->strings['show_label']),
				'default' => array(
					'show' => true)),
			'rows' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => __('Number of Rows in Comment Input Box', 'thesis'),
				'tooltip' => __('The number of rows determines the height of the comment input box. The higher the number, the taller the input box.', 'thesis'),
				'default' => 6));
	}

	public function html($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		echo
			"$tab<p id=\"comment_form_comment\">\n".
			(isset($this->options['label']['show']) ? '' :
			"$tab\t<label for=\"comment\">{$thesis->api->strings['comment']}</label>\n").
			"$tab\t<textarea name=\"comment\" id=\"comment\" class=\"input_text\" tabindex=\"4\" rows=\"" . (!empty($this->options['rows']) && is_numeric($this->options['rows']) ? (int) $this->options['rows'] : 6) . "\"></textarea>\n".
			"$tab</p>\n";
	}
}

class thesis_comment_form_submit extends thesis_box {
	protected function translate() {
		$this->title = __('Submit Button', 'thesis');
	}

	protected function options() {
		global $thesis;
		return array(
			'text' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['submit_button_text'],
				'placeholder' => $thesis->api->strings['submit']));
	}

	public function html($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		echo
			"$tab<p id=\"comment_form_submit\">\n".
			"$tab\t<input type=\"submit\" id=\"submit\" class=\"input_submit\" name=\"submit\" tabindex=\"5\" value=\"" . trim(esc_attr(!empty($this->options['text']) ? stripslashes($this->options['text']) : $thesis->api->strings['submit'])) . "\" />\n".
			"$tab</p>\n";
	}
}

class thesis_comment_form_cancel extends thesis_box {
	protected function translate() {
		$this->title = __('Cancel Reply Link', 'thesis');
		$this->cancel = __('Cancel reply', 'thesis');
	}

	protected function options() {
		return array(
			'text' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('Cancel Link Text', 'thesis'),
				'placeholder' => $this->cancel));
	}

	public function html($args = false) {
		extract($args = is_array($args) ? $args : array());
		echo str_repeat("\t", !empty($depth) ? $depth : 0);
		cancel_comment_reply_link(esc_attr(!empty($this->options['text']) ? stripslashes($this->options['text']) : $this->cancel)); #wp
		echo "\n";
	}
}

class thesis_trackbacks extends thesis_box {
	public $type = 'rotator';
	public $dependents = array(
		'thesis_comment_author',
		'thesis_comment_date',
		'thesis_comment_text');
	public $children = array(
		'thesis_comment_author',
		'thesis_comment_date',
		'thesis_comment_text');

	protected function translate() {
		$this->title = $this->name = __('Trackbacks', 'thesis');
	}

	public function html($args = false) {
		global $wp_query;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", $depth = !empty($depth) ? $depth : 0);
		if (empty($wp_query->comments_by_type)) // separate the comments and put them in wp_query if they aren't there already
			$wp_query->comments_by_type = &separate_comments($wp_query->comments);
		foreach ($wp_query->comments_by_type as $a)
			if ($a->comment_type == 'pingback' || $a->comment_type == 'trackback')
				$b[] = $a;
		if (empty($b)) return;
		echo "$tab<ul id=\"trackback_list\">\n";
		foreach ($b as $t) {
			echo "$tab\t<li>";
			$this->rotator(array_merge($args, array('depth' => $depth + 1, 't' => $t)));
			echo "</li>\n";
		}
		echo "$tab</ul>\n";
	}
}

class thesis_previous_post_link extends thesis_box {
	public $templates = array('single');

	protected function translate() {
		$this->title = __('Previous Post Link', 'thesis');
	}

	protected function options() {
		global $thesis;
		$options = $thesis->api->html_options(array('span' => 'span', 'p' => 'p'), 'span');
		unset($options['id'], $options['class']);
		return array_merge($options, array(
			'intro' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['intro_text'],
				'placeholder' => __('Previous Post:', 'thesis')),
			'link' => array(
				'type' => 'radio',
				'label' => $thesis->api->strings['link_text'],
				'options' => array(
					'title' => $thesis->api->strings['use_post_title'],
					'custom' => $thesis->api->strings['use_custom_text']),
				'default' => 'title',
				'dependents' => array(
					'custom' => true)),
			'text' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['custom_link_text'],
				'parent' => array(
					'link' => 'custom'))));
	}

	public function html($args = false) {
		global $thesis, $wp_query;
		if (!$wp_query->is_single || !get_previous_post()) return;
		extract($args = is_array($args) ? $args : array());
		$html = !empty($this->options['html']) ? $this->options['html'] : 'span';
		echo str_repeat("\t", !empty($depth) ? $depth : 0) . "<$html class=\"previous_post\">";
		previous_post_link((!empty($this->options['intro']) ? trim($thesis->api->escht($this->options['intro'], true)) . ' ' : '') . '%link', !empty($this->options['link']) && $this->options['link'] == 'custom' ? (!empty($this->options['text']) ? trim($thesis->api->escht($this->options['text'], true)) : '%title') : '%title'); #wp
		echo "</$html>\n";
	}
}

class thesis_next_post_link extends thesis_box {
	public $templates = array('single');

	protected function translate() {
		$this->title = __('Next Post Link', 'thesis');
	}

	protected function options() {
		global $thesis;
		$options = $thesis->api->html_options(array('span' => 'span', 'p' => 'p'), 'span');
		unset($options['id'], $options['class']);
		return array_merge($options, array(
			'intro' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['intro_text'],
				'placeholder' => __('Next Post:', 'thesis')),
			'link' => array(
				'type' => 'radio',
				'label' => $thesis->api->strings['link_text'],
				'options' => array(
					'title' => $thesis->api->strings['use_post_title'],
					'custom' => $thesis->api->strings['use_custom_text']),
				'default' => 'title',
				'dependents' => array(
					'custom' => true)),
			'text' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['custom_link_text'],
				'parent' => array(
					'link' => 'custom'))));
	}

	public function html($args = false) {
		global $thesis, $wp_query;
		if (!$wp_query->is_single || !get_next_post()) return;
		extract($args = is_array($args) ? $args : array());
		$html = !empty($this->options['html']) ? $this->options['html'] : 'span';
		echo str_repeat("\t", !empty($depth) ? $depth : 0) . "<$html class=\"next_post\">";
		next_post_link((!empty($this->options['intro']) ? trim($thesis->api->escht($this->options['intro'], true)) . ' ' : '') . '%link', !empty($this->options['link']) && $this->options['link'] == 'custom' ? (!empty($this->options['text']) ? trim($thesis->api->escht($this->options['text'], true)) : '%title') : '%title'); #wp
		echo "</$html>\n";
	}
}

class thesis_previous_posts_link extends thesis_box {
	public $templates = array('home', 'archive');

	protected function translate() {
		$this->previous = __('Previous Posts', 'thesis');
		$this->title = sprintf(__('%s Link', 'thesis'), $this->previous);
	}

	protected function options() {
		global $thesis;
		$options = $thesis->api->html_options(array('span' => 'span', 'p' => 'p'), 'span');
		unset($options['id'], $options['class']);
		return array_merge($options, array(
			'text' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['link_text'],
				'placeholder' => $this->previous,
				'description' => $thesis->api->strings['no_html'])));
	}

	public function html($args = false) {
		global $thesis, $wp_query; #wp
		if (!(($wp_query->is_home || $wp_query->is_archive || $wp_query->is_search) && $wp_query->max_num_pages > 1 && ((!empty($wp_query->query_vars['paged']) ? $wp_query->query_vars['paged'] : 1) < $wp_query->max_num_pages))) return;
		extract($args = is_array($args) ? $args : array());
		$html = !empty($this->options['html']) ? $this->options['html'] : 'span';
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0) . "<$html class=\"previous_posts\">".
			get_next_posts_link(trim($thesis->api->escht(apply_filters($this->_class, !empty($this->options['text']) ? stripslashes($this->options['text']) : $this->previous)))).
			"</$html>\n";
	}
}

class thesis_next_posts_link extends thesis_box {
	public $templates = array('home', 'archive');

	protected function translate() {
		$this->next = __('Next Posts', 'thesis');
		$this->title = sprintf(__('%s Link', 'thesis'), $this->next);
	}

	protected function options() {
		global $thesis;
		$options = $thesis->api->html_options(array('span' => 'span', 'p' => 'p'), 'span');
		unset($options['id'], $options['class']);
		return array_merge($options, array(
			'text' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['link_text'],
				'placeholder' => $this->next,
				'description' => $thesis->api->strings['no_html'])));
	}

	public function html($args = false) {
		global $thesis, $wp_query; #wp
		if (!(($wp_query->is_home || $wp_query->is_archive || $wp_query->is_search) && $wp_query->max_num_pages > 1 && ((!empty($wp_query->query_vars['paged']) ? $wp_query->query_vars['paged'] : 1) > 1))) return;
		extract($args = is_array($args) ? $args : array());
		$html = !empty($this->options['html']) ? $this->options['html'] : 'span';
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0) . "<$html class=\"next_posts\">".
			get_previous_posts_link(trim($thesis->api->escht(apply_filters($this->_class, !empty($this->options['text']) ? stripslashes($this->options['text']) : $this->next)))).
			"</$html>\n";
	}
}

class thesis_archive_title extends thesis_box {
	public $templates = array('archive');

	protected function translate() {
		$this->title = __('Archive Title', 'thesis');
	}

	protected function construct() {
		global $thesis;
		$thesis->wp->filter($this->_class, array(
			'wptexturize' => false,
			'convert_chars' => false));
	}

	protected function term_options() {
		return array(
			'title' => array(
				'type' => 'text',
				'code' => true,
				'label' => $this->title));
	}

	public function html($args = false) {
		global $thesis, $wp_query; #wp
		extract($args = is_array($args) ? $args : array());
		$title = !empty($this->term_options['title']) ?
			stripslashes($this->term_options['title']) : ($wp_query->is_archive ? ($wp_query->is_author ?
			$thesis->wp->author($wp_query->query_vars['author'], 'display_name') : ($wp_query->is_day ?
			get_the_time('l, F j, Y') : ($wp_query->is_month ?
			get_the_time('F Y') : ($wp_query->is_year ?
			get_the_time('Y') : ($wp_query->is_search ?
			__('Search:', 'thesis') . ' ' . esc_html($wp_query->query_vars['s']) : $wp_query->queried_object->name))))) : false);
		if ($title)
			echo str_repeat("\t", !empty($depth) ? $depth : 0) . "<h1 class=\"archive_title\">" . trim(apply_filters($this->_class, $title)) . "</h1>\n";
	}
}

class thesis_archive_content extends thesis_box {
	public $templates = array('archive');

	protected function translate() {
		$this->title = __('Archive Content', 'thesis');
	}

	protected function construct() {
		global $thesis;
		$thesis->wp->filter($this->_class, array(
			'wptexturize' => false,
			'convert_smilies' => false,
			'convert_chars' => false,
			'wpautop' => false,
			'shortcode_unautop' => false,
			'do_shortcode' => false));
	}

	protected function options() {
		global $thesis;
		$options = $thesis->api->html_options();
		$options['class']['tooltip'] = sprintf(__('This box already contains a %1$s called <code>archive_content</code>. If you wish to add an additional %1$s, you can do that here. Separate multiple %1$ses with spaces.%2$s', 'thesis'), $thesis->api->base['class'], $thesis->api->strings['class_note']);
		unset($options['id']);
		return $options;
	}

	protected function term_options() {
		return array(
			'content' => array(
				'type' => 'textarea',
				'rows' => 8,
				'label' => $this->title));
	}

	public function html($args = false) {
		global $thesis;
		if (!($content = !empty($this->term_options['content']) ? stripslashes($this->term_options['content']) : false)) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		echo
			"$tab<div class=\"archive_content" . (!empty($this->options['class']) ? ' ' . trim($thesis->api->esc($this->options['class'])) : '') . "\">\n".
			apply_filters($this->_class, trim($content)).
			"$tab</div>\n";
	}
}

class thesis_wp_widgets extends thesis_box {
	private $html = array('div', 'li', 'article', 'section');
	private $title_html = array('h1', 'h2', 'h3', 'h4', 'h5', 'p');
	private $tag = false;
	private $title_tag = false;

	protected function translate() {
		$this->title = $this->name = __('Widgets', 'thesis');
	}

	protected function construct() {
		global $thesis;
		$this->tag = ($html = apply_filters("{$this->_class}_html", 'div')) && in_array($html, $this->html) ? $html : 'div';
		$this->title_tag = ($title_html = apply_filters("{$this->_class}_title_html", !empty($this->options['title_tag']) ? $this->options['title_tag'] : 'h4')) && in_array($title_html, $this->title_html) ? $title_html : 'h4';
		register_sidebar(array(
			'name' => $this->name,
			'id' => $this->_id,
			'before_widget' => "<$this->tag class=\"widget %2\$s" . (!empty($this->options['class']) ? ' ' . trim($thesis->api->esc($this->options['class'])) : '') . '" id="%1$s">',
			'after_widget' => "</$this->tag>",
			'before_title' => "<$this->title_tag class=\"widget_title\">",
			'after_title' => "</$this->title_tag>"));
	}

	protected function options() {
		global $thesis;
		$options = $thesis->api->html_options();
		unset($options['id']);
		return array_merge($options, array(
			'title_tag' => array(
				'type' => 'select',
				'label' => sprintf(__('Widget Title %s', 'thesis'), $thesis->api->strings['html_tag']),
				'options' => array(
					'h3' => 'h3',
					'h4' => 'h4',
					'h5' => 'h5'),
				'default' => 'h4')));
	}

	public function html($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		if ($list = $this->tag == 'li' ? true : false)
			echo "$tab<ul" . (($class = apply_filters("{$this->_class}_ul_class", 'widget_list')) ? ' class="' . trim(esc_attr($class)) . '"' : '') . ">\n";
		do_action("thesis_hook_{$this->_id}_first");
		if (!dynamic_sidebar($this->_id) && is_user_logged_in())
			echo
				"$tab<div class=\"widget" . (!empty($this->options['class']) ? ' ' . trim($thesis->api->esc($this->options['class'])) : '') . "\">\n".
				"$tab\t<p>" . sprintf(__('This is a widget box called %1$s, but there are no widgets in it yet. <a href="%2$s">Add a widget here</a>.', 'thesis'), $this->name, admin_url('widgets.php')) . "</p>\n".
				"$tab</div>\n";
		do_action("thesis_hook_{$this->_id}_last");
		if ($list)
			echo "\n$tab</ul>\n";
	}
}

class thesis_text_box extends thesis_box {
	protected function translate() {
		$this->title = $this->name = __('Text Box', 'thesis');
	}

	protected function construct() {
		global $thesis;
		$filters = !empty($this->options['filter']['on']) ?
			array(
				'wptexturize' => false,
				'convert_smilies' => false,
				'convert_chars' => false,
				'do_shortcode' => false) :
			array(
				'wptexturize' => false,
				'convert_smilies' => false,
				'convert_chars' => false,
				'wpautop' => false,
				'shortcode_unautop' => false,
				'do_shortcode' => false);
		$thesis->wp->filter($this->_id, $filters);
	}

	protected function options() {
		global $thesis;
		$options = $thesis->api->html_options(array(
			'div' => 'div',
			'none' => sprintf(__('No %s wrapper', 'thesis'), $thesis->api->base['html'])), 'div');
		$options['html']['dependents'] = array('div' => true);
		$options['id']['parent'] = $options['class']['parent'] = array('html' => 'div');
		return array_merge(array(
			'text' => array(
				'type' => 'textarea',
				'rows' => 8,
				'code' => true,
				'label' => sprintf(__('Text/%s', 'thesis'), $thesis->api->base['html']),
				'tooltip' => sprintf(__('This box allows you to insert %1$s or plain text. All text will be formatted just like a normal WordPress post, and all valid %1$s tags are allowed.<br /><br /><strong>Note:</strong> Scripts and %2$s are not allowed here.', 'thesis'), $thesis->api->base['html'], $thesis->api->base['php'])),
			'filter' => array(
				'type' => 'checkbox',
				'options' => array(
					'on' => __('disable automatic <code>&lt;p&gt;</code> tags for this Text Box', 'thesis')))), $options);
	}

	public function html($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$html = !empty($this->options['html']) ? ($this->options['html'] == 'none' ? false : $this->options['html']) : 'div';
		echo
			($html ?
			"$tab<div" . (!empty($this->options['id']) ? ' id="' . trim($thesis->api->esc($this->options['id'])) . '"' : '') . ' class="' . (!empty($this->options['class']) ? trim($thesis->api->esc($this->options['class'])) : 'text_box') . "\">\n" : '').
			$tab . ($html ? "\t" : '') . trim(apply_filters($this->_id, !empty($this->options['text']) ?
				stripslashes($this->options['text']) :
				sprintf(__('This is a Text Box named %s. You can write anything you want in here, and Thesis will format it just like a WordPress post.', 'thesis'), $this->name))) . "\n".
			($html ?
			"$tab</div>\n" : '');
	}
}

class thesis_attribution extends thesis_box {
	protected function translate() {
		$this->title = __('Thesis Attribution', 'thesis');
	}

	protected function options() {
		global $thesis;
		return array(
			'url' => array(
				'type' => 'text',
				'width' => 'full',
				'label' => sprintf(__('Thesis Attribution Link %s', 'thesis'), $thesis->api->base['url']),
				'tooltip' => sprintf(__('Did you know that you can earn money by referring people to Thesis? Once you&#8217;ve signed up for the <a href="%1$s"><strong>DIY</strong>themes Affiliate Program</a>, you should place your affiliate link in this field.<br /><br />For more information, please see the <a href="%2$s">Affiliate section</a> of the Thesis User&#8217;s Guide.', 'thesis'), 'http://diythemes.com/affiliate-program/', 'http://diythemes.com/thesis/rtfm/#affiliates')));
	}

	public function html($args = false) {
		extract($args = is_array($args) ? $args : array());
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0).
			"<p class=\"attribution\">".
			sprintf(__('This site rocks the <a href="%s">Thesis Framework</a> from DIYthemes.', 'thesis'), esc_url(apply_filters("{$this->_class}_url", !empty($this->options['url']) ? stripslashes($this->options['url']) : 'http://diythemes.com/'))).
			"</p>\n";
	}
}

class thesis_wp_admin extends thesis_box {
	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('%s Admin Link', 'thesis'), $thesis->api->base['wp']);
	}

	public function html($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		echo str_repeat("\t", !empty($depth) ? $depth : 0) . "<p><a href=\"" . admin_url() . '">' . sprintf(__('%s Admin', 'thesis'), $thesis->api->base['wp']) . "</a></p>\n"; #wp
	}
}

class thesis_query_box extends thesis_box {
	public $type = 'rotator';
	public $dependents = array(
		'thesis_post_headline',
		'thesis_post_date',
		'thesis_post_author',
		'thesis_post_author_avatar',
		'thesis_post_author_description',
		'thesis_post_edit',
		'thesis_post_content',
		'thesis_post_excerpt',
		'thesis_post_num_comments',
		'thesis_post_categories',
		'thesis_post_tags',
		'thesis_post_image',
		'thesis_post_thumbnail',
		'thesis_wp_featured_image');
	public $children = array(
		'thesis_post_headline',
		'thesis_post_author',
		'thesis_post_edit',
		'thesis_post_excerpt');

	protected function translate() {
		$this->title = $this->name = __('Query Box', 'thesis');
	}

	protected function options() {
		global $thesis, $wpdb, $wp_taxonomies;
		// get the post types
		$get_post_types = get_post_types('', 'objects');
		$post_types = array();
		foreach ($get_post_types as $name => $pt_obj)
			if (!in_array($name, array('revision', 'nav_menu_item', 'attachment')))
				$post_types[$name] = !empty($pt_obj->labels->name) ? esc_html($pt_obj->labels->name) : esc_html($pt_obj->name);
		$loop_post_types = $post_types;
		// now get the taxes associated with each post type, set up the dependents list
		$pt_has_dep = array();
		$term_args = array(
			'number' => 50, // get 50 terms for each tax
			'orderby' => 'count'); // but only the most popular ones!
		if (isset($loop_post_types['page'])) unset($loop_post_types['page']); // doing this so it appears in the menu in the right order, but we have to handle the options below.
		foreach ($loop_post_types as $name => $output) {
			$t = get_object_taxonomies($name, 'objects');
			$pt_has_dep[$name] = true; // indicate that the post type has dependents
			if (!!$t) {
				$options_later = array(); // clear out the options_later array
				$options_later[$name . '_tax'] = array( // begin setup of taxonomy list for this post type
					'type' => 'select',
					'label' => sprintf(__("Select Query Type", 'thesis'), $output));
				$t_options = array(); // $t_options will be an array of slug => label for the taxes associated with this post type
				$t_options[''] = sprintf(__('Recent %s', 'thesis'), $output);
				foreach ($t as $tax_name => $tax_obj) {
					// make the post type specific list of taxonomies
					$t_options[$tax_name] = ! empty($tax_obj->label) ? $tax_obj->label : (! empty($tax_obj->labels->name) ? $tax_obj->labels->name : $tax_name);
					// now let's make the term options for this category
					$options_later[$name . '_' . $tax_name . '_term'] = array(
						'type' => 'select',
						'label' => sprintf(__("Choose from available %s", 'thesis'), $t_options[$tax_name]));
					$get_terms = get_terms($tax_name, $term_args);
					$options_later[$name . '_' . $tax_name . '_term']['options'][''] = sprintf(__('Select %s Entries'), $t_options[$tax_name]);
					foreach ($get_terms as $term_obj) {
						// make the term list for this taxonomy
						$options_later[$name . '_' . $tax_name . '_term']['options'][$term_obj->term_id] = (! empty($term_obj->name) ? $term_obj->name : $term_obj->slug);
						// tell the taxonomy it has dependents, and which one has it
						if (empty($options_later[$name . '_tax']['dependents'][$tax_name]))
							$options_later[$name . '_tax']['dependents'][$tax_name] = true;
					}
					$options_later[$name . '_' . $tax_name . '_term']['parent'] = array($name . '_tax' => $tax_name);
					if (count($get_terms) == 50) { // did we hit the 50 threshhold? if so, add in a text box
						$options_later[$name . '_' . $tax_name . '_term_text']['type'] = 'text';
						$options_later[$name . '_' . $tax_name . '_term_text']['label'] = __('Optionally, provide an ID or slug.', 'thesis');
						$options_later[$name . '_' . $tax_name . '_term_text']['width'] = 'medium';
//						$options_later[$name . '_' . $tax_name . '_term_text']['description'] = sprintf(__('You may specify a %s ID or slug here.', 'thesis'), $tax_name);
						$options_later[$name . '_' . $tax_name . '_term_text']['parent'] = array($name . '_tax' => $tax_name);
					}
				}
				$options_later[$name . '_tax']['options'] = $t_options;
				$options_grouped[$name . '_group'] = array( // the group
					'type' => 'group',
					'parent' => array('post_type' => $name),
					'fields' => $options_later);
			}
		}
		// add on pages
		$pt_has_dep['page'] = true;
		$get_pages = get_pages();
		$pages_option = array('' => __('Choose from below', 'thesis'));
		foreach ($get_pages as $page_object)
			$pages_option[$page_object->ID] = $page_object->post_title;
		$options['post_type'] = array( // create the post type option
			'type' => 'select',
			'label' => __('Select Post Type', 'thesis'),
			'options' => $post_types,
			'dependents' => $pt_has_dep);
		foreach ($options_grouped as $name => $make)
			$options[$name] = $make;
		$options['pages'] = array(
			'type' => 'group',
			'parent' => array('post_type' => 'page'),
			'fields' => array(
				'page' => array(
					'type' => 'select',
					'label' => __('Select a Page'),
					'options' => $pages_option)));
		$options['num'] = array(
			'type' => 'text',
			'width' => 'tiny',
			'label' => $thesis->api->strings['posts_to_show'],
			'parent' => array('post_type' => array_keys($loop_post_types)));
		$author = array(
			'label' => __('Filter by Author', 'thesis'));
		if (!$users = wp_cache_get('thesis_editor_users')) {
			$user_args = array(
				'orderby' => 'post_count',
				'number' => 50);
			$users = get_users($user_args);
			wp_cache_add('thesis_editor_users', $users); // use this for the users list in the editor (if needed)
		}
		$user_data = array('' => '----');
		foreach ($users as $user_obj)
			$user_data[$user_obj->ID] = !empty($user_obj->display_name) ? $user_obj->display_name : (!empty($user_obj->user_nicename) ? $user_obj->user_nicename : $user_obj->user_login);
		$author['type'] = 'select';
		$author['options'] = $user_data;
		$more['author'] = $author;
		$more['offset'] = array(
			'type' => 'text',
			'width' => 'short',
			'label' => __('Offset', 'thesis')); # need a tooltip here
		$more['order'] = array(
			'type' => 'select',
			'label' => __('Order', 'thesis'),
			'tooltip' => __('Ascending means 1,2,3; a,b,c. Descending means 3,2,1; c,b,a.', 'thesis'),
			'options' => array(
				'ASC' => __('Ascending', 'thesis'),
				'DESC' => __('Descending', 'thesis')));
		$more['orderby'] = array(
			'type' => 'select',
			'label' => __('Orderby', 'thesis'),
			'tooltip' => __('Choose a field to sort by', 'thesis'),
			'options' => array(
				'' => '----',
				'ID' => __('ID', 'thesis'),
				'author' => __('Author', 'thesis'),
				'title' => __('Title', 'thesis'),
				'date' => __('Date', 'thesis'),
				'modified' => __('Modified', 'thesis'),
				'rand' => __('Random', 'thesis'),
				'comment_count' => __('Comment count', 'thesis'),
				'menu_order' => __('Menu order', 'thesis')));
		$more['sticky'] = array(
			'type' => 'radio',
			'label' => __('Sticky posts', 'thesis'),
			'options' => array( # change default to '' and adjust html() per this change
				'default' => __('Show sticky posts, but in their natural position (default)', 'thesis'),
				'show' => __('Show sticky posts', 'thesis')),
			'default' => 'default');
		$options['more'] = array(
			'type' => 'group',
			'label' => __('Advanced Query Options', 'thesis'),
			'fields' => $more);
		$options = array_merge($options, $thesis->api->html_options(array(
			'div' => 'div',
			'ul' => 'ul',
			'ol' => 'ol'), 'div', true));
		$options['html']['fields']['html']['dependents'] = array(
			'div' => true,
			'ul' => true,
			'ol' => true);
		$options['html']['fields']['id']['parent'] = array(
			'html' => array('ul', 'ol'));
		$options['html']['fields']['class']['parent'] = array(
			'html' => array('div', 'ul', 'ol'));
		$options['html']['fields'] = array_merge($options['html']['fields'], array(
			'wp' => array(
				'type' => 'checkbox',
				'label' => $thesis->api->strings['auto_wp_label'],
				'tooltip' => $thesis->api->strings['auto_wp_tooltip'],
				'options' => array(
					'auto' => $thesis->api->strings['auto_wp_option']),
				'parent' => array(
					'html' => 'div')),
			'output' => array(
				'type' => 'checkbox',
				'label' => __('Link Output', 'thesis'),
				'tooltip' => __('Selecting this will link each list item to its associated post. All output will be linked.', 'thesis'),
				'options' => array(
					'link' => __('Link list item to post', 'thesis')),
				'parent' => array(
					'html' => array('ul', 'ol'))),
			'hook' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'label' => $thesis->api->strings['hook_label'],
				'tooltip' => $thesis->api->strings['hook_tooltip_1'] . '<br /><br /><code>thesis_hook_before_query_box_{name}</code><br /><code>thesis_hook_query_box_top_{name}</code><br /><code>thesis_hook_query_box_bottom_{name}</code><br /><code>thesis_hook_after_query_box_{name}</code><br /><br />' . $thesis->api->strings['hook_tooltip_2'])));
		return $options;
	}

	public function html($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		if ($this->options['post_type'] == 'page') {
			if (empty($this->options['page'])) return;
			$query = array('page_id' => absint($this->options['page']));
		}
		else {
			$query = array( // start building the query
				'post_type' => $this->options['post_type'],
				'posts_per_page' => ! empty($this->options['num']) ? (int) $this->options['num'] : 5,
				'ignore_sticky_posts' => 1,
				'order' => $this->options['order'] == 'ASC' ? 'ASC' : 'DESC',
				'orderby' => !empty($this->options['orderby']) && in_array($this->options['orderby'], array('ID', 'author', 'title', 'date', 'modified', 'rand', 'comment_count', 'menu_order')) ? (string) $this->options['orderby'] : 'date');
			if (! empty($this->options[$this->options['post_type'] . '_tax']) && (!empty($this->options[$this->options['post_type'] . '_' . $this->options[$this->options['post_type'] . '_tax'] . '_text']) || !empty($this->options[$this->options['post_type'] . '_' . $this->options[$this->options['post_type'] . '_tax'] . '_term'])))
				$query['tax_query'] = array(
					array(
						'taxonomy' => (string) $this->options[$this->options['post_type'] . '_tax'],
						'field' => 'id',
						'terms' => !empty($this->options[$this->options['post_type'] . '_' . $this->options[$this->options['post_type'] . '_tax'] . '_text']) ? 
						(int) $this->options[$this->options['post_type'] . '_' . $this->options[$this->options['post_type'] . '_tax'] . '_text'] : 
						(int) $this->options[$this->options['post_type'] . '_' . $this->options[$this->options['post_type'] . '_tax'] . '_term']));
			if (!empty($this->options['author']))
				$query['author'] = (string) $this->options['author'];
			if (!empty($this->options['offset']))
				$query['offset'] = (int) $this->options['offset'];
			if ((!empty($this->options['sticky']) && $this->options['sticky'] !== 'default'))
				$query['ignore_sticky_posts'] = 0;
		}
		$the_query = new WP_Query($query); // new query object
		$html = !empty($this->options['html']) ? $this->options['html'] : 'div';
		$list = $html == 'ul' || $html == 'ol' ? true : false;
		$link = !empty($this->options['output']['link']) ? $this->options['output']['link'] : false;
		$id = !empty($this->options['id']) ? ' id="' . trim($this->options['id']) . '"' : '';
		$class = (!empty($list) ?
			'query_list' : 'query_box').
			(!empty($this->options['class']) ?
			' ' . trim($thesis->api->esc($this->options['class'])) : '').
			(empty($list) && !empty($this->options['wp']['auto']) ?
			' ' . implode(' ', get_post_class()) : '');
		$hook = !empty($this->options['hook']) ? "_{$this->options['hook']}" : '';
		$counter = 1;
		$depth = $list ? $depth + 2 : $depth + 1;
		if (!!$list) {
			do_action("thesis_hook_before_query_list$hook");
			echo "$tab<$html$id class=\"$class\">\n";
			do_action("thesis_hook_query_list_top$hook");
		}
		while ($the_query->have_posts()) {
			$the_query->the_post();
			do_action('thesis_init_post_meta', $the_query->post->ID);
			if (!!$list) {
				do_action("thesis_hook_before_query_list_item$hook", $counter);
				echo
					"$tab\t<li class=\"query_item_$counter\">\n".
					($link ?
					"$tab\t\t<a href=\"" . esc_url(get_permalink()) . "\">\n" : '');
			}
			else {
				do_action("thesis_hook_before_query_box$hook", $counter);
				echo "$tab<div class=\"$class\">\n";
				do_action("thesis_hook_query_box_top$hook", $counter);
			}
			$this->rotator(array_merge($args, array('depth' => $depth, 'post_count' => $counter)));
			if (!!$list) {
				echo ($link ?
					"$tab\t\t</a>\n" : '').
					"$tab\t</li>\n";
				do_action("thesis_hook_after_query_list_item$hook");
			}
			else {
				do_action("thesis_hook_query_box_bottom$hook", $counter);
				echo "$tab</div>\n";
				do_action("thesis_hook_after_query_box$hook", $counter);
			}
			$counter++;
		}
		if (!!$list) {
			do_action("thesis_hook_query_list_bottom$hook");
			echo "$tab</$html>\n";
			do_action("thesis_hook_after_query_list$hook");
		}
		wp_reset_query();
	}

	public function query($query) {
		$query->query_vars['posts_per_page'] = (int) $this->options['num'];
		return $query;
	}
}

class thesis_js extends thesis_box {
	public $type = false;
	private $libs = array();

	protected function construct() {
		add_action('_thesis_head_scripts', array($this, 'head_scripts'));
		add_action('_thesis_skin_scripts', array($this, 'add_scripts'));
	}

	protected function template_options() {
		$description = __('please include <code>&lt;script&gt;</code> tags', 'thesis');
		$libs = array(
			'jquery' => 'jQuery',
			'jquery-ui-core' => 'jQuery UI',
			'jquery-effects-core' => 'jQuery Effects',
			'thickbox' => 'Thickbox',
			'prototype' => 'Prototype',
			'scriptaculous' => 'Scriptaculous');
		return array(
			'title' => __('JavaScript', 'thesis'),
			'fields' => array(
				'libs' => array(
					'type' => 'checkbox',
					'label' => __('JavaScript Libraries', 'thesis'),
					'options' => is_array($js = apply_filters('thesis_js_libs', $libs)) ? $js : $libs),
				'scripts' => array(
					'type' => 'textarea',
					'rows' => 4,
					'code' => true,
					'label' => __('Footer Scripts', 'thesis'),
					'tooltip' => __('The optimal location for most scripts is just before the closing <code>&lt;/body&gt;</code> tag. If you want to add JavaScript to your site, this is the preferred place to do that.<br /><br /><strong>Note:</strong> Certain scripts will only function properly if placed in the document <code>&lt;head&gt;</code>. Please place those scripts in the &ldquo;Head Scripts&rdquo; box below.', 'thesis'),
					'description' => $description),
				'head_scripts' => array(
					'type' => 'textarea',
					'rows' => 4,
					'code' => true,
					'label' => __('Head Scripts', 'thesis'),
					'tooltip' => __('If you wish to add scripts that will only function properly when placed in the document <code>&lt;head&gt;</code>, you should add them here.<br /><br /><strong>Note:</strong> Only do this if you have no other option. Scripts placed in the <code>&lt;head&gt;</code> will negatively impact skin performance.', 'thesis'),
					'description' => $description)));
	}

	public function head_scripts() {
		if (!empty($this->template_options['head_scripts']))
			echo trim(stripslashes($this->template_options['head_scripts'])) . "\n";
		if (is_array($scripts = apply_filters('thesis_head_scripts', false)))
			foreach ($scripts as $script)
				echo "$script\n";
	}

	public function add_scripts() {
		$this->libs(!empty($this->template_options['libs']) && is_array($this->template_options['libs']) ? array_keys($this->template_options['libs']) : false);
		foreach ($this->libs as $lib => $src)
			echo "<script type=\"text/javascript\" src=\"$src\"></script>\n";
		if (!empty($this->template_options['scripts']))
			echo trim(stripslashes($this->template_options['scripts'])) . "\n";
		if (is_array($scripts = apply_filters('thesis_footer_scripts', false)))
			foreach ($scripts as $script)
				echo "$script\n";
		
	}

	private function libs($libs) {
		global $wp_scripts;
		if (!is_array($libs)) return;
		$s = is_object($wp_scripts) ? $wp_scripts : new WP_Scripts;
		foreach ($libs as $lib)
			if (is_object($s->registered[$lib]) && empty($this->libs[$lib]) && !in_array($lib, $s->done)) {
				if (!empty($s->registered[$lib]->deps))
					$this->libs($s->registered[$lib]->deps);
				if (!empty($s->registered[$lib]->src))
					$this->libs[$lib] = $s->base_url . $s->registered[$lib]->src;
			}
	}
}

class thesis_tracking_scripts extends thesis_box {
	public $type = false;
	private $analytics = false;
	private $scripts = false;

	public function construct() {
		global $thesis;
		$this->analytics = ($analytics = $thesis->api->get_option('thesis_analytics')) ? $analytics : $this->analytics;
		$this->scripts = ($scripts = $thesis->api->get_option('thesis_scripts')) ? $scripts : $this->scripts;
		if (is_admin()) return;
		add_action('_thesis_analytics', array($this, 'analytics'));
		add_action('_thesis_tracking_scripts', array($this, 'scripts'));
	}

	protected function admin() {
		global $thesis;
		return array(
			'page' => 'canvas',
			'menu' => 'site',
			'text' => $thesis->api->strings['tracking_scripts']);
	}

	protected function admin_init() {
		global $thesis;
		wp_enqueue_style('thesis-objects', THESIS_CSS_URL . '/objects.css', array('thesis-admin'), $thesis->version);
		wp_enqueue_script('thesis-tracking', THESIS_JS_URL . '/tracking.js', array('thesis-menu'), $thesis->version);
	}

	protected function admin_ajax() {
		add_action('wp_ajax_save_tracking', array($this, 'save'));
	}

	public function analytics() {
		global $thesis;
		if (empty($this->analytics)) return;
		echo
			"<script type=\"text/javascript\">\n".
			"var _gaq = _gaq || [];\n".
			"_gaq.push(['_setAccount', '" . trim($thesis->api->esc($this->analytics)) . "']);\n".
			"_gaq.push(['_trackPageview']);\n".
			"(function() {\n".
			"var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;\n".
			"ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';\n".
			"var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);\n".
			"})();\n".
			"</script>\n";
	}

	public function scripts() {
		if (empty($this->scripts)) return;
		echo trim(stripslashes($this->scripts)) . "\n";
	}

	public function canvas() {
		global $thesis;
		$tab = str_repeat("\t", $depth = 2);
		$tracking = $thesis->api->form->fields(array(
			'analytics' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('Google Analytics Tracking ID', 'thesis'),
				'tooltip' => __('To add Google Analytics tracking to Thesis, simply enter your Tracking ID here. This number takes the general form UA-XXXXXXX-Y and can be found by clicking the Admin link in your Google Analytics dashboard.', 'thesis')),
			'scripts' => array(
				'type' => 'textarea',
				'rows' => 10,
				'code' => true,
				'label' => sprintf(__('Other %s', 'thesis'), $thesis->api->strings['tracking_scripts']),
				'tooltip' => sprintf(__('Any scripts you add here will be displayed just before the closing <code>&lt;/body&gt;</code> tag on every page of your site.<br /><br />If you need to add a script to your %1$s <code>&lt;head&gt;</code>, visit the <a href="%2$s">%1$s Head Editor</a> and click on the <strong>Head Scripts</strong> box.', 'thesis'), $thesis->api->base['html'], admin_url('admin.php?page=thesis&canvas=head')))), array('analytics' => $this->analytics, 'scripts' => $this->scripts), false, false, 10, $depth + 1);
		echo
			"$tab<h3>{$thesis->api->strings['tracking_scripts']}</h3>\n".
			"$tab<form id=\"t_tracking\" method=\"post\" action=\"\">\n".
			$tracking['output'].
			"$tab\t" . wp_nonce_field('thesis-save-tracking-scripts', '_wpnonce-thesis-ajax', true, false) . "\n".
			"$tab\t<input type=\"submit\" data-style=\"button save\" class=\"t_save\" id=\"save_tracking\" name=\"save_tracking\" value=\"" . esc_attr(sprintf(__('%1$s %2$s', 'thesis'), $thesis->api->strings['save'], $thesis->api->strings['tracking_scripts'])) . "\" />\n".
			"$tab</form>\n";
	}

	public function save() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		parse_str(stripslashes($_POST['form']), $form);
		$thesis->wp->nonce($form['_wpnonce-thesis-ajax'], 'thesis-save-tracking-scripts');
		if (is_array($form)) {
			if (isset($form['analytics'])) {
				if (empty($form['analytics']))
					delete_option('thesis_analytics');
				else
					update_option('thesis_analytics', trim($form['analytics']));
			}
			if (isset($form['scripts'])) {
				if (empty($form['scripts']))
					delete_option('thesis_scripts');
				else
					update_option('thesis_scripts', trim($form['scripts']));
			}
			echo $thesis->api->alert(__('Tracking scripts saved!', 'thesis'), 'tracking_saved', true);
		}
		else
			echo $thesis->api->alert(__('Tracking scripts not saved.', 'thesis'), 'tracking_saved', true);
		if ($thesis->environment == 'ajax') die();
	}
}

class thesis_404 extends thesis_box {
	public $type = false;
	private $page = false;

	public function translate() {
		global $thesis;
		$this->title = sprintf(__('404 %s', 'thesis'), $thesis->api->strings['page']);
	}

	public function construct() {
		global $thesis;
		$this->page = is_numeric($page = $thesis->api->get_option('thesis_404')) ? $page : $this->page;
		if (empty($this->page) || is_admin()) return;
		add_filter('thesis_404', array($this, 'query'));
	}

	protected function admin() {
		return array(
			'page' => 'canvas',
			'menu' => 'site',
			'text' => $this->title);
	}

	protected function admin_init() {
		global $thesis;
		wp_enqueue_style('thesis-objects', THESIS_CSS_URL . '/objects.css', array('thesis-admin'), $thesis->version); #wp
		wp_enqueue_style('thesis-404', THESIS_CSS_URL . '/404.css', array('thesis-objects'), $thesis->version); #wp
		wp_enqueue_script('thesis-404', THESIS_JS_URL . '/404.js', array('thesis-menu'), $thesis->version); #wp
	}
	
	protected function admin_ajax() {
		add_action('wp_ajax_save_404', array($this, 'save'));
		add_action('wp_ajax_update_404', array($this, 'update'));
	}

	public function query($query) {
		return $this->page ? new WP_Query("page_id=$this->page") : $query;
	}

	public function canvas() {
		global $thesis;
		$tab = str_repeat("\t", $depth = 2);
		echo
			"$tab<h3>$this->title</h3>\n".
			"$tab<p class=\"primer\">" . __('Designate a 404 page from the dropdown list below, and you&#8217;ll be able to edit your 404 page like any other page of your site!', 'thesis') . "</p>\n".
			"$tab<form id=\"thesis_select_404\" method=\"post\" action=\"\">\n".
			"$tab\t<div class=\"option_field\">\n".
			wp_dropdown_pages(array('name' => 'thesis_404', 'echo' => 0, 'show_option_none' => __('Select a 404 page', 'thesis') . ':', 'option_none_value' => '0', 'selected' => $this->page)).
			"$tab\t</div>\n".
			"$tab\t" . wp_nonce_field('thesis-save-404', '_wpnonce-thesis-ajax', true, false) . "\n".
			"$tab\t<input type=\"submit\" data-style=\"button save\" class=\"t_save\" id=\"save_404\" name=\"save_404\" value=\"" . esc_attr(sprintf(__('%1$s %2$s', 'thesis'), $thesis->api->strings['save'], $this->title)) . "\" />\n".
			"$tab</form>\n".
			(($edit = $this->edit($this->page)) ?
			"$tab$edit\n" : '');
	}

	private function edit($id = false) {
		global $thesis;
		$page = $id ? $id : false;
		return empty($page) || !is_numeric($page) ? false :
			"<a id=\"edit_404\" data-style=\"button action\" href=\"" . admin_url("post.php?post=$page&action=edit") . "\">" . sprintf(__('%1$s %2$s', 'thesis'), $thesis->api->strings['edit'], $this->title) . "</a>";
	}

	public function save() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		parse_str(stripslashes($_POST['form']), $form);
		$thesis->wp->nonce($form['_wpnonce-thesis-ajax'], 'thesis-save-404');
		if (is_numeric($page = $form['thesis_404'])) {
			if ($page == '0')
				delete_option('thesis_404');
			else
				update_option('thesis_404', $page);
			echo $thesis->api->alert(__('404 saved!', 'thesis'), 'saved_404', true);
		}
		else
			echo $thesis->api->alert(__('404 not saved.', 'thesis'), 'saved_404', true);
		if ($thesis->environment == 'ajax') die();
	}

	public function update() {
		global $thesis;
		parse_str(stripslashes($_POST['form']), $form);
		$thesis->wp->nonce($form['_wpnonce-thesis-ajax'], 'thesis-save-404');
		if (is_numeric($form['thesis_404']) && $edit = $this->edit($form['thesis_404']))
			echo $edit;
		if ($thesis->environment == 'ajax') die();
	}
}