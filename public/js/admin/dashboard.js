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
				$(this).closest('tr').find('.app').append('<button class="app btn btn-success" rel="tooltip" title="See fancrank working with your data." data-id="' + $(this).attr('data-id') + '">Preview</button>');
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

	$(document).delegate('.app', 'click', function(event) {
		//preview the app
		id = $(event.target).attr('data-id');
		//window.open('/dashboard/preview?id=' + id, 'preview', null, true);
		window.location = '/dashboard/preview?id=' + id;
	})

	$(document).delegate('.install', 'click', function(event){
		//show the install screen in the iframe
		$.ajax({
			'url': '/api/fanpages/' + $(this).attr('data-id'),
			'type': 'INSTALL',
			'success': function(xhr) {	
				/*if (window.opener != null) {
		            //window.opener.location.reload();
		            window.opener.installSuccess();
				    self.close($(this).attr('data-id'));
		        }*/
		        installSuccess($(this).attr('data-id'));
			}.bind(this)
		});
		
	});

});

function installSuccess(page)
{
	new Alert.create('success', 'Page tab installed successfully!');
}