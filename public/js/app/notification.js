jQuery(document).ready(function($){

	//setInterval(isLoginNotification, 60000);
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
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}