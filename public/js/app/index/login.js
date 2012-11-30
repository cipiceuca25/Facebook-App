jQuery(document).ready(function($){
	$('.fancrank_login').click(function(event){
		event.preventDefault();

		//confirmFunction();
		if(newuser) {
			$(".profile-content").html("<div class='rvrow'><div class='rvgrid-11'><div class='box'><div class='user'>" +
					"					<div class='policy_message'>"	+		
					"							Policy Message" +
					"					</div>" +
					"					</div>" +
					"					 " +
					"						<div class='post-container'>" +
					"							<div class='post' >" +
					"							Hello, You're about to use <span class='bold'>FanCrank</span>. <br/>" +
					"							We're going to be needing some of your information:<br/><br/>" +
					"							" +
					"								<span class='bold'>Your Basic Information,<br/> Birthday,<br/> Hometown,<br/> Locale,<br/>" +
					"								Your Friends List <br/></span><br/>" +
					"								Also, we're going to be posting on your behalf, don't worry though its only on this Fanpage; we won't post on your feed<br/>" +
					"							 "+
					"							</div> <div style='text-align:center; margin-top:5px; margin-bottom:5px;'><button  class='btn fc-Follow' style='width:200px; height:auto;' onclick=confirmFunction()>Login To FanCrank</button></div>" +
					"						</div> " +
					"					</div></div></div>" +
					""
					);
			popup();
			
			
			
			/*
			var message = 'Fancrank will access the following information: ' +
							'<ul><li>Basic Information</li><li>Friend Lists</li><li>Post on your behalf</li></ul>';
			var title = 'Policy Message';
			var buttonLabel = '<input class="btn" type="button" name="login" value="continue" id="continue" onClick="FB.Dialog.remove(this);confirmFunction();"><input class="btn" type="button" name="disagree" value="cancel" id="cancel" onClick="FB.Dialog.remove(this);">';
			var dialog = FB.Dialog.create({
				content: '<div id="window_container"><div id="title_bar">' +title+ '</div><p id="message">' + message + '</p><div id="bottom_bar">' +buttonLabel+ '</div></div>',
				closeIcon: true,
				onClose: function() {
					FB.Dialog.remove(this);
				},
				visible: true
			});
			dialog.style.width='250px';
			dialog.style.height='150px';*/
		}else {
			checkLoginUser();
			//confirmFunction();
		}
	});	
});

function confirmFunction() {
	var url = '/auth/facebook/authorize/' + fanpageId;
	//alert($('#fancrank_fanpage_id').val());
	//alert(getFanpageId()); return;
	// check isLogin
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


function popup(){
	$('.light-box').css('display', 'block');
	$('.user-profile').css('display', 'block');
	$('.profile-content').css('height', 'auto');
	
	$('.user-profile').css('top', 200);
	
	FB.Canvas.getPageInfo(function(info) {
		$('.user-profile').css('top', info.scrollTop + 200);
	});

		$('.profile-content').animate({
			height : 'toggle',
		//top:'20px'
		}, 10, function() {
	
		});
}

function checkLoginUser() {
	//alert('getting top postsss');
	$.ajax({
		type : "GET",
		url : serverUrl + '/auth/facebook/islogin/' + fanpageId,
		dataType : "json",
		cache : false,
		async : false,
		success : function(data) {
			console.log(data.isLogin == 1);
			if (data.isLogin == 1) {
				window.location.href = '/app/app/index/'+fanpageId;
				//top.location.href='https://facebook.com/' +fanpageId+'?sk=app_359633657407080';
				//console.log(data.isLogin);
			} else {
				confirmFunction();
			}	
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}