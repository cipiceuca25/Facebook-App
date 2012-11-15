'use strict';

$(document).ready(function() {

	function animateText() {
		$(".new-fanpage").animate({
			"height" : "toggle",
			"opacity" : "toggle"
		}, "slow");

	}
	
	setInterval(animateText, 8000);
	
});