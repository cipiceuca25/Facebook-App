var setFeed = 'all';
var myfeedoffset = 0;

var ffb = true;
var ttb = true;
var tcb = true;
var tfdb = true;

var mouseX;
var mouseY;

$(document).ready(
		
	function() {
		
// trick to indentify parent container
		if (window.location != window.parent.location) {
			$(document.body).css({
				'overflow' : 'hidden'
			});
		} else {
			$(document.body).css({
				'overflow' : 'auto'
			});
		}	

		FB.api(fanpageId, function(response) {
			if (!response || response.error) {
			} else {
				var x = 0;
				var y;
				try {
					y = response.cover.offset_y;
				} catch (err) {
					y = 0;
				}

				if (y > 35) {
					x = -1 * (parseInt(y) + 50);
				} else {
					x = 0;
				}

				try {
					$('#logo').html(
						'<img src =" ' + response.cover.source
							+ '"style=" top:' + x + 'px" />');
				} catch (err) {
			}
		}

	});

	getNewsfeed('#news-feed');

});

$(document).mousemove(function(e) {
	mouseX = e.pageX;
	mouseY = e.pageY;
});

$(document).on('mouseover', 'a[rel=popover]', function() {
	popover($(this));

	if ($(this).data('isPopoverLoaded') == true) {
		return;
	}
	$(this).data('isPopoverLoaded', true).popover({
		delay : {
			show : 500,
			hide : 10
		},
		placement : $(this).attr('data-placement')
	}).trigger('mouseover');

});

$(document).on('mouseover', 'a[rel=tooltip]', function() {
	if ($(this).data('isTooltipLoaded') == true) {
		return;
	}
	$(this).data('isTooltipLoaded', true).tooltip({
		'placement' : 'left'
	}).trigger('mouseover');
});

$(document).on('mouseover', 'a[rel=tooltip-award]', function() {
	if ($(this).data('isTooltipLoaded') == true) {
		return;
	}
	$(this).data('isTooltipLoaded', true).tooltip({
		'placement' : 'top'
	}).trigger('mouseover');
});

$('.badge-Following').live("mouseenter", function() {
	$(this).text('Unfollow');
});

$('.badge-Following').live("mouseleave", function() {
	$(this).text('Following');
});

$('.badge-Follower').live("mouseenter", function() {
	$(this).text('Follow');
});
$('.badge-Follower').live("mouseleave", function() {
	$(this).text('Follower');
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
	feedLimit = 0;
	getNewsfeed('#news-feed');

	$('#leaderboard').html('');
	$('#profile').html('');
	$('#achievements').html('');
	$('#redeem').html('');
	
});

$('#leaderboard-tab').live('click', function() {
	ffb = true;
	ttb = true;
	tcb = true;
	tfdb = true;
	getLeaderboard();

	$('#profile').html('');
	$('#achievements').html('');
	$('#news-feed').html('');
	$('#redeem').html('');
	//$('.bubble').html('');
	FB.Canvas.setSize({
		width : 810,
		height : 600
	});

});

$('#profile-tab').live('click', function() {

	getMyProfile();
	$('#leaderboard').html('');
	$('#achievements').html('');
	$('#news-feed').html('');
	$('#redeem').html('');
	FB.Canvas.setSize({
		width : 810,
		height : 600
	});

});


$('#redeem-tab').live('click', function() {
	getRedeem();
	$('#leaderboard').html('');
	$('#profile').html('');
	$('#news-feed').html('');
	$('#achievements').html('');
	FB.Canvas.setSize({
		width : 810,
		height : 600
	});

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
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}

function feedbackAnimation(ui) {
	$(ui).css({
		'top' : mouseY - 16,
		'left' : mouseX - 62,
		'opacity' : '1',
		'display' : 'block'
	});
	$(ui).animate({
		opacity : '0',
		top : "-=30px",
	}, 1000, function() {
	});
	
	$(ui).delay(500).hide(0);
	
}


function closeProfile() {
	$('.light-box').css('display', 'none');
	$('.user-profile').css('display', 'none');
	$('.profile-content').css('display', 'none');
	$('.profile-content').html('');
	
}

function ImgError(source) {
	source.src = "/img/profile-picture.png";
	source.onerror = "";
	return true;
}

function userProfile(user, load) {
	load = typeof load !== 'undefined' ? load : true;
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/userprofile/' + fanpageId + '/?target='
				+ user + '&facebook_user_id=' + userId,
		dataType : "html",
		cache : false,
		async : true,
	
		success : function(data) {

			$('.profile-content').html(data);
			changeTime('.profile-content .time');
			popup(load);
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting popup profile');
		}
	});
}

function comment_feed(post_id, type, limiter, total, toggle) {
	ui = '#post_' + post_id;
	//alert(ui);

	getFeedComment(ui, post_id, type, limiter, total, toggle, false);

	$('.social.comment.' + post_id).css('display', 'none');
	changeTime('#post_' + post_id + ' .time');

}

function comment_feed2(post_id, type, limiter, total, toggle) {
	ui = '#postn_' + post_id;
	//alert(ui);
	getFeedComment(ui, post_id, type, limiter, total, toggle, true);

	$('.social.commentn.' + post_id).css('display', 'none');
	
	changeTime('#postn_' + post_id + ' .time');
}


//ui where is this going 
//post_id the post id 
// what type of post it is required for saving activities?
// limit how many to show
// total = what's the total number of comments
// toggle = pop up ?
// is this on the latest
function getFeedComment(ui, post_id, type, limiter, total, toggle, latest) {
	
	
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/fancrankfeedcomment/' + fanpageId
				+ '?post_id=' + post_id + '&post_type=' + type + '&limit='
				+ limiter + '&total=' + total + '&latest=' + latest + '&until=' + until,
		dataType : "html",
		cache : false,
		async : false,
		success : function(data) {
			$(ui).html(data);
			if (toggle) {
				$(ui).animate({
					height : 'toggle',
				}, 1000, 'swing', function() {
					// Animation complete.
				});
			} 
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


function popup_post(post_id, limiter, load) {
	load = typeof load !== 'undefined' ? load : true;
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/popuppost/' + fanpageId + '?post_id='
				+ post_id + '&limit=' + limiter,

		dataType : "html",
		cache : false,
		async : true,
		success : function(data) {
			$('.profile-content').html(data);
			changeTime('#popup_comment .time');
			popup(load);
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
			
		
			$('#news-feed').html(data);
			
			getTopFan();
			getTopPost();
			changeTime('.time');

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
			$('#toppost').html("<div style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
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
	$
			.ajax({
				type : "GET",
				url : serverUrl + '/app/user/' + userId + '/likes/?post_id='
						+ post_id + '&fanpage_id=' + fanpageId + '&post_type='
						+ post_type + '&access_token=' + userAccessToken,
				dataType : "html",
				cache : false,
				success : function(data) {
					//alert("target followed")
					//alert(post_id + 'liked');
					feedbackAnimation('#like-animation');

					addActivities('like-' + post_type, post_id,
							target_id, target_name);

					num = $('.like_' + post_id).html();

					if (post_type == 'comment') {
						if ((num == null) || (num == 0)) {
							$('.like_' + post_id).html('1');
							//alert('.social.like.'+post_id);

						} else {
							$('.like_' + post_id).html((parseInt(num) + 1));

						}
					} else {
						if ((num == null) || (num == 0)) {
							$('.like_' + post_id).html('1 person');
							//alert('.social.like.'+post_id);

						} else {
							$('.like_' + post_id).html(
									(parseInt(num) + 1) + ' people');

						}
					}
					$('.social.like.' + post_id).css('display', 'block');

					$('.like_control_' + post_id).attr(
							'onclick',
							"unlike('" + post_id + "','" + post_type + "','"
									+ target_id + "','" + target_name + "')");
					$('.like_control_' + post_id).html('Unlike');

				},
				error : function(xhr, errorMessage, thrownErro) {
					console.log(xhr.statusText, errorMessage);
					console.log('error processing likes');
				}
			});
}

function unlike(post_id, post_type, target_id, target_name) {
	$
			.ajax({
				type : "GET",
				url : serverUrl + '/app/user/' + userId + '/unlike/?post_id='
						+ post_id + '&fanpage_id=' + fanpageId + '&post_type='
						+ post_type + '&access_token=' + userAccessToken,
				dataType : "html",
				cache : false,
				success : function(data) {
					//alert("target followed")
					//alert(post_id + 'liked');
					feedbackAnimation('#unlike-animation');
					addActivities('unlike-' + post_type, post_id,
							target_id, target_name);

					num = $('.like_' + post_id).html();
					if (post_type == 'comment') {
						if (num == 2) {
							$('.like_' + post_id).html('1');
						} else {
							$('.like_' + post_id).html((parseInt(num) - 1));
						}
					} else {
						if (num == 2) {
							$('.like_' + post_id).html('1 person');
						} else {
							$('.like_' + post_id).html(
									(parseInt(num) - 1) + ' people');
						}
					}
					$('.like_control_' + post_id).attr(
							'onclick',
							"like('" + post_id + "','" + post_type + "','"
									+ target_id + "','" + target_name + "')");
					$('.like_control_' + post_id).html('Like');

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
	if((setFeed == view) && (view != 'myfeed')){
		last = $('#last_post_time').attr('data-time');
		myfeedoffset = 0;
	}else if (view == 'myfeed'){
		
		last = myfeedoffset;
		myfeedoffset +=10;
	}else{
		last = undefined;
	}

	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/fancrankfeed/' + fanpageId + '?viewAs='
				+ view + '&until=' + last ,
		dataType : "html",
		cache : false,
		async :  false,
		beforeSend: function(){
			$('#fancrankfeed').append("<div id='loader' style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
		},
		success : function(data) {
			//alert(data);
			$('#loader').remove();
			$('#last_post_time').remove();
			$('#more_post').remove();
			if((setFeed != view)){
				//alert(last);
				$(ui).html(data);
			}else{
				$(ui).append(data);
				
			}
			changeTime('#fancrankfeed .time');
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting the feed');
		}
	});
	if(view == 'post'){
		view = 'all';	
	};
	setFeed = view;
	
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
			$('#topfan').html("<div style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
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
			$('#leaderboard').html(data);

			//getLatestPost();
		},
		error : function(xhr, errorMessage, thrownErro) {
			alert(url);
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
			$('#profile').html(data);
			getRecentActivities();
			getMiniFollowingList(userName, userId);
			getMiniFollowersList(userName, userId);
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
			$('#redeem').html(data);
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting the redemption page');
		}
	});
}



function getRecentActivities() {
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/recentactivities/' + fanpageId,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend: function(){
			$('#recent_activities').html("<div style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
		
		},
		success : function(data) {
			
			$('#recent_activities').html(data);
			changeTime('.time');
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
					$('.' + ui).html(
							'<div class="badge badge-' + data + '">' + data
									+ '</div>');

				},
				error : function(xhr, errorMessage, thrownErro) {
					console.log(xhr.statusText, errorMessage);
					console.log('there was an error getting the user relation');
				}
			});

}

function follow(target, name, ui) {
	if (target != userId) {

		$.ajax({
			type : "GET",
			url : serverUrl + '/app/user/' + userId + '/follow/?subscribe_to='
					+ target + '&facebook_user_id=' + userId + '&fanpage_id='
					+ fanpageId + '&subscribe_ref_id=1',
			dataType : "html",
			cache : false,
			success : function(data) {
				//alert("target followed")
				addActivities('follow', target, target, name);
				//getUserProfile('.profile-content', target);
				getRelation(target, ui);
				//alert(relation);
				$('.' + ui).attr(
						'onclick',
						"unfollow('" + target + "','" + name + "','" + ui
								+ "')");
				//$('.'+ui).html('<div class="badge badge-'+relation+'">'+relation+'</div>');
				feedbackAnimation('#follow-animation');
			},
			error : function(xhr, errorMessage, thrownErro) {
				console.log(xhr.statusText, errorMessage);
				console.log('there was an error with following');
			}
		});
	}
}

function unfollow(target, name, ui) {

	$.ajax({
		type : "GET",
		url : serverUrl + '/app/user/' + userId + '/unfollow/?subscribe_to='
				+ target + '&facebook_user_id=' + userId + '&fanpage_id='
				+ fanpageId + '&subscribe_ref_id=1',
		dataType : "html",
		cache : false,
		success : function(data) {
			//alert("target unfollowed")
			addActivities('unfollow', target, target, name);
			//getUserProfile('.profile-content', target);
			getRelation(target, ui);

			$('.' + ui).attr('onclick',
					"follow('" + target + "','" + name + "','" + ui + "')");
			//$('.'+ui).html('<div class="badge badge-'+relation+'">'+relation+'</div>');
			feedbackAnimation('#unfollow-animation');

		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('there was an error with unfollow');
		}
	});
}

function addActivities(act_type, event, target_id, target_name) {
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/user/' + userId + '/addactivity/?owner_name='
				+ userName + '&activity_type=' + act_type + '&event=' + event
				+ '&fanpage_id=' + fanpageId + '&target_id=' + target_id
				+ '&target_name=' + target_name,
		dataType : "html",
		cache : false,
		success : function(data) {

		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('issue with activity save');
		}
	});

}

function post(fanpage_name) {

	FB.api('/' + fanpageId + '/feed', 'post', {
		'message' : $('#post_box').val(),
		'access_token' : userAccessToken
	},
			function(response) {
				if (!response || response.error) {
					alert(response.error.message);
				} else {
					//alert('Post ID: ' + response.id)
					$('#post_box').val('');
					addActivities('post-status', response.id, fanpageId, fanpage_name);
					getFancrankfeed('post');
				}
			});

}

function commentSubmit(post_id, post_type, post_message_id, post_owner_id, post_owner_name, isLatestAdminPost){
	FB.api('/' + post_id + '/comments', 'post', {
		'message' : $('#'+post_message_id).val(),
		'access_token' : userAccessToken
	}, function(response) {
		if (!response || response.error) {
			alert(response.error.message);
		} else {
			$('#'+post_message_id).val('');
			
			addActivities('comment-' + post_type, post_id, post_owner_id, post_owner_name);
			
			comment_feed(post_id, type, limiter, total, toggle);
		}
	});
}

function commentSubmit(postid, type, message, target_id, target, latest) {
	var m = '#' + message;

	//alert(userAccessToken);
	FB.api('/' + postid + '/comments', 'post', {
		'message' : $(m).val(),
		'access_token' : userAccessToken
	}, function(response) {
		if (!response || response.error) {
			alert(response.error.message);
		} else {
			//alert('Post ID: ' + response.id);
			$(m).val('');
			addActivities('comment-' + type, postid, target_id,
					target);
			$('.comment_' + postid).html(postcommentcount + 1);
			if (latest) {
				alert('calling comment feed 2');
				comment_feed2(postid, type, postcommentcount + 1,
						postcommentcount + 1, false);
			} else {
				comment_feed(postid, type, postcommentcount + 1,
						postcommentcount + 1, false);
			}
		}
	});
}

function commentpopupSubmit(postid, type, message, target_id, target) {
	var m = '#' + message;

	//alert(userAccessToken);
	FB.api('/' + postid + '/comments', 'post', {
		'message' : $(m).val(),
		'access_token' : userAccessToken
	}, function(response) {
		if (!response || response.error) {
			alert(response.error.message);
		} else {
			//alert('Post ID: ' + response.id);
			$(m).val('');
			addActivities('comment-' + type, postid, target_id,
					target);
			num = $('.comment_' + postid).html();
			$('.comment_' + postid).html((parseInt(num) + 1));
			comment_popover(postid, num + 1, false);

		}
	});
}

function getFollowingList(targetname, target, limit, load) {
	load = typeof load !== 'undefined' ? load : true;
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/getfollowing/' + fanpageId + '?limit='
				+ limit + '&target=' + target + '&targetname=' + targetname
				+ '&mini=0',
		dataType : "html",
		cache : false,
		async : true,
		success : function(data) {
			
			$('.profile-content').html(data);
			popup(load);
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('there was an error with getting the following list');
		}
	});

}


function getFollowersList(targetname, target, limit, load) {
	load = typeof load !== 'undefined' ? load : true;
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/getfollowers/' + fanpageId + '?limit='
				+ limit + '&target=' + target + '&targetname=' + targetname
				+ '&mini=0',
		dataType : "html",
		cache : false,
		async : true,
		success : function(data) {

			$('.profile-content').html(data);
			popup(load);
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
		$('.user-profile').css('top', info.scrollTop - 100);
	});

	if (load) {
		$('.profile-content').animate({
			height : 'toggle',
		//top:'20px'
		}, 500, function() {

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
			$('#followinglist').html("<div style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");		
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
			$('#followerslist').html("<div style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
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

function resizeCommentBox(x) {
	if (x.scrollHeight > 26) {
		x.style.height = "1px";
		x.style.height = (x.scrollHeight) + "px";
	}
	$('.post-button-container').css('display', 'block');
	$('.post-box').css('border-bottom', '0px');
}

function changeTime(ui){
	$.each($(ui), function(index) {
		$(this).html(timeZone($(this).attr('data-unix-time')));
		$(this).attr('title', timeZoneTitle($(this).attr('data-unix-time')));
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

