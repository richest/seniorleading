/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
var thesis_objects;
(function($) {
thesis_objects = {
	init: function() {
		$('#object_upload').click(function() { thesis_objects.popup('#popup_object_uploader'); return false; });
		$('#objects_saved').css({'right': $('#save_objects').outerWidth()+35+'px'}).fadeOut(3000, function() { $(this).remove(); });
		$('.select_object').on('change', function() {
			if ($(this).is(':checked'))
				$(this).parent().addClass('active_object');
			else
				$(this).parent().removeClass('active_object');
		});
		$('.delete_object').on('click', function() {
			thesis_objects.delete_popup($(this).attr('data-type'), $(this).attr('data-class'), $(this).attr('data-url'));
			return false;
		});
	},
	popup: function(popup) {
		$('body').addClass('no-scroll');
		$(popup).show();
		var body = $(popup+' .t_popup_body');
		$(body).css({'margin-top': $(popup+' .t_popup_head').outerHeight()});
		$('.t_popup_close').on('click', function() {
			$(popup).hide();
			$('body').removeClass('no-scroll');
		});
	},
	add_item: function(iframe, div, append, url) {
		if (div !== false)
			$(iframe).contents().find(div).insertAfter(append);
		setTimeout(function(){
			$(iframe).attr('src', url);
		}, 1500);
	},
	delete_popup: function(type, object, url) {
		if (confirm('Are you sure you want to delete this '+type+'? (You can always re-install this '+type+' at a later time.)') && typeof type == 'string' && typeof object == 'string' && typeof url == 'string') {
			$.post(ajaxurl, { action: 'delete_'+type, class: object, url: url }, function(popup) {
				$('#t_canvas').append(popup);
				thesis_objects.popup('#popup_delete_'+object);
			});
		}
	}
};
$(document).ready(function($){ thesis_objects.init(); });
})(jQuery);