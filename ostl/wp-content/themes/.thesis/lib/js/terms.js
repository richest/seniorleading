/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
jQuery(document).ready(function($) {
	$('.count_field').each(function() {
		var count = $(this).val().length;
		$(this).siblings('.counter').val(count);
		$(this).siblings('label').children('.counter').val(count);
	}).keyup(function() {
		var count = $(this).val().length;
		$(this).siblings('.counter').val(count);
		$(this).siblings('label').children('.counter').val(count);
	});
});