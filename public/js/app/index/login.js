jQuery(document).ready(function($){
	$('.fancrank_login').click(function(event){
		event.preventDefault();
		//confirmFunction();

		var message = 'Authorization Policy: blablabla';
		var title = 'Policy Message';
		var buttonLabel = '<input class="btn" type="button" name="login" value="agree" id="agree" onClick="FB.Dialog.remove(this);confirmFunction();"><input class="btn" type="button" name="disagree" value="Disagree" id="disagree" onClick="FB.Dialog.remove(this);">';
		var dialog = FB.Dialog.create({
			content: '<div id="window_container"><div id="title_bar">' +title+ '</div><p id="message">' + message + '</p><div id="bottom_bar">' +buttonLabel+ '</div></div>',
			closeIcon: true,
			onClose: function() {
				FB.Dialog.remove(this);
			},
			visible: true
		});
		dialog.style.width='250px';
		dialog.style.height='150px';

	});	
});

function confirmFunction() {
	var url = '/auth/facebook/authorize/' + fanpageId;
	//alert($('#fancrank_fanpage_id').val());
	//alert(getFanpageId()); return;
	$.oauthpopup({
        path: url,
        callback: function(){
        	//alert(window.location.href);
        	window.location.href = '/app/app/index/'+fanpageId;
        }
    });
};

(function($){
    //  inspired by DISQUS
    $.oauthpopup = function(options)
    {
        if (!options || !options.path) {
            throw new Error("options.path must not be empty");
        }
        $.extend({
            windowName: 'ConnectWithOAuth' // should not include space for IE
          , windowOptions: 'location=0,status=0,width=250,height=100'
          , callback: function(){ window.location.reload(); }
        }, options);

        var oauthWindow   = window.open(options.path, options.windowName, options.windowOptions);
        var oauthInterval = window.setInterval(function(){
            if (oauthWindow.closed) {
                window.clearInterval(oauthInterval);
                options.callback();
            }
        }, 1000);
    };
})(jQuery);
