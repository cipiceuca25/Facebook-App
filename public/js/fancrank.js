jQuery(document).ready(function($){

	//set up ajax call
	$.ajaxSetup({	
		'contentType': 'application/x-www-form-urlencoded',
		'dataType': 'json',
		'error': function(xhr, status, error) {
			new Alert.create('error', xhr.responseText);
		}
	});

	//responsive menu
	$(".collapse").collapse();
	//tooltip class
	$('[rel=tooltip]').tooltip();

});

$.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

var Alert = new function() {

    this.create = function(type, message) {
        var id = new Date().getTime();
        var element = '<div id="' + id + '" class="alert alert-' + type + ' fade in"><a class="close" data-dismiss="alert" href="#">×</a><strong>' + message + '</strong>.</div>';
        $(document.body).prepend(element);
        $('#' + id).slideDown().delay(5000).slideUp(700, function(){
            $('#' + id).remove();
        });
    }
}