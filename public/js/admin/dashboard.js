jQuery(document).ready(function($){

	$(document).delegate('.activate', 'click', function(event) {
		//approve the current selected page for collection
		$.ajax({
			'url': '/api/fanpages/' + $(this).attr('data-id'),
			'type': 'ACTIVATE',
			'success': function(xhr) {
				//update the button class and html
				$(this).attr('class', 'btn btn-danger deactivate').html('Deactivate');
				$(this).attr('data-original-title', 'Turn off data collection for this page');
				$('.tooltip-inner').html('Turn off data collection for this page');

				//add the install button
				$(this).closest('tr').find('.app').append('<button class="app btn btn-success" rel="tooltip" title="See fancrank working with your data." data-id="' + $(this).attr('data-id') + '">App</button>');
			}.bind(this)
		});
	});

	$(document).delegate('.deactivate', 'click', function(event) {
		//approve the current selected page for collection
		$.ajax({
			'url': '/api/fanpages/' + $(this).attr('data-id'),
			'type': 'DEACTIVATE',
			'success': function(xhr) {
				//update the button class and html
				$(this).attr('class', 'btn btn-success activate').html('Activate');
				$(this).attr('data-original-title', 'Turn on data collection for this page');
				$('.tooltip-inner').html('Turn on data collection for this page');

				//remove the install button
				$(this).closest('tr').find('.app').find('button').remove();
			}.bind(this)
		});
	});

});