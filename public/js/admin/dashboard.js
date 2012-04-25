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

				addPreviewButton(this, $(this).attr('data-id'));
				addInstallButton(this, $(this).attr('data-id'));
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
				$(this).closest('tr').find('.preview').find('button').remove();
				$(this).closest('tr').find('.tab').find('button').remove();
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

	$(document).delegate('.install-tab', 'click', function(event){
		//show the install screen in the iframe
		$.ajax({
			'url': '/api/fanpages/' + $(this).attr('data-id'),
			'type': 'INSTALL',
			'success': function(xhr) {	
		        installSuccess($(this).attr('data-id'));

		        $(this).attr('class', 'btn btn-danger delete-tab').html('Delete Tab');
				$(this).attr('data-original-title', 'Delete the fancrank app on your page');
				$('.tooltip-inner').html('Delete the fancrank app on your page');
			}.bind(this)
		});
		
	});

	$(document).delegate('.delete-tab', 'click', function(event){
		//show the install screen in the iframe
		$.ajax({
			'url': '/api/fanpages/' + $(this).attr('data-id'),
			'type': 'UNINSTALL',
			'success': function(xhr) {	
		        deleteSuccess($(this).attr('data-id'));

		        $(this).attr('class', 'btn btn-success install-tab').html('Install Tab');
				$(this).attr('data-original-title', 'Install the fancrank app on your page');
				$('.tooltip-inner').html('Install the fancrank app on your page');
			}.bind(this)
		});
		
	});

});

function addPreviewButton(btn, id) 
{
	$(btn).closest('tr').find('.preview').append('<button class="app btn btn-success" rel="tooltip" title="See fancrank working with your data" data-id="' + id + '">Preview</button>');
}

function addInstallButton(btn, id)
{
	$(btn).closest('tr').find('.tab').append('<button class="install-tab btn btn-success" rel="tooltip" title="Install the fancrank app on your page" data-id="' + id + '">Install Tab</button>');
}

function installSuccess(page)
{
	new Alert.create('success', 'Page tab installed successfully!');
}

function deleteSuccess(page)
{
	new Alert.create('success', 'Page tab removed successfully!');
}