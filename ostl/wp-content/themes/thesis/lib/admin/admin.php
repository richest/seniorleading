<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
final class thesis_admin {
	public function __construct() {
		if (!is_admin()) return;
		add_action('admin_menu', array($this, 'menu')); #wp
		add_action('admin_post_thesis_upgrade', array($this, 'upgrade'));
		add_action('after_switch_theme', array($this, 'upgrade'));
		add_action('update_option_theme_switched', array($this, 'redirect'), 10, 3);
		if (empty($_GET['page']) || !($_GET['page'] == 'thesis')) return;
		add_action('init', array($this, 'admin_queue'));
		add_action('admin_footer', array($this, 'update_script'));
	}

	public function menu() {
		global $menu, $wp_version; #wp
		if (version_compare($wp_version, '2.9', '>=')) #wp
			$menu[30] = array('', 'read', 'separator-thesis', '', 'wp-menu-separator'); #wp
		add_menu_page('Thesis', 'Thesis', 'edit_theme_options', 'thesis', array($this, 'options_page'), THESIS_IMAGES_URL . '/icon-swatch.png', 31); #wp
		add_submenu_page('thesis', 'Thesis', __('Thesis Home', 'thesis'), 'edit_theme_options', 'thesis');
		add_submenu_page('thesis', '', __('Head Editor', 'thesis'), 'edit_theme_options', 'admin.php?page=thesis&canvas=head'); #wp
		add_submenu_page('thesis', '', __('Skin Editor', 'thesis'), 'edit_theme_options', 'admin.php?page=thesis&canvas=skin-editor-quicklaunch&_wpnonce=' . wp_create_nonce('thesis-skin-editor-quicklaunch')); #wp
		add_submenu_page('thesis', '', __('Skins', 'thesis'), 'edit_theme_options', 'admin.php?page=thesis&canvas=skins'); #wp
		add_submenu_page('thesis', '', __('Boxes', 'thesis'), 'edit_theme_options', 'admin.php?page=thesis&canvas=boxes'); #wp
	}

	public function admin_queue() {
		global $thesis;
		if (!empty($_GET['canvas']) && $_GET['canvas'] == 'skin-editor-quicklaunch' && wp_verify_nonce($_GET['_wpnonce'], 'thesis-skin-editor-quicklaunch')) {
			wp_redirect(home_url('?thesis_editor=1'));
			exit;
		}
		else {
			wp_enqueue_style('thesis-admin', THESIS_CSS_URL . '/admin.css', false, $thesis->version); #wp
			wp_enqueue_script('thesis-menu', THESIS_JS_URL . '/menu.js', false, $thesis->version); #wp
		}
		// do this early, well before the page renders
		if (!empty($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'thesis_did_update'))
			delete_transient('thesis_core_update');
	}

	public function options_page() {
		echo
			"<div id=\"t_admin\"" . (get_bloginfo('text_direction') == 'rtl' ? ' class="rtl"' : '') . ">\n". #wp
			"\t<div id=\"t_header\">\n".
			"\t\t<h2><a id=\"t_logo\" href=\"" . admin_url('admin.php?page=thesis') . "\">Thesis</a></h2>\n".
			$this->nav().
			"\t</div>\n".
			"\t<div id=\"t_canvas\">\n";
		!empty($_GET['canvas']) ? do_action('thesis_admin_canvas') : $this->canvas();
		echo
			"\t</div>\n".
			"</div>\n";
	}

	private function nav() {
		global $thesis;
		$menu = '';
		$links = array(
			'site_menu' => array(
				'text' => $thesis->api->strings['site'],
				'url' => false,
				'submenu' => apply_filters('thesis_site_menu', array())),
			'skin_menu' => array(
				'text' => __('Skins', 'thesis'),
				'url' => false,
				'submenu' => apply_filters('thesis_skins_menu', array(
					'skins' => array(
						'text' => __('Select Skin', 'thesis'),
						'url' => admin_url('admin.php?page=thesis&canvas=skins')),
					'editor' => array(
						'text' => __('Skin Editor', 'thesis'),
						'url' => home_url('?thesis_editor=1'))))),
			'box_menu' => array(
				'text' => __('Boxes', 'thesis'),
				'url' => false,
				'submenu' => apply_filters('thesis_boxes_menu', array(
					'boxes' => array(
						'text' => __('Select Boxes', 'thesis'),
						'url' => admin_url('admin.php?page=thesis&canvas=boxes'))))),
			'package_menu' => array(
				'text' => __('Packages', 'thesis'),
				'url' => false,
				'submenu' => apply_filters('thesis_packages_menu', array(
					'packages' => array(
						'text' => __('Select Packages', 'thesis'),
						'url' => admin_url('admin.php?page=thesis&canvas=packages'))))));
		$links['more'] = array(
			'text' => __('More', 'thesis'),
			'url' => false,
			'class' => 'more_menu',
			'submenu' => array(
				'blog' => array(
					'text' => __('Thesis Blog', 'thesis'),
					'url' => 'http://diythemes.com/thesis/',
					'title' => __('Thesis news plus tutorials and advice from Thesis pros!', 'thesis'),
					'target' => '_blank'),
				'rtfm' => array(
					'text' => __('User&#8217;s Guide', 'thesis'),
					'url' => 'http://diythemes.com/thesis/rtfm/',
					'title' => __('Documentation, tutorials, and how-tos that will help you get the most out of Thesis.', 'thesis'),
					'target' => '_blank'),
				'forums' => array(
					'text' => __('Support Forums', 'thesis'),
					'url' => 'http://diythemes.com/forums/',
					'title' => __('Stuck? Don&#8217;t worry&#8212;you can find expert help in our support forums.', 'thesis'),
					'target' => '_blank'),
				'aff' => array(
					'text' => __('Affiliate Program', 'thesis'),
					'url' => 'http://diythemes.com/affiliate-program/',
					'title' => __('Join the Thesis Affiliate Program and earn money selling Thesis!', 'thesis'),
					'target' => '_blank'),
				'version' => array(
					'id' => 't_version',
					'text' => sprintf(__('Version %s', 'thesis'), $thesis->version))));
		$links['view_site'] = array(
			'text' => __('View Site', 'thesis'),
			'url' => home_url(),
			'title' => __('Check out your site!', 'thesis'),
			'class' => 'view_site',
			'target' => '_blank');
		foreach ($links as $name => $link) {
			$submenu = '';
			$id = !empty($link['id']) ? " id=\"{$link['id']}\"" : '';
			$classes = !empty($link['class']) ? array($link['class']) : array();
			if (!empty($_GET['canvas']) && $name == $_GET['canvas']) $classes[] = 'current';
			if (isset($link['submenu'])) $classes[] = 'topmenu';
			$classes = is_array($classes) ? ' class="' . implode(' ', $classes) . '"' : '';
			$target = !empty($link['target']) ? " target=\"{$link['target']}\"" : '';
			if (!empty($link['submenu']) && is_array($link['submenu'])) {
				foreach ($link['submenu'] as $item_name => $item) {
					$id = !empty($item['id']) ? " id=\"{$item['id']}\"" : '';
					$current = !empty($_GET['canvas']) && $item_name == $_GET['canvas'] ? ' class="current"' : '';
					$title = !empty($item['title']) ? " title=\"{$item['title']}\"" : '';
					$target = !empty($item['target']) ? " target=\"{$item['target']}\"" : '';
					$text = !empty($item['url']) ? "<a$id href=\"{$item['url']}\"$title$target>{$item['text']}</a>" : "<span$id>{$item['text']}</span>";
					$submenu .= "\t\t\t\t\t<li$current>$text</li>\n";
				}
				$menu .=
					"\t\t\t<li$classes><a$id class=\"topitem\"" . (!empty($link['url']) ? " href=\"{$link['url']}\"" : '') . ">{$link['text']}<span>{</span></a>\n".
					"\t\t\t\t<ul class=\"submenu\">\n".
					$submenu.
					"\t\t\t\t</ul>\n".
					"\t\t\t</li>\n";
			}
			else
				$menu .= "\t\t\t<li$classes><a$id class=\"toplink\" href=\"{$link['url']}\"$target>{$link['text']}</a></li>\n";
		}
		return
			"\t\t<ul id=\"t_nav\">\n".
			$menu.
			"\t\t</ul>\n";
	}

	private function canvas() {
		global $thesis;
		if (!is_dir(WP_CONTENT_DIR . '/thesis'))
			echo
				"<p><a style=\"margin-bottom: 24px;\" data-style=\"button save\" href=\"" . wp_nonce_url(admin_url('update.php?action=thesis-install-components'), 'thesis-install') . "\">" . __('Click to get started!', 'thesis') . "</a></p>";
		$tip = $this->bubble_tips();
		echo
			"\t\t<div class=\"t_canvas_left\"". (!file_exists(WP_CONTENT_DIR . '/thesis') ? "style=\"opacity: 0.15;\"" : '') .">\n".
			"\t\t\t<h3>" . __('Getting Started with Thesis 2', 'thesis') . "</h3>\n".
			"\t\t\t<p>" . __('So you&#8217;ve got the greatest website template system on the planet&hellip;now what? ', 'thesis') . "</p>\n".
			"\t\t\t<p>" . __('First, you can manage your site&#8217;s vital information from one handy location. See the Site menu at the top of this page, inside the Thesis menu bar? Hover your mouse over that, and you&#8217;ll see links to the following tools:', 'thesis') . "</p>\n".
			"\t\t\t<ul>\n".
			"\t\t\t\t<li>" . __('<strong>HTML Head Editor</strong>, where you can manage your site&#8217;s <code>&lt;head&gt;</code> output and do things like set your favicon, verify your site with Google and Bing webmaster tools, implement the Google+ rel author spec, and more', 'thesis') . "</li>\n".
			"\t\t\t\t<li>" . __('<strong>Tracking Scripts</strong>, which give you a handy way to integrate Google Analytics and other scripts that monitor site performance', 'thesis') . "</li>\n".
			"\t\t\t\t<li>" . __('<strong>404 Page Selector</strong>, which allows you to select (and edit!) your site&#8217;s 404 page', 'thesis') . "</li>\n".
			"\t\t\t\t<li>" . __('<strong>Home Page SEO Controls</strong> that will help you optimize the most important page of your site', 'thesis') . "</li>\n".
			"\t\t\t</ul>\n".
			"\t\t\t<p>" . __('Also, check out the Skins, Boxes, and Packages links in the Thesis menu bar. You can use these to manage your Thesis add-ons.', 'thesis') . "</p>\n".
			"\t\t\t<p>" . __('For example, click on the Select Skin link that appears in the Skins menu. On the resulting page, you&#8217;ll see an <strong>Upload Skin</strong> button on the upper right&#8212;you could use this button to upload a new Skin. Boxes and Packages work the same way.', 'thesis') . "</p>\n".
			"\t\t\t<p>" . __('Next, I&#8217;d like to introduce you to the ultimate piece of the Thesis 2 puzzle, the Thesis Skin Editor.', 'thesis') . "</p>\n".
			"\t\t\t<h4>" . __('Thesis Skin Editor', 'thesis') . "</h4>\n".
			"\t\t\t<p>" . __('There are a zillion reasons why the Thesis Skin Editor is remarkable, but for now, let&#8217;s focus on the two most important ones.', 'thesis') . "</p>\n".
			"\t\t\t<p>" . __('First, consider this: You&#8217;ve never actually <em>seen</em> a template before.', 'thesis') . "</p>\n".
			"\t\t\t<p>" . __('In the past, if you wanted to edit a template, you had to either hack your theme files or else use a hook and supply your own custom code. In both cases, you had to imagine the final outcome, because you couldn&#8217;t see your entire template in one place. Fortunately, those days are over.', 'thesis') . "</p>\n".
			"\t\t\t<p>" . __('The <strong>visual template editor</strong> allows you to view and edit all of your Skin&#8217;s templates in a simple, powerful interface. Because you can see your templates in their entirety for the first time, you&#8217;ll know precisely why certain items appear on certain pages. And most important, you now have the power to edit your templates to get exactly the outcomes you want&hellip;without writing any code, because it&#8217;s all drag and drop.', 'thesis') . "</p>\n".
			"\t\t\t<p>" . __('Second, you now have a <strong>live CSS editor</strong> that delivers world class design power in a point-and-click interface.', 'thesis') . "</p>\n".
			"\t\t\t<h4>" . __('Launching the Thesis Skin Editor', 'thesis') . "</h4>\n".
			"\t\t\t<p>" . __('There are two ways to launch the Thesis Skin Editor:', 'thesis') . "</p>\n".
			"\t\t\t<ol>\n".
			"\t\t\t\t<li>" . __('mouse over Skins in the Thesis menu bar, and then click on <strong>Skin Editor</strong>', 'thesis') . "</li>\n".
			"\t\t\t\t<li>" . __('while logged in, visit any page of your site, and locate the <strong>click to edit</strong> button in the lower-left corner&#8212;use this to edit the current template for that page', 'thesis') . "</li>\n".
			"\t\t\t</ol>\n".
			"\t\t\t<p><strong>" . __('Note:', 'thesis') . "</strong> " . __('When you launch the Thesis Skin Editor, it will create a pop-up window called the Canvas. Be sure to enable pop-ups in your browser so you can see the Canvas&#8212;this is how you&#8217;ll get live HTML and CSS feedback while working with the Thesis Skin Editor!', 'thesis') . "</p>\n".
			"\t\t</div>\n".
			"\t\t<div class=\"t_canvas_right\">\n".
			"\t\t\t<div class=\"t_bubble\">\n".
			"\t\t\t\t<p>{$tip['tip']}</p>\n".
			"\t\t\t</div>\n".
			"\t\t\t<div class=\"t_bubble_cite\">\n".
			"\t\t\t\t<img class=\"t_bubble_pic\" src=\"{$tip['img']}\" alt=\"{$tip['name']}\" width=\"90\" height=\"90\" />\n".
			"\t\t\t\t<p>{$tip['name']}</p>\n".
			"\t\t\t</div>\n".
			"\t\t</div>\n";
	}

	private function bubble_tips() {
		global $thesis;
		$authors = array(
			'pearsonified' => array(
				'name' => 'Chris Pearson',
				'img' => 'pearsonified.png'),
			'missieur' => array(
				'name' => 'Missieur',
				'img' => 'missieur.png'),
			'lola' => array(
				'name' => 'Lola',
				'img' => 'lola.png'),
			'matt' => array(
				'name' => 'Matt Gross',
				'img' => 'matt.png'));
		$tips = array(
			'schema' => array(
				'tip' => __('<strong>Did you know?</strong><br />Search engines love markup Schema, and you can enable these via the Post Box in the Template Editor.', 'thesis'),
				'author' => 'missieur'),
			'category-seo' => array(
				'tip' => sprintf(__('Supercharge the %s of your archive pages by supplying Archive Title and Archive Content information on the editing page for categories, tags, and taxonomies.', 'thesis'), $thesis->api->base['seo']),
				'author' => 'pearsonified'),
			'404page' => array(
				'tip' => sprintf(__('Thesis lets you control the content of your 404 page. All you have to do is <a href="%s">specify a 404 page</a>, and boom&#8212;it&#8217;s like magic!', 'thesis'), admin_url('admin.php?page=thesis&canvas=thesis_404')),
				'author' => 'missieur'),
			'typography' => array(
				'tip' => __('<strong>Did you know?</strong><br />Many Thesis Packages have golden ratio typography baked right in, so you get perfect typography without having to think about it.', 'thesis'),
				'author' => 'pearsonified'),
			'variables' => array(
				'tip' => sprintf(__('Thesis&#8217; %1$s variables can contain other variables. It&#8217;s like inception for your %1$s!', 'thesis'), $thesis->api->base['css']),
				'author' => 'missieur'),
			'blog' => array(
				'tip' => sprintf(__('In addition to making Thesis, DIYthemes publishes a killer blog dedicated to helping you run a better website. <a href="%s">Check it out</a>.', 'thesis'), esc_url('http://diythemes.com/thesis/')),
				'author' => 'pearsonified'),
			'canvas' => array(
				'tip' => __('If you&#8217;re using the Thesis Skin Editor and click on a link in the Canvas, the Template Editor will automatically adjust and show you the template that is currently active in the Canvas.', 'thesis'),
				'author' => 'pearsonified'),
			'updates' => array(
				'tip' => __('Thesis 2 features automatic updates for Skins, Boxes, Packages, <em>and</em> the Thesis core. You win.', 'thesis'),
				'author' => 'missieur'),
			'verify' => array(
				'tip' => sprintf(__('You like ranking in search engines, don&#8217;t ya? Then be sure to verify your site with both Google and Bing Webmaster Tools on the <a href="%s">Tracking Scripts page.</a>', 'thesis'), admin_url('admin.php?page=thesis&canvas=thesis_tracking_scripts')),
				'author' => 'missieur'),
			'march-2008' => array(
				'tip' => __('<strong>Did you know?</strong><br />Thesis launched on March 29, 2008.', 'thesis'),
				'author' => 'pearsonified'),
			'seo-tips' => array(
				'tip' => sprintf(__('Outside of using Thesis, what else can you do to improve your %1$s? Check out DIYthemes&#8217; series on <a href="%2$s">WordPress %1$s for Everybody</a>.', 'thesis'), $thesis->api->base['seo'], esc_url('http://diythemes.com/thesis/wordpress-seo/')),
				'author' => 'pearsonified'),
			'templates' => array(
				'tip' => sprintf(__('<strong>Did you know?</strong><br>You can click on the template name in the Skin %1$s Editor to view or edit other templates.', 'thesis'), $thesis->api->base['html']),
				'author' => 'matt'),
			'analytics'	=> array(
				'tip' => sprintf(__('Amp up your site&#8217;s search engine performance by providing <a href="%1$s">Home Page %2$s</a> details.', 'thesis'), admin_url('admin.php?page=thesis&canvas=home_seo'), $thesis->api->base['seo']),
				'author' => 'lola'),
			'custom-templates' => array(
				'tip' => __('No matter what Skin you use, you can always create custom templates in the Skin Editor for things like landing pages, checkout pages, and more.', 'thesis'),
				'author' => 'matt'),
			'custom-css' => array(
				'tip' => sprintf(__('<strong>How do I add custom %1$s?</strong><br />With Thesis 2, you no longer need a separate file for your customizations&#8212;you can simply type your custom %1$s directly into the Skin %1$s Editor and see your changes LIVE in the Canvas!', 'thesis'), $thesis->api->base['css']),
				'author' => 'lola'),
			'copy' => array(
				'tip' => sprintf(__('Adding templates to your Skin is easy with Thesis, and it&#8217;s even easier when you use the <strong>Copy from Template</strong> feature in the Skin %s Editor.', 'thesis'), $thesis->api->base['html']),
				'author' => 'matt'),
			'backup' => array(
				'tip' => __('Have you checked out the Manage tab inside the Thesis Skin Editor yet? With the Manager, you can backup, restore, import, and export your Skins. I like to call this &ldquo;winning.&rdquo;', 'thesis'),
				'author' => 'lola'),
			'email-marketing' => array(
				'tip' => sprintf(__('<strong>Did you know?</strong><br />Email marketing is probably the best way to leverage the web to grow your business. Get started today with DIYthemes&#8217; exclusive guide: <a href="%1$s">Email Marketing for Everybody</a>.', 'thesis'), 'http://diythemes.com/thesis/email-marketing-everybody/'),
				'author' => 'missieur'),
			'popups' => array(
				'tip' => __('When you launch the Skin Editor, Thesis will attempt to open a pop-up window called the Canvas, which will show any changes you make to your site while you&#8217;re working in the Editor. Be sure to enable pop-ups for your site so you can use this amazing feature!', 'thesis'),
				'author' => 'pearsonified'));
		$pick = $tips;
		shuffle($pick);
		$tip = array_shift($pick);
		$tip['name'] = $authors[$tip['author']]['name'];
		$tip['img'] = THESIS_IMAGES_URL . "/{$authors[$tip['author']]['img']}";
		return $tip;
	}

	function update_script() {
		$transients = array(
			'thesis_skins_update',
			'thesis_boxes_update',
			'thesis_packages_update',
			'thesis_core_update');
		$show = false;
		foreach ($transients as $transient) {
			if (get_transient($transient)) {
				$show = true;
				break;
			}
		}
		echo !! $show ?
			"<script type=\"text/javascript\">\n".
			"\tfunction thesis_update_message(){\n".
			"\t\treturn confirm('". __('Are you sure you want to update? Some or all files will be overwritten. Click OK to continue or cancel to quit.', 'thesis') ."');\n".
			"\t}\n".
			"</script>\n": '';
	}

	public function upgrade() {
		global $thesis;
		add_option('_thesis_did_db_upgrade', 1);
		if (get_option('_thesis_did_db_upgrade') === 1) {
			$this->upgrade_meta();
			$this->convert_terms();
			update_option('_thesis_did_db_upgrade', 0);
			wp_cache_flush();
		}
	}

	public function redirect($option) {
		if (strlen($option) > 0) {
			wp_redirect(admin_url('admin.php?page=thesis&upgraded=true')); #wp
			exit;
		}
	}

	private function upgrade_meta() {
		global $thesis, $wpdb;
		$all_entries = array();
		$or = array(
			'thesis_title' => array(
				'meta' => 'thesis_title_tag',
				'field' => 'title'),
			'thesis_description' => array(
				'meta' => 'thesis_meta_description',
				'field' => 'description'),
			'thesis_keywords' => array(
				'meta' => 'thesis_meta_keywords',
				'field' => 'keywords'),
			'thesis_robots' => array(
				'meta' => 'thesis_meta_robots',
				'field' => 'robots'),
			'thesis_canonical' => array(
				'meta' => 'thesis_canonical_link',
				'field' => 'url'),
			'thesis_slug' => array(
				'meta' => 'thesis_html_body',
				'field' => 'class'),
			'thesis_readmore' => array(
				'meta' => 'thesis_post_content',
				'field' => 'read_more'),
			'thesis_post_image' => array(
				'meta' => 'thesis_post_image',
				'field' => 'image',
				'additional' => 'url'),
			'thesis_post_image_alt'	 => array(
				'meta' => 'thesis_post_image',
				'field' => 'alt'),
			'thesis_post_image_frame' => array(
				'meta' => 'thesis_post_image',
				'field' => 'frame',
				'additional' => 'on'),
			'thesis_post_image_horizontal' => array(
				'meta' => 'thesis_post_image',
				'field' => 'alignment'),
			'thesis_thumb' => array(
				'meta' => 'thesis_post_thumbnail',
				'field' => 'image',
				'additional' => 'url'),
			'thesis_thumb_alt' => array(
				'meta' => 'thesis_post_thumbnail',
				'field' => 'alt'),
			'thesis_thumb_horizontal' => array(
				'meta' => 'thesis_post_thumbnail',
				'field' => 'alignment'),
			'thesis_redirect' => array(
				'meta' => 'thesis_redirect',
				'field' => 'url'));
		$ors = implode("' OR meta_key = '", array_keys($or));
		$metas = (array) $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE meta_key = '$ors'");
		if (!!! $metas)
			return;
		$new_sorted = array();
		foreach ($metas as $results) {
			$results = (array) $results;
			if (isset($or[$results['meta_key']]['additional']))
				$new_sorted[$results['post_id']][$or[$results['meta_key']]['meta']][$or[$results['meta_key']]['field']][$or[$results['meta_key']]['additional']] = maybe_unserialize($results['meta_value']);
			else
				$new_sorted[$results['post_id']][$or[$results['meta_key']]['meta']][$or[$results['meta_key']]['field']] = maybe_unserialize($results['meta_value']);
		}		
		foreach ($new_sorted as $id => $meta_keys) {
			if (! isset($meta_keys['thesis_thumb_frame']))
				$meta_keys['thesis_post_thumbnail']['frame']['on'] = true;
			foreach ($meta_keys as $meta_key => $save)
				update_post_meta($id, "_$meta_key", $save);
		}
	}

	function version() {
		$theme_data = get_theme_data(TEMPLATEPATH . '/style.css'); #wp
		$version = trim($theme_data['Version']);
		return $version;
	}

	function convert_terms() {
		global $thesis, $wpdb; #wp
		$table = $wpdb->prefix . 'thesis_terms';
		if (! $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE '%s'", $table))) return;
		$whats = array(
			'title' => array(
				'class' => 'thesis_title_tag',
				'field' => 'title'),
			'description' => array(
				'class' => 'thesis_meta_description',
				'field' => 'description'),
			'keywords' => array(
				'class' => 'thesis_meta_keywords',
				'field' => 'keywords'),
			'robots' => array(
				'class' => 'thesis_meta_robots',
				'field' => 'robots'),
			'canonical' => array(
				'class' => 'thesis_canonical_link',
				'field' => 'url'),
			'headline' => array(
				'class' => 'thesis_archive_title',
				'field' => 'title'),
			'content' => array(
				'class' => 'thesis_archive_content',
				'field' => 'content'));
		$sql = implode(',', array_keys($whats));
		$terms = $wpdb->get_results("SELECT term_id,$sql FROM $table", ARRAY_A); #wp
		if (empty($terms)) return;
		$new = array();
		foreach ($terms as $data) {
			$id = array_shift($data);
			foreach ($data as $column => $value)
				if (!empty($value))
					$new[$id][$whats[$column]['class']][$whats[$column]['field']] = maybe_unserialize($value);
		}
		if (!empty($new) && is_array($new))
			update_option('thesis_terms', $new);
	}
}