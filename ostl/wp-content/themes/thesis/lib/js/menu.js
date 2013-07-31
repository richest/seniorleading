/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
jQuery(document).ready(function($) {
	$('.topmenu').hover(function() { $(this).children('.submenu').show(); $(this).children('.topitem').addClass('active'); }, function() { $(this).children('.submenu').hide(); $(this).children('.topitem').removeClass('active'); });
	$('.topitem').click(function() { return false; });
});