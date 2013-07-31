/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
var thesis_head;
(function($) {
thesis_head = {
	init: function() {
		thesis_ui.box_form.init();
		$('#save_head').click(function() {
			thesis_head.save();
			return false;
		});
	},
	save: function() {
		$('#save_head').prop('disabled', true);
		$.post(ajaxurl, { action: 'save_head', form: $('#t_boxes').serialize() }, function(saved) {
			$('#save_head').prop('disabled', false);
			$('#t_canvas').append(saved);
			$('#head_saved').css({'right': $('#save_head').outerWidth()+35+'px'});
			$('#head_saved').fadeOut(3000, function() { $(this).remove(); });
			thesis_ui.box_form.reset();
		});
	}
};
$(document).ready(function($){ thesis_head.init(); });
})(jQuery);