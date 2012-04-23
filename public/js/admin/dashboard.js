jQuery(document).ready(function($){

	$(document).delegate('.activate', 'click', function(event) {
		//approve the current selected page for collection
		var btn = this;
		$.ajax({
			'url': '/api/fanpages/' + $(this).closest('tr').attr('id'),
			'type': 'ACTIVATE',
			'success': function(xhr) {
				$(btn).attr('class', 'btn btn-danger deactivate').html('Deactivate');
			}
		});
	});

	$(document).delegate('.deactivate', 'click', function(event) {
		//approve the current selected page for collection
		var btn = this;
		$.ajax({
			'url': '/api/fanpages/' + $(this).closest('tr').attr('id'),
			'type': 'DEACTIVATE',
			'success': function(xhr) {
				$(btn).attr('class', 'btn btn-success activate').html('Activate');
			}
		});
	});

});