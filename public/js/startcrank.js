var ffb = true;
var ttb = true;
var tcb = true;
var tfdb = true;


$(document).ready(function() {
	
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
			/*
			if ($('#logo').attr('data-login') == "true"){
				
				$('#logo').html(
						'<img src ="/img/test.png" />');
				
			}else{
				if(fanpageId == 216821905014540) {
					$('#logo').html(
					'<img src ="/img/beach.jpg" />');
				
				}else{
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
		
				});}
			}*/

	});

$(document).mousemove(function(e) {
	//$('.popover').css('display', 'none');
	//FB.Canvas.setAutoGrow();
	
	if (fb == false){
		
		FB.init({
			 appId  : appId,
			 status : true, // check login status
			 cookie : true, // enable cookies to allow the server to access the session
			 xfbml  : true// parse XFBML
			 
		});
		fb=true;
		FB.Canvas.setAutoGrow();
	
	}
	
	
});

$(document).on('mouseover', 'a[rel=tooltip]', function() {
	if ($(this).data('isTooltipLoaded') == true) {
		return;
	}
	$(this).data('isTooltipLoaded', true).tooltip({placement:'left'}).trigger('mouseover');
});

$('#fan-favorite-btn').live('click', function() {
	if ((ffb) == true) {
		$('#fan-favorite-btn').text('- Close');
	} else {
		$('#fan-favorite-btn').text("+ More");
	}
	ffb = !ffb;
});

$('#top-talker-btn').live('click', function() {
	if ((ttb) == true) {
		$('#top-talker-btn').text('- Close');
	} else {
		$('#top-talker-btn').text("+ More");
	}
	ttb = !ttb;
});

$('#top-clicker-btn').live('click', function() {
	if ((tcb) == true) {
		$('#top-clicker-btn').text('- Close');
	} else {
		$('#top-clicker-btn').text("+ More");
	}
	tcb = !tcb;
});

$('#top-followed-btn').live('click', function() {
	if ((tfdb) == true) {
		$('#top-followed-btn').text('- Close');
	} else {
		$('#top-followed-btn').text("+ More");
	}
	tfdb = !tfdb;
});

function ImgError(source) {
	source.src = "/img/profile-picture.png";
	source.onerror = "";
	return true;
}
function closeProfile() {
	$('.light-box').css('display', 'none');
	$('.user-profile').css('display', 'none');
	$('.profile-content').css('display', 'none');
	$('.profile-content').css('background-color', backgroundcolor);
	$('.profile-content').html('');
	
}