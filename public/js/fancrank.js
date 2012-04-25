jQuery(document).ready(function($){

	//set up ajax call
	$.ajaxSetup({	
		'accepts': 'application/json',
		'contentType': 'application/x-www-form-urlencoded',
		'error': function(xhr, status, error) {
			new Alert.create('error', xhr.responseText);
		}
	});

	//responsive menu
	$(".collapse").collapse();
	//tooltip class
	$('[rel=tooltip]').tooltip()

});

var Alert = new function() {

    this.create = function(type, message) {
        //becuase jquery can't create f***ing elements without downloading a plugin!
        var id = new Date().getTime();
        var element = '<div id="' + id + '" class="alert alert-' + type + ' fade in"><a class="close" data-dismiss="alert" href="#">×</a><strong>' + message + '</strong>.</div>';
        $(document.body).prepend(element);
        $('#' + id).slideDown().delay(5000).slideUp(700, function(){
            $('#' + id).remove();
        });
    }
}