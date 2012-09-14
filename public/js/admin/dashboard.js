jQuery(document).ready(function($){

	$(document).delegate('.activate', 'click', function(event) {
		//approve the current selected page for collection
		$.ajax({
			'url': '/api/fanpages/' + $(this).attr('data-id'),
			'type': 'ACTIVATE',
			beforeSend: function() {
				//$(this).css('background-image', "url(/img/loading.gif)");
	    	},
			'error': function(xhr) {
				$('.tooltip-inner').html(xhr.responseText);
			},
			'success': function(xhr) {
				$(this).attr('class', 'btn btn-danger deactivate').html('Deactivate');
				$(this).attr('data-original-title', 'Turn off data collection for this page');
				$('.tooltip-inner').html('Turn off data collection for this page');

				addPreviewButton(this, $(this).attr('data-id'));
				addInstallButton(this, $(this).attr('data-id'));
				addAnalyticButton(this, $(this).attr('data-id'));
				addUpgradeMenu(this, $(this).attr('data-id'), $(this).attr('data-level'));
			}.bind(this)
		});
	});

	$(document).delegate('.deactivate', 'click', function(event) {
		//approve the current selected page for collection
		$.ajax({
			'url': '/api/fanpages/' + $(this).attr('data-id'),
			'type': 'DEACTIVATE',
			'error': function(xhr) {
				//errors handle here
				$('.tooltip-inner').html(xhr.responseText);
				//alert(xhr.responseText);
			},
			beforeSend: function() {
			},			
			'success': function(xhr) {
				//update the button class and html
				$(this).attr('class', 'btn btn-success activate').html('Activate');
				$(this).attr('data-original-title', 'Turn on data collection for this page');
				$('.tooltip-inner').html('Turn on data collection for this page');

				//remove the install button
				$(this).closest('tr').find('.preview').find('button').remove();
				$(this).closest('tr').find('.analytic').find('button').remove();
				$(this).closest('tr').find('.upgrade').find('.btn-group').remove();
				$(this).closest('tr').find('.tab').find('button').remove();
			}.bind(this)
		});
	});

	$(document).delegate('.app', 'click', function(event) {
		//preview the app
		id = $(event.target).attr('data-id');
		//window.open('/dashboard/preview?id=' + id, 'preview', null, true);
		window.location = '/admin/dashboard/preview?id=' + id;
	})

	$(document).delegate('.app-analytic', 'click', function(event) {
		//preview the app
		id = $(event.target).attr('data-id');
		//window.open('/dashboard/preview?id=' + id, 'preview', null, true);
		window.location = '/admin/dashboard/analytic?id=' + id;
	})
	
	$(document).delegate('.install', 'click', function(event){
		//show the install screen in the iframe
		$.ajax({
			'url': '/api/fanpages/' + $(this).attr('data-id'),
			'type': 'INSTALL',
			'success': function(xhr) {	
				$(this).remove();
				$('h1').html('This app is now installed');
		        installSuccess($(this).attr('data-id'));
			}.bind(this)
		});
		
	});

	$(document).delegate('.install-tab:not(.disabled)', 'click', function(event){
		//show the install screen in the iframe
		$.ajax({
			'url': '/api/fanpages/' + $(this).attr('data-id'),
			'type': 'INSTALL',
			'success': function(xhr) {	
		        installSuccess($(this).attr('data-id'));

		        $(this).addClass('disabled');
				$('.tooltip-inner').html('Fancrank is already installed');
			}.bind(this)
		});
		
	});
	
	/*$(document).delegate('.delete-tab', 'click', function(event){
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
		
	});*/

	$('form[name=account]').submit(function(event) {
		event.preventDefault();

		$.ajax({
			'url': $(this).attr('method'),
			'type': 'UPDATE',
			'data': $(this).serializeObject(),
			'success': function(xhr) {	
				new Alert.create('success', 'Updated user account successfully!');		
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

function addAnalyticButton(btn, id)
{
	$(btn).closest('tr').find('.analytic').append('<button class="app-analytic btn btn-analytic" rel="tooltip" title="See your fanpage statistics" data-id="' + id + '">DashBoard</button>');
}

function addUpgradeMenu(btn, id, level)
{
	var text = ['Free', 'Premium', 'Enterprise'];
	console.log(level);
	console.log(text[level]);

	$(btn).closest('tr').find('.upgrade').append(
		'<div class="btn-group">'+
			'<ul class="nav nav-pills">'+
				'<li class="dropdown"><a class="dropdown-toggle"'+
					'data-toggle="dropdown" href="#menu2"><span id="'+id+'_level_text">'+text[level-1]+'</span> <b class="caret"></b></a>'+
					'<ul class="dropdown-menu level-dropdown">'+
						'<li><a href="#" data-id="'+id+'" data-level=1>Free</a></li>'+
						'<li><a href="#" data-id="'+id+'" data-level=2>Premium</a></li>'+
						'<li><a href="#" data-id="'+id+'" data-level=3>Enterprise</a></li>'+
					'</ul></li>'+
		'</ul></div>');
}