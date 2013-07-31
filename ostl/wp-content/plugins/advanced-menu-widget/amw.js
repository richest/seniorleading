(function($) {

	$(".amw").change(function() {
		if ( this.options[this.selectedIndex].value ) {
			console.log(this);
			location.href = this.options[this.selectedIndex].value;
		}
	});

})(jQuery);