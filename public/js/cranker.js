var setFeed = 'All';
var feedLimit = 0;

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



$('#news-feed a').click(function(e) {
	e.preventDefault();
	$(this).tab('show');
});

$('#post_post a').click(function(e) {
	e.preventDefault();
	$(this).tab('show');
});

$('#post_photo a').click(function(e) {
	e.preventDefault();
	$(this).tab('show');
});

$('#fan-favorite a').click(function(e) {
	e.preventDefault();
	$(this).tab('show');
});

$('#profile a').click(function(e) {
	e.preventDefault();
	$(this).tab('show');
});

$('#achievements a').click(function(e) {
	e.preventDefault();
	$(this).tab('show');
});

$('#redeem a').click(function(e) {
	e.preventDefault();
	$(this).tab('show');
});

$('#newsfeed-tab').live('click', function() {
	feedLimit = 0;
	mtfb = true;
	tfb = true;
	ffb = true;
	ttb = true;
	tcb = true;
	tfdb = true;
	getNewsfeed('#news-feed');

	$('#leaderboard').html('');
	$('#profile').html('');
	$('#achievements').html('');
	$('#redeem').html('');
	FB.Canvas.setSize({
		width : 810,
		height : 600
	});

});

$('#leaderboard-tab').live('click', function() {
	mtfb = true;
	tfb = false;
	ffb = true;
	ttb = true;
	tcb = true;
	tfdb = true;
	getLeaderboard();

	$('#profile').html('');
	$('#achievements').html('');
	$('#newsfeed').html('');
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
	$('#newsfeed').html('');
	$('#redeem').html('');
	FB.Canvas.setSize({
		width : 810,
		height : 600
	});

});

$('#achievements-tab').live('click', function() {
	getAwards();
	$('#leaderboard').html('');
	$('#profile').html('');
	$('#newsfeed').html('');
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
	$('#newsfeed').html('');
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
		async : false,

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
	
	$(ui).delay(1500).hide(0);
	
}


function closeProfile() {
	$('.light-box').css('display', 'none');
	$('.user-profile').css('display', 'none');
	$('.profile-content').css('display', 'none');
	$('.profile-content').html('');
	$('#moreComment').removeAttr('postid');
}

function ImgError(source) {
	source.src = "/img/profile-picture.png";
	source.onerror = "";
	return true;
}

function userProfile(user, refresh) {
	refresh = typeof refresh !== 'undefined' ? refresh : true;
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/userprofile/' + fanpageId + '/?target='
				+ user + '&facebook_user_id=' + userId,
		dataType : "html",
		cache : false,
		async : true,
	
		success : function(data) {

			$('.profile-content').html(data);
			$.each($('.profile-content .time'), function(index) {
				$(this).html(timeZone($(this).html()));
				$(this).attr('title', timeZoneTitle($(this).attr('title')));
			});
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});

	$('.light-box').css('display', 'block');
	$('.user-profile').css('display', 'block');
	$('.profile-content').css('height', 'auto');

	FB.Canvas.getPageInfo(function(info) {
		$('.user-profile').css('top', info.scrollTop - 100);
	});

	if (refresh) {

		$('.profile-content').animate({
			height : 'toggle',
		}, 1000, function() {

		});
	}

}



function comment_feed(post_id, type, limiter, total, toggle) {
	ui = '#post_' + post_id;
	//alert(ui);

	getFeedComment(ui, post_id, type, limiter, total, toggle, false);

	$('.social.comment.' + post_id).css('display', 'none');
	$.each($('#post_' + post_id + ' .time'),
			function(index) {
				$(this).html(timeZone($(this).html()));
				$(this).attr('title',
						timeZoneTitle($(this).attr('title')));
			});
}

function comment_feed2(post_id, type, limiter, total, toggle) {
	ui = '#postn_' + post_id;
	//alert(ui);
	getFeedComment(ui, post_id, type, limiter, total, toggle, true);

	$('.social.commentn.' + post_id).css('display', 'none');
	$.each($('#postn_' + post_id + ' .time'),
			function(index) {
				$(this).html(timeZone($(this).html()));
				$(this).attr('title',
						timeZoneTitle($(this).attr('title')));
			});

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
				+ limiter + '&total=' + total + '&latest=' + latest,

		dataType : "html",
		cache : false,
		async : false,
	
		success : function(data) {

			$(ui).html(data);
		
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});

	//alert('are we animating?' + toggle);
	if (toggle) {
		$(ui).animate({
			height : 'toggle',
		}, 1000, 'swing', function() {
			// Animation complete.
		});

	} 

}

// ui = where to load the comments
// post_id = 


function comment_popup(post_id, limiter, open) {
	//alert(open);
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/comment/' + fanpageId + '?post_id='
				+ post_id + '&limit=' + limiter,

		dataType : "html",
		cache : false,
		async : false,
		success : function(data) {
			$('.profile-content').html(data);
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});
	
	$.each($('#popup_comment .time'), function(index) {
		$(this).html(timeZone($(this).html()));
		$(this).attr('title', timeZoneTitle($(this).attr('title')));
	});
	
	if (open) {
		$('.light-box').css('display', 'block');
		$('.user-profile').css('display', 'block');


		FB.Canvas.getPageInfo(function(info) {
			$('.user-profile').css('top', info.scrollTop - 200);
		});
		//$('.profile-content').css('height', 'auto');

		$('.profile-content').animate({
			height : 'toggle',
		//top:'20px'
		}, 1000, function() {

		});
	}
	
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
			getFancrankfeed(setFeed);
			getTopFan();
			getTopPost();
			$('#latestpost .time')
					.html(timeZone($('#latestpost .time').html()));
			$('#latestpost .time').attr('title',
					timeZoneTitle($('#latestpost .time').attr('title')));
	
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
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
		async : false,
	
		success : function(data) {

			$('#toppost').html(data);
			
			$.each($('#toppost .time'), function(index) {
				$(this).html(timeZone($(this).html()));
				$(this).attr('title', timeZoneTitle($(this).attr('title')));
			});

		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
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

					addActivities('like-' + post_type, userName, post_id,
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
					addActivities('unlike-' + post_type, userName, post_id,
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
				}
			});
}

function getFancrankfeed(view) {
	ui = '#fancrankfeed';
	feedLimit += 6;
	setFeed = view;
	//alert(setFeed);
	//alert(feedLimit);
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/fancrankfeed/' + fanpageId + '?viewAs='
				+ view + '&limit=' + feedLimit,
		dataType : "html",
		cache : false,
		async : false,
		beforeSend: function(){
			$(ui).html("<div style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
		},
		success : function(data) {

			$(ui).html(data);
			$.each($('#fancrankfeed .time'), function(index) {
				$(this).html(timeZone($(this).html()));
				$(this).attr('title', timeZoneTitle($(this).attr('title')));
			});

		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}

function getTopFan() {

	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/topfan/' + fanpageId + '?facebook_user_id='
				+ userId,
		dataType : "html",
		cache : false,
		async : false,
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
		}
	});
}

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
		}
	});
}

function getRecentActivities() {
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/recentactivities/' + fanpageId,
		dataType : "html",
		cache : false,
		async : false,
		beforeSend: function(){
			$('#recent_activities').html("<div style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
		
		},
		success : function(data) {
			
			$('#recent_activities').html(data);
			$.each($('.time'), function(index) {
				$(this).html(timeZone($(this).html()));
				$(this).attr('title', timeZoneTitle($(this).attr('title')));
			});
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}

function getRelation(target, ui) {

	$
			.ajax({
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
				addActivities('follow', userName, target, target, name);
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
			addActivities('unfollow', userName, target, target, name);
			//getUserProfile('.profile-content', target);
			getRelation(target, ui);

			$('.' + ui).attr('onclick',
					"follow('" + target + "','" + name + "','" + ui + "')");
			//$('.'+ui).html('<div class="badge badge-'+relation+'">'+relation+'</div>');
			feedbackAnimation('#unfollow-animation');

		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}

function addActivities(act_type, owner_name, event, target_id, target_name) {
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/user/' + userId + '/addactivity/?owner_name='
				+ owner_name + '&activity_type=' + act_type + '&event=' + event
				+ '&fanpage_id=' + fanpageId + '&target_id=' + target_id
				+ '&target_name=' + target_name,
		dataType : "html",
		cache : false,
		success : function(data) {

			//alert('act saved');
			//$(ui).html(data);
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			alert('issue with activity save');
		}
	});

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

function post(target_id, target) {

	FB.api('/' + fanpageId + '/feed', 'post', {
		'message' : $('#post_box').val(),
		'access_token' : userAccessToken
	},
			function(response) {
				if (!response || response.error) {
					alert(response.error.message);
				} else {
					//alert('Post ID: ' + response.id);
					$('#post_box').val('');
					addActivities('post-status', userName, fanpageId,
							target_id, target);

					setFeed = 0;
					getFancrankfeed(setFeed);
				}
			});

}

function commentSubmit(postid, type, message, target_id, target,
		postcommentcount, latest) {
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
			addActivities('comment-' + type, userName, postid, target_id,
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
			addActivities('comment-' + type, userName, postid, target_id,
					target);
			num = $('.comment_' + postid).html();
			$('.comment_' + postid).html((parseInt(num) + 1));
			comment_popover(postid, num + 1, false);

		}
	});
}
function getFollowingList(targetname, target, limit, refresh) {
	ui = '.profile-content';
	refresh = typeof refresh !== 'undefined' ? refresh : true;
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/getfollowing/' + fanpageId + '?limit='
				+ limit + '&target=' + target + '&targetname=' + targetname
				+ '&mini=0',
		dataType : "html",
		cache : false,
	
		success : function(data) {
			
			$(ui).html(data);
			$('.light-box').css('display', 'block');
			$('.user-profile').css('display', 'block');
			$('.profile-content').css('height', 'auto');

			FB.Canvas.getPageInfo(function(info) {
				$('.user-profile').css('top', info.scrollTop - 100);
			});

			if (refresh) {
				$('.profile-content').animate({
					height : 'toggle',
				//top:'20px'
				}, 500, function() {

				});
			}
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});

}
function getFollowersList(targetname, target, limit, refresh) {
	refresh = typeof refresh !== 'undefined' ? refresh : true;
	ui = '.profile-content';
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/app/getfollowers/' + fanpageId + '?limit='
				+ limit + '&target=' + target + '&targetname=' + targetname
				+ '&mini=0',
		dataType : "html",
		cache : false,

		success : function(data) {

			$(ui).html(data);
			$('.light-box').css('display', 'block');
			$('.user-profile').css('display', 'block');
			$('.profile-content').css('height', 'auto');

			FB.Canvas.getPageInfo(function(info) {
				$('.user-profile').css('top', info.scrollTop - 100);
			});

			if (refresh) {
				$('.profile-content').animate({
					height : 'toggle',
				//top:'20px'
				}, 500, function() {

				});
			}
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});

}
function getMiniFollowingList(targetname, target) {
	ui = '#followinglist';

	$
			.ajax({
				type : "GET",
				url : serverUrl + '/app/app/getfollowing/' + fanpageId
						+ '?limit=5' + '&target=' + target + '&targetname='
						+ targetname + '&mini=1',
				dataType : "html",
				cache : false,
				async : false,
				beforeSend:function(){
					
					$(ui).html("<div style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
					
				},
				success : function(data) {

					$(ui).html(data);

				},
				error : function(xhr, errorMessage, thrownErro) {
					console.log(xhr.statusText, errorMessage);
				}
			});

}
function getMiniFollowersList(targetname, target) {

	ui = '#followerslist';
	$
			.ajax({
				type : "GET",
				url : serverUrl + '/app/app/getfollowers/' + fanpageId
						+ '?limit=5' + '&target=' + target + '&targetname='
						+ targetname + '&mini=1',
				dataType : "html",
				cache : false,
				async : false,
				
				beforeSend:function(){
					
					$(ui).html("<div style='text-align:center; padding:10px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
					
				},
				success : function(data) {

					$(ui).html(data);

				},
				error : function(xhr, errorMessage, thrownErro) {
					console.log(xhr.statusText, errorMessage);
				}
			});

}

function resizeCommentBox(o) {
	if (o.scrollHeight > 26) {
		o.style.height = "1px";
		o.style.height = (o.scrollHeight) + "px";
	}
	$('.post-button-container').css('display', 'block');
	$('.post-box').css('border-bottom', '0px');
}

function timeZoneTitle(time) {

	var date = new Date(time * 1000);
	return date.toLocaleString();
}

function timeZone(time) {

	var date = new Date(time * 1000);
	var now = new Date();
	var weekday = new Array(7);
	weekday[0] = "Sunday";
	weekday[1] = "Monday";
	weekday[2] = "Tuesday";
	weekday[3] = "Wednesday";
	weekday[4] = "Thursday";
	weekday[5] = "Friday";
	weekday[6] = "Saturday";
	apm = '';

	var hour = date.getHours();
	if (hour < 12) {
		apm = 'am';
	} else {
		apm = 'pm';
	}
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

	if (now.getYear() > date.getYear()) {
		return (date.toDateString());
	} else {
		if (now.getMonth() > date.getMonth()) {
			return (date.toDateString());
		} else {
			if (now.getDate() == date.getDate()) {
				if (now.getHours() == date.getHours()) {
					if (now.getMinutes() == date.getMinutes()) {
						return 'a few seconds ago';
					} else if (now.getMinutes() - date.getMinutes() == 1) {
						return now.getMinutes() - date.getMinutes()
								+ ' minute ago';
					} else {
						return now.getMinutes() - date.getMinutes()
								+ ' minutes ago';
					}
				} else if (now.getHours() - date.getHours == 1) {
					return (now.getHours() - date.getHours()) + ' hour ago';
				} else {
					return (now.getHours() - date.getHours()) + ' hours ago';
				}

			} else if (now.getDate() - date.getDate() == 1) {
				return 'Yesterday at ' + hour + ':' + min + '' + apm;
			} else if (now.getDate() - date.getDate() <= 3) {
				return weekday[date.getDay()] + ' at ' + hour + ':' + min + ''
						+ apm;
			} else {
				return (date.toDateString());
			}

		}
	}

}

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
