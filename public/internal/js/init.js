$(function() {
	$(".metro").metro();

	// this is for the appbar-demo page
	if ($("#appbar-theme-select").length) {
		$("#appbar-theme-select").change(
				function() {
					var ui = $(this).val();

					if (ui != '')
						$("footer.win-commandlayout").removeClass(
								"win-ui-light win-ui-dark").addClass(ui);
				});
	}

	// style switcher 
	if ($("#win-theme-select").length) {
		$("#win-theme-select").change(function() {
			var css = $(this).val();

			if (css != '')
				updateCSS(css);
		});
	}

	$("#settings").click(function(e) {
		e.preventDefault();
		$('#charms').charms('showSection', 'theme-charms-section');
	});

	// listview demo
	$('#listview-grid-demo').on('click', '.mediumListIconTextItem',
			function(e) {
				e.preventDefault();
				$(this).toggleClass('selected');
			});

	// Datepicker
	$('.datepicker').datepicker()

	//$('#home-carousel').carousel({interval: 5000});

});

//function to append a new theme stylesheet with the new style changes
function updateCSS(css) {

	$("head").append(
			'<link rel="stylesheet" type="text/css" href="css/' + css
					+ '.css">');

	if ($("link[href*=metro-ui-]").size() > 1) {
		$("link[href*=metro-ui-]:first").remove();
	}

};
