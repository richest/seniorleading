/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
var thesis_home_seo;
(function($) {
thesis_home_seo = {
	init: function() {
		$('.count_field').each(function() {
			var count = $(this).val().length;
			$(this).siblings('.counter').val(count);
			$(this).siblings('label').children('.counter').val(count);
		}).keyup(function() {
			var count = $(this).val().length;
			$(this).siblings('.counter').val(count);
			$(this).siblings('label').children('.counter').val(count);
		});
		$('#save_home_seo').click(function() {
			thesis_home_seo.save();
			return false;
		});
	},
	save: function() {
		$('#save_home_seo').prop('disabled', true);
		$.post(ajaxurl, { action: 'save_home_seo', form: $('#t_home_seo').serialize() }, function(saved) {
			$('#save_home_seo').prop('disabled', false);
			$('#t_canvas').append(saved);
			$('#home_seo_saved').css({'right': $('#save_home_seo').outerWidth()+35+'px'});
			$('#home_seo_saved').fadeOut(3000, function() { $(this).remove(); });
		});
	}
};
$(document).ready(function($){ thesis_home_seo.init(); });
})(jQuery);