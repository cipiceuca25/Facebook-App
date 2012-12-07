var setFeed = 'start';
var myfeedoffset = 0;

var ffb = true;
var ttb = true;
var tcb = true;
var tfdb = true;
var pointlog =false;
var logout =false;
var upcoming_badges = false;
var currentpage = 'newsfeed';

var backgroundcolor;

$(document).ready(function() {
	setInterval(MouseWheelHandler, 500);
	document.addEventListener("mousewheel", MouseWheelHandler, false);  
    // Firefox  
	document.addEventListener("DOMMouseScroll", MouseWheelHandler, false);  
// trick to indentify parent container

	
	backgroundcolor = $('.profile-content').css('background-color');
	getNewsfeed('#news-feed');
	getUpcomingBadges('#notification_upcoming_badges' ,3);

	/*
	if(images.length > 0){
		setTimeout( function(){;
		getBadgeNotification();
		}, 3000);
	}
*/
});

$(document).mousemove(function(e) {
	
	//$('.popover').css('display', 'none');
	//FB.Canvas.setAutoGrow();
	
	if (fb == false){
		try{
			FB.init({
				 appId  : appId,
				 status : true, // check login status
				 cookie : true, // enable cookies to allow the server to access the session
				 xfbml  : true// parse XFBML
				 
			});
			fb=true;
			FB.Canvas.setAutoGrow();	
		}catch(err){
			console.log(err);
		}
	}
	
	
});
//===========================================================================================

$(document).on('mouseenter', '#badges2-container', function (){
	//console.log('hover on');
	//console.log(this.scrollHeight);
	//console.log(parseInt($(this).css('max-height')));
	if(document.addEventListener  && this.scrollHeight > parseInt($(this).css('max-height'))){ //Firefox only
	    document.addEventListener("DOMMouseScroll", mouseScroll, true);
	    document.addEventListener("mousewheel",  mouseScroll, true);  
	}
});

$(document).on('mouseleave', '#badges2-container', function (){
	//console.log('hover off');

	
	document.removeEventListener("DOMMouseScroll", mouseScroll, true);
	document.removeEventListener("mousewheel", mouseScroll, true);
	
});


$(document).on('mouseenter', '#profile_earned_badges', function (){
	//console.log('hover on');
	//console.log(this.scrollHeight);
	//console.log(parseInt($(this).css('max-height')));
	if(document.addEventListener  && this.scrollHeight > parseInt($(this).css('max-height'))){ //Firefox only
	    document.addEventListener("DOMMouseScroll", mouseScroll, true);
	    document.addEventListener("mousewheel",  mouseScroll, true);  
	}
});

$(document).on('mouseleave', '#profile_earned_badges', function (){
	//console.log('hover off');

	
	document.removeEventListener("DOMMouseScroll", mouseScroll, true);
	document.removeEventListener("mousewheel", mouseScroll, true);
	
});


$(document).on('mouseenter', '#recent_activities', function (){
	//console.log('hover on');
	//console.log(this.scrollHeight);
	//console.log(parseInt($(this).css('max-height')));
	if(document.addEventListener  && this.scrollHeight > parseInt($(this).css('max-height'))){ //Firefox only
	    document.addEventListener("DOMMouseScroll", mouseScroll, true);
	    document.addEventListener("mousewheel",  mouseScroll, true);  
	}
});

$(document).on('mouseleave', '#recent_activities', function (){
	//console.log('hover off');

	
	document.removeEventListener("DOMMouseScroll", mouseScroll, true);
	document.removeEventListener("mousewheel", mouseScroll, true);
	
});

$(document).on('mouseenter', '.comments', function (){
	
	//console.log($(this).css('max-height'));
	//console.log($(this).height());
	if(document.addEventListener && this.scrollHeight > parseInt($(this).css('max-height'))){ //Firefox only
		//console.log('hover on');
	    document.addEventListener("DOMMouseScroll",  mouseScroll, true);
	    document.addEventListener("mousewheel",  mouseScroll, true);  
	}

});

$(document).on('mouseleave', '.comments', function (){
	//console.log('hover off');
	document.removeEventListener("DOMMouseScroll", mouseScroll, true);
	document.removeEventListener("mousewheel", mouseScroll, true);
	
});

$(document).on('mouseenter', '.comments.popup_scroll', function (){
	
	//console.log($(this).css('max-height'));
	if(document.addEventListener && this.scrollHeight > parseInt($(this).css('max-height'))){ //Firefox only
		//console.log('hover on');
	    document.addEventListener("DOMMouseScroll",  mouseScroll, true);
	    document.addEventListener("mousewheel",  mouseScroll, true);  
	}

});

$(document).on('mouseleave', '.comments.popup_scroll', function (){
	//console.log('hover off');
	document.removeEventListener("DOMMouseScroll", mouseScroll, true);
	document.removeEventListener("mousewheel", mouseScroll, true);
	

});


/**
$(document).on('mouseenter', '.comments.popup_scoll', function (){
	console.log('hover on');
	if(window.addEventListener){ //Firefox only
	    window.addEventListener("DOMMouseScroll", function(e){e.preventDefault()}, true);
	    window.addEventListener("mousewheel", function(e){e.preventDefault()}, true);  
	}
	
});
$(document).on('mouseleave', '.comments.popup_scoll', function (){
	console.log('hover off');
	if(window.addEventListener){ //Firefox only
	    window.addEventListener("DOMMouseScroll", function(e){console.log('trying to move');return true}, true);
	    window.addEventListener("mousewheel", function(e){console.log('trying to move');return true}, true);  
	}

});
**/
//===========================================================================================


$(document).on('mouseover', '[rel=popover]', function() {

	popover(this);
	if ($(this).data('isPopoverLoaded') == true) {
		return;
	}
	$(this).data('isPopoverLoaded', true).popover({
		delay : {show:1500, hide:500},
		
	}).trigger('mouseover');
	//console.log('test');
});

function mouseScroll(e){
	//console.log('scroll prevented');
	e.preventDefault();
}

function MouseWheelHandler() {  $("#toppost").height();
    // cross-browser wheel delta  
    // old IE support  
   // alert(e.wheelDelta);
	$('#info-box-container').stop();
	
	x = $("#toppost").height();
    FB.Canvas.getPageInfo(function(info) {
		//alert(info.scrollTop);
    	if (info.scrollTop > 500 + x){
			//$('#menu').css('top',info.scrollTop-28);
    		$('#info-box-container').animate({'top':info.scrollTop-500},150, function(){});
    	
    	}else{
    		//$('#menu').css('top','170px');
    		$('#info-box-container').animate({'top':'0px'},150, function(){});
    
    	}
    });
    
    //var delta = Math.max(-1, Math.min(1, (e.wheelDelta || -e.detail)));  
}


$(document).on('mouseover', '[rel=tooltip]', function() {
	if ($(this).data('isTooltipLoaded') == true) {
		return;
	}
	$(this).data('isTooltipLoaded', true).tooltip({placement:'left' }).trigger('mouseover');
});

$(document).on('mouseover', '[rel=tooltip-follow]', function() {
	if ($(this).data('isTooltipLoaded') == true) {
		return;
	}
	$(this).data('isTooltipLoaded', true).tooltip({
		delay : {show:2000, hide:500},
	}).trigger('mouseover');
});

$(document).on('mouseover', '[rel=tooltip-award]', function() {
	if ($(this).data('isTooltipLoaded') == true) {
		return;
	}
	$(this).data('isTooltipLoaded', true).tooltip({
		'placement' : 'top-left'
	}).trigger('mouseover');

});

$('.fc-Following').live("mouseenter", function() {
	$(this).text('Unfollow');
	//$(this).css('background-color', '#BD362F');
});

$('.fc-Following').live("mouseleave", function() {
	$(this).text('Following');
	//$(this).css('background-color', '#56A556');
});

$('.fc-Follower').live("mouseenter", function() {
	$(this).text('Follow');
	//$(this).css('background-color', '#BD362F');
});
$('.fc-Follower').live("mouseleave", function() {
	$(this).text('Follower');
	//$(this).css('background-color', '#56A556');
});


$('#post_box').live('keyup', function(){
	//console.log('works');
	growTextbox(this);
});
$('.comment-box').live('keyup', function(){
	//console.log('works');
	growTextbox2(this);
});

$('#fan-favorite-btn').live('click', function() {
	if ((ffb) == true) {
		$('#fan-favorite-btn').text('- Close');
	} else {
		$('#fan-favorite-btn').text("+ More");
	}
	ffb = !ffb;
});

$('#top-talker-btn').live('click', function() {
	if ((ttb) == true) {
		$('#top-talker-btn').text('- Close');
	} else {
		$('#top-talker-btn').text("+ More");
	}
	ttb = !ttb;
});

$('#top-clicker-btn').live('click', function() {
	if ((tcb) == true) {
		$('#top-clicker-btn').text('- Close');
	} else {
		$('#top-clicker-btn').text("+ More");
	}
	tcb = !tcb;
});

$('#top-followed-btn').live('click', function() {
	if ((tfdb) == true) {
		$('#top-followed-btn').text('- Close');
	} else {
		$('#top-followed-btn').text("+ More");
	}
	tfdb = !tfdb;
});




$('#newsfeed-tab').live('click', function() {
	if (currentpage != 'newsfeed'){
		feedLimit = 0;
		getNewsfeed('#news-feed');
	
		$('#leaderboard').html('');
		$('#profile').html('');
		//$('#achievements').html('');
		$('#redeem').html('');
		currentpage = 'newsfeed';
	}
	
});

$('#leaderboard-tab').live('click', function() {
	if (currentpage != 'leaderboard'){
		ffb = true;
		ttb = true;
		tcb = true;
		tfdb = true;
		getLeaderboard();
	
		$('#profile').html('');
		//$('#achievements').html('');
		$('#news-feed').html('');
		$('#redeem').html('');
		//$('.bubble').html('');
		FB.Canvas.setSize({
			width : 810,
			height : 600
		});
		currentpage = 'leaderboard';
	}

});

$('#profile-tab').live('click', function() {
	if (currentpage != 'profile'){
		getMyProfile();
		$('#leaderboard').html('');
		//$('#achievements').html('');
		$('#news-feed').html('');
		$('#redeem').html('');
		FB.Canvas.setSize({
			width : 810,
			height : 400
		});
		currentpage = 'profile';
	}
});


$('#redeem-tab').live('click', function() {
	if (currentpage != 'redeem'){
		getRedeem();
		$('#leaderboard').html('');
		$('#profile').html('');
		$('#news-feed').html('');
		//$('#achievements').html('');
		FB.Canvas.setSize({
			width : 810,
			height : 600
		});
		currentpage = 'redeem';
	}

});

function popover(x) {
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/popoverprofile/' + fanpageId
				+ '?facebook_user_id=' + $(x).attr('data-userid'),
		dataType : "html",
		cache : false,
		async : true,
	
		success : function(data) {
			$(x).attr('data-content', data);
			//$(x).popover('show');
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}

function choosebadges(){
	popup(true);
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/choosebadges/' + fanpageId,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$('.profile-content').html("<div class='rvrow'><div class=' rvgrid-11 '><div class=' box '><div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div></div></div></div>");
		},
		success : function(data) {
			$('.profile-content').html(data);
			//$(x).popover('show');
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}


function feedbackAnimation(ui, type) {

	switch(type){
	
	case 'like':
		$(ui).before('<div class = "like-animation" style="width:60px; left:-7px; z-index:100;"></div>')
		setTimeout(function(){
			$('.like-animation').remove();
		}, 2000);
		break;
	case 'unlike':
		$(ui).before('<div class = "unlike-animation" style="width:75px; left:-11px; z-index:100;"></div>')
		setTimeout(function(){
			$('.unlike-animation').remove();
		}, 2000);
		break;
	case 'follow':
		$(ui).before('<div class = "follow-animation" style=" left:-20px; width:86px; z-index:100;"></div>')
		setTimeout(function(){
			$('.follow-animation').remove();
		}, 2000);
		break;
	case 'unfollow':
		$(ui).before('<div class = "unfollow-animation" style="left:-38px; width:102px;z-index:100;"></div>')
		setTimeout(function(){
			$('.unfollow-animation').remove();
		}, 2000);
		break;
	}	
}


function closeProfile() {
	selected_badges = 0;
	$('.light-box').css('display', 'none');
	$('.user-profile').css('display', 'none');
	$('.profile-content').css('display', 'none');
	$('.profile-content').css('background-color', backgroundcolor);
	$('.profile-content').html('');
	//document.removeEventListener("DOMMouseScroll", mouseScroll, true);
	//document.removeEventListener("mousewheel", mouseScroll, true);
}


function ImgError(source) {
	source.src = "/img/profile-picture.png";
	source.onerror = "";
	return true;
}


function userProfile(user, load) {
	load = typeof load !== 'undefined' ? load : true;
	popup(load);
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/userprofile/' + fanpageId + '/?target='
				+ user,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$('.profile-content').html("<div class='rvrow'><div class=' rvgrid-11 '><div class=' box '><div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div></div></div></div>");
		},
		success : function(data) {
			if (data.length < 1) {
				window.location.href = '/app/index/index/'+fanpageId+'?user_id='+userId;
			} else {
				$('.profile-content').html(data);
				getRecentActivities('#user_activities', user); 
				changeTime('.profile-content .time');
			}	
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting popup profile');
		}
	});
}

function getUpcomingBadges(ui, limit){
	
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/upcomingbadges/' + fanpageId + '/?limit='
				+ limit + '&notifier=false',
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			
			if (ui == '#profile_upcoming_badges'){
				$(ui).html("<div style='text-align:center;margin-top:-1px; padding: 0 25px 0 ' ><img src='/img/ajax-loader.gif' /></div>");
			}else{
				$(ui).html("<div style='text-align:center;margin-top:-1px; padding: 0 25px 0 ' ><img src='/img/ajax-loader-white.gif' /></div>");
		
			}
		},
		success : function(data) {

			$(ui).html(data);
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting upcoming');
		}
	});
	
	
	
}

function comment_feed_filter(post_id, type, total, toggle) {
	ui = '#post_' + post_id;
	//alert(ui);
	getFeedComment(ui, post_id, type,  total, toggle, false, true,false);
	//$('.social.comment.' + post_id).css('display', 'none');
	//changeTime(ui + ' .time');
}

function comment_feed2_filter(post_id, type, total, toggle) {
	ui = '#postn_' + post_id;
	//alert(ui);
	getFeedComment(ui, post_id, type,total, toggle, true, true,false);
	//$('.social.commentn.' + post_id).css('display', 'none');
	//changeTime(ui + ' .time');
}

function comment_feed3_filter(post_id, type, total, toggle) {
	ui = '#popup_post_' + post_id;
	//alert(ui);
	getFeedComment(ui, post_id, type, total, toggle, false, true,true);
	//$('.social.comment.' + post_id).css('display', 'none');
	//changeTime(ui + ' .time');
}



function comment_feed(post_id, type, total, toggle) {
	ui = '#post_' + post_id;
	//alert(ui);
	console.log(ui);
	getFeedComment(ui, post_id, type, total, toggle, false, false,false);
	//$('.social.comment.' + post_id).css('display', 'none');
	
}

function comment_feed2(post_id, type,  total, toggle) {
	ui = '#postn_' + post_id;
	//alert(ui);
	getFeedComment(ui, post_id, type, total, toggle, true, false,false);
	//$('.social.commentn.' + post_id).css('display', 'none');
	//changeTime(ui + ' .time');
}

function comment_feed3(post_id, type,total, toggle) {
	ui = '#popup_post_' + post_id;
	//alert(ui);
	getFeedComment(ui, post_id, type, total, toggle, false, false,true);
	//$('.social.comment.' + post_id).css('display', 'none');
	//changeTime(ui + ' .time');
}

//ui where is this going 
//post_id the post id 
// what type of post it is required for saving activities?
// limit how many to show
// total = what's the total number of comments
// toggle = pop up ?
// is this on the latest
function getFeedComment(ui, post_id, type, total, toggle, latest, filter, popup) {
	//alert(filter);
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/fancrankfeedcomment/' + fanpageId + 
				'?post_id=' + post_id + 
				'&post_type=' + type + 
			
				'&total=' + total + 
				'&latest=' + latest +
				'&filter=' + filter +
				'&popup=' +popup
				,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$('.comments').css('display','block');
			$(ui).html("<div class='comments' style='text-align:center;'><li class='comment-container'><img src='/img/ajax-loader.gif' /></li></div>");
		},
		success : function(data) {
			$(ui).html(data);
			changeTime(ui + ' .time');
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting comments on feed');
		}
	});
	//alert('are we animating?' + toggle);
}

// ui = where to load the comments
// post_id = 


function popup_post(post_id, load) {
	load = typeof load !== 'undefined' ? load : true;
	popup(load);
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/popuppost/' + fanpageId + '?post_id='
				+ post_id,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$('.profile-content').html("<div class='rvrow'><div class=' rvgrid-11 '><div class=' box '><div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div></div></div></div>");
		},
		success : function(data) {
			$('.profile-content').html(data);

			changeTime('#popup_post .time');
			
			
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting comment popup');
		}
	});
	
}

function getNewsfeed() {
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/newsfeed/' + fanpageId
				+ '?facebook_user_id=' + userId,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$('#news-feed').html("<div style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
		},
		success : function(data) {
			
			if (data.length < 1) {
				window.location.href = '/app/index/index/'+fanpageId+'?user_id='+userId;
			} else {
				$('#news-feed').html(data);
				setFeed = 'start';
				//getTopFan();
				getTopList('top-fan-mini', '#topfan');
				getTopPost();
				getFancrankfeed('myfeed');
				getAllActivities();
				changeTime('.time');
			}
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting news feed');
		}
	});
}

function getTopPost() {
	//alert('getting top postsss');
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/gettoppost/' + fanpageId,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$('#toppost').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
		},
		success : function(data) {

			$('#toppost').html(data);
			changeTime('#toppost .time');

		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting topposts');
		}
	});
}

function like(post_id, post_type, target_id, target_name) {
	
	if(post_type.indexOf('comment') != -1){
		mes = $('.comment-container.'+post_id + ' .user .message').text().substring(0,99);
	}else{
		mes = $('.post-container.'+post_id + ' .post .message').text().substring(0,99);
	}
	
	$
			.ajax({
				type : "GET",
				url : serverUrl + '/app/user/' + userId + '/likes/?post_id='
						+ post_id + '&fanpage_id=' + fanpageId + '&post_type='
						+ post_type + '&target_id='+target_id + '&target_name='+ target_name + '&access_token=' + userAccessToken + '&mes='+mes,
				dataType : "json",
				cache : false,
				async: true,
				success : function(data) {
					;
					//alert("target followed")
					//alert(post_id + 'liked');
					//feedbackAnimation('.like_control_' + post_id, 'like');
					//addActivities('like-' + post_type, post_id, target_id, target_name, mes);
					//console.log(data.fan_point);
					// update fan point on top menu bar
					if (! isNaN(data.fan_point)) {
						$('.my_fan_point').html(numberFormat(data.fan_point));
						$('.my_fan_point_tooltip').attr('data-original-title', 'You have '+data.fan_point+ ' points');
					}
					num = parseInt($('.like_' + post_id).attr('data-like-count')) + 1;
					$('.like_' + post_id).attr('data-like-count', num);
					
					
					if (post_type.indexOf('comment') != -1) {
						
						$('.like_' + post_id).html(num);
						
					} else {
						if (num == 1) {
							$('.like_' + post_id).html('1 person');
							//alert('.social.like.'+post_id);

						} else {
							$('.like_' + post_id).html(
									num + ' people');

						}
					}
					$('.social.like.' + post_id).css('display', 'block');

					$('.like_control_' + post_id).attr(
							'onclick',
							"unlike('" + post_id + "','" + post_type + "','"
									+ target_id + "','" + target_name + "')");
					$('.like_control_' + post_id).html('Unlike');
					$('.like_control_' + post_id).attr('data-original-title', 'You like this');
					
					temp=$('.social.like.'+post_id+ ' a').attr('data-original-title');
	
					if (num == 1){
						$('.social.like.'+post_id+ ' a').attr('data-original-title', target_name +' likes this');
					}else{
						$('.social.like.'+post_id+ ' a').attr('data-original-title', target_name + ', ' + temp);
					}
					
				},
				error : function(xhr, errorMessage, thrownErro) {
					console.log(xhr.statusText, errorMessage);
					console.log('error processing likes');
				}
			});
}

function unlike(post_id, post_type, target_id, target_name) {
	if(post_type.indexOf('comment') != -1){
		mes = $('.comment-container.'+post_id + ' .user .message').text().substring(0,99);
	}else{
		mes = $('.post-container.'+post_id + ' .post .message').text().substring(0,99);
	}
	$.ajax({
				type : "GET",
		
				url : serverUrl + '/app/user/' + userId + '/unlike/?post_id='
						+ post_id + '&fanpage_id=' + fanpageId + '&post_type='
						+ post_type + '&target_id='+target_id + '&target_name='+ target_name + '&mes='+mes + '&access_token=' + userAccessToken,
				
				dataType : "html",
				cache : false,
				success : function(data) {
					console.log(data);
					//alert("target followed")
					//alert(post_id + 'liked');
					//feedbackAnimation('.like_control_' + post_id, 'unlike');
					
					
				
					
					//addActivities('unlike-' + post_type, post_id,target_id, target_name, mes);

					num = parseInt($('.like_' + post_id).attr('data-like-count')) - 1;
					if (num < 1){
						num = 0;
					}
					$('.like_' + post_id).attr('data-like-count', num);
					
					
					if (post_type.indexOf('comment') != -1) {
						
						$('.like_' + post_id).html(num);
						
					} else {
						if (num == 1) {
							$('.like_' + post_id).html('1 person');
							//alert('.social.like.'+post_id);

						} else {
							$('.like_' + post_id).html(
									num + ' people');

						}
					}
					
					$('.like_control_' + post_id).attr(
							'onclick',
							"like('" + post_id + "','" + post_type + "','"
									+ target_id + "','" + target_name + "')");
					$('.like_control_' + post_id).html('Like');
					$('.like_control_' + post_id).attr('data-original-title', 'Click to like this');
					
					
					temp=$('.social.like.'+post_id+ ' a').attr('data-original-title');
					
					
					if (num > 0){
						temp = temp.replace(target_name+',', "");
						temp = temp.replace(', '+target_name, "");
						$('.social.like.'+post_id+ ' a').attr('data-original-title',temp);
					} else {
						$('.social.like.'+post_id+ ' a').attr('data-original-title', 'No one likes this yet');
					}
					
				},
				error : function(xhr, errorMessage, thrownErro) {
					console.log(xhr.statusText, errorMessage);
					console.log('error processing unlikes');
				}
			});
}

function getFancrankfeed(view) {
	ui = '#fancrankfeed';
	
	//alert(serverUrl + '/app/app/fancrankfeed/' + fanpageId + '?viewAs='+ view + '&limit=' + feedLimit);
	//alert(setFeed);
	//alert(feedLimit);
	//if (view != 'myfeed'){
	//	myfeedoffset = 0;
		
	//}
	if(setFeed == view){
		last = parseInt($('#last_post_time').attr('data-time')) - 1;
		
	//}else if (view == 'myfeed'){
		
		//last = myfeedoffset;
		//myfeedoffset +=10;
	}else{
		last = undefined;
	}

	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/fancrankfeed/' + fanpageId + '?viewAs='
				+ view + '&until=' + last ,
		dataType : "html",
		cache : false,
		async :  true,
		beforeSend: function(){
			
			
			if((setFeed != view)){
				$('#fancrankfeed').html("<div id='loader' style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' style='margin-top:20px'/></div>");
			}else{
				$('#more_post').remove();
				$('#fancrankfeed').append("<div id='loader' style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' style='margin-top:20px'/></div>");
			}
			
		},
		success : function(data) { 
			//alert(data);
			$('#loader').remove();
			$('#last_post_time').remove();
			$('#more_post').remove();
			//alert(view + ' ' + setFeed);
			if((setFeed != view)){
				//alert(last);
				$(ui).html(data);
			}else{
				$(ui).append(data);
				
			}
			if(view == 'post'){
				view = 'all';	
			};
			setFeed = view;
			changeTime('#fancrankfeed .time');
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting the feed');
		}
	});
	
	
	
	$('#all-title').attr('style', 'font-weight:normal');
	$('#myfeed-title').attr('style', 'font-weight:normal');
	$('#pagepost-title').attr('style', 'font-weight:normal');
	
	switch(view){
		case 'all':
			$('#all-title').attr('style', 'font-weight:bold');
		break;
		case 'myfeed':
			$('#myfeed-title').attr('style', 'font-weight:bold');
		break;	
		case 'admin':
			$('#pagepost-title').attr('style', 'font-weight:bold');
		break;	
		default:
			$('#all-title').attr('style', 'font-weight:bold');
			break;
	}
	
	
	
}

function getTopFan() {

	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/topfan/' + fanpageId + '?facebook_user_id='
				+ userId,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$('#topfan').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
		},
		success : function(data) {
			$('#topfan').html(data);
			//getLatestPost();
		},
		error : function(xhr, errorMessage, thrownErro) {
			alert(url);
			console.log(xhr.statusText, errorMessage);
			console.log('error getting the topfan board');
		}
	});
}

function getLeaderboard() {

	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/leaderboard/' + fanpageId
				+ '?facebook_user_id=' + userId,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$('#leaderboard').html("<div style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
		},
		success : function(data) {
			if (data.length < 1) {
				window.location.href = '/app/index/index/'+fanpageId+'?user_id='+userId;
			} else {
				$('#leaderboard').html(data);
				getTopList('top-fan', '.top-fan.box');
				getTopList('fan-favorite', '.fan-favorite.box');
				getTopList('top-talker', '.top-talker.box');
				getTopList('top-clicker','.top-clicker.box');
				getTopList('top-followed','.top-followed.box');
				getTopList('top-fan-all','.top-fan-all.box');
			}
			//getLatestPost();
		},
		error : function(xhr, errorMessage, thrownErro) {
			alert(url);
			console.log(xhr.statusText, errorMessage);
			console.log('error getting the leaderboards');
		}
	});
}

function getTopList(list, ui){
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/toplist/' + fanpageId
				+ '?facebook_user_id=' + userId + "&list=" + list,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$(ui).append("<div class='removal' style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
		},
		success : function(data) {
			$(ui + ' .removal').remove();
			$(ui).append(data);
				
			//getLatestPost();
		},
		error : function(xhr, errorMessage, thrownErro) {
			//alert(url);
			console.log(xhr.statusText, errorMessage);
			console.log('error getting the leaderboards');
		}
	});
	
	
}

function getMyProfile() {
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/myprofile/' + fanpageId
				+ '?facebook_user_id=' + userId,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$('#profile').html("<div style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
		},
		success : function(data) {
			//alert("rdy");
			// if data empty, reload page
			if (data.length < 1) {
				window.location.href = '/app/index/index/'+fanpageId+'?user_id='+userId;
			} else {
				$('#profile').html(data);
				getRecentActivities('#recent_activities',userId);
				getMiniFollowingList(userName, userId);
				getMiniFollowersList(userName, userId);
				getUpcomingBadges('#profile_upcoming_badges',3);
			}
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting user profile');
		}
	});
}

function getRedeem() {
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/redeem/' + fanpageId + '?facebook_user_id='
				+ userId,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$('#redeem').html("<div style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
		},
		success : function(data) {
			if (data.length < 1) {
				window.location.href = '/app/index/index/'+fanpageId+'?user_id='+userId;
			} else {
				$('#redeem').html(data);
			}
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting the redemption page');
		}
	});
}


$('#noti2').live('click', function(event){
	//alert(notifier);
	
	if(!upcoming_badges){
		//console.log('reload/open');
	//if(pointCount + badgeCount > 0 ){
		getupcomingbadges_notifier();
		notifier = false;
		pointlog=false;
		upcoming_badges =true;
		logout = false;
		//$('.notification').css('background-color',color2);
		//$('.notification a').css('color',color1);
		//$('.notification').css('opacity','1');
		//$('.notification a').addClass('noclick');
		//alert(date);
	//}
	}else{
		$('#menu .notifier').remove();
		$('#menu2 .notifier').remove();
		pointlog = false;
		notifier = false;
		upcoming_badges = false;
		logout = false;
	}
	
});

$('#logout-noti').live('click', function(){
	if(!logout){
		getLogout();
		pointlog = false;
		upcoming_badges = false;
		notifier=false;
		logout = true;
	}else{
		$('#menu .notifier').remove();
		$('#menu2 .notifier').remove();
		pointlog = false;
		notifier = false;
		upcoming_badges = false;
		logout = false;
	}
});


$('#pointlog').live('click', function(event){
	//alert(notifier);
	
	if(!pointlog){
		//console.log('reload/open');
	//if(pointCount + badgeCount > 0 ){
		getpointlog();
		notifier = false;
		upcoming_badges = false;
		logout = false;
		pointlog = true;
		//$('.notification').css('background-color',color2);
		//$('.notification a').css('color',color1);
		//$('.notification').css('opacity','1');
		//$('.notification a').addClass('noclick');
		//alert(date);
	//}
	}else{
		$('#menu .notifier').remove();
		$('#menu2 .notifier').remove();
		pointlog = false;
		notifier = false;
		upcoming_badges = false;
		logout = false;
	}
	
});
function getpointlog(){
	$('.notifier').remove();
	$('#menu').append('<div class="notifier"></div>');
	
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/pointlog/' + fanpageId,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$('.notifier').html("<div class='arrow' style='left:140px'></div><div class='rvgrid-7' style='text-align:center;'><div class='box'><img src='/img/ajax-loader-white.gif' /></div></div>");
		},
		success : function(data) {
			$('.notifier').html(data);
			$('.notifier .arrow').css('left','140px');
			//$(x).popover('show');
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}


function getLogout(){
	$('.notifier').remove();
	$('#menu').append('<div class="notifier"></div>');
	$('#menu2').append('<div class="notifier"></div>');
	$('.notifier').html($('#logout-noti').attr('data-content'));
	$('.notifier .arrow').css('left','262px');
}

function getupcomingbadges_notifier(){
	$('.notifier').remove();
	$('#menu').append('<div class="notifier"></div>');
	$('#menu2').append('<div class="notifier"></div>');
	upcoming_badges= true;
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/upcomingbadges/' + fanpageId + '/?limit=3&notifier=true',
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
	
			$('.notifier').html("<div class='arrow' style='left:190px'></div><div class='rvgrid-7' style= text-align:center;'><div class='box'><img src='/img/ajax-loader-white.gif' /></div></div>");
		},
		success : function(data) {
			$('.notifier').html(data);
			$('.notifier .arrow').css('left','190px');
			upcoming_badges = true;
			//$(x).popover('show');
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}


function getListNotification(){
	$('.notifier').remove();
	$('#menu').append('<div class="notifier"></div>');
	$('#menu2').append('<div class="notifier"></div>');
	$('.notifier').html($('.notification').attr('data-content'));
	changeTime('.notifier .time');
	$('.notifier .arrow').css('left','227px');
	//console.log(points);
	
	//SAVE THE LAST TIME THE NOTIFICATION WAS SEEN
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/user/' + userId	+ '/savelastnotification/'  + '?fanpage=' + fanpageId,
		dataType : "html",
		cache : false,
		async : true,
		success : function(data) {
			console.log(data);
			console.log('saved notification viewed');
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting list notification');
		}
	})

}


function getBadgeNotification(){
	load = typeof load !== 'undefined' ? load : true;
	popup(load);
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/badgesnotification/' + fanpageId,
		dataType : "html",
		cache : false,
		async : false,
		beforeSend: function(){
			$('.profile-content').html("<div style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
			
		},
		success : function(data) {
			$('.profile-content').css('background-color', 'transparent');
			$('.profile-content').html(data);
			play();
		
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting badges notification');
		}
	});
	
}

function getRecentActivities(ui, user_id) {
	x=(ui == '#recent_activities')?true:false;

	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/recentactivities/' + fanpageId + '?userid=' + user_id + '&source=' + x,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$(ui).html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
		
		},
		success : function(data) {
	
			$(ui).html(data);
			changeTime('#recent_activities .time');
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting user recent activities');
		}
	});
}

function getAllActivities() {


	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/allactivities/' + fanpageId ,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$('#allactivities').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
		
		},
		success : function(data) {
	
			$('#allactivities').html(data);
			changeTime('#allactivities .time');
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting user recent activities');
		}
	});
}

function getRelation(target, ui) {
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/user/' + userId
				+ '/relation/?target_id=' + target + '&fanpage_id='
				+ fanpageId,
		dataType : "html",
		cache : false,
		success : function(data) {
			data = $.trim(data);
			$('.' + ui).html(
					'<span class="btn btn-mini fc-' + data + '">' + data
							+ '</span>');
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('there was an error getting the user relation');
		}
	});

}

function follow(target, name) {
	ui = "follow_"+target;
	if (target != userId) {

		$.ajax({
			type : "GET",
			url : serverUrl + '/app/user/' + userId + '/follow/?subscribe_to=' 
					+ target + '&target_name='+ name + '&fanpage_id='
					+ fanpageId + '&subscribe_ref_id=1',
			dataType : "html",
			cache : false,
			success : function(data) {
				//alert("target followed")
				//addActivities('follow', target, target, name, null);
				//getUserProfile('.profile-content', target);
				getRelation(target, ui);
				//alert(relation);
				$('.' + ui).attr('onclick',	"unfollow('" + target + "','" + name + "','" + ui + "')");
				$('.' + ui).attr('data-original-title', 'Click to Unfollow this User');
				//$('.'+ui).html('<span class="badge badge-'+relation+'">'+relation+'</span>');
				//feedbackAnimation('.' + ui, 'follow');
			},
			error : function(xhr, errorMessage, thrownErro) {
				console.log(xhr.statusText, errorMessage);
				console.log('there was an error with following');
			}
		});
	}
}

function unfollow(target, name) {
	ui = "follow_"+target;
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/user/' + userId + '/unfollow/?subscribe_to='
				+ target + '&target_name='+ name + '&fanpage_id='
				+ fanpageId + '&subscribe_ref_id=1',
		dataType : "html",
		cache : false,
		success : function(data) {
			//alert("target unfollowed")
			//addActivities('unfollow', target, target, name, null);
			//getUserProfile('.profile-content', target);
			getRelation(target, ui);

			$('.' + ui).attr('onclick',	"follow('" + target + "','" + name + "','" + ui + "')");
			$('.' + ui).attr('data-original-title', 'Click to Follow this User');
		
			//$('.'+ui).html('<span class="badge badge-'+relation+'">'+relation+'</span>');
			//feedbackAnimation('.'+ui, 'unfollow');

		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('there was an error with unfollow');
		}
	});
}

/*
function addActivities(act_type, event, target_id, target_name, message) {
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/user/' + userId + '/addactivity/?owner_name='
				+ userName + '&activity_type=' + act_type + '&event=' + event
				+ '&fanpage_id=' + fanpageId + '&target_id=' + target_id
				+ '&target_name=' + target_name + '&message=' + message,
		dataType : "html",
		cache : false,
		async: true,
		success : function(data) {
			console.log('activity recorded');
			//alert('adding act');
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('issue with activity save');
		}
	});

}
*/

function post(button) {
	button.disabled = true;
	mes =  $('#post_box').val();
	if (mes == '' || mes == null){
		
	}else{
			//alert('Post ID: ' + response.id)
			$.ajax({
				type : "GET",
				url : serverUrl + '/app/user/' +userId +'/post/?fanpage_id=' + fanpageId + '&access_token=' + userAccessToken + '&fanpage_name='+fanpageName +'&message=' + mes,
				dataType : "html",
				cache : false,
				async : true,
				beforeSend: function(){
					$('.profile-content').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
				},
				success : function(data) {
					$('.profile-content').html(data);
					button.disabled = false;
					$('#post_box').css('height','20px');
					console.log(data);
				},
				error : function(xhr, errorMessage, thrownErro) {
					console.log(xhr.statusText, errorMessage);
				}
			});
			//addActivities('post-status', response.id, fanpageId, fanpage_name,mes.substring(0,99) );
			$('#post_box').val('');
			getFancrankfeed('post');
	}
}


function commentSubmit(button,post_id, post_type, post_owner_id, post_owner_name, isLatestAdminPost){
	
	button.disabled = true;
	
	mes= $('#comment_box_'+post_id).val();
	
	if (mes == '' || mes == null){
		
	}else{
		$.ajax({
			type : "GET",
			url : serverUrl + '/app/user/' +userId +'/comment/?post_id='+post_id + '&post_type='+post_type
							+ '&target_id=' + post_owner_id + '&target_name=' +post_owner_name
							+ '&fanpage_id=' + fanpageId + '&fanpage_name='+fanpageName +'&message=' + mes
							+ '&access_token=' + userAccessToken ,
			dataType : "html",
			cache : false,
			async : true,
			beforeSend: function(){
				
			},
			success : function(data) {
				//addActivities('comment-' + post_type, post_id, post_owner_id, post_owner_name, mes.substring(0,99));
				$('#comment_box_'+post_id).val('');
				
				post_comment_count = parseInt($('#post_'+post_id).attr('data-comment-count')) + 1;
				console.log(post_comment_count);
				//alert(post_comment_count);
				$('.comment_'+post_id).attr('data-comment-count', post_comment_count);
				$('.comment_'+post_id).html(' '+post_comment_count);
				
				if (isLatestAdminPost){
					comment_feed2(post_id, post_type,  post_comment_count, false);
				}else{
					comment_feed(post_id, post_type,  post_comment_count, false);
				}
			},
			error : function(xhr, errorMessage, thrownErro) {
				console.log(xhr.statusText, errorMessage);
			}
		});
	}

	

}

function commentSubmit2(post_id, post_type, post_owner_id, post_owner_name){
	//alert($('#comment_box_popup_'+post_id).val());
	mes= $('#comment_box_'+post_id).val();
	if (mes == '' || mes == null){
		
	}else{
		$.ajax({
			type : "GET",
			url : serverUrl + '/app/user/' +userId +'/comment/?post_id='+post_id + '&post_type='+post_type
							+ '&target_id=' + post_owner_id + '&target_name=' +post_owner_name
							+ '&fanpage_id=' + fanpageId + '&fanpage_name='+fanpageName +'&message=' + mes,
			dataType : "html",
			cache : false,
			async : true,
			beforeSend: function(){
			},
			success : function(data) {
				//addActivities('comment-' + post_type, post_id, post_owner_id, post_owner_name, mes.substring(0,99));
				$('#comment_box_popup_'+post_id).val('');
				
				post_comment_count = parseInt($('#post_'+post_id).attr('data-comment-count')) + 1;
				//alert(post_comment_count);
				$('.comment_'+post_id).html(post_comment_count);
	
				popup_post(post_id, false);
			},
			error : function(xhr, errorMessage, thrownErro) {
				console.log(xhr.statusText, errorMessage);
			}
		});
	}

}


function getLikesList(postid, limit, load) {
	load = typeof load !== 'undefined' ? load : true;
	popup(load);
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/getlikeslist/' + fanpageId + '?post_id='
				+ postid + '&limit=' + limit,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$('.profile-content').html("<div class='rvrow'><div class=' rvgrid-11 '><div class=' box '><div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div></div></div></div>");
		},
		success : function(data) {
			$('.profile-content').html(data);
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('there was an error with getting the likes list');
		}
	});

}

function getFollowingList(targetname, target, limit, load) {
	load = typeof load !== 'undefined' ? load : true;
	popup(load);
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/getfollowing/' + fanpageId + '?limit='
				+ limit + '&target=' + target + '&targetname=' + targetname
				+ '&mini=0',
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$('.profile-content').html("<div class='rvrow'><div class=' rvgrid-11 '><div class=' box '><div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div></div></div></div>");
		
		},
		success : function(data) {
			
			$('.profile-content').html(data);
			
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('there was an error with getting the following list');
		}
	});

}


function getFollowersList(targetname, target, limit, load) {
	load = typeof load !== 'undefined' ? load : true;
	popup(load);
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/getfollowers/' + fanpageId + '?limit='
				+ limit + '&target=' + target + '&targetname=' + targetname
				+ '&mini=0',
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$('.profile-content').html("<div class='rvrow'><div class=' rvgrid-11 '><div class=' box '><div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div></div></div></div>");
		
		},
		success : function(data) {

			$('.profile-content').html(data);
			
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('there was an error with getting the followers list');
		}
	});

}


function popup(load){
	$('.light-box').css('display', 'block');
	$('.user-profile').css('display', 'block');
	$('.profile-content').css('height', 'auto');
	FB.Canvas.getPageInfo(function(info) {
			//alert(info.scrollTop);
			$('.user-profile').css('top', info.scrollTop +100);
	});
	if (load) {
		//document.addEventListener("DOMMouseScroll", mouseScroll, true);
		//document.addEventListener("mousewheel",  mouseScroll, true); 
		$('.profile-content').animate({
			height : 'toggle',
		//top:'20px'
		}, 10, function() {

		});
	}
	 
}



function getMiniFollowingList(targetname, target) {
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/getfollowing/' + fanpageId
						+ '?limit=5' + '&target=' + target + '&targetname='
						+ targetname + '&mini=1',
		dataType : "html",
		cache : false,
		async : true,
		beforeSend:function(){	
			$('#followinglist').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");		
		},
		success : function(data) {
			$('#followinglist').html(data);
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting the mini following list');
		}
	});

}
function getMiniFollowersList(targetname, target) {

	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/getfollowers/' + fanpageId
						+ '?limit=5' + '&target=' + target + '&targetname='
						+ targetname + '&mini=1',
		dataType : "html",
		cache : false,
		async : true,
		beforeSend : function() {
			$('#followerslist').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
		},
		success : function(data) {
			$('#followerslist').html(data);
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting the mini followers list');
		}
	});

}

	
function growTextbox(x){

	var linesCount = 0;
	//console.log(x);
    var lines = x.value.split('\n');

    for (var i=lines.length-1; i>=0; --i)
    {
        linesCount += Math.floor((lines[i].length / 72) + 1);
    }

    if (linesCount > 1)
        x.rows = linesCount + 1;
	else
        x.rows = 1;
    
   // console.log(x.rows);
    x.style.height = (20 * x.rows) + 'px';
}

function growTextbox2(x){
	
	var linesCount = 0;
	//console.log(x);
    var lines = x.value.split('\n');

    for (var i=lines.length-1; i>=0; --i)
    {
        linesCount += Math.floor((lines[i].length / 50) + 1);
    }

    if (linesCount > 1)
        x.rows = linesCount + 1;
	else
        x.rows = 1;
    
   // console.log(x.rows);
    x.style.height = (18 * x.rows) + 'px';
}


function PostBox(){

	if($('#post_box').val() == 'Type in your Post here!'){
		$('#post_box').val('');	
			
	}
	$('.post-button-container').css('display', 'block');
	$('.post-box').css('border-bottom', '0px');
}

function changeTime(ui){
	$.each($(ui), function(index) {
		$(this).html(timeZone($(this).attr('data-unix-time')));
		$(this).attr('data-original-title', timeZoneTitle($(this).attr('data-unix-time')));
	});
}

function timeZoneTitle(time) {

	var date = new Date(time * 1000);
	return date.toLocaleString();
}

function timeZone(time) {

	var date = new Date(time * 1000);
	
	var now = new Date();

	var weekday = new Array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
	var apm = '';

	var hour = date.getHours();
	apm = (hour < 12)? 'am' : 'pm';
	
	if (hour == 0) {
		hour = 12;
	}
	
	if (hour > 12) {
		hour = hour - 12;
	}
	
	var min = date.getMinutes();
	
	if (min < 10) {
		min = '0' + min;
	}
	
	var difference = now.getTime() - date.getTime();
	var daydiff = Math.floor(difference/1000/60/60/24);
	if (daydiff < 4){
		if (daydiff==1){
			return 'Yesterday at ' + hour + ':' + min + '' + apm;
		}else if (daydiff < 1){
			var hourdiff = Math.floor(difference/1000/60/60);
			if (hourdiff < 1){
				var mindiff = Math.floor(difference/1000/60);
				if(mindiff <1){
					var secdiff = Math.floor(difference/1000);
					if (secdiff < 0){
						return 'A few seconds ago';
					}
					return secdiff + ((secdiff==1)? ' second ago' : ' seconds ago');
				}
		
				return mindiff + ((mindiff==1)? ' minute ago' : ' minutes ago');
			}
			return hourdiff + ((hourdiff==1)?' hour ago' : ' hours ago');
		}
		return weekday[date.getDay()] + ' at ' + hour + ':' + min + '' + apm;
	}else{
		return (date.toDateString());
	}
}

function editdescription(){
	msg = $('.user-description').html();
	$('.user-description-button').remove();
	$('.user-description').html("<textarea id='user-description-box'>"+$.trim(msg)+"</textarea><a class='user-description-button' onclick='submit_user_description()'><span class='btn btn-mini fc-edit'>Save Description</span></a>");
	$('.user-description').after('<div id="charcount" style="margin-left:9px; color:#bbb">160 Characters Left</div>');
	charcheck();

}

function submit_user_description(){
	
	msg = $('#user-description-box').val();
	if ((msg).length > 160){
		alert("Your description is too long, please reduce it to 160 characters");	
	}else{
		$('.user-description').html($.trim(msg));
		$('.user-description').after('<a class="user-description-button" onclick="editdescription()"><span class="btn btn-mini fc-edit-2"><img src="/img/icons/edit_icon.png" class="edit_icon" />Edit Description</span></a>');
		$('#charcount').remove();
		$.ajax({
			type : "GET",
			url : serverUrl + '/app/user/' + userId + '/saveuserdescription/' + '?fanpage_id='+ fanpageId+ '&message=' + $.trim(msg),

			dataType : "html",
			cache : false,
			async : true,
			success : function(data) {
				//alert($.trim(data));
			},
			error : function(xhr, errorMessage, thrownErro) {
				console.log(xhr.statusText, errorMessage);
				console.log('error saving user description');
			}
		});
	}
	
}

function charcheck(){
	
	msg = $('#user-description-box').val();
	
	if (msg.length < 161){
		$('#charcount').html(160-msg.length + ' characters left.');
	}else{
		$('#charcount').html('0 characters left.');
		//alert(msg.substring(0,160));
		$('#user-description-box').val(msg.substring(0,160));
	}
	
}

function resetTour(){
	feedLimit = 0;
	getNewsfeed('#news-feed');
	
	$('#leaderboard').html('');
	$('#profile').html('');
	$('#achievements').html('');
	$('#redeem').html('');
	$('.nav.nav-tabs a:first').tab('show');
	$('.light-box').css('display', 'block');
	
	$.fancrankTour(tourOptions);
}

var tourOptions = {
		welcomeMessage : '<h3>Welcome to FanCrank</h3><p>Hi ' + userName + ', <br/> Let\'s learn about using FanCrank. <br/> Click Start to begin</p>',
		data : [
		        //-1
		        { element: 	'#pageTabs', 
		        			'position' : 'T',
		        			'tooltip':'Page Tabs',
		        			'text' : 'These are page tabs, use these to navigate between the pages <br/><br/>'},
		        //0
		        { element: 	'#newsfeed-tab', 
		        			'position' : 'TL',
		        			'tooltip' : 'News Feed', 
		        			'text' : 'These are page tabs, use these to navigate between the pages <br/><br/>' },
    			//1
		        { element: 	'#leaderboard-tab', 
		        			'position' : 'TL',
		        			'tooltip' : 'Leaderboard', 
		        			'text' : 'This tab brings us to the leaderboard, click Next to go there now <br/><br/>' },	
		        
		        //2			
		        { element: 	'#profile-tab', 
			       			'position' : 'TL',
			       			'tooltip' : 'Profile', 
			       			'text' : 'These are page tabs, use these to navigate between the pages <br/><br/>' },	
			    //3
			    { element: 	'#redeem-tab', 
				   			'position' : 'TL',
				   			'tooltip' : 'Redeem', 
				   			'text' : 'These are page tabs, use these to navigate between the pages <br/><br/>' },	
				//4
				{ element: 	'#help-tab', 
				  			'position' : 'TR',
				   			'tooltip' : 'Help', 
				   			'text' : 'These are page tabs, use these to navigate between the pages <br/><br/>' },
				//5
				{ element: 	'#logout-tab', 
					  		'position' : 'TR',
					   		'tooltip' : 'Log Out', 
					   		'text' : 'These are page tabs, use these to navigate between the pages <br/><br/>' },
				//6
				{ element: 	'#newsfeed-tab', 
					  		'position' : 'TL',
					   		'tooltip' : 'News Feed', 
					   		'text' : 'This is the News Feed page, where you can check out recent information about  <br/><br/>'},   			
		   		//7
		   		{ element: 	'#latest-post-container', 
					  		'position' : 'T-Lowered',
					   		'tooltip' : 'Latest Posts', 
					   		'text' : 'Latest Posts will always show the most recent post of the '+fanpageName+' has made.<br/><br/>' }, 
				//8
				{ element: 	'#latest-post-container .post-container', 
						  	'position' : 'T',
						   	'tooltip' : 'Posts', 
						   	'text' : 'Let\'s take a moment to talk about posts. <br/><br/>' },      		
				//9
				{ element: 	'#latest-post-container .post-container .user', 
							'position' : 'TL',
							'tooltip' : 'Poster\'s Information', 
							'text' : 'Each post had the Poster\'s information <br/><br/>' },  	
			
				//10
				{ element: 	'#latest-post-container .post-container .user .photo', 
							'position' : 'TL',
							'tooltip' : ' Facebook Picture and Name', 
							'text' : 'Their Facebook Picture and Name <br/><br/>' }, 
				//11
				{ element: 	'#latest-post-container .post-container .user .user-badge', 
							'position' : 'T',
							'tooltip' : 'Follow Button', 
							'text' : 'If you can click on this button for a specific user, FanCrank will make it possible to only see posts by this user<br/><br/>' }, 			
				//12
				{ element: 	'#latest-post-container  .post-container .post', 
							'position' : 'T',
							'tooltip' : 'Post Contents', 
							'text' : 'The actual post itself <br/><br/>' },
				//13
				{ element: 	'#latest-post-container  .post-container .social', 
							'position' : 'T',
							'tooltip' : 'Post Information', 
							'text' : 'and information about the post <br/><br/>' },
				//14		
				{ element: 	'#latest-post-container  #latest-like-container', 
							'position' : 'TL',
							'tooltip' : 'Likes', 
							'text' : 'Click the word [Like] to like something, If someone has liked this post you can would see something similar to  <br/> \
									  <img src="/img/tutorial2.png"/> <br/> If you hover over it, it will show some of the people who liked it. If you click on \
									  it, it will show you a list of everyone who liked it <br/><br/>'},	
				//15					
				{ element: 	'#latest-post-container  #latest-comment-container', 
							'position' : 'TL',
							'tooltip' : 'Comments', 
							'text' : 'Click on the comment to post a comment or see comments others have post it.  <br/> \
									  <img src="/img/tutorial3.png"/> <br/> If you hover over it, it will show some of the people who commented on it. If you click on \
									  it, it will show you the list of comments <br/><br/>'},	
				//16			
				{ element: 	'#latest-post-container  .post-container .social .time', 
							'position' : 'TL',
							'tooltip' : 'Time', 
							'text' : 'Hover over the time to see the actual time the post was made. <br/><br/>' },	  	   		
		   		//17
		   		{ element: 	'#fancrank-feed-container', 
					  		'position' : 'T',
					   		'tooltip' : 'FanCrank Feed', 
					   		'text' : 'This is the feed, just like in Facebook <br/><br/>' },   
				//18	   		
				{ element: 	'#feed-controller', 
					  		'position' : 'T',
					   		'tooltip' : 'Feed Controller', 
					   		'text' : 'Here you can choose how you want to view your posts, All, My Feed or Page Post <br/> \
					   		All is view all the post on the page, My Feed is view only things related to people You\'ve followed \
					   		Page Post is show only post made by <span style="font-weight:bold">' +fanpageName + '</span \
					   		<br/><br/>' }, 	   		
				//19 		
				{ element: 	'#fancrank-feed-container .submit-form', 
					  		'position' : 'T',
					   		'tooltip' : 'Posting Box', 
					   		'text' : 'If you want to post something through FanCrank, you can by simply typing here and clicking Share <br/>'+
					   				'Share will only show up after you\'ve tried to type something'+'<br/><br/>' }, 	   			
		   		//20
		   		{ element: 	'#top-fan-container', 
					  		'position' : 'T',
					   		'tooltip' : 'Top Fans This Week', 
					   		'text' : 'This is a simplifed version of the Top Fans Leaderboard. <br/><br/>' },   
		   		//21
		   		{ element: 	'#top-post-container', 
					  		'position' : 'T',
					   		'tooltip' : 'Top Post This Week', 
					   		'text' : 'Top Posts who you the most interesting posts of the week <br/><br/>' },   
								
				//22			
				{ element: 	'#leaderboard-tab', 
							'position' : 'T',
							'tooltip' : 'Leaderboards Page', 
							'text' : 'Leaderboards are a way to compete with other fans <br/><br/>' },
				//23   
				{ element: 	'.top-fan', 
							'position' : 'T',
							'tooltip' : 'Top Fan', 
							'text' : '"Top Fans" ranks the users that have the most activity on the page, based on Posts, Likes and Comments. Try to get on the Top Fan to earn awesome prizes! <br/><br/>' },
				//24
				{ element: 	'.fan-favorite', 
							'position' : 'T',
							'tooltip' : 'Fan Favorite', 
							'text' : '"Fan Favorite" ranks users by how much activity they garnered. <br/><br/>' },
				//25	   			   		
				{ element: 	'.top-talker', 
							'position' : 'T',
							'tooltip' : 'Top Talker', 
							'text' : '"Top Talker" are the people that\'ve posted the most <br/><br/>' },
				//26
				{ element: 	'.top-clicker', 
							'position' : 'T',
							'tooltip' : 'Top Clicker', 
							'text' : '"Top Clicker" ranks users by most [Likes] <br/><br/>' },
				//27
				{ element: 	'.top-followed', 
							'position' : 'T',
							'tooltip' : 'Top Followed', 
							'text' : '"Top Followed" ranks people by the amount of people that follow them <br/><br/>' },
				//28
				{ element: 	'.top-followed .btn-more', 
							'position' : 'T',
							'tooltip' : 'More', 
							'text' : 'Click more to see rank 2-4. <br/><br/>' },
				//29	   			   			   			   			   			   			   			   		
				{ element: 	'#profile-tab', 
							'position' : 'TL',
							'tooltip' : 'Profile Page', 
							'text' : 'This is your profile, view your FanCrank Information Here <br/><br/>' },	   		
				//30	   			   			   			   			   			   			   			   		
				{ element: 	'#general-stats-container', 
							'position' : 'T',
							'tooltip' : 'FanCrank Statistics', 
							'text' : 'This table displays your level , experience, points and achievement progress <br/><br/>' },		   		
				//31
				{ element: 	'#general-stats-container #level-container', 
							'position' : 'T',
							'tooltip' : 'Level', 
							'text' : 'This table displays your level , experience, points and achievement progress <br/><br/>' },	   		
				//32
				{ element: 	'#general-stats-container #points-container', 
							'position' : 'T',
							'tooltip' : 'Points', 
							'text' : 'This table displays your level , experience, points and achievement progress <br/><br/>' },	   		
				//33
				{ element: 	'#general-stats-container #exp-container', 
							'position' : 'T',
							'tooltip' : 'Experience', 
							'text' : 'This table displays your level , experience, points and achievement progress <br/><br/>' },	   		
				//34
				{ element: 	'#upcoming-badges-container', 
							'position' : 'T',
							'tooltip' : 'Upcoming Badges', 
							'text' : 'These are your upcoming badges <br/><br/>' },	   		
				//35
				{ element: 	'.upcoming-badges', 
							'position' : 'TL',
							'tooltip' : 'Badges', 
							'text' : 'These show how close you are to obtaining a new badge<br/><br/>' },	
				//36
				{ element: 	'#follow-list-container', 
							'position' : 'TL',
							'tooltip' : 'Following/Followers List', 
							'text' : 'These two display the list of followers and the list of people you are following <br/><br/>' },		   		
				//37
				{ element: 	'#follow-list-container .btn-more', 
							'position' : 'T',
							'tooltip' : 'View More', 
							'text' : 'Click the [View Full List] button to see the entire list of people <br/><br/>' },			
				//38
				{ element: 	'#other-stats-container', 
							'position' : 'TL',
							'tooltip' : 'Other Statistics', 
							'text' : 'Here are a list of other statistics about you! <br/><br/>' },			
				//39
				{ element: 	'#recent-activities-container', 
							'position' : 'T-Lowered',
							'tooltip' : 'Recent Activities', 
							'text' : 'This is a list of activities of things you did, you can go back to view specific comments or posts by clicking on them.<br/><br/>' },			
				//40	   			   			   			   			   			   			   			   		
				{ element: 	'#redeem-tab', 
							'position' : 'TL',
							'tooltip' : 'Redemption Page', 
							'text' : 'This is the Redemption Page <br/><br/>' },	   					
				//41	   			   			   			   			   			   			   			   		
				{ element: 	'', 
							'position' : 'TL',
							'tooltip' : '&nbsp;', 
							'text' : 'There are only a few more things to talk about. <br/><br/>' },				
				//42	   			   			   			   			   			   			   			   		
				{ element: 	'#top-post-container #toppost .post-container .user .name', 
							'position' : 'TL',
							'tooltip' : 'Click on any Username or Picture', 
							'text' : 'If you click on a username or picture, it will bring out the user profile screen  <br/><br/>' },
				//43
				{ element: 	'', 
							'position' : 'TL',
							'tooltip' : '&nbsp;', 
							'text' : 'You can see it it very similar to your own profile page, with the exception of their recent activities being on the bottom<br/><br/>' },
				//44		   
				{ element: 	'', 
							'position' : 'TL',
							'tooltip' : '&nbsp;', 
							'text' : 'If you had only hovered over a name or profile, you will see something similar to this <br/><br/> \
									  <img src="/img/tutorial1.png"/> <br/> ' },
				//45					  
				{ element: 	'', 
							'position' : 'TL',
							'tooltip' : '&nbsp;', 
							'text' : 'If you click on Comments in Top Post or in Recent Activities it will bring out another window showing the  <br/><br/> ' },			  
				
				{ element: 	'', 
							'position' : 'TL',
							'tooltip' : '&nbsp;', 
							'text' : 'And that concludes our tutorial of FanCrank, Thanks for going through our tutorial. If you encounter any issues please email help@fancrank.com  <br/><br/> ' },			  

							],	
		controlsPosition : 'custom'
	};


var selected_badges = 0;


$('.cbadges').live('click', function(){
	
	var $this = $(this);
	
	if(!$this.hasClass('selected') && selected_badges >= 3){
		alert('You have already selected 3 badges');
	}else if($this.hasClass('selected')){
		$this.toggleClass('selected');
		
		
		selected_badges--;
		
	}else{
		
		
		selected_badges++;
		//console.log($this.attr('data-id'));
		$this.toggleClass('selected');
	}
    
});
function submit_badges_choice(){
	var sbadges = new Array();
	$('.selected').each(function(i){
		sbadges.push($(this).attr('data-id'));
		//console.log($(this).attr('data-id'));
	});

	console.log(sbadges[0]+ ' ' +sbadges[1] + ' ' + sbadges[2]) ;

	$.ajax({
		type : "GET",
		url : serverUrl + '/app/user/' + userId + '/savechosenbadges/' + '?c1=' +sbadges[0]+ '&c2=' +sbadges[1]
						+ '&c3=' + sbadges[2] + '&fanpage=' + fanpageId,
		dataType : "html",
		cache : false,
		async : true,
		success : function(data) {
			//alert($.trim(data));
			closeProfile();
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error saving badge choices');
		}
	});
	
}

function reset_badges_choice(){

	$.ajax({
		type : "GET",
		url : serverUrl + '/app/user/' + userId + '/savechosenbadges/ '  + '&fanpage=' + fanpageId,
		dataType : "html",
		cache : false,
		async : true,
		success : function(data) {
			//alert($.trim(data));
			//closeProfile();
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error saving badge choices');
		}
	});
	
}
/*
function isLogin(data) {
	if($.trim(data) == "") {
		alert('hi');
		//window.location.href = '/app/index/index/'+fanpageId;
		return false;
	}else {
		alert('data');
		return true;
	}
}
*/
/*
function isLogin(data) {
	if($.trim(data) == "") {
		alert('hi');
		//window.location.href = '/app/index/index/'+fanpageId;
		return false;
	}else {
		alert('data');
		return true;
	}
}
*/
/*
function upload(form, action_url, div_id, target_id, target) {
	fileUpload(form, action_url, div_id);
	addActivities('post-photo', userName, fanpageId, target_id, target);
	setFeed = 0;
	getFancrankfeed(setFeed);
}

function fileUpload(form, action_url, div_id) {

	// Create the iframe...
	var iframe = document.createElement("iframe");
	iframe.setAttribute("id", "upload_iframe");
	iframe.setAttribute("name", "upload_iframe");
	iframe.setAttribute("style",
			"width: 100; height: 50; border: 1; display:block");

	// Add to document...
	form.parentNode.appendChild(iframe);
	window.frames['upload_iframe'].name = "upload_iframe";

	iframeId = document.getElementById("upload_iframe");

	// Add event...
	var eventHandler = function() {

		if (iframeId.detachEvent)
			iframeId.detachEvent("onload", eventHandler);
		else
			iframeId.removeEventListener("load", eventHandler, false);

		// Message from server...
		if (iframeId.contentDocument) {
			content = iframeId.contentDocument.body.innerHTML;
		} else if (iframeId.contentWindow) {
			content = iframeId.contentWindow.document.body.innerHTML;
		} else if (iframeId.document) {
			content = iframeId.document.body.innerHTML;
		}

		document.getElementById(div_id).innerHTML = content;

		// Del the iframe...
		setTimeout('iframeId.parentNode.removeChild(iframeId)', 250);
	};

	if (iframeId.addEventListener)
		iframeId.addEventListener("load", eventHandler, true);
	if (iframeId.attachEvent)
		iframeId.attachEvent("onload", eventHandler);

	// alert('now im gonna send the form');
	action_url = action_url + '&message=' + $('#post_box').val();
	action_url = action_url + '&access_token=' + userAccessToken;
	alert(action_url);
	// Set properties of form...
	form.setAttribute("target", "upload_iframe");
	form.setAttribute("action", action_url);
	form.setAttribute("method", "post");
	form.setAttribute("enctype", "multipart/form-data");
	form.setAttribute("encoding", "multipart/form-data");

	// Submit the form...
	form.submit();

	document.getElementById(div_id).innerHTML = "Uploading...";

}

function colorChange(choice) {
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/user/' + userId + '/color/?choice=' + choice
				+ '&fanpage_id=' + fanpageId,
		dataType : "html",
		cache : false,
		success : function(data) {
			window.location.reload();

			//$(ui).html(data);
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});

}


$('#achievements-tab').live('click', function() {
	getAwards();
	$('#leaderboard').html('');
	$('#profile').html('');
	$('#news-feed').html('');
	$('#redeem').html('');
	FB.Canvas.setSize({
		width : 810,
		height : 600
	});

});

function getAwards() {
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/awards/' + fanpageId + '?facebook_user_id='
				+ userId,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$('#achievements').html("<div style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
		},
		success : function(data) {
			$('#achievements').html(data);

		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting the awards page');
		}
	});
}

*/

function numberFormat(num) {
    if (num >= 1000000000) {
        return (num / 1000000000).toFixed(1) + 'G';
    }
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num;
}

/**redeemable badge section*********************/
// confirm popup, note: following script can be moved to cranker.js
$(document).ready(function() {
	var selectBadgeId;
	
	$('.redeemableBadge').live('click', function() {
		selectBadgeId = $(this).attr('data-redeem-id');
		console.log(selectBadgeId);
		// show popup
		popup(true);
		$.ajax({
			type : "GET",
			url : serverUrl + '/app/redeem/index/' + fanpageId + '?redeemId=' + selectBadgeId,
			dataType : "html",
			cache : false,
			async : false,
			beforeSend: function(){
				$('.profile-content').html("<div class='rvrow'><div class=' rvgrid-11 '><div class=' box '><div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div></div></div></div>");
			},
			success : function(data) {
				$('.profile-content').html(data);
				//$(x).popover('show');
			},
			error : function(xhr, errorMessage, thrownErro) {
				console.log(xhr.statusText, errorMessage);
			}
		});
	});

	// add redeem confirm listner
	$('#redeem_submit').live('click', function(e) {
		e.preventDefault();
		console.log('redeem confirm');
		var formData = $('#redeem-form').serialize();
		console.log(formData+selectBadgeId);
		
		if ($('#redeemItemId').val() == '') {
			alert('Please Select an Item to Redeem');
			return;
		}
		
		if (!$('#redeem-form').valid()) {
			$('#redeem-form').validate().focusInvalid();
		} else {
			$.ajax({
				type : "POST",
				url : serverUrl + '/app/redeem/confirm/' + fanpageId,
				dataType : "html",
				cache : false,
				async : true,
				data : formData+"&badgeId="+selectBadgeId,
				success : function(data) {
					//$('.profile-content').html(data);
					console.log(data);
					if ($.trim(data) == 'ok') {
						alert('done!');
					}
					closeProfile();
					//$(x).popover('show');
				},
				error : function(xhr, errorMessage, thrownErro) {
					console.log(xhr.statusText, errorMessage);
				}
			});			
		}
	});

	// open update shipping info form
	$('#updateShipping').live('click', function(e) {
		e.preventDefault();
		// show popup
		popup(true);
		$.ajax({
			type : "GET",
			url : serverUrl + '/app/redeem/updateshipping/' + fanpageId,
			dataType : "html",
			cache : false,
			async : true,
			beforeSend: function(){
				$('.profile-content').html("<div class='rvrow'><div class=' rvgrid-11 '><div class=' box '><div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div></div></div></div>");
			},
			success : function(data) {
				$('.profile-content').html(data);
				//$(x).popover('show');
			},
			error : function(xhr, errorMessage, thrownErro) {
				console.log(xhr.statusText, errorMessage);
			}
		});
	});
	
	// update shipping info
	$('#updateShippingConfirm').live('click', function(e) {
		e.preventDefault();

		var formData = $('#update-shipping-form').serialize();
		console.log(formData+selectBadgeId);
		
		if (!$('#update-shipping-form').valid()) {
			$('#update-shipping-form').validate().focusInvalid();
		} else {
			$.ajax({
				type : "POST",
				url : serverUrl + '/app/redeem/updateshipping/' + fanpageId,
				dataType : "html",
				cache : false,
				async : true,
				data : formData,
				success : function(data) {
					//$('.profile-content').html(data);
					if ($.trim(data) == 'ok') {
						alert('done!');
					}
					closeProfile();
					//$(x).popover('show');
				},
				error : function(xhr, errorMessage, thrownErro) {
					console.log(xhr.statusText, errorMessage);
				}
			});			
		}
	});
	// redeem form validate function
	function confimFormValidate(formData, jqForm, options) { 

	}
});

