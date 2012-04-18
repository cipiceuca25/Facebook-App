jQuery(document).ready(function($){

	$('.login').click(function(event){
		window.open('/auth/facebook/login', 'auth', null, true);
	});

});