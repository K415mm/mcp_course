/*
Template Name: HUD - Responsive Bootstrap 5 Admin Template
Version: 6.0.0
Author: Sean Ngu
Website: http://www.kamanext.com/hud/
*/

var handleRenderSummernote = function() {
	var totalHeight = ($(window).width() >= 767) ? $(window).height() - $('.summernote').offset().top - 90 : 400;
	$('.summernote').summernote({
		height: totalHeight
	});
};

var handleEmailTagsInput = function() {
	$('#email-to').tagit({
		availableTags: ["admin2@kamanext.com", "admin3@kamanext.com", "admin4@kamanext.com", "admin5@kamanext.com", "admin6@kamanext.com", "admin7@kamanext.com", "admin8@kamanext.com"]
	});
	$('#email-cc').tagit({
		availableTags: ["admin2@kamanext.com", "admin3@kamanext.com", "admin4@kamanext.com", "admin5@kamanext.com", "admin6@kamanext.com", "admin7@kamanext.com", "admin8@kamanext.com"]
	});
	$('#email-bcc').tagit({
		availableTags: ["admin2@kamanext.com", "admin3@kamanext.com", "admin4@kamanext.com", "admin5@kamanext.com", "admin6@kamanext.com", "admin7@kamanext.com", "admin8@kamanext.com"]
	});
};


/* Controller
------------------------------------------------ */
$(document).ready(function() {
	handleRenderSummernote();
	handleEmailTagsInput();
});
