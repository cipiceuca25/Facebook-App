jQuery(document).ready(function($){

	$('.activate').click(function(event){
		//approve the current selected page for collection
		$.ajax({
			'url': '/api/fanpage/' + $(this).closest('tr').attr('id'),
			'type': 'APPROVE'
		});
	});

});