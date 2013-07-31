/*
Copyright 2013 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
var thesis_colors;
(function($) {
thesis_colors = {
	init: function() {
		$('.scheme').on('change', function() {
			if ($(this).siblings('.complements').hasClass('triggered'))
				thesis_colors.get_complements($(this).siblings('.complements'), $(this).attr('id'), $(this).val());
		});
		$('.color_scale').on('click', function() {
			$('.color_picker').css({ 'top': $(this).outerHeight()+14+'px' });
			$(this).toggleClass('active');
			$(this).siblings('.color_picker').slideToggle(50);
			return false;
		});
		$('.control_swatch').on('click', function() {
			$(this).parent().parent().parent('.scheme_color_scale').siblings('.scheme_colors').children('.scheme_color').each(function() {
				thesis_colors.reset_complements($(this).children('.complements'));
			});
			$(this).siblings('.'+$(this).attr('data-value')).each(function() {
				var id = $(this).attr('data-scheme')+'-'+$(this).attr('data-id');
				$('#'+id).val($(this).attr('data-value'));
				jscolor.color(id);
			});
		});
		$('.complement').on('click', function() {
			var complements = $(this).siblings('.complements');
			$(complements).toggleClass('triggered').slideToggle(50);
			if ($(complements).hasClass('triggered'))
				thesis_colors.get_complements(complements, $(this).attr('data-id'), $(this).siblings('.scheme').val());
		});
	},
	get_complements: function(complements, id, color) {
		$.post(ajaxurl, { action: 'color_complement', id: id, color: color }, function(swatches) {
			$(complements).html(swatches);
			thesis_colors.init_complements(complements, id);
		});
	},
	init_complements: function(complements, id) {
		$(complements).children('.complement_swatch').on('click', function() {
			$('#'+id).val($(this).attr('data-value'));
			jscolor.color(id);
		});
	},
	reset_complements: function(complements) {
		$(complements).html('');
		if ($(complements).hasClass('triggered'))
			$(complements).removeClass('triggered').slideToggle(50);
	}
};
$(document).ready(function($){ thesis_colors.init(); });
})(jQuery);