/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
jQuery(document).ready(function($) {
	$("#post").attr('enctype', 'multipart/form-data');
	$("#post").attr('encoding', 'multipart/form-data');
	$('.option_field label .toggle_tooltip').on('click', function() {
		$(this).parents('label').parents('p').siblings('.tooltip:first').toggle();
		return false;
	});
	$('.option_field .list_label .toggle_tooltip').on('click', function() {
		$(this).parents('.list_label').siblings('.tooltip:first').toggle();
		return false;
	});
	$('.tooltip').on('mouseleave', function() { $(this).hide(); });
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