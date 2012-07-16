/*

>>>>>>> mannix/master
jQuery(document).ready(function($){

	getUserfeed(userId, accessToken);
	
	$('.tabbable a').click(function (e) {
		
		switch ($(this).html()) {
		case 'News Feed': getUserfeed(userId, accessToken); break; 
		default: break;	
		}	
	});	
});


function getUserfeed(userId, accessToken) {
	$.ajax({
		type: "GET",
		url: '/app/user/'+userId+'/feed?access_token='+accessToken,
		dataType: "html",
		cache: false,
		success: function( data ) {
			try{
				var post = jQuery.parseJSON(data);
				if(post.length > 0) {
					$('#userfeed').html('');
					$('#userfeed').append('<ul>')
					for(var i=0; i < post.length; i++) {
						$('#userfeed').append('<li>'+post[i].id+' '+post[i].story+'</li>')
					}
					$('#userfeed').append('</ul>')
				}
			}	
			catch(e){
				return false;
			}
		},	
		error: function( xhr, errorMessage, thrownErro ) {
			alert('error');
			console.log(xhr.statusText, errorMessage);
		}
	});
<<<<<<< HEAD
}
=======
}

*/



