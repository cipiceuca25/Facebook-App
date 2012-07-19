jQuery(document).ready(function($){

	$('.fancrank_login').click(function(event){
		var url = '/auth/facebook/authorize/' + event.target.href.split('login/')[1].replace(/\/.*/, '');
		event.preventDefault();
		confirmFunction(url);
	});	
});

function confirmFunction(url) {
	//alert($('#fancrank_fanpage_id').val());
	//alert(getFanpageId()); return;
	$.oauthpopup({
        path: url,
        callback: function(){
        	//alert(window.location.href);
        	window.location.reload();
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
