<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
final class thesis_admin {
	public function __construct() {
		if (!is_admin()) return;
		add_action('admin_menu', array($this, 'menu')); #wp
		add_action('admin_post_thesis_upgrade', array($this, 'upgrade'));
		add_action('after_switch_theme', array($this, 'upgrade'));
		add_action('update_option_theme_switched', array($this, 'redirect'), 10, 3);
		add_action('admin_head', array($this, 'beta_css'));
		if (empty($_GET['page']) || !($_GET['page'] == 'thesis')) return;
		add_action('init', array($this, 'admin_queue'));
		add_action('admin_footer', array($this, 'update_script'));
		add_action('admin_footer', array($this, 'menu_fix'));
		if (!empty($_GET['canvas']) && $_GET['canvas'] === 'system_status')
			add_action('thesis_admin_canvas', array($this, 'system_status'));
	}

	public function menu() {
		global $menu, $wp_version, $thesis; #wp
		if (version_compare($wp_version, '2.9', '>=')) #wp
			$menu[30] = array('', 'read', 'separator-thesis', '', 'wp-menu-separator'); #wp
		$beta = preg_match('/a|b/i', $thesis->version) ? __(' <span class="t_beta">Beta!</span>', 'thesis') : '';
		add_menu_page("Thesis{$beta}", "Thesis{$beta}", 'edit_theme_options', 'thesis', array($this, 'options_page'), THESIS_IMAGES_URL . '/favicon.ico', 31); #wp
		add_submenu_page('thesis', 'Thesis', __('Thesis Home', 'thesis'), 'edit_theme_options', 'thesis');
		if (is_array($quicklaunch = apply_filters('thesis_quicklaunch_menu', array())) && !empty($quicklaunch))
			foreach ($quicklaunch as $link)
				if (is_array($link) && !empty($link))
					add_submenu_page('thesis', '', $link['text'], 'edit_theme_options', $link['url']);
	}

	public function admin_queue() {
		global $thesis;
		if (!empty($_GET['canvas']) && $_GET['canvas'] == 'skin-editor-quicklaunch' && wp_verify_nonce($_GET['_wpnonce'], 'thesis-skin-editor-quicklaunch')) {
			wp_redirect(set_url_scheme(home_url('?thesis_editor=1')));
			exit;
		}
		else {
			$styles = array(
				'thesis-admin' => array(
					'url' =>  'admin.css'),
				'thesis-home' => array(
					'url' => 'home.css',
					'deps' => array('thesis-admin')),
				'thesis-options' => array(
					'url' => 'options.css',
					'deps' => array('thesis-admin')),
				'thesis-objects' => array(
					'url' => 'objects.css',
					'deps' => array('thesis-options', 'thesis-popup')),
				'thesis-box-form' => array(
					'url' => 'box_form.css',
					'deps' => array('thesis-options', 'thesis-popup')),
				'thesis-popup' => array(
					'url' => 'popup.css',
					'deps' => array('thesis-options')),
				'codemirror' => array(
					'url' => 'codemirror.css'));
			foreach ($styles as $name => $atts)
				wp_register_style($name, THESIS_CSS_URL . "/{$atts['url']}", (!empty($atts['deps']) ? $atts['deps'] : array()), $thesis->version);
			$scripts = array(
				'thesis-menu' => array(
					'url' => 'menu.js'),
				'thesis-options' => array(
					'url' => 'options.js',
					'deps' => array('thesis-menu')),
				'thesis-objects' => array(
					'url' => 'objects.js',
					'deps' => array('thesis-menu')),
				'codemirror' => array(
					'url' => 'codemirror.js'));
			foreach ($scripts as $name => $atts)
				wp_register_script($name, THESIS_JS_URL . "/{$atts['url']}", (!empty($atts['deps']) ? $atts['deps'] : array()), $thesis->version);
			wp_enqueue_style('thesis-admin'); #wp
			wp_enqueue_script('thesis-menu'); #wp
			if (empty($_GET['canvas']))
				wp_enqueue_style('thesis-home');
			elseif ($_GET['canvas'] == 'system_status') {
				wp_enqueue_style('thesis-options');
			}
		}
	}

	public function options_page() {
		echo
			"<div id=\"t_admin\"", (get_bloginfo('text_direction') == 'rtl' ? ' class="rtl"' : ''), ">\n", #wp
			"\t<div id=\"t_header\">\n",
			"\t\t<h2><a id=\"t_logo\" href=\"", admin_url('admin.php?page=thesis'), "\">Thesis</a></h2>\n",
			$this->nav(),
			"\t</div>\n",
			"\t<div id=\"t_canvas\">\n";
		!empty($_GET['canvas']) ? do_action('thesis_admin_canvas') : $this->canvas();
		echo
			"\t</div>\n",
			"</div>\n";
	}

	private function nav() {
		global $thesis;
		$menu = '';
		$links = array(
			'skin_menu' => array(
				'text' => __('Skin', 'thesis'),
				'url' => false,
				'submenu' => apply_filters('thesis_skin_menu', array())),
			'site_menu' => array(
				'text' => $thesis->api->strings['site'],
				'url' => false,
				'submenu' => apply_filters('thesis_site_menu', array())),
			'box_menu' => array(
				'text' => __('Boxes', 'thesis'),
				'url' => false,
				'submenu' => apply_filters('thesis_boxes_menu', array())),
			'package_menu' => array(
				'text' => __('Packages', 'thesis'),
				'url' => false,
				'submenu' => apply_filters('thesis_packages_menu', array())));
		$links['more'] = array(
			'text' => __('More', 'thesis'),
			'url' => false,
			'class' => 'more_menu',
			'submenu' => array(
				'blog' => array(
					'text' => __('Thesis Blog', 'thesis'),
					'url' => 'http://diythemes.com/thesis/',
					'title' => __('Thesis news plus tutorials and advice from Thesis pros!', 'thesis')),
				'rtfm' => array(
					'text' => __('User&#8217;s Guide', 'thesis'),
					'url' => 'http://diythemes.com/thesis/rtfm/',
					'title' => __('Documentation, tutorials, and how-tos that will help you get the most out of Thesis.', 'thesis')),
				'forums' => array(
					'text' => __('Support Forums', 'thesis'),
					'url' => 'http://diythemes.com/forums/',
					'title' => __('Stuck? Don&#8217;t worry&#8212;you can find expert help in our support forums.', 'thesis')),
				'aff' => array(
					'text' => __('Affiliate Program', 'thesis'),
					'url' => 'http://diythemes.com/affiliate-program/',
					'title' => __('Join the Thesis Affiliate Program and earn money selling Thesis!', 'thesis')),
				'system_status' => array(
					'text' => __('System Status', 'thesis'),
					'url' => admin_url('admin.php?page=thesis&canvas=system_status'),
					'title' => __('Check your system for compatibility with Thesis.', 'thesis')),
				'version' => array(
					'id' => 't_version',
					'text' => sprintf(__('Version %s', 'thesis'), $thesis->version))));
		$links['view_site'] = array(
			'text' => __('View Site', 'thesis'),
			'url' => home_url(),
			'title' => __('Check out your site!', 'thesis'),
			'class' => 'view_site',
			'icon' => '&#59392;');
		foreach ($links as $name => $link) {
			$submenu = '';
			$id = !empty($link['id']) ? " id=\"{$link['id']}\"" : '';
			$classes = !empty($link['class']) ? array($link['class']) : array();
			if (!empty($_GET['canvas']) && $name == $_GET['canvas']) $classes[] = 'current';
			if (isset($link['submenu'])) $classes[] = 'topmenu';
			$classes = is_array($classes) ? ' class="' . implode(' ', $classes) . '"' : '';
			if (!empty($link['submenu']) && is_array($link['submenu'])) {
				foreach ($link['submenu'] as $item_name => $item) {
					$id = !empty($item['id']) ? " id=\"{$item['id']}\"" : '';
					$current = !empty($_GET['canvas']) && $item_name == $_GET['canvas'] ? ' class="current"' : '';
					$title = !empty($item['title']) ? " title=\"{$item['title']}\"" : '';
					$text = !empty($item['icon']) ?
						"<span>". esc_html($item['icon']). "</span> {$item['text']}" :
						"{$item['text']}";
					$submenu .=
						"\t\t\t\t\t<li$current>". (!empty($item['url']) ?
						"<a$id href=\"{$item['url']}\"$title>$text</a>" :
						"<span$id>$text</span>"). "</li>\n";
				}
				$menu .=
					"\t\t\t<li$classes><a$id class=\"topitem\"" . (!empty($link['url']) ? " href=\"{$link['url']}\"" : '') . ">{$link['text']}<span>&#9662;</span></a>\n".
					"\t\t\t\t<ul class=\"submenu\">\n".
					$submenu.
					"\t\t\t\t</ul>\n".
					"\t\t\t</li>\n";
			}
			else
				$menu .=
					"\t\t\t<li$classes><a$id class=\"toplink\" href=\"{$link['url']}\">". (!empty($link['icon']) ? 
					"<span>". esc_html($link['icon']). "</span> " : '').
					"{$link['text']}</a></li>\n";
		}
		return
			"\t\t<ul id=\"t_nav\">\n".
			$menu.
			"\t\t</ul>\n";
	}

	private function canvas() {
		global $thesis;
		$tip = $this->bubble_tips();
		echo
			(!is_dir(WP_CONTENT_DIR. '/thesis') ?
			"<p><a data-style=\"button save\" style=\"margin-bottom: 24px;\" href=\"".
			wp_nonce_url(admin_url('update.php?action=thesis-install-components'), 'thesis-install').
			"\">". __('Click to get started!', 'thesis'). "</a></p>" : ''),
			"\t\t<div class=\"t_canvas_left t_text\"", (!file_exists(WP_CONTENT_DIR. '/thesis') ? " style=\"opacity: 0.15;\"" : ''), ">\n",
			"\t\t\t<h3>", __('What&#8217;s New in Version 2.1?', 'thesis'), "</h3>\n",
			"\t\t\t<p>", __('The biggest innovations in 2.1 are the new Skin control panels, which let you manage both the content and design of your Skin.', 'thesis'), "</p>\n",
			"\t\t\t<p>", __('If your current Skin supports these new features, you&#8217;ll see quicklinks to the content and design control panels in the yellow box to the right. You can also access these panels from the Skin dropdown menu at the top of this page.', 'thesis'), "</p>\n",
			"\t\t\t<p>", sprintf(__('<strong>Note:</strong> If you&#8217;re not using the Classic Responsive Skin, you should visit the <a href="%1$s">Manage Skins</a> page and Preview the Classic Responsive Skin so you can check out the new content and design controls.', 'thesis'), admin_url('admin.php?page=thesis&canvas=select_skin')), "</p>\n",
			"\t\t\t<p>", __('After you get acquainted with the new color scheme and font options, you&#8217;ll be hooked!', 'thesis'), "</p>\n",
			"\t\t\t<p>", sprintf(__('Finally, your Custom %1$s is now easier to access, and it includes new syntax highlighting. To check it out, click the Custom %1$s link in the yellow box on the right, or else click on Custom %1$s in the Skin menu at the top of this page.', 'thesis'), $thesis->api->base['css']), "</p>\n",
			"\t\t\t<h4>", __('Getting Started with Thesis 2.1', 'thesis'), "</h4>\n",
			"\t\t\t<p>", sprintf(__('If you&#8217;re a new Thesis user, or even if you&#8217;d just like to get acquainted with version 2.1, then I highly recommend the <a href="%1$s">Thesis Admin section</a> of our new <a href="%2$s">Getting Started Guide</a>.', 'thesis'), esc_url('http://diythemes.com/thesis/rtfm/getting-started/admin/'), esc_url('http://diythemes.com/thesis/rtfm/getting-started/')), "</p>\n",
			"\t\t\t<h4>", __('Easy Website Management', 'thesis'), "</h4>\n",
			"\t\t\t<p>", __('You can now manage your site&#8217;s vital information from one handy location. Hover your mouse over the Site menu at the top of this page, and you&#8217;ll find links to <strong>essential tools</strong>, including:', 'thesis'), "</p>\n",
			"\t\t\t<ul>\n",
			"\t\t\t\t<li>", sprintf(__('<a href="%s"><strong>Google Analytics</strong></a> &#8212; easily incorporate this stat-tracking platform into your site', 'thesis'), admin_url('admin.php?page=thesis&canvas=thesis_google_analytics')), "</li>\n",
			"\t\t\t\t<li>", sprintf(__('<a href="%s"><strong>Google Authorship</strong></a> &#8212; have your author avatar appear next to search results', 'thesis'), admin_url('admin.php?page=thesis&canvas=thesis_google_authorship')), "</li>\n",
			"\t\t\t\t<li>", sprintf(__('<a href="%s"><strong>Site Verification</strong></a> &#8212; verify your site with Google or Bing Webmaster Tools', 'thesis'), admin_url('admin.php?page=thesis&canvas=thesis_meta_verify')), "</li>\n",
			"\t\t\t\t<li>", sprintf(__('<a href="%s"><strong>Tracking Scripts</strong></a> &#8212; add the code for any stats, click-tracking, or analytics package', 'thesis'), admin_url('admin.php?page=thesis&canvas=thesis_tracking_scripts')), "</li>\n",
			"\t\t\t\t<li>", sprintf(__('<a href="%s"><strong>Favicon</strong></a> &#8212; set your site&#8217;s favicon in seconds', 'thesis'), admin_url('admin.php?page=thesis&canvas=thesis_favicon')), "</li>\n",
			"\t\t\t\t<li>", sprintf(__('<a href="%1$s"><strong>%2$s Feed</strong></a> &#8212; easily manage your %2$s feed %3$s', 'thesis'), admin_url('admin.php?page=thesis&canvas=thesis_feed_link'), $thesis->api->base['rss'], $thesis->api->base['url']), "</li>\n",
			"\t\t\t\t<li>", sprintf(__('<a href="%s"><strong>Blog Page SEO</strong></a> &#8212; optimize the most important page of your site', 'thesis'), admin_url('admin.php?page=thesis&canvas=thesis_home_seo')), "</li>\n",
			"\t\t\t\t<li>", sprintf(__('<a href="%s"><strong>404 Page</strong></a> &#8212; select and edit your site&#8217;s 404 page', 'thesis'), admin_url('admin.php?page=thesis&canvas=thesis_404')), "</li>\n",
			"\t\t\t</ul>\n",
			"\t\t\t<p>", __('So far, so good. Now let&#8217;s move on to the most important pieces of the Thesis puzzle, Skins and Boxes.', 'thesis'), "</p>\n",
			"\t\t\t<h4>", __('Thesis Skins', 'thesis'), "</h4>\n",
			"\t\t\t<p>", __('Thesis Skins are an intelligent mixture of design and content. They&#8217;re similar to WordPress themes, except they are more integrated with WordPress and give you as much&#8212;or as little&#8212;control as you need. In the following paragraphs, we&#8217;ll take a closer look at how you can interact with your current Skin.', 'thesis'), "</p>\n",
			"\t\t\t<p>", __('Locate the Skin menu at the top of this page, and hover your mouse over it to see the current Skin controls. The available options will differ depending on your active Skin, so keep that in mind as you continue through this section.', 'thesis'), "</p>\n",
			"\t\t\t<p>", __('If your Skin contains editable content, you&#8217;ll see a Content link in the Skin menu. From here, you can do things like select a WordPress navigation menu, edit text boxes, and modify other content within your Skin.', 'thesis'), "</p>\n",
			"\t\t\t<p>", __('Likewise, if your Skin contains design options, you&#8217;ll see a Design link in the Skin menu. The resulting options depend on your current Skin, but this is where you&#8217;ll be able to customize fonts, colors, and other design elements.', 'thesis'), "</p>\n",
			"\t\t\t<p>", __('Here are some quick tips for getting the most out of your Skin:', 'thesis'), "</p>\n",
			"\t\t\t<ul>\n",
			"\t\t\t\t<li>", __('If your Skin has custom templates, you can assign a custom template to any post or page by selecting it in the <strong>Thesis Skin Custom Template</strong> dropdown menu on the post editing screen.', 'thesis'), "</li>\n",
			"\t\t\t\t<li>", __('The above tip also applies to categories, tags, and any taxonomy pages that you&#8217;ve registered&#8212;assign a custom template to <em>any</em> of these pages!', 'thesis'), "</li>\n",
			"\t\t\t\t<li>", sprintf(__('If your Skin contains widget areas, you&#8217;ll be able to control them on the <a href="%s">WordPress Widgets page</a>.', 'thesis'), admin_url('widgets.php')), "</li>\n",
			"\t\t\t\t<li>", sprintf(__('To change your Skin or to upload a new Skin, please visit the <a href="%s">Manage Skins page</a>.', 'thesis'), admin_url('admin.php?page=thesis&canvas=select_skin')), "</li>\n",
			"\t\t\t</ul>\n",
			"\t\t\t<h4>", __('Thesis Boxes', 'thesis'), "</h4>\n",
			"\t\t\t<p>", __('Boxes are how you add specialized content and functionality to your Thesis website. Although they are similar to WordPress Plugins, Boxes are more powerful because they are fully integrated into both the WordPress and Thesis environments.', 'thesis'), "</p>\n",
			"\t\t\t<p>", __('For example, let&#8217;s say that you&#8217;d like to add an AWeber Email Signup Form to your Website. In order to do that, you&#8217;d first need to connect your site to AWeber, and then you&#8217;d have to copy and paste the appropriate email form code into your template files (and in the right location so as not to break anything!).', 'thesis'), "</p>\n",
			"\t\t\t<p>", sprintf(__('This is where Boxes save the day. With the AWeber Box for Thesis, you can connect to the AWeber %1$s from a simple interface right here in the Thesis Admin. Next, you can drag and drop your signup form into your desired template in the Skin %2$s Editor (more on this in a moment).', 'thesis'), $thesis->api->base['api'], $thesis->api->base['html']), "</p>\n",
			"\t\t\t<p>", __('The bottom line is that Boxes can be extremely simple or extremely complex&#8212;it just depends on their purpose. Some add-on Boxes will contain options pages, and those that do will be available in the Boxes menu here in the Thesis Admin.', 'thesis'), "</p>\n",
			"\t\t\t<p>", sprintf(__('Any Boxes that contain %1$s output will be available to drag and drop into your desired template in the Skin %1$s Editor, which we&#8217;ll look at now.', 'thesis'), $thesis->api->base['html']), "</p>\n",
			"\t\t\t<h4>", __('Thesis Skin Editor', 'thesis'), "</h4>\n",
			"\t\t\t<p>", __('The Thesis Skin Editor is a tool that allows you to edit your Skin without having to touch template files or dig through hard-to-understand code. With its drag and drop interface, the Editor brings you unprecedented power and control over your templates.', 'thesis'), "</p>\n",
			"\t\t\t<p>", __('There are a zillion reasons why the Thesis Skin Editor is remarkable, but for now, let&#8217;s focus on the two most important ones.', 'thesis'), "</p>\n",
			"\t\t\t<p>", __('First, consider this: You&#8217;ve never actually <em>seen</em> a template before.', 'thesis'), "</p>\n",
			"\t\t\t<p>", __('In the past, if you wanted to edit a template, you had to either hack your theme files or else use a hook and supply your own custom code. In both cases, you had to imagine the final outcome, because you couldn&#8217;t see your entire template in one place. Fortunately, those days are over.', 'thesis'), "</p>\n",
			"\t\t\t<p>", __('The <strong>visual template editor</strong> allows you to view and edit all of your Skin&#8217;s templates in a simple, powerful interface. Because you can see your templates in their entirety for the first time, you&#8217;ll know precisely why certain items appear on certain pages. And most important, you now have the power to edit your templates to get exactly the outcomes you want&hellip;without writing any code, because <strong>it&#8217;s all drag and drop</strong>.', 'thesis'), "</p>\n",
			"\t\t\t<p>", sprintf(__('Second, you now have a <strong>live %1$s editor</strong> that delivers world class design power in a progressive interface that works like a normal %1$s file but also offers point-and-click design controls.', 'thesis'), $thesis->api->base['css']), "</p>\n",
			"\t\t\t<p>", sprintf(__('The bottom line? The Skin Editor gives you <strong>absolute control</strong> over your Skin&#8217;s %1$s, %2$s, and images.', 'thesis'), $thesis->api->base['html'], $thesis->api->base['css']), "</p>\n",
			"\t\t\t<p>", __('Best of all? By learning to use the Skin Editor, you&#8217;ll be able to control not only your current Skin, but also any other Skins you might use in the future. This eliminates the biggest headache of all when changing your design&#8212;learning how to adapt to a new environment and still get the results you want.', 'thesis'), "</p>\n",
			"\t\t\t<h4>", __('One More Thing&hellip;', 'thesis'), "</h4>\n",
			"\t\t\t<p>", sprintf(__('As of version 2.1, <strong>%1$s Packages are deprecated</strong>. While your Packages will still function properly, we recommend using regular %1$s + Variables from now on.', 'thesis'), $thesis->api->base['css']), "</p>\n",
			"\t\t\t<p>", __('If you tried out Packages in a previous version of Thesis, we&#8217;d like to thank you for giving them a shot. Because of your feedback, we were able to determine that Packages weren&#8217;t hitting the sweet spot for enough of our users.', 'thesis'), "</p>\n",
			"\t\t\t<p>", __('As a result, we now have a more powerful, more flexible system in place. To see some of this power in action, load up the Classic Responsive Skin and check out the new design options&#8212;it&#8217;ll be the last time you ever think about Packages!', 'thesis'), "</p>\n",
			"\t\t</div>\n",
			"\t\t<div class=\"t_canvas_right\">\n";
		do_action('thesis_current_skin');
		echo
			"\t\t\t<div class=\"t_bubble\">\n",
			"\t\t\t\t<p>{$tip['tip']}</p>\n",
			"\t\t\t</div>\n",
			"\t\t\t<div class=\"t_bubble_cite\">\n",
			"\t\t\t\t<img class=\"t_bubble_pic\" src=\"{$tip['img']}\" alt=\"{$tip['name']}\" width=\"90\" height=\"90\" />\n",
			"\t\t\t\t<p>{$tip['name']}</p>\n",
			"\t\t\t</div>\n",
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
			'category-seo' => array(
				'tip' => sprintf(__('Supercharge the %s of your archive pages by supplying Archive Title and Archive Content information on the editing page for categories, tags, and taxonomies.', 'thesis'), $thesis->api->base['seo']),
				'author' => 'pearsonified'),
			'404page' => array(
				'tip' => sprintf(__('Thesis lets you control the content of your 404 page. All you have to do is <a href="%s">specify a 404 page</a>, and boom&#8212;magic!', 'thesis'), admin_url('admin.php?page=thesis&canvas=thesis_404')),
				'author' => 'matt'),
			'blog' => array(
				'tip' => sprintf(__('In addition to making Thesis, DIYthemes publishes a killer blog dedicated to helping you run a better website. <a href="%s">Check out our blog</a>.', 'thesis'), esc_url('http://diythemes.com/thesis/')),
				'author' => 'pearsonified'),
			'updates' => array(
				'tip' => __('Thesis 2 features automatic updates for Skins, Boxes, Packages, <em>and</em> the Thesis core. You win.', 'thesis'),
				'author' => 'matt'),
			'verify' => array(
				'tip' => sprintf(__('You like ranking in search engines, don&#8217;t ya? Then be sure to verify your site with both Google and Bing Webmaster Tools on the <a href="%s">Site Verification page.</a>', 'thesis'), admin_url('admin.php?page=thesis&canvas=thesis_meta_verify')),
				'author' => 'lola'),
			'march-2008' => array(
				'tip' => __('<strong>Did you know?</strong><br />Thesis launched on March 29, 2008.', 'thesis'),
				'author' => 'missieur'),
			'seo-tips' => array(
				'tip' => sprintf(__('Besides using Thesis, what else can you do to improve your %1$s? Check out DIYthemes&#8217; series on <a href="%2$s">WordPress %1$s for Everybody</a>.', 'thesis'), $thesis->api->base['seo'], esc_url('http://diythemes.com/thesis/wordpress-seo/')),
				'author' => 'pearsonified'),
			'analytics'	=> array(
				'tip' => sprintf(__('Amp up your site&#8217;s search engine performance by providing <a href="%1$s">Blog Page %2$s</a> details.', 'thesis'), admin_url('admin.php?page=thesis&canvas=thesis_home_seo'), $thesis->api->base['seo']),
				'author' => 'lola'),
			'email-marketing' => array(
				'tip' => sprintf(__('<strong>Did you know?</strong><br />Email marketing is probably the best way to leverage the web to grow your business. Get started today with DIYthemes&#8217; exclusive guide: <a href="%1$s">Email Marketing for Everybody</a>.', 'thesis'), 'http://diythemes.com/thesis/email-marketing-everybody/'),
				'author' => 'missieur'),
			'custom-templates' => array(
				'tip' => __('No matter which Skin you use, you can always create custom templates in the Skin Editor for things like landing pages, checkout pages, and more.', 'thesis'),
				'author' => 'pearsonified'),
/*			'schema' => array(
				'tip' => __('<strong>Did you know?</strong><br />Search engines love markup Schema, and you can enable these via the Post Box in the Template Editor.', 'thesis'),
				'author' => 'missieur'),
			'typography' => array(
				'tip' => __('<strong>Did you know?</strong><br />Many Thesis Packages have golden ratio typography baked right in, so you get perfect typography without having to think about it.', 'thesis'),
				'author' => 'pearsonified'),
			'variables' => array(
				'tip' => sprintf(__('Thesis&#8217; %1$s variables can contain other variables. It&#8217;s like inception for your %1$s!', 'thesis'), $thesis->api->base['css']),
				'author' => 'missieur'),
			'canvas' => array(
				'tip' => sprintf(__('If you&#8217;re using the %1$s Editor and click on a link in the Canvas, the %1$s Editor will automatically adjust and show you the template that is currently active in the Canvas.', 'thesis'), $thesis->api->base['html']),
				'author' => 'pearsonified'),
			'templates' => array(
				'tip' => sprintf(__('<strong>Did you know?</strong><br>You can click on the template name in the Skin %1$s Editor to view or edit other templates.', 'thesis'), $thesis->api->base['html']),
				'author' => 'matt'),
			'custom-css' => array(
				'tip' => sprintf(__('<strong>How do I add custom %1$s?</strong><br />With Thesis 2, you no longer need a separate file for your customizations&#8212;you can simply type your custom %1$s directly into the Skin %1$s Editor. Better still, you&#8217;ll be able to see any changes you make <em>live</em> in the Canvas as you type!', 'thesis'), $thesis->api->base['css']),
				'author' => 'lola'),
			'copy' => array(
				'tip' => sprintf(__('Adding templates to your Skin is easy with Thesis, and it&#8217;s even easier when you use the <strong>Copy from Template</strong> feature in the Skin %s Editor.', 'thesis'), $thesis->api->base['html']),
				'author' => 'matt'),
			'backup' => array(
				'tip' => __('Have you checked out the Manager inside the Skin Editor yet? With the Manager, you can backup, restore, import, and export your Skins. I like to call this &ldquo;winning.&rdquo;', 'thesis'),
				'author' => 'lola'),*/);
		$pick = $tips;
		shuffle($pick);
		$tip = array_shift($pick);
		$tip['name'] = $authors[$tip['author']]['name'];
		$tip['img'] = THESIS_IMAGES_URL . "/{$authors[$tip['author']]['img']}";
		return $tip;
	}
	
	public function menu_fix() {
		if (!empty($_GET['canvas']))
			echo	"<script type=\"text/javascript\">\n",
					"\tjQuery(document).ready(function($){\n",
					"\t\t$('#toplevel_page_thesis .wp-submenu li a').each(function(){\n",
					"\t\t\t$(this).parent('li').removeClass('current');\n",
					"\t\t\tif (/", esc_attr($_GET['canvas']), "/.test($(this).attr('href')))\n",
					"\t\t\t\t$(this).parent('li').addClass('current');\n",
					"\t\t});\n",
					"\t});\n",
					"</script>\n";
	}

	public function update_script() {
		$transients = array(
			'thesis_skins_update',
			'thesis_boxes_update',
			'thesis_packages_update',
			'thesis_core_update');
		$show = $out = false;
		foreach ($transients as $transient) {
			if (get_transient($transient)) {
				$show = true;
				break;
			}
		}
		if (!empty($show))
			echo
				"<script type=\"text/javascript\">\n",
				"\tfunction thesis_update_message() {\n",
				"\t\treturn confirm('", __('Are you sure you want to update? Core files will be overwritten. Click OK to continue or cancel to quit.', 'thesis'), "');\n",
				"\t}\n",
				"</script>\n";
		
		// script for system status
		if (!empty($_GET['canvas']) && $_GET['canvas'] === 'system_status')
			echo
				"\t\t<script type=\"text/javascript\">\n",
				"\t\t\tjQuery(document).ready(function($){\n",
				"\t\t\t\tjQuery('#t_system_status').focus(function(){\n",
				"\t\t\t\t\tvar \$this = jQuery(this);\n",
				"\t\t\t\t\t\$this.select();\n",
				"\t\t\t\t\t\$this.mouseup(function() {\n",
				"\t\t\t\t\t\t\$this.unbind(\"mouseup\");\n",
				"\t\t\t\t\t\treturn false;\n",
				"\t\t\t\t\t});\n",
				"\t\t\t\t});\n",
				"\t\t\t});\n",
				"\t\t</script>\n";
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

	public function version() {
		$theme_data = get_theme_data(TEMPLATEPATH . '/style.css'); #wp
		$version = trim($theme_data['Version']);
		return $version;
	}

	public function convert_terms() {
		global $thesis, $wpdb; #wp
		$table = $wpdb->prefix. 'thesis_terms';
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

	public function beta_css() {
		global $thesis;
		if (preg_match('/a|b/i', $thesis->version))
			echo "<style type=\"text/css\">.toplevel_page_thesis .t_beta { color: orange; }</style>\n";
	}

	public function system_status() {
		global $thesis, $wp_version, $wpdb;
		include_once(ABSPATH . '/wp-admin/includes/file.php');
		echo
			"\t\t<h3>", __('System Status', 'thesis'), "</h3>\n",
			"\t\t<div class=\"option_item option_field\">\n",
			"\t\t\t<p>\n",
			"\t\t\t\t<textarea id=\"t_system_status\" rows=\"25\">\n",
			__('About Thesis', 'thesis'), "\n",
			"===========================\n",
			sprintf(__('Thesis Version: %s', 'thesis'), esc_attr($thesis->version)), "\n",
			sprintf(__('Current Skin Name: %s', 'thesis'), esc_attr($thesis->skins->skin['name'])), "\n",
			sprintf(__('Current Skin Version: %s', 'thesis'), esc_attr($thesis->skins->skin['version'])), "\n",
			sprintf(__('Current Skin Author: %s', 'thesis'), esc_attr($thesis->skins->skin['author'])), "\n\n",
			__('Thesis Filesystem Check', 'thesis'), "\n",
			"===========================\n",
			"wp-content/thesis: ", (is_dir(WP_CONTENT_DIR. '/thesis') ? 'YES' : 'NO'), "\n",
			"wp-content/thesis/skins: ", (is_dir(WP_CONTENT_DIR. '/thesis/skins') ? 'YES' : 'NO'), "\n",
			"wp-content/thesis/boxes: ", (is_dir(WP_CONTENT_DIR. '/thesis/boxes') ? 'YES' : 'NO'), "\n",
			"wp-content/thesis/packages: ", (is_dir(WP_CONTENT_DIR. '/thesis/packages') ? 'YES' : 'NO'), "\n",
			"wp-content/thesis/master.php: ", (is_file(WP_CONTENT_DIR. '/thesis/master.php') ? 'YES' : 'NO'), "\n\n",
			__('About WordPress', 'thesis'), "\n",
			"===========================\n",
			sprintf(__('WordPress Version: %s', 'thesis'), esc_attr($wp_version)), "\n",
			sprintf(__('Filesystem Method: %s', 'thesis'), get_filesystem_method()), "\n",
			sprintf(__('Using Multisite: %s', 'thesis'), (is_multisite() ? 'YES' : 'NO')), "\n\n",
			__('About PHP', 'thesis'), "\n",
			"===========================\n",
			sprintf(__('Version: %s', 'thesis'), PHP_VERSION), "\n",
			sprintf(__('cURL: %s', 'thesis'), (function_exists('curl_init') ? 'YES' : 'NO')), "\n",
			sprintf(__('Max Upload (according to WP): %s', 'thesis'), size_format(wp_max_upload_size())), "\n",
			sprintf(__('Memory Limit: %s', 'thesis'), ini_get('memory_limit')), "\n\n",
			__('About Server/Database', 'thesis'), "\n",
			"===========================\n",
			sprintf(__('Server Software: %s', 'thesis'), esc_attr($_SERVER['SERVER_SOFTWARE'])), "\n",
			sprintf(__('Database Charset: %s', 'thesis'), esc_attr($wpdb->charset)), "\n",
			sprintf(__('MySQL Version: %s', 'thesis'), esc_attr(mysql_get_server_info())), "\n",
			sprintf(__('PHP Handler: %s', 'thesis'), esc_attr((function_exists('php_sapi_name') ? php_sapi_name() : 'Unknown'))),
			"</textarea>\n",
			"\t\t\t</p>\n",
			"\t\t</div>\n";
	}
}