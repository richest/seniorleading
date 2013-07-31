/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
var thesis_tracking;
(function($) {
thesis_tracking = {
	init: function() {
		$('.option_field label .toggle_tooltip').on('click', function() {
			$(this).parents('label').parents('p').siblings('.tooltip:first').toggle();
			return false;
		});
		$('.tooltip').on('mouseleave', function() { $(this).hide(); });
		$('#save_tracking').click(function() {
			thesis_tracking.save();
			return false;
		});
	},
	save: function() {
		$('#save_tracking').prop('disabled', true);
		$.post(ajaxurl, { action: 'save_tracking', form: $('#t_tracking').serialize() }, function(saved) {
			$('#save_tracking').prop('disabled', false);
			$('#t_canvas').append(saved);
			$('#tracking_saved').css({'right': $('#save_tracking').outerWidth()+35+'px'});
			$('#tracking_saved').fadeOut(3000, function() { $(this).remove(); });
		});
	}
};
$(document).ready(function($){ thesis_tracking.init(); });
})(jQuery);