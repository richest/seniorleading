<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
/*
	Name: Thesis Classic
	Author: Chris Pearson
	Description: Elegant and versatile, Thesis Classic features clean lines and mathematical precision with an emphasis on typography.
	Version: 1.0.3
	Class: thesis_classic
*/
class thesis_classic extends thesis_skin {
	function construct() {
		add_filter('thesis_post_num_comments', array($this, 'num_comments'));
		add_filter('thesis_comments_intro', array($this, 'comments_intro'));
	}

	function num_comments($content) {
		return "<span class=\"bracket\">{</span> $content <span class=\"bracket\">}</span>";
	}

	function comments_intro($text) {
		return "<span class=\"bracket\">{</span> $text <span class=\"bracket\">}</span>";
	}
}