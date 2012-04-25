jQuery(document).ready(function($){

	$('.login').click(function(event){
		window.open('/auth/facebook/authorize/' + $(event.target).attr('data-id'), 'auth', null, true);
	});

});