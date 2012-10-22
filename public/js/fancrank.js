
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
	
	$('a[title]').tooltip({
		    placement: 'left'
	});
		  
	var tourOptions = {
		welcomeMessage : '<h2>Tour</h2><p>Welcome to FanCrank Admin Page</p>',
		data : [
		        { element: '#dummyName', 'tooltip' : 'This is current target page' },
		        { element: '#dummyStatus', 'tooltip' : 'This is page current status' },
		        { element: '#dummyTab', 'tooltip' : 'Clicking this will install the app to the fanpage tab' },
		        { element: '#dummyPreview', 'tooltip' : 'Clicking this will link to a preview page' },
		        { element: '#dummyDashboard', 'tooltip' : 'Clicking this will link to a Analytic page' }
		       ],
		controlsPosition : 'TR'
	};

	$('#fancrankTour').click(function(e){
		$('#dummyPageRow').show();	
		$.fancrankTour(tourOptions);
	});
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
        //becuase jquery can't create f***ing elements without downloading a plugin!
        var id = new Date().getTime();
        var element = '<div id="' + id + '" class="alert alert-' + type + ' fade in"><a class="close" data-dismiss="alert" href="#">×</a><strong>' + message + '</strong>.</div>';
        $(document.body).prepend(element);
        $('#' + id).slideDown().delay(5000).slideUp(700, function(){
            $('#' + id).remove();
        });
    }

}

function linkify(inputText) {
    // http://, https://, ftp://
    var urlPattern = /\b(?:https?|ftp):\/\/[a-z0-9-+&@#\/%?=~_|!:,.;]*[a-z0-9-+&@#\/%=~_|]/gim;

    // www. sans http:// or https://
    var pseudoUrlPattern = /(^|[^\/])(www\.[\S]+(\b|$))/gim;

    // Email addresses *** here I've changed the expression ***
    var emailAddressPattern = /(([a-zA-Z0-9_\-\.]+)@[a-zA-Z_]+?(?:\.[a-zA-Z]{2,6}))+/gim;

    return inputText
        .replace(urlPattern, '<a target="_blank" href="$&">$&</a>')
        .replace(pseudoUrlPattern, '$1<a target="_blank" href="http://$2">$2</a>')
        .replace(emailAddressPattern, '<a target="_blank" href="mailto:$1">$1</a>');
}