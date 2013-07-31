/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
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