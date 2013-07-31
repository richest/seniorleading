/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
jQuery(document).ready(function($) {
	$('.topmenu').hover(function() { $(this).children('.submenu').show(); $(this).children('.topitem').addClass('active'); }, function() { $(this).children('.submenu').hide(); $(this).children('.topitem').removeClass('active'); });
	$('.topitem').click(function() { return false; });
});