<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_wp_admin extends thesis_box {
	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('%s Admin Link', 'thesis'), $thesis->api->base['wp']);
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		echo str_repeat("\t", !empty($depth) ? $depth : 0),
			"<p><a href=\"", admin_url(), '">', sprintf(__('%s Admin', 'thesis'), $thesis->api->base['wp']), "</a></p>\n"; #wp
	}
}