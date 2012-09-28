jQuery(document).ready(function($){

	setInterval(isLoginNotification, 15000);
	
	$('.notification').live('mouseover', function(){
		$(this).stop();
	});
});

function isLoginNotification() {
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/user/' +userId +'/notification',
		dataType : "json",
		cache : false,
		async : false,
		beforeSend: function(){
		},
		success : function(data) {
			console.log(data['message']);
			if(data['message'] == "ok") {
				$('#badge-notification-count').html(data['notification']['newBadgeCount']);
				$('.notification').effect("shake", { times:3 }, 300);
			}
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}