
jQuery(document).ready(function($){
	color1 = $('.toolbar li').css("color");
	color2 = $('.toolbar li').css("background-color");
	//alert(color1 + color2);
	
	setInterval(isLoginNotification, 15000);
	
	$('.notification').live('click', function(){
		$('.notification').css('background-color',color2);
		$('.notification a').css('color',color1);
		$('.notification').css('opacity','1');
		$('.notification a').attr('data-original-title','You have no new Notifications');
		$(this).stop();
	});
});

function isLoginNotification() {
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/user/' +userId +'/notification',
		dataType : "json",
		cache : false,
		async : true,
		beforeSend: function(){
		},
		success : function(data) {
			console.log(data['message']);
			if(data['message'] == "ok") {
				$('#badge-notification-count').html(data['notification']['newBadgeCount']);
				
				$('.notification a').attr('data-original-title','You have ' + data['notification']['newBadgeCount'] + ' new Notifications');
				$('.notification').css('background-color','#56A556');
				$('.notification a').css('color',color2);
				$('.notification').effect("pulsate", { times:3 }, 300);
				$('.notification').effect("bounce", { times:3 }, 300);
				//$('.notification').animate({'background-color':color2 , 'color': color1});
			}
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}