$(document).ready(
	function() {
	// trick to indentify parent container
		if (window.location != window.parent.location) {
			$(document.body).css({
				'overflow' : 'hidden'
			});
		} else {
			$(document.body).css({
				'overflow' : 'auto'
			});
		}	

		FB.api(fanpageId, function(response) {
			if (!response || response.error) {
			} else {
				var x = 0;
				var y;
				try {
					y = response.cover.offset_y;
				} catch (err) {
					y = 0;
				}

				if (y > 35) {
					x = -1 * (parseInt(y) + 50);
				} else {
					x = 0;
				}

				try {
					$('#logo').html(
						'<img src =" ' + response.cover.source
							+ '"style=" top:' + x + 'px" />');
				} catch (err) {
			}
		}

	});

});