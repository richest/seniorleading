/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
var thesis_404;
(function($) {
thesis_404 = {
	init: function() {
		$('#save_404').click(function() {
			thesis_404.save();
			return false;
		});
	},
	save: function() {
		var form = $('#thesis_select_404').serialize();
		$('#save_404').prop('disabled', true);
		$.post(ajaxurl, { action: 'save_404', form: form }, function(saved) {
			$('#save_404').prop('disabled', false);
			$('#t_canvas').append(saved);
			$('#saved_404').css({'right': $('#save_404').outerWidth()+35+'px'});
			$('#saved_404').fadeOut(3000, function() { $(this).remove(); });
			$('#edit_404').remove();
			$.post(ajaxurl, { action: 'update_404', form: form }, function(update) {
				$('#t_canvas').append(update);
			});
		});
	}
};
$(document).ready(function($){ thesis_404.init(); });
})(jQuery);