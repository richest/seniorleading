<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_favicon extends thesis_box {
	public $type = false;
	protected $filters = array(
		'menu' => 'site',
		'priority' => 20);

	protected function translate() {
		$this->title = __('Favicon', 'thesis');
	}

	protected function class_options() {
		return array(
			'image' => array(
				'type' => 'image_upload',
				'label' => $this->title,
				'upload_label' => __('Upload Image', 'thesis'),
				'prefix' => $this->_class));
	}

	protected function construct() {
		global $thesis;
		if ($thesis->environment == 'admin') {
			$args = array(
				'title' => __('Upload Image', 'thesis'),
				'prefix' => $this->_class,
				'file_type' => 'image',
				'show_delete' => !empty($this->class_options['image']['url']) ? true : false,
				'delete_text' => __('Remove Image', 'thesis'));
			if (method_exists($this, 'save'))
				$args['save_callback'] = array($this, 'save');
			new thesis_upload($args);
			add_action("{$this->_class}_before_thesis_iframe_form", array($this, '_script'));
		}
		elseif (empty($thesis->environment))
			add_action('hook_head', array($this, 'html'));
	}

	public function _script() {
		$url = !empty($_GET['url']) ?
			esc_url(urldecode($_GET['url'])) : (!empty($this->class_options['image']['url']) ?
			esc_url($this->class_options['image']['url']) : false);
		if (!!$url)
			echo "<img style=\"max-width: 90%;\" id=\"", esc_attr($this->_id), "_box_image\" src=\"$url\" />\n";
	}

	public function admin_init() {
		add_action('admin_head', array($this, 'admin_css'));
	}

	public function admin_css() {
		echo
			"<style type=\"text/css\">\n",
			"#t_canvas #save_options { display: none; }\n",
			"</style>\n";
	}

	public function html() {
		$url = esc_url(!empty($this->class_options['image']['url']) ?
			stripslashes($this->class_options['image']['url']) :
			THESIS_IMAGES_URL. '/favicon.ico');
		echo "<link rel=\"shortcut icon\" href=\"$url\" />\n";
	}

	public function save($image, $delete) {
		global $thesis;
		$save = !empty($image) ? $thesis->api->set_options($this->_class_options(), array('image' => $image)) : false;
		if (empty($save)) {
			if (!empty($delete))
				delete_option($this->_class);
		}
		else
			update_option($this->_class, $save);
	}
}

class thesis_feed_link extends thesis_box {
	public $type = false;
	protected $filters = array(
		'menu' => 'site',
		'priority' => 25);

	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('%s Feed', 'thesis'), $thesis->api->base['rss']);
	}

	protected function construct() {
		add_action('hook_head', array($this, 'html'), 1);
	}

	protected function class_options() {
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
		global $thesis;
		if (($url = apply_filters($this->_class, !empty($this->options['url']) ? $this->options['url'] : get_bloginfo(get_default_feed() . '_url'))) && is_string($url) && !empty($url))
			echo '<link rel="alternate" type="application/rss+xml" title="', trim((!empty($thesis->api->options['blogname']) ?
				wptexturize(htmlspecialchars_decode(stripslashes($thesis->api->options['blogname']), ENT_QUOTES)) : __('site', 'thesis')). ' '. __('feed', 'thesis')), '" href="', esc_attr(esc_url($url)), "\" />\n";
	}
}

class thesis_pingback_link extends thesis_box {
	public $type = false;

	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('Pingback %s', 'thesis'), $thesis->api->base['url']);
	}
	
	protected function construct() {
		if (apply_filters($this->_class, true))
			add_action('hook_head', array($this, 'html'), 1);
	}

	public function html() {
		echo '<link rel="pingback" href="', esc_url(get_bloginfo('pingback_url')), "\" />\n"; #wp
	}
}

class thesis_google_analytics extends thesis_box {
	public $type = false;
	protected $filters = array('menu' => 'site');

	protected function translate() {
		$this->title = __('Google Analytics', 'thesis');
	}

	protected function construct() {
		global $thesis;
		if (is_admin() && ($update = $thesis->api->get_option('thesis_analytics')) && !empty($update)) {
			update_option($this->_class, ($this->options = array('ga' => $update)));
			delete_option('thesis_analytics');
			wp_cache_flush();
		}
		elseif (!empty($this->options['ga']))
			add_action('hook_before_html', array($this, 'html'), 1);
	}

	protected function class_options() {
		return array(
			'ga' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('Google Analytics Tracking ID', 'thesis'),
				'tooltip' => sprintf(__('To add Google Analytics tracking to Thesis, simply enter your Tracking ID here. This number takes the general form <code>UA-XXXXXXX-Y</code> and can be found by clicking the Home link in your <a href="%s">Google Analytics dashboard</a> (login required).', 'thesis'), 'https://google.com/analytics/')));
	}

	public function html() {
		global $thesis;
		if (empty($this->options['ga']) || is_user_logged_in()) return;
		echo
			"<script type=\"text/javascript\">\n",
			"var _gaq = _gaq || [];\n",
			"_gaq.push(['_setAccount', '", trim($thesis->api->esc($this->options['ga'])), "']);\n",
			"_gaq.push(['_trackPageview']);\n",
			"(function() {\n",
			"var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;\n",
			"ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';\n",
			"var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);\n",
			"})();\n",
			"</script>\n";
	}
}

class thesis_tracking_scripts extends thesis_box {
	public $type = false;
	protected $filters = array('menu' => 'site');

	protected function translate() {
		global $thesis;
		$this->title = $thesis->api->strings['tracking_scripts'];
	}

	protected function construct() {
		global $thesis;
		if (is_admin() && ($update = $thesis->api->get_option('thesis_scripts')) && !empty($update)) {
			update_option($this->_class, ($this->options = array('scripts' => $update)));
			delete_option('thesis_scripts');
			wp_cache_flush();
		}
		elseif (!empty($this->options['scripts']))
			add_action('hook_after_html', array($this, 'html'), 9);
	}

	protected function class_options() {
		global $thesis;
		return array(
			'scripts' => array(
				'type' => 'textarea',
				'rows' => 10,
				'code' => true,
				'label' => $this->title,
				'description' => __('please include <code>&lt;script&gt;</code> tags', 'thesis'),
				'tooltip' => sprintf(__('Any scripts you add here will be displayed just before the closing <code>&lt;/body&gt;</code> tag on every page of your site.<br /><br />If you need to add a script to your %1$s <code>&lt;head&gt;</code>, visit the <a href="%2$s">%1$s Head Editor</a> and click on the <strong>Head Scripts</strong> box.', 'thesis'), $thesis->api->base['html'], admin_url('admin.php?page=thesis&canvas=head'))));
	}

	public function html() {
		if (empty($this->options['scripts'])) return;
		echo trim(stripslashes($this->options['scripts'])), "\n";
	}
}

class thesis_google_authorship extends thesis_box {
	public $type = false;
	protected $filters = array(
		'menu' => 'site',
		'canvas_left' => true);

	protected function translate() {
		global $thesis;
		$this->title = __('Google Authorship', 'thesis');
		$this->label = __('Google+ Profile Link', 'thesis');
	}

	protected function construct() {
		add_action('hook_head', array($this, 'html'), 1);
		add_filter('user_contactmethods', array($this, 'add_gplus'));
	}

	protected function class_options() {
		return array(
			'gplus' => array(
				'type' => 'text',
				'width' => 'full',
				'label' => $this->label,
				'tooltip' => sprintf(__('If you want your author information to display in Google search results, enter your %1$s here. If you run a multi-author website, be sure to enter each author&#8217;s %1$s on their <a href="%2$s">user profile page</a>.', 'thesis'), $this->label, admin_url('users.php'))));
	}

	public function html() {
		global $thesis, $wp_query;
		if (empty($this->options['gplus']) && !$wp_query->is_singular) return;
		if ($wp_query->is_singular)
			$gplus = get_user_option('gplus', $wp_query->post->post_author);
		if (empty($gplus) && !empty($this->options['gplus']))
			$gplus = $this->options['gplus'];
		if (!empty($gplus))
			echo '<link rel="author" href="', (preg_match('/http|https/', $gplus) ? esc_url($gplus) : 'https://plus.google.com/' . trim($thesis->api->esc($gplus))), "\" />\n";
	}

	public function add_gplus($contacts) {
		$contacts['gplus'] = $this->label;
		return $contacts;
	}
}

class thesis_meta_verify extends thesis_box {
	public $type = false;
	protected $filters = array(
		'menu' => 'site',
		'canvas_left' => true);
	private $allowed = array(
		'meta' => array(
			'name' => array(),
			'content' => array()));

	protected function translate() {
		$this->title = __('Site Verification', 'thesis');
	}

	protected function construct() {
		if (empty($this->options)) return;
		add_action('hook_head', array($this, 'html'), 1);
	}

	protected function class_options() {
		$tooltip = __('For optimal search engine performance, we recommend verifying your site with', 'thesis');
		return array(
			'google' => array(
				'type' => 'text',
				'width' => 'full',
				'label' => __('Google Site Verification', 'thesis'),
				'tooltip' => sprintf(__('%1$s <a href="%2$s" target="_blank">Google Webmaster Tools</a>. Copy and paste the entire Google verification <code>&lt;meta&gt;</code> tag or just the unique <code>content=&quot;&quot;</code> value into this field.', 'thesis'), $tooltip, 'https://www.google.com/webmasters/tools/')),
			'bing' => array(
				'type' => 'text',
				'width' => 'full',
				'label' => __('Bing Site Verification', 'thesis'),
				'tooltip' => sprintf(__('%1$s <a href="%2$s" target="_blank">Bing Webmaster Tools</a>. Copy and paste the entire Bing verification <code>&lt;meta&gt;</code> tag or just the unique <code>content=&quot;&quot;</code> value into this field.', 'thesis'), $tooltip, 'http://www.bing.com/toolbox/webmasters/')));
	}

	public function html() {
		global $thesis;
		if (!is_front_page()) return;
		echo
			(!empty($this->options['google']) ? (preg_match('/<meta/', $this->options['google']) ?
			trim(wp_kses(stripslashes($this->options['google']), $this->allowed)) . "\n" :
			"<meta name=\"google-site-verification\" content=\"" . trim($thesis->api->esc($this->options['google'])) . "\" />\n") : ''),
			(!empty($this->options['bing']) ? (preg_match('/<meta/', $this->options['bing']) ?
			trim(wp_kses(stripslashes($this->options['bing']), $this->allowed)) . "\n" :
			"<meta name=\"msvalidate.01\" content=\"" . trim($thesis->api->esc($this->options['bing'])) . "\" />\n") : '');
	}
}

class thesis_home_seo extends thesis_box {
	public $type = false;
	public $filters = array(
		'menu' => 'site',
		'priority' => 30,
		'canvas_left' => true);

	public function translate() {
		global $thesis;
		$this->title = sprintf(__('Blog Page %s', 'thesis'), $thesis->api->base['seo']);
	}

	protected function class_options() {
		global $thesis;
		return array(
			'title' => array(
				'type' => 'text',
				'width' => 'full',
				'label' => $thesis->api->strings['title_tag'],
				'counter' => $thesis->api->strings['title_counter']),
			'description' => array(
				'type' => 'textarea',
				'rows' => 2,
				'label' => $thesis->api->strings['meta_description'],
				'counter' => $thesis->api->strings['description_counter']),
			'keywords' => array(
				'type' => 'text',
				'width' => 'full',
				'label' => $thesis->api->strings['meta_keywords'],
				'tooltip' => sprintf(__('Please note that keywords will not appear unless you also include the Meta Keywords Box in your <a href="%s">HTML Head template</a>.', 'thesis'), admin_url('admin.php?page=thesis&canvas=head'))));
	}
}

class thesis_404 extends thesis_box {
	public $type = false;
	protected $filters = array(
		'menu' => 'site',
		'priority' => 40);
	private $page = false;

	public function translate() {
		global $thesis;
		$this->title = sprintf(__('404 %s', 'thesis'), $thesis->api->strings['page']);
	}

	protected function construct() {
		global $thesis;
		$this->page = is_numeric($page = $thesis->api->get_option('thesis_404')) ? $page : $this->page;
		if (!empty($this->page))
			add_filter('thesis_404', array($this, 'query'));
		if ($thesis->environment == 'admin')
			add_action('admin_post_thesis_404', array($this, 'save'));
	}

	public function query($query) {
		return $this->page ? new WP_Query("page_id=$this->page") : $query;
	}

	public function admin_init() {
		add_action('admin_head', array($this, 'css_js'));
	}

	public function css_js() {
		echo
			"<script>\n",
			"var thesis_404;\n",
			"(function($) {\n",
			"thesis_404 = {\n",
			"\tinit: function() {\n",
			"\t\t$('#edit_404').on('click', function() {\n",
			"\t\t\tvar page = $('#thesis_404').val();\n",
			"\t\t\tif (page != 0)\n",
			"\t\t\t\t$(this).attr('href', $('#edit_404').attr('data-base') + page + '&action=edit');\n",
			"\t\t\telse\n",
			"\t\t\t\treturn false;\n",
			"\t\t});\n",
			"\t}\n",
			"};\n",
			"$(document).ready(function($){ thesis_404.init(); });\n",
			"})(jQuery);\n",
			"</script>\n";
	}

	public function admin() {
		global $thesis;
		$tab = str_repeat("\t", $depth = 2);
		echo
			(!empty($_GET['saved']) ? $thesis->api->alert($_GET['saved'] === 'yes' ?
			__('404 page saved!', 'thesis') :
			__('404 not saved. Please try again.', 'thesis'), 'options_saved', true, false, $depth) : ''),
			"$tab<h3>", wptexturize($this->title), "</h3>\n",
			"$tab<form class=\"thesis_options_form\" method=\"post\" action=\"", admin_url('admin-post.php?action=thesis_404'), "\">\n",
			"$tab\t<div class=\"option_item option_field\">\n",
			wp_dropdown_pages(array('name' => 'thesis_404', 'echo' => 0, 'show_option_none' => __('Select a 404 page', 'thesis'). ':', 'option_none_value' => '0', 'selected' => $this->page)),
			"$tab\t</div>\n",
			"$tab\t", wp_nonce_field('thesis-save-404', '_wpnonce-thesis-save-404', true, false), "\n",
			"$tab\t<input type=\"submit\" data-style=\"button save\" class=\"t_save\" id=\"save_options\" value=\"", esc_attr(wptexturize(strip_tags(sprintf(__('%1$s %2$s', 'thesis'), $thesis->api->strings['save'], $this->title)))), "\" />\n",
			"$tab</form>\n",
			"$tab<a id=\"edit_404\" data-style=\"button action\" href=\"", admin_url("post.php?post=$this->page&action=edit"), "\" data-base=\"", admin_url('post.php?post='), "\">", wptexturize(sprintf(__('%1$s %2$s', 'thesis'), $thesis->api->strings['edit'], $this->title)), "</a>\n";
	}

	public function save() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		$thesis->wp->nonce($_POST['_wpnonce-thesis-save-404'], 'thesis-save-404');
		$saved = 'no';
		if (is_numeric($page = $_POST['thesis_404'])) {
			if ($page == '0')
				delete_option('thesis_404');
			else
				update_option('thesis_404', $page);
			$saved = 'yes';
		}
		wp_redirect("admin.php?page=thesis&canvas=$this->_class&saved=$saved");
		exit;
	}
}