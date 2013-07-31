<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_html_head extends thesis_box {
	public $type = 'rotator';
	public $root = true;
	public $head = true;

	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('%s Head', 'thesis'), $thesis->api->base['html']);
	}

	public function html() {
		global $thesis;
		$attributes = apply_filters('thesis_head_attributes', '');
		echo
			"<head", (!empty($attributes) ? " $attributes" : ''), ">\n",
			(($charset = apply_filters('thesis_meta_charset', (!empty($thesis->api->options['blog_charset']) ? $thesis->api->options['blog_charset'] : 'utf-8'))) !== false ?
			"<meta charset=\"". esc_attr(wp_strip_all_tags($charset)) ."\" />\n" : '');
			$this->rotator();
			do_action('hook_head');
		echo
			"</head>\n";
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
					'counter' => $thesis->api->strings['title_counter'])));
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
		$site = !empty($thesis->api->options['blogname']) ? htmlspecialchars_decode($thesis->api->options['blogname'], ENT_QUOTES) : '';
		$separator = !empty($this->options['separator']) ? trim($this->options['separator']) : $this->separator;
		$title = !empty($this->post_meta['title']) ?
			$this->post_meta['title'] : (!empty($this->term_options['title']) ?
			$this->term_options['title'] : (!!$wp_query->is_home || is_front_page() ? (!empty($thesis->api->home_seo->options['title']) ?
			$thesis->api->home_seo->options['title'] : (($tagline = !empty($thesis->api->options['blogdescription']) ? htmlspecialchars_decode($thesis->api->options['blogdescription']) : false) ?
			"$site $separator $tagline" :
			$site)) : (!!$wp_query->is_search ?
			$thesis->api->strings['search']. ': '. esc_html($wp_query->query_vars['s']) :
			wp_title('', false))));
		$title .= ($wp_query->query_vars['paged'] > 1 ?
			" $separator {$thesis->api->strings['page']} {$wp_query->query_vars['paged']}" : '').
			(!empty($this->options['branded']['on']) && !$wp_query->is_home ?
			" $separator $site" : '');
		echo '<title>', trim($thesis->api->escht(apply_filters($this->_class, stripslashes($title), stripslashes($separator)))), "</title>\n";
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
					'counter' => $thesis->api->strings['description_counter'])));
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
		global $thesis, $wp_query, $post;
		$description = !empty($wp_query->is_singular) ? (!empty($this->post_meta['description']) ?
			$this->post_meta['description'] : (!empty($post->post_excerpt) ?
			$post->post_excerpt :
			$thesis->api->trim_excerpt($post->post_content, true))) : (!empty($this->term_options['description']) ?
			$this->term_options['description'] : (!!$wp_query->is_home ? (!empty($thesis->api->home_seo->options['description']) ?
			$thesis->api->home_seo->options['description'] : (!empty($thesis->api->options['blogdescription']) ? 
			htmlspecialchars_decode($thesis->api->options['blogdescription'], ENT_QUOTES) : false)) : false));
		$description = apply_filters($this->_class, stripslashes($description));
		if (!empty($description))
			echo "<meta name=\"description\" content=\"", trim($thesis->api->escht($description)), "\" />\n";
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
					'tooltip' => sprintf(__('Like the %1$s description, %1$s keywords are yet another on-page %2$s opportunity. Enter a few keywords that are relevant to your article, but don&#8217;t go crazy here&#8212;just a few should suffice.', 'thesis'), '<code>&lt;meta&gt;</code>', $thesis->api->base['seo']))));
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
			$this->term_options['keywords'] : (!!$wp_query->is_home && !empty($thesis->api->home_seo->options['keywords']) ?
			$thesis->api->home_seo->options['keywords'] : false));
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
			echo "<meta name=\"keywords\" content=\"", trim($thesis->api->escht($keywords)), "\" />\n";
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
		global $thesis;
		$fields = $merged = array(
			'robots' => array(
				'type' => 'checkbox',
				'options' => array(
					'noindex' => '<code>noindex</code>',
					'nofollow' => '<code>nofollow</code>',
					'noarchive' => '<code>noarchive</code>'),
				'default' => array(
					'noindex' => true)));
		unset($fields['robots']['default']);
		return array(
			'robots' => array(
				'type' => 'object_set',
				'label' => __('Set Robots By Page Type', 'thesis'),
				'select' => __('Select a page type:', 'thesis'),
				'objects' => array(
					'category' => array(
						'type' => 'object',
						'label' => __('Category', 'thesis'),
						'fields' => $fields),
					'post_tag' => array(
						'type' => 'object',
						'label' => __('Tag', 'thesis'),
						'fields' => $fields),
					'tax' => array(
						'type' => 'object',
						'label' => __('Taxonomy', 'thesis'),
						'fields' => $fields),
					'author' => array(
						'type' => 'object',
						'label' => __('Author', 'thesis'),
						'fields' => $merged),
					'day' => array(
						'type' => 'object',
						'label' => __('Daily Archive', 'thesis'),
						'fields' => $merged),
					'month' => array(
						'type' => 'object',
						'label' => __('Monthly Archive', 'thesis'),
						'fields' => $merged),
					'year' => array(
						'type' => 'object',
						'label' => __('Yearly Archive', 'thesis'),
						'fields' => $merged),
					'blog' => array(
						'type' => 'object',
						'label' => __('Blog', 'thesis'),
						'fields' => array(
							'robots' => array(
								'type' => 'checkbox',
								'options' => array(
									'noindex' => '<code>noindex</code> (not recommended)',
									'nofollow' => '<code>nofollow</code> (not recommended)',
									'noarchive' => '<code>noarchive</code> (not recommended)')))),
					'sub' => array(
						'type' => 'object',
						'label' => __('Blog Sub-pages', 'thesis'),
						'fields' => $merged))),
			'directory' => array(
				'type' => 'checkbox',
				'label' => __('Directory Tags (Sitewide)', 'thesis'),
				'tooltip' => sprintf(__('For %s purposes, we recommend turning on both of these options.', 'thesis'), $thesis->api->base['seo']),
				'options' => array(
					'noodp' => '<code>noodp</code>',
					'noydir' => '<code>noydir</code>'),
				'default' => array(
					'noodp' => true,
					'noydir' => true)));
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
						'noarchive' => sprintf(__('<code>noarchive</code> %s', 'thesis'), $thesis->api->strings['this_page'])))));
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
		$options = $thesis->api->get_options($this->_options(), $this->options);
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
			$this->term_options['robots'] : ($wp_query->is_home && empty($page_type) && !empty($options['blog']['robots']) ?
			$options['blog']['robots'] : ($wp_query->is_search || $wp_query->is_404 ?
			array('noindex' => true, 'nofollow' => true, 'noarchive' => true) : (!empty($page_type) && !empty($options[$page_type]['robots']) ?
			$options[$page_type]['robots'] : (!empty($options[$page_type]) ? $options[$page_type] : false)))));
		if (!empty($options['directory']['noodp']))
			$robots['noodp'] = true;
		if (!empty($options['directory']['noydir']))
			$robots['noydir'] = true;
		if (!empty($robots) && is_array($robots))
			foreach ($robots as $tag => $value)
				if ($value)
					$content[] = $tag;
		if (!empty($content))
			echo '<meta name="robots" content="', apply_filters($this->_class, implode(', ', $content)), "\" />\n";
	}
}

class thesis_stylesheets_link extends thesis_box {
	public $head = true;

	protected function translate() {
		$this->title = __('Stylesheets', 'thesis');
	}

	public function html() {
		$styles = $links = array();
		$styles['skin'] = array(
			'url' => is_multisite() && is_user_logged_in() ? admin_url('admin-post.php?action=thesis_do_css') : THESIS_USER_SKIN_URL . '/css.css',
			'media' => 'screen, projection');
		if ($ie_stylesheet = apply_filters('thesis_ie_stylesheet', false)) $styles['ie'] = $ie_stylesheet;
		foreach ($styles as $type => $style)
			$links[$type] = $type == 'ie' ? $style : sprintf('<link rel="stylesheet" type="text/css" href="%1$s" media="%2$s" />', $style['url'], $style['media']);
		if (($viewport = apply_filters('thesis_meta_viewport', false)))
			echo "<meta name=\"viewport\" content=\"", esc_attr(wp_strip_all_tags(is_array($viewport) ? implode(', ', array_filter($viewport)) : $viewport)), "\" />\n";
		if (($font_script = apply_filters('thesis_font_script', false)))
			echo "<script src=\"", esc_url($font_script), "\"></script>\n";
		if (($font_stylesheet = apply_filters('thesis_font_stylesheet', false)))
			echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"", esc_url($font_stylesheet), "\" />\n";
		if (!empty($links) && !((is_user_logged_in() && current_user_can('edit_theme_options')) && (!empty($_GET['thesis_editor']) && $_GET['thesis_editor'] === '1' || !empty($_GET['thesis_canvas']) && in_array($_GET['thesis_canvas'], array(1, 2)))))
			echo implode("\n", $links), "\n";
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
					'description' => $thesis->api->strings['include_http'])));
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
			echo "<link rel=\"canonical\" href=\"", esc_url(apply_filters($this->_class, stripslashes($url))), "\" />\n";
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
				'rows' => 8,
				'code' => true,
				'label' => __('Scripts', 'thesis'),
				'tooltip' => __('If you wish to add scripts that will only function properly when placed in the document <code>&lt;head&gt;</code>, you should add them here.<br /><br /><strong>Note:</strong> Only do this if you have no other option. Scripts placed in the <code>&lt;head&gt;</code> can have a negative impact on site performance.', 'thesis'),
				'description' => __('include <code>&lt;script&gt;</code> and other tags as necessary', 'thesis')));
	}

	public function html() {
		if (empty($this->options['scripts'])) return;
		echo trim(stripslashes($this->options['scripts'])), "\n";
	}
}

class thesis_html_body extends thesis_box {
	public $type = 'rotator';
	public $root = true;
	public $switch = true;

	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('%s Body', 'thesis'), $thesis->api->base['html']);
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options();
		unset($html['id']);
		return array_merge($html, array(
			'wp' => array(
				'type' => 'checkbox',
				'label' => __('Automatic WordPress Body Classes', 'thesis'),
				'tooltip' => sprintf(__('WordPress can output body classes that allow you to target specific types of posts and content more easily. You may experience a %1$s naming conflict if you use this option (and most of the output adds unnecessary weight to the %2$s), so we do not recommend it.', 'thesis'), $thesis->api->base['class'], $thesis->api->base['html']),
				'options' => array(
					'auto' => __('Use automatically-generated WordPress <code>&lt;body&gt;</code> classes', 'thesis')))));
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
					'tooltip' => sprintf(__('If you want to style this post individually, you should enter a %1$s name here. Anything you enter here will appear on this page&#8217;s <code>&lt;body&gt;</code> tag. Separate multiple classes with spaces.<br /></br /><strong>Note:</strong> %1$s names cannot begin with numbers!', 'thesis'), $thesis->api->base['class']))));
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
		echo "<body", $this->classes(), ">\n";
		do_action('hook_before_html');
		$this->rotator();
		do_action('hook_after_html');
		echo "\n</body>\n";
	}

	private function classes() {
		global $thesis;
		$classes = array();
		if (!empty($this->post_meta['class']))
			$classes[] = trim(stripslashes($this->post_meta['class']));
		if (!empty($this->template_options['class']))
			$classes[] = trim(stripslashes($this->template_options['class']));
		if (!empty($this->options['class']))
			$classes[] = trim($thesis->api->esc($this->options['class']));
		$classes = is_array($filtered = apply_filters("{$this->_class}_class", $classes)) && !empty($filtered) ? $filtered : $classes;
		if (!empty($this->options['wp']['auto']))
			$classes = is_array($wp = get_body_class()) ? array_merge($classes, $wp) : $classes;
		return !empty($classes) ?
			' class="'. trim(esc_attr(implode(' ', $classes))). '"' : '';
	}
}

class thesis_html_container extends thesis_box {
	public $type = 'rotator';

	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('%1$s %2$s', 'thesis'), $thesis->api->base['html'], $this->name = __('Container', 'thesis'));
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array(
			'div' => 'div',
			'p' => 'p',
			'section' => 'section',
			'article' => 'article',
			'header' => 'header',
			'footer' => 'footer',
			'aside' => 'aside',
			'span' => 'span',
			'nav' => 'nav',
			'none' => sprintf(__('no %s wrap', 'thesis'), $thesis->api->base['html'])), 'div');
		$html['html']['dependents'] =
			array('div', 'p', 'section', 'article', 'header', 'footer', 'aside', 'span', 'nav');
		$html['id']['parent'] = $html['class']['parent'] =
			array('html' => array('div', 'p', 'section', 'article', 'header', 'footer', 'aside', 'span', 'nav'));
		return $html;
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", $depth = !empty($depth) ? $depth : 0);
		$html = !empty($this->options['html']) ? $this->options['html'] : 'div';
		$hook = trim($thesis->api->esc(!empty($this->options['_id']) ?
			$this->options['_id'] : (!empty($this->options['hook']) ?
			$this->options['hook'] : $this->_id)));
		do_action("thesis_hook_before_container_$hook");
		do_action("hook_before_$hook");
		if ($html != 'none') {
			echo
				"$tab<$html", (!empty($this->options['id']) ? ' id="'. trim($thesis->api->esc($this->options['id'])). '"' : ''),
				(!empty($this->options['class']) ? ' class="' . trim($thesis->api->esc($this->options['class'])). '"' : ''), ">\n";
			do_action("thesis_hook_container_{$hook}_top");
			do_action("hook_top_$hook");
		}
		$this->rotator(array_merge($args, array('depth' => $html == 'none' ? $depth : $depth + 1)));
		if ($html != 'none') {
			do_action("thesis_hook_container_{$hook}_bottom");
			do_action("hook_bottom_$hook");
			echo
				"$tab</$html>\n";
		}
		do_action("thesis_hook_after_container_$hook");
		do_action("hook_after_$hook");
	}
}

class thesis_site_title extends thesis_box {
	protected function translate() {
		$this->title = __('Site Title', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array('div' => 'div', 'p' => 'p'), 'div');
		$html['html']['tooltip'] = __('Your site title will be contained within <code>&lt;h1&gt;</code> tags on your home page, but the tag you specify here will be used on all other pages.', 'thesis');
		unset($html['id'], $html['class']);
		return $html;
	}

	public function html($args = array()) {
		global $thesis, $wp_query; #wp
		if (!($text = apply_filters($this->_class, !empty($thesis->api->options['blogname']) ?
			htmlspecialchars_decode($thesis->api->options['blogname'], ENT_QUOTES) : false))) return;
		extract($args = is_array($args) ? $args : array());
		$html = apply_filters("{$this->_class}_html", $wp_query->is_home || is_front_page() ? 'h1' : (!empty($this->options['html']) ? $this->options['html'] : 'div')); #wp
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			"<$html id=\"site_title\">",
			"<a href=\"", esc_url(home_url()), "\">", #wp
			trim($thesis->api->escht(apply_filters($this->_class, $text))),
			"</a></$html>\n";
	}
}

class thesis_site_tagline extends thesis_box {
	protected function translate() {
		$this->title = __('Site Tagline', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array('div' => 'div', 'p' => 'p'), 'div');
		unset($html['id'], $html['class']);
		return $html;
	}

	public function html($args = array()) {
		global $thesis;
		if (!($text = apply_filters($this->_class, !empty($thesis->api->options['blogdescription']) ?
			htmlspecialchars_decode($thesis->api->options['blogdescription'], ENT_QUOTES) : false))) return;
		extract($args = is_array($args) ? $args : array());
		$html = apply_filters("{$this->_class}_html", !empty($this->options['html']) ? $this->options['html'] : 'div');
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			"<$html id=\"site_tagline\">",
			trim($thesis->api->escht($text)),
			"</$html>\n";
	}
}

class thesis_wp_nav_menu extends thesis_box {
	protected function translate() {
		global $thesis;
		$this->name = __('Nav Menu', 'thesis');
		$this->title = sprintf(__('%1$s (%2$s)', 'thesis'), $this->name, $thesis->api->base['wp']);
		$this->control = '≡ '. __('Menu', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		return array(
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
				'placeholder' => 'menu'),
			'control' => array(
				'type' => 'checkbox',
				'label' => __('Responsive Menu Control', 'thesis'),
				'options' => array(
					'yes' => __('Output menu control button for responsive layouts', 'thesis')),
				'dependents' => array('yes')),
			'control_text' => array(
				'type' => 'text',
				'width' => 'short',
				'label' => __('Menu Control Text', 'thesis'),
				'default' => $this->control,
				'parent' => array(
					'control' => 'yes')));
	}

	protected function options() {
		$menus[''] = __('Select a WP menu:', 'thesis');
		foreach (wp_get_nav_menus() as $menu)
			$menus[(int) $menu->term_id] = esc_attr($menu->name);
		return array(
			'menu' => array(
				'type' => 'select',
				'label' => __('Menu To Display', 'thesis'),
				'tooltip' => sprintf(__('Select a WordPress nav menu for this box to display. To edit your menus, visit the <a href="%s">WordPress nav menu editor</a>.', 'thesis'), admin_url('nav-menus.php')),
				'options' => $menus));
	}

	public function preload() {
		static $did = false;
		if (!$did)
			add_filter('thesis_footer_scripts', array($this, 'js'));
		$did = true;
	}

	public function html($args = array()) {
		extract($args = is_array($args) ? $args : array());
		add_filter('wp_page_menu', array($this, 'filter_menu'), 10, 2);
		$menu = wp_nav_menu(array_merge($this->options, array('container' => false, 'echo' => false, 'thesis' => true)));
		remove_filter('wp_page_menu', array($this, 'filter_menu'), 10, 2);
		echo str_repeat("\t", !empty($depth) ? $depth : 0),
			(!empty($this->options['control']['yes']) ?
			"<span class=\"menu_control\">". (!empty($this->options['control_text']) ?
				esc_html($this->options['control_text']) : $this->control). "</span>\n" : ''),
			apply_filters($this->_class, $menu), "\n";
	}

	public function filter_menu($menu, $args) {
		if (empty($args['thesis']))
			return $menu;
		preg_match('/<ul>(.*)<\/ul>/', $menu, $li);
		return "<ul class=\"". esc_attr($args['menu_class']). "\">{$li[1]}</ul>";
	}

	public function js() {
		$class = !empty($this->options['menu_class']) ? array_pop(explode(' ', $this->options['menu_class'])) : 'menu';
		return !empty($this->options['control']['yes']) ? array(
			"<script type=\"text/javascript\">\n".
			"(function(){\n".
			"\tvar classes = document.getElementsByClassName('menu_control');\n".
			"\tfor (i = 0; i < classes.length; i++) {\n".
			"\t\tclasses[i].onclick = function() {\n".
			"\t\t\tvar menu = this.nextSibling;\n".
			"\t\t\tdo {\n".
			"\t\t\t\tmenu = menu.nextSibling;\n".
			"\t\t\t} while (menu && menu.nodeType != 1);\n".
			"\t\t\tif (/show_menu/.test(menu.className))\n".
			"\t\t\t\tmenu.className = menu.className.replace('show_menu', '');\n".
			"\t\t\telse\n".
			"\t\t\t\tmenu.className += ' show_menu';\n".
			"\t\t};\n".
			"\t}\n".
			"})();\n".
			"</script>\n".
			"<noscript><style type=\"text/css\" scoped>.". esc_attr($class). " { display: block; }</style></noscript>") : '';
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

	public function html($args = array()) {
		global $thesis, $wp_query, $post;
		extract($args = is_array($args) ? $args : array());
		$post_count = 1;
		if ($wp_query->is_404)
			$wp_query = apply_filters('thesis_404', $wp_query);
		if (apply_filters('thesis_use_custom_loop', false))
			do_action('thesis_custom_loop', $args);
		else {
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

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array(
			'div' => 'div',
			'section' => 'section',
			'article' => 'article'), 'div');
		unset($html['id']);
		$html['class']['tooltip'] = sprintf(__('This box already contains a %1$s, <code>post_box</code>. If you wish to add an additional %1$s, you can do that here. Separate multiple %1$ses with spaces.%2$s', 'thesis'), $thesis->api->base['class'], $thesis->api->strings['class_note']);
		return array_merge($html, array(
			'wp' => array(
				'type' => 'checkbox',
				'label' => $thesis->api->strings['auto_wp_label'],
				'tooltip' => $thesis->api->strings['auto_wp_tooltip'],
				'options' => array(
					'auto' => $thesis->api->strings['auto_wp_option'])),
			'schema' => $thesis->api->schema->select()));
	}

	public function html($args = array()) {
		global $thesis, $wp_query, $post; #wp
		extract($args = is_array($args) ? $args : array());
		$classes = array();
		$tab = str_repeat("\t", $depth = !empty($depth) ? $depth : 0);
		$post_count = !empty($post_count) ? $post_count : false;
		$html = !empty($this->options['html']) ? $this->options['html'] : 'div';
		if (!empty($this->options['class']))
			$classes[] = trim($thesis->api->esc($this->options['class']));
		if (empty($post_count) || $post_count == 1)
			$classes[] = 'top';
		if (!empty($this->options['wp']['auto']))
			$classes = is_array($wp = get_post_class()) ? $classes + $wp : $classes;
		$schema = !empty($this->options['schema']) ? $this->options['schema'] : false;
		$hook = trim($thesis->api->esc(!empty($this->options['_id']) ?
			$this->options['_id'] : (!empty($this->options['hook']) ?
			$this->options['hook'] : $this->_id)));
		do_action("thesis_hook_before_post_box_$hook", $post_count);
		do_action("hook_before_$hook", $post_count);
		echo "$tab<$html", ($wp_query->is_404 ? '' : " id=\"post-$post->ID\""), ' class="post_box', (!empty($classes) ? ' '. trim(esc_attr(implode(' ', $classes))) : ''), '"', ($schema ? ' itemscope itemtype="'. esc_url($thesis->api->schema->types[$schema]). '"' : ''), ">\n"; #wp
		do_action("thesis_hook_post_box_{$hook}_top", $post_count);
		do_action("hook_top_$hook", $post_count);
		$this->rotator(array_merge($args, array('depth' => $depth + 1, 'schema' => $schema)));
		do_action("thesis_hook_post_box_{$hook}_bottom", $post_count);
		do_action("hook_bottom_$hook", $post_count);
		echo "$tab</$html>\n";
		do_action("thesis_hook_after_post_box_$hook", $post_count);
		do_action("hook_after_$hook", $post_count);
	}
}

class thesis_post_list extends thesis_box {
	public $type = 'rotator';
	public $dependents = array(
		'thesis_post_headline',
		'thesis_post_date',
		'thesis_post_author',
		'thesis_post_author_avatar',
		'thesis_wp_featured_image',
		'thesis_post_num_comments',
		'thesis_post_edit');
	public $children = array(
		'thesis_post_headline',
		'thesis_post_num_comments',
		'thesis_post_edit');
	public $templates = array(
		'home',
		'archive');

	protected function translate() {
		$this->title = $this->name = __('Post List', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array(
			'ul' => 'ul',
			'ol' => 'ol'), 'ul');
		unset($html['id']);
		return array_merge($html, array(
			'schema' => $thesis->api->schema->select()));
	}

	public function html($args = array()) {
		global $thesis, $wp_query, $post; #wp
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", $depth = !empty($depth) ? $depth : 0);
		$post_count = !empty($post_count) ? $post_count : false;
		$schema = !empty($this->options['schema']) ? $this->options['schema'] : false;
		$html = $hook = false;
		if (!empty($post_count) && ($post_count == 1 || ($wp_query->post_count > 1 && $post_count == $wp_query->post_count))) {
			$html = !empty($this->options['html']) ? $this->options['html'] : 'ul';
			$class = !empty($this->options['class']) ? trim($thesis->api->esc($this->options['class'])) : false;
			$hook = trim($thesis->api->esc(!empty($this->options['_id']) ?
				$this->options['_id'] : (!empty($this->options['hook']) ?
				$this->options['hook'] :
				$this->_id)));
		}
		if (!empty($post_count) && $post_count == 1) {
			do_action("hook_before_$hook", $post_count);
			echo "$tab<$html class=\"post_list", (!empty($class) ? " $class" : ''), "\">\n";
		}
		$tab = "$tab\t";
		$depth = $depth + 1;
		echo "$tab<li id=\"post-$post->ID\"", ($schema ? ' itemscope itemtype="'. esc_url($thesis->api->schema->types[$schema]). '"' : ''), ">\n";
		$this->rotator(array_merge($args, array('depth' => $depth + 1, 'schema' => $schema)));
		echo "$tab</li>\n";
		if ($wp_query->post_count > 1 && $post_count == $wp_query->post_count) {
			$tab = str_repeat("\t", $depth - 1);
			echo "$tab</$html>\n";
			do_action("hook_after_$hook", $post_count);
		}
	}
}

class thesis_post_headline extends thesis_box {
	protected function translate() {
		$this->title = __('Headline', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array(
			'h1' => 'h1',
			'h2' => 'h2',
			'h3' => 'h3',
			'h4' => 'h4',
			'p' => 'p',
			'span' => 'span'), 'h1');
		$html['class']['tooltip'] = sprintf(__('This box already contains a %1$s, <code>headline</code>. If you wish to add an additional %1$s, you can do that here. Separate multiple %1$ses with spaces.%2$s', 'thesis'), $thesis->api->base['class'], $thesis->api->strings['class_note']);
		unset($html['id']);
		return array_merge($html, array(
			'link' => array(
				'type' => 'checkbox',
				'options' => array(
					'on' => __('Link headline to article page', 'thesis')))));
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$html = !empty($this->options['html']) ? $this->options['html'] : 'h1';
		$class = !empty($this->options['class']) ? " {$thesis->api->esc($this->options['class'])}" : '';
	 	echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			"<$html class=\"headline$class\"", (!empty($schema) ? ' itemprop="name"' : ''), '>',
			(!empty($this->options['link']['on']) ?
			'<a href="'. get_permalink(). '" rel="bookmark">'. get_the_title(). '</a>' :
			get_the_title()),
			"</$html>\n";
	}
}

class thesis_post_author extends thesis_box {
	protected function translate() {
		$this->title = __('Author', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options();
		$html['class']['tooltip'] = sprintf(__('This box already contains a %1$s of <code>post_author</code>. If you&#8217;d like to supply another %1$s, you can do that here.%2$s', 'thesis'), $thesis->api->base['class'], $thesis->api->strings['class_note']);
		unset($html['id']);
		return array_merge($html, array(
			'intro' => array(
				'type' => 'text',
				'width' => 'short',
				'label' => __('Author Intro Text', 'thesis'),
				'tooltip' => sprintf(__('Any text you supply here will be wrapped in %s, like so:<br /><code>&lt;span class="post_author_intro"&gt</code>your text<code>&lt;/span&gt;</code>.', 'thesis'), $thesis->api->base['html'])),
			'link' => array(
				'type' => 'checkbox',
				'options' => array(
					'on' => __('Link author names to archives', 'thesis')),
				'dependents' => array('on')),
			'nofollow' => array(
				'type' => 'checkbox',
				'options' => array(
					'on' => __('Add <code>nofollow</code> to author link', 'thesis')),
				'parent' => array(
					'link' => 'on'))));
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$author = !empty($this->options['link']['on']) ?
			'<a href="'. esc_url(get_author_posts_url(get_the_author_meta('ID'))). '"'. (!empty($this->options['nofollow']['on']) ?
				' rel="nofollow"' : ''). '>'. get_the_author(). '</a>' :
			get_the_author();
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0), (!empty($this->options['intro']) ?
			'<span class="post_author_intro">'. $thesis->api->esch($this->options['intro']). '</span> ' : ''),
			apply_filters($this->_class,
			'<span class="post_author'. (!empty($this->options['class']) ?
				' '. trim($thesis->api->esc($this->options['class'])) : ''). '"'. (!empty($schema) ?
				' itemprop="author"' : ''). ">$author</span>"), "\n";
	}
}

class thesis_post_author_avatar extends thesis_box {
	protected function translate() {
		$this->title = __('Author Avatar', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		return array(
			'size' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => $thesis->api->strings['avatar_size'],
				'tooltip' => $thesis->api->strings['avatar_tooltip'],
				'description' => 'px'));
	}

	public function html($args = array()) {
		global $post;
		extract($args = is_array($args) ? $args : array());
		echo str_repeat("\t", !empty($depth) ? $depth : 0) . get_avatar(
			$post->post_author,
			!empty($this->options['size']) && is_numeric($this->options['size']) ? $this->options['size'] : false, false). "\n";
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
			'shortcode_unautop' => false,
			'do_shortcode' => false));
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array('div' => 'div', 'p' => 'p'), 'p');
		unset($html['id'], $html['class']);
		return array_merge($html, array(
			'display' => array(
				'type' => 'checkbox',
				'options' => array(
					'intro' => __('Show author description intro text', 'thesis'),
					'avatar' => __('Include author avatar', 'thesis')),
				'default' => array(
					'intro' => true,
					'avatar' => true),
				'dependents' => array('intro')),
			'intro' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('Description Intro Text', 'thesis'),
				'placeholder' => __('About the author:', 'thesis'),
				'parent' => array(
					'display' => 'intro'))));
	}

	public function html($args = array()) {
		global $thesis, $post;
		if (($text = apply_filters($this->_class, get_the_author_meta('user_description', get_the_author_meta('ID')))) == '') return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$options = $thesis->api->get_options(array_merge($this->_html_options(), $this->_options()), $this->options);
		$html = !empty($options['html']) ? $options['html'] : 'p';
		echo
			"$tab<$html class=\"author_description\">\n",
			(!empty($options['display']['avatar']) ?
			"$tab\t". get_avatar($post->post_author, false, false). "\n" : ''),
			"$tab\t", (!empty($options['display']['intro']) ?
			'<span class="author_description_intro">'.
			trim($thesis->api->escht(!empty($options['intro']) ? $options['intro'] : __('About the author:', 'thesis'), true)).
			'</span> ' : ''),
			trim($text), "\n",
			"$tab</$html>\n";
	}
}

class thesis_post_date extends thesis_box {
	protected function translate() {
		$this->title = __('Date', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options();
		$html['class']['tooltip'] = sprintf(__('This box already contains a %1$s of <code>post_date</code>. If you&#8217;d like to supply another %1$s, you can do that here.%2$s', 'thesis'), $thesis->api->base['class'], $thesis->api->strings['class_note']);
		unset($html['id'], $html['class']);
		return array_merge($html, array(
			'format' => array(
				'type' => 'text',
				'width' => 'short',
				'code' => true,
				'label' => __('Date Format', 'thesis'),
				'tooltip' => $thesis->api->strings['date_tooltip'],
				'default' => get_option('date_format')),
			'intro' => array(
				'type' => 'text',
				'width' => 'short',
				'label' => __('Date Intro Text', 'thesis'),
				'tooltip' => sprintf(__('Any text you supply here will be wrapped in %s, like so:<br /><code>&lt;span class="post_date_intro"&gt</code>your text<code>&lt;/span&gt;</code>.', 'thesis'), $thesis->api->base['html'])),
			'schema' => array(
				'type' => 'checkbox',
				'label' => __('If a Markup Schema Is Present&hellip;', 'thesis'),
				'tooltip' => __('If a markup schema is present, this box will output the date <code>&lt;meta&gt;</code> automatically. This option is only intended to control whether or not the date actually displays on the page when a schema is present.', 'thesis'),
				'options' => array(
					'only' => sprintf(__('do not show the date, but include the date <code>&lt;meta&gt;</code> in the %s', 'thesis'), $thesis->api->base['html'])))));
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$time = get_the_time('Y-m-d');
		$format = strip_tags(!empty($this->options['format']) ?
			stripslashes($this->options['format']) :
			apply_filters("{$this->_class}_format", get_option('date_format')));
		echo
			(!empty($schema) ?
			"$tab<meta itemprop=\"datePublished\" content=\"$time\" />\n".
			"$tab<meta itemprop=\"dateModified\" content=\"". get_the_modified_date('Y-m-d'). "\" />\n" : ''),
			(empty($schema) || (!empty($schema) && !isset($this->options['schema']['only'])) ?
			$tab. (!empty($this->options['intro']) ?
			'<span class="post_date_intro">'. $thesis->api->esch($this->options['intro']). '</span> ' : '').
			"<span class=\"post_date". (!empty($this->options['class']) ? ' '. trim($thesis->api->esc($this->options['class'])) : ''). "\" title=\"$time\">".
			get_the_time($format).
			"</span>\n" : '');
	}
}

class thesis_post_edit extends thesis_box {
	protected function translate() {
		global $thesis;
		$this->title = __('Edit Link', 'thesis');
		$this->edit = apply_filters("{$this->_class}_text", strtolower($thesis->api->strings['edit']));
	}

	protected function html_options() {
		global $thesis;
		return array(
			'text' => array(
				'type' => 'text',
				'label' => sprintf(__('%s Text', 'thesis'), $this->title),
				'tooltip' => sprintf(__('The default edit link text is &lsquo;%s&rsquo;, but you can change that by entering your own text here.', 'thesis'), $this->edit),
				'placeholder' => $this->edit));
	}

	public function html($args = array()) {
		global $thesis;
		$url = get_edit_post_link();
		if (empty($url)) return;
		extract($args = is_array($args) ? $args : array());
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			"<a class=\"post_edit\" href=\"$url\" title=\"{$thesis->api->strings['click_to_edit']}\" rel=\"nofollow\">",
			trim(!empty($this->options['text']) ? $thesis->api->esch($this->options['text']) : $this->edit),
			"</a>\n";
	}
}

class thesis_post_content extends thesis_box {
	protected function translate() {
		$this->title = __('Content', 'thesis');
		$this->custom = __('Custom &ldquo;Read More&rdquo; Text', 'thesis');
		$this->read_more = apply_filters("{$this->_class}_read_more", __('[click to continue&hellip;]', 'thesis'));
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options();
		$html['class']['tooltip'] = sprintf(__('This box already contains a %1$s of <code>post_content</code>. If you&#8217;d like to supply another %1$s, you can do that here.%2$s', 'thesis'), $thesis->api->base['class'], $thesis->api->strings['class_note']);
		unset($html['id']);
		return array_merge($html, array(
			'read_more' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('&ldquo;Read More&rdquo; Text', 'thesis'),
				'tooltip' => sprintf(__('If you use <code>&lt;!--more--&gt;</code> within your post, the text you enter here will be shown to your visitors to encourage them to click through (on blog and archive pages only).<br/><br/>You can override this text on any post or page by filling out the <strong>%s</strong> field on the post editing screen.', 'thesis'), $this->custom),
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
					'tooltip' => __('If you use <code>&lt;!--more--&gt;</code> within your post, you can specify custom &ldquo;Read More&rdquo; text here. If you don&#8217;t specify anything, Thesis will use the default text. Please note that the &ldquo;Read More&rdquo; text only appears on blog and archive pages.', 'thesis'))));
	}

	public function html($args = array()) {
		global $thesis, $wp_query;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$schema = !empty($schema) ? ' itemprop="' . ($schema == 'article' ? 'articleBody' : 'text') . '"' : '';
		echo "$tab<div class=\"post_content", (!empty($this->options['class']) ? ' ' . trim($thesis->api->esc($this->options['class'])) : ''), "\"$schema>\n";
		do_action('thesis_hook_before_post');
		do_action('hook_before_post');
		the_content(trim($thesis->api->escht(!empty($this->post_meta['read_more']) ? #wp
			$this->post_meta['read_more'] : (!empty($this->options['read_more']) ?
			$this->options['read_more'] :
			$this->read_more), true)));
		if ($wp_query->is_singular) wp_link_pages("<p><strong>{$thesis->api->strings['pages']}:</strong> ", '</p>', 'number'); #wp
		do_action('thesis_hook_after_post');
		do_action('hook_after_post');
		echo "$tab</div>\n";
	}
}

class thesis_post_excerpt extends thesis_box {
	protected function translate() {
		$this->title = __('Excerpt', 'thesis');
	}

	protected function construct() {
		global $thesis;
		$thesis->wp->filter($this->_class, array('wpautop' => false));
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options();
		$html['style'] = array(
			'type' => 'radio',
			'label' => __('Excerpt Type', 'thesis'),
			'tooltip' => __('The Thesis enhanced excerpt strips <code>h1</code>-<code>h4</code> tags and images, in addition to the typical items removed by WordPress.', 'thesis'),
			'options' => array(
				'thesis' => __('Thesis enhanced (recommended)', 'thesis'),
				'wp' => __('WordPress default', 'thesis')),
			'default' => 'thesis');
		unset($html['id']);
		return $html;
	}

	public function html($args = array()) {
		global $thesis, $post;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		echo
			"$tab<div class=\"post_content post_excerpt", (!empty($this->options['class']) ? ' '. trim($thesis->api->esc($this->options['class'])) : ''), '"', (!empty($schema) ? ' itemprop="description"' : ''), ">\n",
			apply_filters($this->_class, empty($this->options['style']) ? (!empty($post->post_excerpt) ? $post->post_excerpt : $thesis->api->trim_excerpt($post->post_content)) : get_the_excerpt()), #wp
			"$tab</div>\n";
	}
}

class thesis_post_num_comments extends thesis_box {
	protected function translate() {
		$this->title = __('Number of Comments', 'thesis');
	}

	protected function html_options() {
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
				'dependents' => array('term')),
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

	public function html($args = array()) {
		global $thesis;
		$options = $thesis->api->get_options(array_merge($this->_html_options(), $this->_options()), $this->options);
		if (!(comments_open() || (!comments_open() && !empty($options['display']['closed'])))) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$number = get_comments_number(); #wp
		echo (!empty($schema) ?
			"$tab<meta itemprop=\"interactionCount\" content=\"UserComments:$number\" />\n" : ''),
			$tab, apply_filters($this->_class, (!empty($options['display']['link']) ?
				'<a class="num_comments_link" href="'. get_permalink(). ($number > 0 ? '#comments' : '#commentform'). '" rel="nofollow">' : '').
				"<span class=\"num_comments\">$number</span>".
				(!empty($options['display']['term']) ?
			 	' '. trim($thesis->api->esch($number == 1 ? (!empty($options['singular']) ?
				$options['singular'] : $thesis->api->strings['comment_singular']) : (!empty($options['plural']) ?
				$options['plural'] : $thesis->api->strings['comment_plural']))) : '').
				(!empty($options['display']['link']) ?
				'</a>' : '')), "\n";
	}
}

class thesis_post_categories extends thesis_box {
	protected function translate() {
		$this->title = __('Categories', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array(
			'p' => 'p',
			'div' => 'div',
			'span' => 'span'), 'p');
		unset($html['id'], $html['class']);
		return array_merge($html, array(
			'intro' => array(
				'type' => 'text',
				'width' => 'short',
				'label' => $thesis->api->strings['intro_text'],
				'tooltip' => sprintf(__('Any intro text you provide will precede the post category output, and it will be wrapped in %s, like so: <code>&lt;span class="post_cats_intro"&gt;</code>your text<code>&lt;/span&gt;</code>.', 'thesis'), $thesis->api->base['html'])),
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

	public function html($args = array()) {
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
			"$tab<$html class=\"post_cats\"", (!empty($schema) ? ' itemprop="keywords"' : ''), ">\n",
			(!empty($this->options['intro']) ?
			"$tab\t<span class=\"post_cats_intro\">" . trim($thesis->api->escht($this->options['intro'], true)) . "</span>\n" : ''),
			"$tab\t", implode((!empty($this->options['separator']) ? trim($thesis->api->esch($this->options['separator'])) : '') . "\n$tab\t", $cats), "\n",
			"$tab</$html>\n"; #wp
	}
}

class thesis_post_tags extends thesis_box {
	protected function translate() {
		$this->title = __('Tags', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array(
			'p' => 'p',
			'div' => 'div',
			'span' => 'span'), 'p');
		unset($html['id'], $html['class']);
		return array_merge($html, array(
			'intro' => array(
				'type' => 'text',
				'width' => 'short',
				'label' => $thesis->api->strings['intro_text'],
				'tooltip' => sprintf(__('Any intro text you provide will precede the post tag output, and it will be wrapped in %s, like so: <code>&lt;span class="post_tags_intro"&gt;</code>.', 'thesis'), $thesis->api->base['html'])),
			'separator' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => $thesis->api->strings['character_separator'],
				'tooltip' => __('If you&#8217;d like to separate your tags with a particular character (a comma, for instance), you can do that here.', 'thesis')),
			'nofollow' => array(
				'type' => 'checkbox',
				'options' => array(
					'on' => __('Add <code>nofollow</code> to tag links', 'thesis')))));
	}

	public function html($args = array()) {
		global $thesis;
		if (!is_array($post_tags = get_the_tags())) return; #wp
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$tags = array();
		$html = apply_filters("{$this->_class}_html", !empty($this->options['html']) ? $this->options['html'] : 'p');
		$nofollow = !empty($this->options['nofollow']['on']) ? ' nofollow' : '';
		foreach ($post_tags as $tag)
			$tags[] = "<a href=\"" . esc_url(get_tag_link($tag->term_id)) . "\" rel=\"tag$nofollow\">$tag->name</a>"; #wp
		echo
			"$tab<$html class=\"post_tags\"", (!empty($schema) ? ' itemprop="keywords"' : ''), ">\n",
			(!empty($this->options['intro']) ?
			"$tab\t<span class=\"post_tags_intro\">" . trim($thesis->api->escht($this->options['intro'], true)) . "</span>\n" : ''),
			"$tab\t", implode((!empty($this->options['separator']) ? trim($thesis->api->esch($this->options['separator'])) : '') . "\n$tab\t", $tags), "\n",
			"$tab</$html>\n";
	}
}

class thesis_post_image extends thesis_box {
	protected function translate() {
		$this->image_type = __('Post Image', 'thesis');
		$this->title = sprintf(__('Thesis %s', 'thesis'), $this->image_type);
	}

	protected function construct() {
		global $thesis;
		if (empty($thesis->_post_image_rss) && $this->_display()) {
			add_filter('the_content', array($this, 'add_image_to_feed'));
			$thesis->_post_image_rss = true;
		}
	}

	protected function html_options() {
		global $thesis;
		return array(
			'alignment' => array(
				'type' => 'select',
				'label' => $thesis->api->strings['alignment'],
				'tooltip' => $thesis->api->strings['alignment_tooltip'],
				'options' => array(
					'' => $thesis->api->strings['alignnone'],
					'left' => $thesis->api->strings['alignleft'],
					'right' => $thesis->api->strings['alignright'],
					'center' => $thesis->api->strings['aligncenter'])),
			'link' => array(
				'type' => 'checkbox',
				'options' => array(
					'link' => __('Link image to post', 'thesis')),
				'default' => array(
					'link' => true)));
	}

	protected function post_meta() {
		global $thesis;
		return array(
			'title' => $this->title,
			'fields' => array(
				'image' => array(
					'type' => 'add_media',
					'upload_label' => sprintf(__('Upload a %s', 'thesis'), $this->image_type),
					'tooltip' => sprintf(__('Upload a %1$s here, or else input the %2$s of an image you&#8217;d like to use in the <strong>%3$s %2$s</strong> field below.', 'thesis'), strtolower($this->image_type), $thesis->api->base['url'], $this->image_type),
					'label' => "$this->image_type {$thesis->api->base['url']}"),
				'alt' => array(
					'type' => 'text',
					'width' => 'full',
					'label' => sprintf(__('%s <code>alt</code> Text', 'thesis'), $this->image_type),
					'tooltip' => $thesis->api->strings['alt_tooltip']),
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
						'on' => $thesis->api->strings['frame_option'])),
				'alignment' => array(
					'type' => 'select',
					'label' => $thesis->api->strings['alignment'],
					'tooltip' => $thesis->api->strings['alignment_tooltip'],
					'options' => array(
						'' => $thesis->api->strings['skin_default'],
						'left' => $thesis->api->strings['alignleft'],
						'right' => $thesis->api->strings['alignright'],
						'center' => $thesis->api->strings['aligncenter'],
						'flush' => $thesis->api->strings['alignnone']))));
	}

	public function html($args = array()) {
		global $thesis, $wp_query; #wp
		if (empty($this->post_meta['image']) || !is_array($this->post_meta['image'])) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$attachment = !empty($this->post_meta['image']['id']) ? get_post($this->post_meta['image']['id']) : false;
		$alt = !empty($this->post_meta['alt']) ?
			$this->post_meta['alt'] : (!empty($this->post_meta['image']['id']) && ($wp_alt = get_post_meta($this->post_meta['image']['id'], '_wp_attachment_image_alt', true)) ?
			$wp_alt : get_the_title(). ' '. strtolower($this->image_type));
		$caption = !empty($this->post_meta['caption']) ?
			$this->post_meta['caption'] : (is_object($attachment) && $attachment->post_excerpt ?
			$attachment->post_excerpt : false);
		$align = !empty($this->post_meta['alignment']) ?
			$this->post_meta['alignment'] : (!empty($this->options['alignment']) ?
			$this->options['alignment'] : false);
		$alignment = !empty($align) ? ' '. ($align == 'left' ?
			'alignleft' : ($align == 'right' ?
			'alignright' : ($align == 'center' ?
			'aligncenter' : 'alignnone'))) : '';
		$frame = !empty($this->post_meta['frame']) ? ' frame' : '';
		if (empty($this->post_meta['image']['width']) || empty($this->post_meta['image']['height']) && ($image_data = getimagesize($this->post_meta['image']['url']))) {
			$this->post_meta['image']['width'] = !empty($image_data[0]) ? $image_data[0] : false;
			$this->post_meta['image']['height'] = !empty($image_data[1]) ? $image_data[1] : false;
		}
		$dimensions = !empty($this->post_meta['image']['width']) && !empty($this->post_meta['image']['height']) ?
			" width=\"{$this->post_meta['image']['width']}\" height=\"{$this->post_meta['image']['height']}\"" : '';
		$img = '';
		if (!empty($this->post_meta['image']['url']))	
			$img = "<img class=\"post_image$alignment$frame\" src=\"". esc_url($this->post_meta['image']['url']) ."\"$dimensions alt=\"" . trim($thesis->api->escht($alt, true)) . "\"" . (!empty($schema) ? ' itemprop="image"' : '') . " />";
		if (!isset($this->options['link']))
			$img = "<a class=\"post_image_link\" href=\"". get_permalink(). "\" title=\"". esc_attr($thesis->api->strings['click_to_read']). "\">$img</a>"; #wp
		echo $caption ?
			"$tab<div class=\"post_image_box wp-caption$alignment\"". (!empty($this->post_meta['image']['width']) ? " style=\"width: {$this->post_meta['image']['width']}px\"" : ''). ">\n".
			"$tab\t$img\n".
			"$tab\t<p class=\"wp-caption-text\">". $thesis->api->allow_html(stripslashes($caption)). "</p>\n".
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
			"<p class=\"caption\">" . $thesis->api->allow_html(stripslashes($caption)) . "</p>\n" : '').
			$content;
	}
}

class thesis_post_thumbnail extends thesis_box {
	protected function translate() {
		$this->image_type = __('Thumbnail', 'thesis');
		$this->title = "Thesis $this->image_type";
	}

	protected function html_options() {
		global $thesis;
		return array(
			'alignment' => array(
				'type' => 'select',
				'label' => $thesis->api->strings['alignment'],
				'tooltip' => $thesis->api->strings['alignment_tooltip'],
				'options' => array(
					'' => $thesis->api->strings['alignnone'],
					'left' => $thesis->api->strings['alignleft'],
					'right' => $thesis->api->strings['alignright'],
					'center' => $thesis->api->strings['aligncenter'])),
			'link' => array(
				'type' => 'checkbox',
				'options' => array(
					'link' => __('Link image to post', 'thesis')),
				'default' => array(
					'link' => true)));
	}

	protected function post_meta() {
		global $thesis;
		return array(
			'title' => $this->title,
			'fields' => array(
				'image' => array(
					'type' => 'add_media',
					'upload_label' => sprintf(__('Upload a %s', 'thesis'), $this->image_type),
					'tooltip' => sprintf(__('Upload a %1$s here, or else input the %2$s of an image you&#8217;d like to use in the <strong>%3$s %2$s</strong> field below.', 'thesis'), strtolower($this->image_type), $thesis->api->base['url'], $this->image_type),
					'label' => "$this->image_type {$thesis->api->base['url']}"),
				'alt' => array(
					'type' => 'text',
					'width' => 'full',
					'label' => sprintf(__('%s <code>alt</code> Text', 'thesis'), $this->image_type),
					'tooltip' => $thesis->api->strings['alt_tooltip']),
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
						'on' => $thesis->api->strings['frame_option'])),
				'alignment' => array(
					'type' => 'select',
					'label' => $thesis->api->strings['alignment'],
					'tooltip' => $thesis->api->strings['alignment_tooltip'],
					'options' => array(
						'' => $thesis->api->strings['skin_default'],
						'left' => $thesis->api->strings['alignleft'],
						'right' => $thesis->api->strings['alignright'],
						'center' => $thesis->api->strings['aligncenter'],
						'flush' => $thesis->api->strings['alignnone']))));
	}

	public function html($args = array()) {
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
		$align = !empty($this->post_meta['alignment']) ?
			$this->post_meta['alignment'] : (!empty($this->options['alignment']) ?
			$this->options['alignment'] : false);
		$alignment = !empty($align) ? ' '. ($align == 'left' ?
			'alignleft' : ($align == 'right' ?
			'alignright' : ($align == 'center' ?
			'aligncenter' : 'alignnone'))) : '';
		$frame = !empty($this->post_meta['frame']) ? ' frame' : '';
		if (empty($this->post_meta['image']['width']) || empty($this->post_meta['image']['height']) && ($image_data = getimagesize($this->post_meta['image']['url']))) {
			$this->post_meta['image']['width'] = !empty($image_data[0]) ? $image_data[0] : false;
			$this->post_meta['image']['height'] = !empty($image_data[1]) ? $image_data[1] : false;
		}
		$dimensions = !empty($this->post_meta['image']['width']) && !empty($this->post_meta['image']['height']) ?
			" width=\"". (int)$this->post_meta['image']['width']. "\" height=\"". (int)$this->post_meta['image']['height']. "\"" : '';
		$img = '';
		if (!empty($this->post_meta['image']['url']))	
			$img = "<img class=\"thumb$alignment$frame\" src=\"". esc_url($this->post_meta['image']['url']). "\"$dimensions alt=\"". trim($thesis->api->escht($alt, true)). '"'. (!empty($schema) ? ' itemprop="thumbnailUrl"' : ''). " />";
		if (!isset($this->options['link']))
			$img = "<a class=\"thumb_link\" href=\"". get_permalink(). "\" title=\"{$thesis->api->strings['click_to_read']}\">$img</a>"; #wp
		echo $caption ?
			"$tab<div class=\"thumb_box wp-caption$alignment\"". (!empty($this->post_meta['image']['width']) ? " style=\"width: {$this->post_meta['image']['width']}px\"" : ''). ">\n".
			"$tab\t$img\n".
			"$tab\t<p class=\"wp-caption-text\">". $thesis->api->allow_html(stripslashes($caption)). "</p>\n".
			"$tab</div>\n" : "$tab$img\n";
	}
}

class thesis_wp_featured_image extends thesis_box {
	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('%s Featured Image', 'thesis'), $thesis->api->base['wp']);
	}

	protected function construct() {
		global $thesis;
		if (!$this->_display()) return;
		add_theme_support('post-thumbnails');
		if (empty($thesis->_wp_featured_image_rss)) {
			add_filter('the_content', array($this, 'add_image_to_feed'));
			$thesis->_wp_featured_image_rss = true;
		}
	}

	protected function html_options() {
		global $thesis, $_wp_additional_image_sizes;
		$options = array(
			'full' => __('Full size (default)', 'thesis'),
			'thumbnail' => __('Thumbnail', 'thesis'),
			'medium' => __('Medium', 'thesis'),
			'large' => __('Large', 'thesis'));
		if (!empty($_wp_additional_image_sizes))
			foreach ($_wp_additional_image_sizes as $size => $data)
				$options[$size] = $size;
		return array(
			'size' => array(
				'type' => 'select',
				'label' => __('Featured Image Size', 'thesis'),
				'tooltip' => sprintf(__('Choose the size of the Feature Image for this location. The list includes <a href="%s">WordPress standard image sizes</a> and any other registered image sizes.', 'thesis'), admin_url('options-media.php')),
				'options' => $options,
				'default' => 'full'),
			'alignment' => array(
				'type' => 'select',
				'label' => $thesis->api->strings['alignment'],
				'tooltip' => $thesis->api->strings['alignment_tooltip'],
				'options' => array(
					'' => $thesis->api->strings['alignnone'],
					'left' => $thesis->api->strings['alignleft'],
					'right' => $thesis->api->strings['alignright'],
					'center' => $thesis->api->strings['aligncenter'])),
			'link' => array(
				'type' => 'checkbox',
				'options' => array(
					'link' => __('Link image to post', 'thesis')),
				'default' => array(
					'link' => true)));
	}

	public function html($args = array()) {
		global $post;
		extract($args = is_array($args) ? $args : array());
		$size = !empty($this->options['size']) ? $this->options['size'] : 'full';
		$alignment = !empty($this->options['alignment']) ? ($this->options['alignment'] == 'left' ?
			'alignleft' : ($this->options['alignment'] == 'right' ?
			'alignright' :
			'aligncenter')) : false;
		$link = !isset($this->options['link']) ?
			"<a class=\"featured_image_link\" href=\"". get_permalink(). "\">%s</a>" : '%s';
		$image = get_the_post_thumbnail($post->ID, $size, !empty($alignment) ? array('class' => $alignment) : array());
		if (empty($image)) return;
		$html = str_repeat("\t", !empty($depth) ? $depth : 0). sprintf($link, $image);
		if (!empty($return))
			return $html;
		else
			echo $html;
	}

	public function add_image_to_feed($content) {
		global $thesis, $post;
		if (!is_feed()) return $content;
		return $this->html(array('return' => true)). $content;
	}
}

class thesis_comments_intro extends thesis_box {
	public $templates = array('single', 'page');

	protected function translate() {
		$this->title = __('Comments Intro', 'thesis');
	}

	protected function html_options() {
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

	public function html($args = array()) {
		global $thesis, $wp_query;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$number = (int) count($wp_query->comments_by_type['comment']);
		if (comments_open())
			echo
				"$tab<p class=\"comments_intro\">",
				apply_filters($this->_class,
				"<span class=\"num_comments\">". count($wp_query->comments_by_type['comment']). "</span> ".
				($number == 1 ? (!empty($this->options['singular']) ?
				$thesis->api->esch($this->options['singular']) : $thesis->api->strings['comment_singular']) : (!empty($this->options['plural']) ?
				$thesis->api->esch($this->options['plural']) : $thesis->api->strings['comment_plural'])).
				"&#8230; <a href=\"#commentform\" rel=\"nofollow\">". trim(apply_filters("{$this->_class}_add", __('add one'), 'thesis')). "</a>"),
				"</p>\n";
		else
			echo "$tab<p class=\"comments_closed\">",
				trim(esc_html(apply_filters("{$this->_class}_closed", __('Comments on this entry are closed.', 'thesis')))), "</p>\n";
	}
}

class thesis_comments_nav extends thesis_box {
	public $templates = array('single', 'page');

	protected function translate() {
		$this->title = $this->name = __('Comment Navigation', 'thesis');
		$this->previous = apply_filters("{$this->_class}_previous", __('Previous Comments', 'thesis'));
		$this->next = apply_filters("{$this->_class}_next", __('Next Comments', 'thesis'));
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options();
		$html['class']['tooltip'] = sprintf(__('This box already contains a %1$s of <code>comment_nav</code>. If you&#8217;d like to supply another %1$s, you can do that here.%2$s', 'thesis'), $thesis->api->base['class'], $thesis->api->strings['class_note']);
		unset($html['id']);
		return array_merge($html, array(
			'previous' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('Previous Comments Link Text', 'thesis'),
				'placeholder' => $this->previous),
			'next' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('Next Comments Link Text', 'thesis'),
				'placeholder' => $this->next)));
	}

	public function html($args = array()) {
		global $thesis;
		if (!get_option('page_comments')) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$previous_link = get_previous_comments_link(trim($thesis->api->escht(!empty($this->options['previous']) ?
			stripslashes($this->options['previous']) :
			$this->previous)));
		$next_link = get_next_comments_link(trim($thesis->api->escht(!empty($this->options['next']) ?
			stripslashes($this->options['next']) :
			$this->next)));
		if (empty($previous_link) && empty($next_link)) return;
		echo
			"$tab<div class=\"comment_nav", (!empty($this->options['class']) ? ' '. trim($thesis->api->esc($this->options['class'])) : ''), "\">\n",
			(!empty($previous_link) ?
			"$tab\t<span class=\"previous_comments\">$previous_link</span>\n" : ''),
			(!empty($next_link) ?
			"$tab\t<span class=\"next_comments\">$next_link</span>\n" : ''),
			"$tab</div>\n";
	}
}

class thesis_comments extends thesis_box {
	public $type = 'rotator';
	public $dependents = array(
		'thesis_comment_author',
		'thesis_comment_avatar',
		'thesis_comment_date',
		'thesis_comment_number',
		'thesis_comment_permalink',
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
		$this->title = $this->name = __('Comment List', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array(
			'ul' => 'ul',
			'ol' => 'ol',
			'div' => 'div',
			'section' => 'section'), 'ul');
		unset($html['id'], $html['class']);
		return array_merge($html, array(
			'per_page' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => __('Comments Per Page', 'thesis'),
				'tooltip' => sprintf(__('The default is set in the <a href="%s">WordPress General &rarr; Discussion options</a>, but you can override that here.', 'thesis'), admin_url('options-discussion.php')),
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

	public function html($args = array()) {
		global $thesis, $wp_query, $post;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", ($this->tab_depth = !empty($depth) ? $depth : 0));
		if ($this->abort === false) {
			if (post_password_required()) {
				echo "$tab\t<p class=\"password_required\">", __('This post is password protected. Enter the password to view comments.', 'thesis'), "</p>\n";
				return;
			}
			$is_it = apply_filters('comments_template', false);
			$html = !empty($this->options['html']) ? $this->options['html'] : 'ul';
			$this->child_html = in_array($html, array('ul', 'ol')) ? 'li' : 'div';
			$hook = trim($thesis->api->esc(!empty($this->options['_id']) ?
				$this->options['_id'] : (!empty($this->options['hook']) ?
				$this->options['hook'] : $this->_id)));
			if (!empty($wp_query->comments)) {
				$args = array(
					'walker' => new thesis_comment_walker,
					'callback' => array($this, 'start'),
					'type' => 'comment',
					'style' => $html);
				if ((bool) get_option('page_comments'))
					$args['per_page'] = (int) !empty($this->options['per_page']) ? $this->options['per_page'] : get_option('comments_per_page');
				do_action("hook_before_$hook");
				echo "$tab<$html class=\"comment_list\">\n";
				if (!in_array($html, array('ul', 'ol')))
					do_action("hook_top_$hook");
				wp_list_comments($args, $wp_query->comments_by_type['comment']);
				if (!in_array($html, array('ul', 'ol')))
					do_action("hook_bottom_$hook");
				echo "$tab</$html>\n";
				do_action("hook_after_$hook");
			}
		}
		else
			include_once($this->abort);
	}

	public function start($comment, $args, $depth) {
		global $thesis;
		$GLOBALS['comment'] = $comment;
		echo
			str_repeat("\t", $this->tab_depth + 1),
			"<$this->child_html class=\"", esc_attr(implode(' ', get_comment_class())), "\" id=\"comment-", get_comment_ID(), "\">\n";
		$this->rotator(array('depth' => $this->tab_depth + 2));
	}
}

class thesis_comment_walker extends Walker_Comment {
	public function start_lvl(&$out, $depth = 0, $args = array()) {
		if (in_array($args['style'], array('ul', 'ol', 'div')))
			echo "<", esc_attr(strtolower($args['style'])), " class=\"children\">\n";
	}

	public function end_lvl(&$out, $depth = 0, $args = array()) {
		if (in_array($args['style'], array('ul', 'ol', 'div')))
			echo "</", esc_attr(strtolower($args['style'])), ">\n";
	}
}

class thesis_comment_author extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Author', 'thesis');
	}

	protected function html_options() {
		return array(
			'author' => array(
				'type' => 'checkbox',
				'options' => array(
					'link' => __('Link comment author name', 'thesis')),
				'default' => array(
					'link' => true)));
	}

	public function html($args = array()) {
		extract($args = is_array($args) ? $args : array());
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			"<span class=\"comment_author\">", (isset($this->options['author']['link']) ? get_comment_author() : get_comment_author_link()), "</span>\n";
	}
}

class thesis_comment_avatar extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Avatar', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		return array(
			'size' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => $thesis->api->strings['avatar_size'],
				'tooltip' => $thesis->api->strings['avatar_tooltip'],
				'description' => 'px'));
	}

	public function html($args = array()) {
		extract($args = is_array($args) ? $args : array());
		$avatar = get_avatar(get_comment_author_email(), !empty($this->options['size']) && is_numeric($this->options['size']) ? $this->options['size'] : 88);
		$author_url = get_comment_author_url();
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			"<span class=\"avatar\">",
			apply_filters($this->_class, empty($author_url) || $author_url == 'http://' ?
				$avatar :
				"<a href=\"$author_url\" rel=\"nofollow\">$avatar</a>"),
			"</span>\n";
	}
}

class thesis_comment_date extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Date', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options();
		$html['class']['tooltip'] = sprintf(__('This box already contains a %1$as of <code>comment_date</code>. If you&#8217;d like to supply another %1$s, you can do that here.%2$s', 'thesis'), $thesis->api->base['class'], $thesis->api->strings['class_note']);
		unset($html['id']);
		return array_merge($html, array(
			'format' => array(
				'type' => 'text',
				'width' => 'short',
				'code' => true,
				'label' => __('Date Format', 'thesis'),
				'tooltip' => $thesis->api->strings['date_tooltip'],
				'default' => get_option('date_format'). ', '. get_option('time_format'))));
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$format = strip_tags(!empty($this->options['format']) ?
			stripslashes($this->options['format']) :
			apply_filters("{$this->_class}_format", get_option('date_format'). ', '. get_option('time_format')));
		$date = get_comment_date(stripslashes($format));
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			'<span class="comment_date', (!empty($this->options['class']) ? ' '. trim($thesis->api->esc($this->options['class'])) : ''), '">',
			apply_filters($this->_class, $date, get_comment_ID()),
			"</span>\n";
	}
}

class thesis_comment_number extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Number', 'thesis');
	}

	public function html($args = array()) {
		global $thesis;
		if ((bool) get_option('thread_comments')) return;
		extract($args = is_array($args) ? $args : array());
		$id = get_comment_ID();
		$number = '<span class="comment_number">'. (int) $GLOBALS['t_comment_counter'][$id]. '</span>';
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			apply_filters($this->_class, $number, $id), "\n";
	}
}

class thesis_comment_permalink extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Permalink', 'thesis');
		$this->link = apply_filters("{$this->_class}_text", __('Link', 'thesis'));
	}

	protected function html_options() {
		return array(
			'text' => array(
				'type' => 'text',
				'width' => 'short',
				'label' => __('Comment Permalink Text', 'thesis'),
				'placeholder' => $this->link));
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$text = trim(esc_html(!empty($this->options['text']) ? stripslashes($this->options['text']) : $this->link));
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			'<a class="comment_permalink" href="#comment-', get_comment_ID(), "\" title=\"{$thesis->api->strings['comment_permalink']}\" rel=\"nofollow\">$text</a>\n";
	}
}

class thesis_comment_edit extends thesis_box {
	protected function translate() {
		$this->title = __('Edit Comment Link', 'thesis');
	}

	public function html($args = array()) {
		global $thesis;
		$url = get_edit_comment_link();
		if (empty($url)) return;
		extract($args = is_array($args) ? $args : array());
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			"<a class=\"comment_edit\" href=\"$url\" rel=\"nofollow\">", trim(esc_html(apply_filters($this->_class, strtolower($thesis->api->strings['edit'])))), "</a>\n";
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

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options();
		unset($html['id']);
		return $html;
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		echo $GLOBALS['comment']->comment_approved == '0' ?
			"$tab<p class=\"comment_moderated\">". __('Your comment is awaiting moderation.', 'thesis'). "</p>\n" :
			"$tab<div class=\"comment_text". (!empty($this->options['class']) ? ' '. trim($thesis->api->esc($this->options['class'])) : ''). "\" id=\"comment-body-". get_comment_ID(). "\">".
			apply_filters($this->_class, get_comment_text()).
			"$tab</div>\n";
	}
}

class thesis_comment_reply extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Reply Link', 'thesis');
		$this->text = apply_filters("{$this->_class}_text", __('Reply', 'thesis'));
	}

	protected function html_options() {
		return array(
			'text' => array(
				'type' => 'text',
				'width' => 'short',
				'label' => __('Reply Link Text', 'thesis'),
				'placeholder' => $this->text));
	}

	public function html($args = array()) {
		if (!get_option('thread_comments')) return;
		extract($args = is_array($args) ? $args : array());
		echo str_repeat("\t", !empty($depth) ? $depth : 0), get_comment_reply_link(array(
			'add_below' => 'comment-body',
			'respond_id' => 'commentform',
			'reply_text' => trim(esc_html(!empty($this->options['text']) ? stripslashes($this->options['text']) : $this->text)),
			'login_text' => __('Log in to reply', 'thesis'),
			'depth' => $GLOBALS['comment_depth'],
			'before' => apply_filters("{$this->_class}_before", ''),
			'after' => apply_filters("{$this->_class}_after", ''),
			'max_depth' => (int) get_option('thread_comments_depth'))), "\n";
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

	public function html($args = array()) {
		global $thesis, $user_ID, $post; #wp
		if (!comments_open()) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", $depth = !empty($depth) ? $depth : 0);
		$hook = trim($thesis->api->esc(!empty($this->options['hook']) ? $this->options['hook'] : 'comment_form'));
		if (get_option('comment_registration') && !!!$user_ID) #wp
			echo
				"$tab<p class=\"login_alert\">",
				__('You must log in to post a comment.', 'thesis'),
				" <a href=\"", wp_login_url(get_permalink()), "\" rel=\"nofollow\">", __('Log in now.', 'thesis'),"</a></p>\n";
		else {
			do_action("hook_before_$hook");
			echo "$tab<form id=\"commentform\" method=\"post\" action=\"", site_url('wp-comments-post.php'), "\">\n"; #wp
			do_action("hook_top_$hook");
			$this->rotator(array_merge($args, array('depth' => $depth + 1, 'req' => get_option('require_name_email'))));
			do_action("hook_bottom_$hook");
			do_action('comment_form', $post->ID); #wp
			comment_id_fields(); #wp
			echo "$tab</form>\n";
			do_action("hook_after_$hook");
		}
	}
}

class thesis_comment_form_title extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Form Title', 'thesis');
		$this->leave = apply_filters("{$this->_class}_text", __('Leave a Comment', 'thesis'));
	}

	protected function html_options() {
		return array(
			'title' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $this->title,
				'placeholder' => $this->leave));
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$title = !empty($this->options['title']) ?
			stripslashes($this->options['title']) :
			$this->leave;
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			"<p class=\"comment_form_title\">",
			trim($thesis->api->escht(apply_filters($this->_class, $title))),
			"</p>\n";
	}
}

class thesis_comment_form_name extends thesis_box {
	protected function translate() {
		$this->title = __('Name Input', 'thesis');
	}

	protected function html_options() {
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

	public function html($args = array()) {
		global $thesis, $user_ID, $user_identity, $commenter;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		if (!!$user_ID) // This should probably be moved to the comment form box to safeguard against unwanted display outcomes
			echo
				"$tab<p>", __('Logged in as', 'thesis'), ' <a href="', admin_url('profile.php'), "\" rel=\"nofollow\">$user_identity</a>. ",
				'<a href="', wp_logout_url(get_permalink()), '" rel="nofollow">', __('Log out &rarr;', 'thesis'), "</a></p>\n";
		else
			echo
				"$tab<p id=\"comment_form_name\">\n",
				(isset($this->options['label']['show']) ? '' :
				"$tab\t<label for=\"author\">{$thesis->api->strings['name']}". (!!$req ? " <span class=\"required\" title=\"{$thesis->api->strings['required']}\">*</span>" : ''). "</label>\n"),
				"$tab\t<input type=\"text\" id=\"author\" class=\"input_text\" name=\"author\" value=\"", esc_attr($commenter['comment_author']), '" ',
				(!empty($this->options['placeholder']) ?
				'placeholder="'. trim($thesis->api->esc($this->options['placeholder'])). '" ' : ''),
				'tabindex="1"', ($req ? ' aria-required="true"' : ''), " />\n",
				"$tab</p>\n";
	}
}

class thesis_comment_form_email extends thesis_box {
	protected function translate() {
		$this->title = __('Email Input', 'thesis');
	}

	protected function html_options() {
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

	public function html($args = array()) {
		global $thesis, $user_ID, $commenter;
		if (!!$user_ID) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		echo
			"$tab<p id=\"comment_form_email\">\n",
			(isset($this->options['label']['show']) ? '' :
			"$tab\t<label for=\"email\">{$thesis->api->strings['email']}". (!!$req ? " <span class=\"required\" title=\"". esc_attr($thesis->api->strings['required']). '">*</span>' : ''). "</label>\n"),
			"$tab\t<input type=\"text\" id=\"email\" class=\"input_text\" name=\"email\" value=\"", esc_attr($commenter['comment_author_email']), '" ',
			(!empty($this->options['placeholder']) ?
			'placeholder="'. trim($thesis->api->esc($this->options['placeholder'])). '" ' : ''),
			'tabindex="2"', (!!$req ? ' aria-required="true"' : ''), " />\n",
			"$tab</p>\n";
	}
}

class thesis_comment_form_url extends thesis_box {
	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('%s Input', 'thesis'), $thesis->api->base['url']);
	}

	protected function html_options() {
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

	public function html($args = array()) {
		global $thesis, $user_ID, $commenter;
		if (!!$user_ID) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		echo
			"$tab<p id=\"comment_form_url\">\n",
			(isset($this->options['label']['show']) ? '' :
			"$tab\t<label for=\"url\">{$thesis->api->strings['website']}</label>\n"),
			"$tab\t<input type=\"text\" id=\"url\" class=\"input_text\" name=\"url\" value=\"", esc_attr($commenter['comment_author_url']), '" ',
			(!empty($this->options['placeholder']) ?
			'placeholder="'. trim($thesis->api->esc($this->options['placeholder'])). '" ' : ''),
			"tabindex=\"3\" />\n",
			"$tab</p>\n";
	}
}

class thesis_comment_form_comment extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Input', 'thesis');
	}

	protected function html_options() {
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

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$rows = !empty($this->options['rows']) && is_numeric($this->options['rows']) ? (int) $this->options['rows'] : 6;
		echo
			"$tab<p id=\"comment_form_comment\">\n",
			(isset($this->options['label']['show']) ? '' :
			"$tab\t<label for=\"comment\">{$thesis->api->strings['comment']}</label>\n"),
			"$tab\t<textarea name=\"comment\" id=\"comment\" class=\"input_text\" tabindex=\"4\" rows=\"$rows\"></textarea>\n",
			"$tab</p>\n";
	}
}

class thesis_comment_form_submit extends thesis_box {
	protected function translate() {
		$this->title = __('Submit Button', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		return array(
			'text' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['submit_button_text'],
				'placeholder' => $thesis->api->strings['submit']));
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$value = trim(esc_attr(!empty($this->options['text']) ? stripslashes($this->options['text']) : $thesis->api->strings['submit']));
		echo
			"$tab<p id=\"comment_form_submit\">\n",
			"$tab\t<input type=\"submit\" id=\"submit\" class=\"input_submit\" name=\"submit\" tabindex=\"5\" value=\"$value\" />\n",
			"$tab</p>\n";
	}
}

class thesis_comment_form_cancel extends thesis_box {
	protected function translate() {
		$this->title = __('Cancel Reply Link', 'thesis');
		$this->cancel = apply_filters("{$this->_class}_text", __('Cancel reply', 'thesis'));
	}

	protected function html_options() {
		return array(
			'text' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('Cancel Link Text', 'thesis'),
				'placeholder' => $this->cancel));
	}

	public function html($args = array()) {
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

	public function preload() {
		if (!class_exists('thesis_comments_dummy'))
			comments_template('/comments.php', true);
	}

	public function html($args = array()) {
		global $thesis, $wp_query;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", $depth = !empty($depth) ? $depth : 0);
		if (empty($wp_query->comments_by_type)) // separate the comments and put them in wp_query if they aren't there already
			$wp_query->comments_by_type = &separate_comments($wp_query->comments);
		foreach ($wp_query->comments as $a)
			if ($a->comment_type == 'pingback' || $a->comment_type == 'trackback')
				$b[] = $a;
		if (empty($b)) return;
		$hook = trim($thesis->api->esc(!empty($this->options['hook']) ? $this->options['hook'] : $this->_id));
		do_action("hook_before_$hook");
		echo "$tab<ul id=\"trackback_list\">\n";
		foreach ($b as $t) {
			$GLOBALS['comment'] = $t;
			echo "$tab\t<li>";
			$this->rotator(array_merge($args, array('depth' => $depth + 1, 't' => $t)));
			echo "</li>\n";
		}
		echo "$tab</ul>\n";
		do_action("hook_after_$hook");
	}
}

class thesis_previous_post_link extends thesis_box {
	public $templates = array('single');

	protected function translate() {
		$this->title = __('Previous Post Link', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array('div' => 'div', 'span' => 'span', 'p' => 'p'), 'span');
		unset($html['id'], $html['class']);
		return array_merge($html, array(
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
				'dependents' => array('custom')),
			'text' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['custom_link_text'],
				'parent' => array(
					'link' => 'custom'))));
	}

	public function html($args = array()) {
		global $thesis, $wp_query;
		if (!$wp_query->is_single || !get_previous_post()) return;
		extract($args = is_array($args) ? $args : array());
		$html = !empty($this->options['html']) ? $this->options['html'] : 'span';
		echo str_repeat("\t", !empty($depth) ? $depth : 0), "<$html class=\"previous_post\">";
		previous_post_link((!empty($this->options['intro']) ? trim($thesis->api->escht($this->options['intro'], true)) . ' ' : '') . '%link', !empty($this->options['link']) && $this->options['link'] == 'custom' ? (!empty($this->options['text']) ? trim($thesis->api->escht($this->options['text'], true)) : '%title') : '%title'); #wp
		echo "</$html>\n";
	}
}

class thesis_next_post_link extends thesis_box {
	public $templates = array('single');

	protected function translate() {
		$this->title = __('Next Post Link', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array('div' => 'div', 'span' => 'span', 'p' => 'p'), 'span');
		unset($html['id'], $html['class']);
		return array_merge($html, array(
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
				'dependents' => array('custom')),
			'text' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['custom_link_text'],
				'parent' => array(
					'link' => 'custom'))));
	}

	public function html($args = array()) {
		global $thesis, $wp_query;
		if (!$wp_query->is_single || !get_next_post()) return;
		extract($args = is_array($args) ? $args : array());
		$html = !empty($this->options['html']) ? $this->options['html'] : 'span';
		echo str_repeat("\t", !empty($depth) ? $depth : 0), "<$html class=\"next_post\">";
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

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array('span' => 'span', 'p' => 'p'), 'span');
		unset($html['id'], $html['class']);
		return array_merge($html, array(
			'text' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['link_text'],
				'placeholder' => $this->previous,
				'description' => $thesis->api->strings['no_html'])));
	}

	public function html($args = array()) {
		global $thesis, $wp_query; #wp
		if (!(($wp_query->is_home || $wp_query->is_archive || $wp_query->is_search) && $wp_query->max_num_pages > 1 && ((!empty($wp_query->query_vars['paged']) ? $wp_query->query_vars['paged'] : 1) < $wp_query->max_num_pages))) return;
		extract($args = is_array($args) ? $args : array());
		$html = !empty($this->options['html']) ? $this->options['html'] : 'span';
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0), "<$html class=\"previous_posts\">",
			get_next_posts_link(trim($thesis->api->escht(apply_filters($this->_class, !empty($this->options['text']) ? stripslashes($this->options['text']) : $this->previous)))),
			"</$html>\n";
	}
}

class thesis_next_posts_link extends thesis_box {
	public $templates = array('home', 'archive');

	protected function translate() {
		$this->next = __('Next Posts', 'thesis');
		$this->title = sprintf(__('%s Link', 'thesis'), $this->next);
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array('span' => 'span', 'p' => 'p'), 'span');
		unset($html['id'], $html['class']);
		return array_merge($html, array(
			'text' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['link_text'],
				'placeholder' => $this->next,
				'description' => $thesis->api->strings['no_html'])));
	}

	public function html($args = array()) {
		global $thesis, $wp_query; #wp
		if (!(($wp_query->is_home || $wp_query->is_archive || $wp_query->is_search) && $wp_query->max_num_pages > 1 && ((!empty($wp_query->query_vars['paged']) ? $wp_query->query_vars['paged'] : 1) > 1))) return;
		extract($args = is_array($args) ? $args : array());
		$html = !empty($this->options['html']) ? $this->options['html'] : 'span';
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0), "<$html class=\"next_posts\">",
			get_previous_posts_link(trim($thesis->api->escht(apply_filters($this->_class, !empty($this->options['text']) ? stripslashes($this->options['text']) : $this->next)))),
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

	public function html($args = array()) {
		global $thesis, $wp_query;
		extract($args = is_array($args) ? $args : array());
		$title = !empty($this->term_options['title']) ?
			stripslashes($this->term_options['title']) : ($wp_query->is_search ?
			__('Search:', 'thesis') . ' ' . esc_html($wp_query->query_vars['s']) : ($wp_query->is_archive ? ($wp_query->is_author ?
			$thesis->wp->author($wp_query->query_vars['author'], 'display_name') : ($wp_query->is_day ?
			get_the_time('l, F j, Y') : ($wp_query->is_month ?
			get_the_time('F Y') : ($wp_query->is_year ?
			get_the_time('Y') : $wp_query->queried_object->name)))) : false));
		if ($title)
			echo str_repeat("\t", !empty($depth) ? $depth : 0),
				"<h1 class=\"archive_title headline\">", trim(apply_filters($this->_class, $title)), "</h1>\n";
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

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options();
		$html['class']['tooltip'] = sprintf(__('This box already contains a %1$s called <code>archive_content</code>. If you wish to add an additional %1$s, you can do that here. Separate multiple %1$ses with spaces.%2$s', 'thesis'), $thesis->api->base['class'], $thesis->api->strings['class_note']);
		unset($html['id']);
		return $html;
	}

	protected function term_options() {
		return array(
			'content' => array(
				'type' => 'textarea',
				'rows' => 8,
				'label' => $this->title));
	}

	public function html($args = array()) {
		global $thesis;
		if (!($content = !empty($this->term_options['content']) ? stripslashes($this->term_options['content']) : false)) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		echo
			"$tab<div class=\"archive_content", (!empty($this->options['class']) ? ' '. trim($thesis->api->esc($this->options['class'])) : ''), "\">\n",
			apply_filters($this->_class, trim($content)),
			"$tab</div>\n";
	}
}

class thesis_wp_widgets extends thesis_box {
	private $tag = false;

	protected function translate() {
		$this->title = $this->name = __('Widgets', 'thesis');
	}

	protected function construct() {
		global $thesis;
		$this->tag = ($html = apply_filters("{$this->_class}_html", 'div')) && in_array($html, array('div', 'li', 'article', 'section')) ?
			$html : 'div';
		$title_tag = ($title_html = apply_filters("{$this->_class}_title_html", !empty($this->options['title_tag']) ? $this->options['title_tag'] : 'p')) && in_array($title_html, array('h1', 'h2', 'h3', 'h4', 'h5', 'p')) ?
			$title_html : 'p';
		register_sidebar(array(
			'name' => $this->name,
			'id' => $this->_id,
			'before_widget' => "<$this->tag class=\"widget %2\$s" . (!empty($this->options['class']) ? ' ' . trim($thesis->api->esc($this->options['class'])) : '') . '" id="%1$s">',
			'after_widget' => "</$this->tag>",
			'before_title' => "<$title_tag class=\"widget_title\">",
			'after_title' => "</$title_tag>"));
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options();
		unset($html['id']);
		return array_merge($html, array(
			'title_tag' => array(
				'type' => 'select',
				'label' => sprintf(__('Widget Title %s', 'thesis'), $thesis->api->strings['html_tag']),
				'options' => array(
					'h3' => 'h3',
					'h4' => 'h4',
					'h5' => 'h5',
					'p' => 'p'),
				'default' => 'p')));
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$hook = !empty($this->options['_id']) ? trim($thesis->api->esc($this->options['_id'])) : $this->_id;
		if ($list = $this->tag == 'li' ? true : false)
			echo "$tab<ul" . (($class = apply_filters("{$this->_class}_ul_class", 'widget_list')) ? ' class="' . trim(esc_attr($class)) . '"' : '') . ">\n";
		do_action("hook_{$hook}_first");
		if (!dynamic_sidebar($this->_id) && is_user_logged_in())
			echo
				"$tab<$this->tag class=\"widget", (!empty($this->options['class']) ? ' ' . trim($thesis->api->esc($this->options['class'])) : ''), "\">\n",
				"$tab\t<p>", sprintf(__('This is a widget box named %1$s, but there are no widgets in it yet. <a href="%2$s">Add a widget here</a>.', 'thesis'), $this->name, admin_url('widgets.php')), "</p>\n",
				"$tab</$this->tag>\n";
		do_action("hook_{$hook}_last");
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

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array(
			'div' => 'div',
			'none' => sprintf(__('No %s wrapper', 'thesis'), $thesis->api->base['html'])), 'div');
		$html['html']['dependents'] = array('div');
		$html['id']['parent'] = $html['class']['parent'] = array('html' => 'div');
		return $html;
	}

	protected function options() {
		global $thesis;
		return array(
			'text' => array(
				'type' => 'textarea',
				'rows' => 8,
				'code' => true,
				'label' => sprintf(__('Text/%s', 'thesis'), $thesis->api->base['html']),
				'tooltip' => sprintf(__('This box allows you to insert plain text and/or %1$s. All text will be formatted just like a normal WordPress post, and all valid %1$s tags are allowed.<br /><br /><strong>Note:</strong> Scripts and %2$s are not allowed here.', 'thesis'), $thesis->api->base['html'], $thesis->api->base['php'])),
			'filter' => array(
				'type' => 'checkbox',
				'options' => array(
					'on' => __('disable automatic <code>&lt;p&gt;</code> tags for this Text Box', 'thesis'))));
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$html = !empty($this->options['html']) ? ($this->options['html'] == 'none' ? false : $this->options['html']) : 'div';
		if (empty($this->options['text']) && !is_user_logged_in()) return;
		echo
			($html ?
			"$tab<div" . (!empty($this->options['id']) ? ' id="' . trim($thesis->api->esc($this->options['id'])) . '"' : '') . ' class="' . (!empty($this->options['class']) ? trim($thesis->api->esc($this->options['class'])) : 'text_box') . "\">\n" : ''),
			$tab, ($html ? "\t" : ''), trim(apply_filters($this->_id, !empty($this->options['text']) ?
				stripslashes($this->options['text']) :
				sprintf(__('This is a Text Box named %1$s. You can write anything you want in here, and Thesis will format it just like a WordPress post. <a href="%2$s">Click here to edit this Text Box</a>.', 'thesis'), $this->name, admin_url("admin.php?page=thesis&canvas=$this->_id")))), "\n",
			($html ?
			"$tab</div>\n" : '');
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
	public $exclude = array();
	private $query = false;

	protected function translate() {
		$this->title = $this->name = __('Query Box', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array(
			'div' => 'div',
			'section' => 'section',
			'article' => 'article',
			'ul' => 'ul',
			'ol' => 'ol'), 'div');
		$html['html']['dependents'] = array('div', 'ul', 'ol', 'article', 'section');
		$html['id']['parent'] = array(
			'html' => array('ul', 'ol'));
		$html['class']['parent'] = array(
			'html' => array('div', 'section', 'article', 'ul', 'ol'));
		return array_merge($html, array(
			'wp' => array(
				'type' => 'checkbox',
				'label' => $thesis->api->strings['auto_wp_label'],
				'tooltip' => $thesis->api->strings['auto_wp_tooltip'],
				'options' => array(
					'auto' => $thesis->api->strings['auto_wp_option']),
				'parent' => array(
					'html' => array('div', 'article', 'section'))),
			'output' => array(
				'type' => 'checkbox',
				'label' => __('Link Output', 'thesis'),
				'tooltip' => __('Selecting this will link each list item to its associated post. All output will be linked.', 'thesis'),
				'options' => array(
					'link' => __('Link list item to post', 'thesis')),
				'parent' => array(
					'html' => array('ul', 'ol'))),
			'schema' => $thesis->api->schema->select()));
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
			'orderby' => 'count',
			'order' => 'DESC'); // but only the most popular ones!
		if (isset($loop_post_types['page'])) unset($loop_post_types['page']); // doing this so it appears in the menu in the right order, but we have to handle the options below.
		foreach ($loop_post_types as $name => $output) {
			$t = get_object_taxonomies($name, 'objects');
			$pt_has_dep[] = $name;
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
						$options_later[$name . '_tax']['dependents'][] = $tax_name;
					}
					$options_later[$name . '_' . $tax_name . '_term']['parent'] = array($name . '_tax' => $tax_name);
					if (count($get_terms) == 50) { // did we hit the 50 threshhold? if so, add in a text box
						$options_later[$name . '_' . $tax_name . '_term_text']['type'] = 'text';
						$options_later[$name . '_' . $tax_name . '_term_text']['label'] = __('Optionally, provide a numeric ID.', 'thesis');
						$options_later[$name . '_' . $tax_name . '_term_text']['width'] = 'medium';
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
		$pt_has_dep[] = 'page';
		$get_pages = get_pages();
		$pages_option = array('' => __('Select a page:', 'thesis'));
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
		$more['order'] = array(
			'type' => 'select',
			'label' => __('Order', 'thesis'),
			'tooltip' => __('Ascending means 1,2,3; a,b,c. Descending means 3,2,1; c,b,a.', 'thesis'),
			'options' => array(
				'' => __('Descending', 'thesis'),
				'ASC' => __('Ascending', 'thesis')));
		$more['orderby'] = array(
			'type' => 'select',
			'label' => __('Orderby', 'thesis'),
			'tooltip' => __('Choose a field to sort by', 'thesis'),
			'options' => array(
				'' => __('Date', 'thesis'),
				'ID' => __('ID', 'thesis'),
				'author' => __('Author', 'thesis'),
				'title' => __('Title', 'thesis'),
				'modified' => __('Modified', 'thesis'),
				'rand' => __('Random', 'thesis'),
				'comment_count' => __('Comment count', 'thesis'),
				'menu_order' => __('Menu order', 'thesis')));
		$more['offset'] = array(
			'type' => 'text',
			'width' => 'short',
			'label' => __('Offset', 'thesis'),
			'tooltip' => __('By entering an offset parameter, you can specify any number of results to skip.', 'thesis'));
		$more['sticky'] = array(
			'type' => 'radio',
			'label' => __('Sticky Posts', 'thesis'),
			'options' => array(
				'' => __('Show sticky posts in their natural position', 'thesis'),
				'show' => __('Show sticky posts at the top', 'thesis')));
		$more['exclude'] = array(
			'type' => 'checkbox',
			'label' => __('Exclude from Main Loop', 'thesis'),
			'tooltip' => __('If your query box is being used as part of the main content output, you may want to account for pagination and duplicate output. Selecting this option will effectively prevent the main loop from showing the posts contained in this query and the output will not be shown on pagination.', 'thesis'),
			'options' => array(
				'yes' => __('Exclude results from the Main Loop.', 'thesis')));
		$pt_has_dep = array_flip($pt_has_dep);
		unset($pt_has_dep['page']);
		$options['more'] = array(
			'type' => 'group',
			'label' => __('Advanced Query Options', 'thesis'),
			'fields' => $more,
			'parent' => array('post_type' => array_keys($pt_has_dep))); // remove advanced options for pages since there is no need to sort
		return $options;
	}

	public function construct() {
		global $thesis;
		if (!$this->_display() || empty($this->options['exclude']['yes'])) return;
		$this->make_query();
		foreach ($this->query->posts as $post)
			$this->exclude[] = (int) $post->ID;
		add_filter('thesis_query', array($this, 'alter_loop'));
	}

	public function make_query() {
		global $thesis;
		if (!empty($this->options['post_type']) && $this->options['post_type'] == 'page') {
			if (empty($this->options['page'])) return;
			$query = array('page_id' => absint($this->options['page']));
		}
		else {
			$query = array( // start building the query
				'post_type' => !empty($this->options['post_type']) ? $this->options['post_type'] : '',
				'posts_per_page' => !empty($this->options['num']) ? (int) $this->options['num'] : 5,
				'ignore_sticky_posts' => !empty($this->options['sticky']) ? 0 : 1,
				'order' => !empty($this->options['order']) && $this->options['order'] == 'ASC' ? 'ASC' : 'DESC',
				'orderby' => !empty($this->options['orderby']) && in_array($this->options['orderby'], array('ID', 'author', 'title', 'modified', 'rand', 'comment_count', 'menu_order')) ? (string) $this->options['orderby'] : 'date');
			if (!empty($this->options['post_type']) && !empty($this->options[$this->options['post_type'] . '_tax']) && (!empty($this->options[$this->options['post_type'] . '_' . $this->options[$this->options['post_type'] . '_tax'] . '_term_text']) || !empty($this->options[$this->options['post_type'] . '_' . $this->options[$this->options['post_type'] . '_tax'] . '_term'])))
				$query['tax_query'] = array(
					array(
						'taxonomy' => (string) $this->options[$this->options['post_type'] . '_tax'],
						'field' => 'id',
						'terms' => !empty($this->options[$this->options['post_type'] . '_' . $this->options[$this->options['post_type'] . '_tax'] . '_term_text']) ? 
						(int) $this->options[$this->options['post_type'] . '_' . $this->options[$this->options['post_type'] . '_tax'] . '_term_text'] : 
						(int) $this->options[$this->options['post_type'] . '_' . $this->options[$this->options['post_type'] . '_tax'] . '_term']));
			if (!empty($this->options['author']))
				$query['author'] = (string) $this->options['author'];
			if (!empty($this->options['offset']))
				$query['offset'] = (int) $this->options['offset'];
		}
		$this->query = new WP_Query($query); // new or cached query object
	}

	public function alter_loop($query) {
		if (!is_home()) return $query;
		$query->query_vars['post__not_in'] = $this->exclude;
		return $query;
	}

	public function html($args = array()) {
		global $thesis;
		if (empty($this->query))
			$this->make_query();
		if (empty($this->query) || (!empty($this->options['exclude']['yes']) && $GLOBALS['wp_query']->query_vars['paged'] > 0)) return;
		extract($args = is_array($args) ? $args : array());
		$depth = isset($depth) ? $depth : 0;
		$tab = str_repeat("\t", $depth);
		$html = !empty($this->options['html']) ? $this->options['html'] : 'div';
		$list = $html == 'ul' || $html == 'ol' ? true : false;
		$link = !empty($this->options['output']['link']) ? $this->options['output']['link'] : false;
		$id = !empty($this->options['id']) ? ' id="' . trim($this->options['id']) . '"' : '';
		$class = (!empty($list) ?
			'query_list' : 'query_box') . (!empty($this->options['class']) ?
			' ' . trim($thesis->api->esc($this->options['class'])) : '');
		$schema = !empty($this->options['schema']) ? $this->options['schema'] : false;
		$schema_att = $schema ? ' itemscope itemtype="'. esc_url($thesis->api->schema->types[$schema]). '"' : '';
		$hook = preg_replace('/\W/', '', !empty($this->options['_id']) ?
			$this->options['_id'] : (!empty($this->options['hook']) ?
			$this->options['hook'] : $this->_id));
		$counter = 1;
		$depth = $list ? $depth + 2 : $depth + 1;
		if (!!$list) {
			do_action("hook_before_$hook");
			echo "$tab<$html$id class=\"$class\">\n";
			do_action("hook_top_$hook");
		}
		while ($this->query->have_posts()) {
			$this->query->the_post();
			do_action('thesis_init_post_meta', $this->query->post->ID);
			if (!!$list) {
				do_action("hook_before_{$hook}_item", $counter);
				echo
					"$tab\t<li class=\"query_item_$counter\"$schema_att>\n",
					($link ?
					"$tab\t\t<a href=\"" . esc_url(get_permalink()) . "\">\n" : '');
			}
			else {
				do_action("hook_before_$hook", $counter);
				echo "$tab<$html class=\"$class", (!empty($this->options['wp']['auto']) ?
				' ' . implode(' ', get_post_class()) : ''), "\"$schema_att>\n";
				do_action("hook_top_$hook", $counter);
			}
			$this->rotator(array_merge($args, array('depth' => $depth, 'schema' => $schema, 'post_count' => $counter, 'post_id' => $this->query->post->ID)));
			if (!!$list) {
				echo ($link ?
					"$tab\t\t</a>\n" : ''),
					"$tab\t</li>\n";
				do_action("hook_after_{$hook}_item", $counter);
			}
			else {
				do_action("hook_bottom_$hook", $counter);
				echo "$tab</$html>\n";
				do_action("hook_after_$hook", $counter);
			}
			$counter++;
		}
		if (!!$list) {
			do_action("hook_bottom_$hook");
			echo "$tab</$html>\n";
			do_action("hook_after_$hook");
		}
		wp_reset_query();
	}

	public function query($query) {
		$query->query_vars['posts_per_page'] = (int) $this->options['num'];
		return $query;
	}
}

class thesis_attribution extends thesis_box {
	protected function translate() {
		$this->title = __('Attribution', 'thesis');
	}

	protected function options() {
		return array(
			'text' => array(
				'type' => 'textarea',
				'rows' => 2,
				'label' => __('Attribution Text', 'thesis'),
				'tooltip' => __('You can override the default attribution text here. If you&#8217;d like to keep the default attribution text, simply leave this field blank.', 'thesis')));
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		if (!empty($this->options['text']))
			$text = $this->options['text'];
		else {
			$skin = esc_attr($thesis->skins->active['name']);
			$skin = property_exists($thesis->skin, 'url') && !empty($thesis->skin->url) ?
				'<a href="'. esc_url($thesis->skin->url) ."\">$skin</a>" : $skin;
			$text = sprintf(__('This site rocks the %1$s Skin for <a href="%2$s">Thesis</a>.', 'thesis'),
				$skin, esc_url(apply_filters("{$this->_class}_url", 'http://diythemes.com/')));
		}
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			"<p class=\"attribution\">", $thesis->api->allow_html(stripslashes($text)), "</p>\n";
	}
}

class thesis_js extends thesis_box {
	public $type = false;
	private $libs = array();

	protected function construct() {
		add_action('hook_head', array($this, 'head_scripts'), 9);
		add_action('hook_after_html', array($this, 'add_scripts'), 8);
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
			echo trim(stripslashes($this->template_options['head_scripts'])), "\n";
		if (is_array($scripts = apply_filters('thesis_head_scripts', false)))
			foreach ($scripts as $script)
				echo "$script\n";
	}

	public function add_scripts() {
		$this->libs(!empty($this->template_options['libs']) && is_array($this->template_options['libs']) ? array_keys($this->template_options['libs']) : false);
		foreach ($this->libs as $lib => $src)
			echo "<script type=\"text/javascript\" src=\"$src\"></script>\n";
		if (!empty($this->template_options['scripts']))
			echo trim(stripslashes($this->template_options['scripts'])), "\n";
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