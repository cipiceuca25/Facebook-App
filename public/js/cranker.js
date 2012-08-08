
	$(document).ready(function() {
		
		FB.api(fanpageId , function(response){
			 if (!response || response.error) {
			 	
		 	 }else{
		 		 var x = 0;
		 			if(!(parseInt(response.cover.offset_y)==34)){
				 	 	try{
				 	 		var x = -1*(parseInt(response.cover.offset_y) + 50);
				 	 	}catch(err){
							
				 	 	}
		 	 		}
			 	 	//alert(x);
			 	 	try{
			 		$('#logo').html('<img src =" ' + response.cover.source + '"style=" top:'+ x +'px" />' );
			 	 	}catch(err){
						
				 	}
			 }

		});
		//alert('getting top posts');
		
		getNewsfeed('#news-feed');
		
	});
	
	setFeed='All';
	feedLimit = 0;
	mtfb= true;
	tfb = true;
	ffb = true;
	ttb = true;
	tcb = true;
	tfdb = true;

	$('a[title]').live("mouseover", function(){
		$(this).tooltip({ placement: 'left'});	
	});
	
	$('.badge-Following').live("mouseover", function(){
		$(this).text('Unfollow');
	});
	$('.badge-Following').live("mouseleave", function(){
		$(this).text('Following');
	});
	/*
	$('.badge-Friends').live("mouseover", function(){
		$(this).text('Unfollow');
	});
	$('.badge-Friends').live("mouseleave", function(){
		$(this).text('Friends');
	});
		*/
	function feedbackAnimation(ui){

		
		
		 $(ui).css({'top':mouseY-16,'left':mouseX-62,'opacity':'1', 'display':'block'});
		 $(ui).animate({
			 	opacity: '0',
				top: "-=30px"
			   },1000, function(){ 
			});
	}
	
	var mouseX;
	var mouseY;
	$(document).mousemove( function(e) {
		   mouseX = e.pageX; 
		   mouseY = e.pageY;
	}); 	
	$('#mini-top-fans-btn').live('click', function() {
		
		if ((mtfb)==true){
		$('#mini-top-fans-btn').text('- Close');
		}else{
			$('#mini-top-fans-btn').text("+ More");
		}
		mtfb = !mtfb;
	});
	$('#top-fans-btn').live('click', function() {
		
		if ((tfb)==true){
		$('#top-fans-btn').text('- Close');
		}else{
			$('#top-fans-btn').text("+ More");
		}
		tfb = !tfb;
	});
	
	$('#fan-favorite-btn').live( 'click', function() {
		if ((ffb)==true){
		$('#fan-favorite-btn').text('- Close');
		}else{
			$('#fan-favorite-btn').text("+ More");
		}
		ffb = !ffb;
	});
	
	$('#top-talker-btn').live( 'click', function() {
		if ((ttb)==true){
		$('#top-talker-btn').text('- Close');
		}else{
			$('#top-talker-btn').text("+ More");
		}
		ttb = !ttb;
	});
	
	$('#top-clicker-btn').live( 'click', function() {
		if ((tcb)==true){
		$('#top-clicker-btn').text('- Close');
		}else{
			$('#top-clicker-btn').text("+ More");
		}
		tcb = !tcb;
	});

	$('#top-followed-btn').live( 'click', function() {
		if ((tfdb)==true){
		$('#top-followed-btn').text('- Close');
		}else{
			$('#top-followed-btn').text("+ More");
		}
		tfdb = !tfdb;
	});
    function closeProfile(){
    	$('.light-box').css('display','none');
		$('.user-profile').css('display', 'none');
		//$('.top-border').css('top','350px');
		//$('.bottom-border').css('top','350px');
		$('.profile-content').css('top','350px');
		//$('.top-border').css('display','none');
		//$('.bottom-border').css('display','none');
		$('.profile-content').css('display','none');
		$('.profile-content').css('height','660px');

		if($('#news-feed').hasClass('active')){
			
			//getNewsfeed('#news-feed');
			//getFancrankfeed('all');getTopPost();
			//alert("top class reload");
		}
		if($('#leaderboard').hasClass('active')){
			getLeaderboard('#leaderboard');
			//getLatestPost();
			//alert("top class reload");
		}
    	$('.profile-content').html('');
    	$('#moreComment').removeAttr('postid');
    }
    
	$('.light-box').live('click',function(){
		closeProfile();
	});
	
	$('#news-feed a').click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	});

	$('#fan-favorite a').click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	});
	
	$('#profile a').click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	});
	
	$('#achievements a').click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	});
	
	$('#redeem a').click(function (e) {
		  e.preventDefault();
		  $(this).tab('show');
		});
	
	$('#newsfeed-tab').live('click',function(){
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
		//alert('getting top posts');
	});
	
	
	$('#leaderboard-tab').live('click',function(){
		mtfb = true;
		tfb = true;
		ffb = true;
		ttb = true;
		tcb = true;
		tfdb = true;
		getLeaderboard('#leaderboard');
		
		$('#profile').html('');
		$('#achievements').html('');
		$('#newsfeed').html('');
		$('#redeem').html('');
		//$('.bubble').html('');
		
	});
	
	$('#profile-tab').live('click', function(){
	
		 getMyProfile('#profile');
		 $('#leaderboard').html('');
		 $('#achievements').html('');
		 $('#newsfeed').html('');
		 $('#redeem').html('');
	});
	
	$('#achievements-tab').live('click', function(){
		getAwards('#achievements');
		$('#leaderboard').html('');
		$('#profile').html('');
		$('#newsfeed').html('');
		$('#redeem').html('');
	});

	$('#redeem-tab').live('click', function(){
		getRedeem('#redeem');
		$('#leaderboard').html('');
		$('#profile').html('');
		$('#newsfeed').html('');
		$('#achievements').html('');
		});
	
	function ImgError(source){
	    source.src = "/img/profile-picture.png";
	    source.onerror = "";
	    return true;
	}
		
	
	
	function userProfile(user, refresh){
		refresh = typeof refresh !== 'undefined' ? refresh : true;
		getUserProfile('.profile-content', user);
		height=0;
		$('.light-box').css('display','block');
		$('.user-profile').css('display', 'block');
			
		
		$('.profile-content').css('height', 'auto');
	
	
		
		FB.Canvas.getPageInfo(
			    function(info) {
			    	$('.user-profile').css('top', info.scrollTop - 100);
			    }
			);
		
		//alert(f_clientHeight());
		if(refresh){
		
			$('.profile-content').animate({
				height:'toggle',
				top:'20px'
			   },500, function(){
				   
			});
		}
		
		/*
		$('.top-border').animate({
		    height: 'toggle',
			top: '0px'
		  }, 1000, function() {
		    // Animation complete.
		  });
	
		$('.bottom-border').animate({
		    height: 'toggle',
		    top: '0px'
		  }, 1000, function() {
		    // Animation complete.
		  });
		  */
	}

	function comment_bubble(post_id, limiter, open){
		//alert(open);
		getBubbleComment('.profile-content', post_id, limiter);
		
		if(open){
			$('.light-box').css('display','block');
			$('.user-profile').css('display', 'block');
		
			/*$('.top-border').animate({
			    height: 'toggle',
				top: '0px'
			  }, 1000, function() {
			    // Animation complete.
			  });
		
			$('.bottom-border').animate({
			    height: 'toggle',
			    top: '0px'
			  }, 1000, function() {
			    // Animation complete.
			  });*/
			FB.Canvas.getPageInfo(
				    function(info) {
				    	$('.user-profile').css('top', info.scrollTop - 200);
				    }
				);
			$('.profile-content').css('height', 'auto');
			//$('.profile-content').css('height', '1100px');
			$('.profile-content').animate({
				height:'toggle',
				top:'20px'
			   },500, function(){
				   
			});
		}
	}

	
	function comment_feed(post_id, type, limiter, total, toggle){
		ui = '#post_' + post_id;
		//alert(ui);
		getFeedComment( ui, post_id, type, limiter, total, toggle);

		//$('.light-box').css('display','block');
		//$('.user-profile').css('display', 'block');
	
		/*$('.top-border').animate({
		    height: 'toggle',
			top: '0px'
		  }, 1000, function() {
		    // Animation complete.
		  });
	
		$('.bottom-border').animate({
		    height: 'toggle',
		    top: '0px'
		  }, 1000, function() {
		    // Animation complete.
		  });*/
		
	}
	

	 

	 
  /*  $(document).ready(function() {

    	var tabs = $( "#pageTabs" ).tabs({
    		load: function(event, ui) {
    			$(ui.panel).delegate('a', 'click', function(event) {
    					$(ui.panel).load(this.href);
    					event.preventDefault();
    				});
    			}
    		});

   
	$(document).ready(function() {		
    		$('#pageTabs').bind('tabsselect', function(event, ui) {
    			$('#pageTabs a').removeAttr('id');
    			$(ui.tab).attr('id', 'currentPageTab').css({'outline': 'none'});
    			
    			//var url = $.data(ui.tab, 'load.tabs');
    			
    		});
    	});*/

	
	
	
	function getUserProfile(ui, target) {
		$.ajax({
			type: "GET",
			url: serverUrl +'/app/app/userprofile/'+ fanpageId +'/?target=' + target + '&facebook_user_id='+ userId,
			dataType: "html",
			cache: false,
			async: true,
			success: function( data ) {
		
				$(ui).html(data);
				
			},	
			error: function( xhr, errorMessage, thrownErro ) {
				console.log(xhr.statusText, errorMessage);
			}
		});
	
	}


	function getFeedComment(ui, post_id,type, limiter, total, toggle){
		$.ajax({
			type: "GET",
			url: serverUrl +'/app/app/fancrankfeedcomment/'+ fanpageId + '?post_id=' +post_id + '&post_type=' + type +'&limit=' + limiter + '&total=' +total,

			dataType: "html",
			cache: false,
			async: false,
			success: function( data ) {
			
				$(ui).html(data);
				
			},	
			error: function( xhr, errorMessage, thrownErro ) {
				console.log(xhr.statusText, errorMessage);
			}
		});


		//alert('are we animating?' + toggle);
		if(toggle){
			$(ui).animate({
			    height: 'toggle',
			
			  }, 1000, 'swing', function() {
			    // Animation complete.
			  });
			  
		}else{
			//alert('hurray it didnt f-ing close');
		}
		

		
		
	}

	
		
	function getBubbleComment(ui, post_id,limiter) {
		      
		$.ajax({
			type: "GET",
			url: serverUrl +'/app/app/comment/'+ fanpageId + '?post_id=' +post_id + '&limit=' + limiter,

			dataType: "html",
			cache: false,
			async: false,
			success: function( data ) {
			
				$(ui).html(data);
				
			},	
			error: function( xhr, errorMessage, thrownErro ) {
				console.log(xhr.statusText, errorMessage);
			}
		});
	}

	
	
	function getNewsfeed(ui) {
		$.ajax({
			type: "GET",
			url: serverUrl +'/app/app/newsfeed/'+ fanpageId +'?facebook_user_id=' + userId,
			dataType: "html",
			cache: false,
			async: true,
			success: function( data ) {
				$(ui).html(data);
				getFancrankfeed(setFeed);
				getTopFan();
				getTopPost();
			},	
			error: function( xhr, errorMessage, thrownErro ) {
				console.log(xhr.statusText, errorMessage);
			}
		});
	}

	function getTopPost(){
		//alert('getting top postsss');
		ui = '#toppost';
		
		$.ajax({
			type: "GET",
			url: serverUrl +'/app/app/gettoppost/'+ fanpageId , 
			dataType: "html",
			cache: false,
			async: false,
			success: function( data ) {
		
				$(ui).html(data);
			},	
			error: function( xhr, errorMessage, thrownErro ) {
				console.log(xhr.statusText, errorMessage);
			}
		});
	}
/*
	function getLatestPost(){
		//alert('getting top postsss');
		ui = '#latestpost';
		$.ajax({
			type: "GET",
			url:  serverUrl +'/app/app/getlatestpost/'+ fanpageId,  
			dataType: "html",
			cache: false,
			async: false,
			success: function( data ) {
				
				$(ui).html(data);
			},	
			error: function( xhr, errorMessage, thrownErro ) {
				console.log(xhr.statusText, errorMessage);
			}
		});
		
	}
*/
	function like(post_id, post_type, target_id, target_name){
		$.ajax({
    		type: "GET",
    		url: serverUrl + '/app/user/' + userId + '/likes/?post_id=' + post_id + '&fanpage_id='+ fanpageId + '&post_type=' + post_type + '&access_token=' + userAccessToken,
    		dataType: "html",
    		cache: false,
    		success: function( data ) {
				//alert("target followed")
				//alert(post_id + 'liked');
    			feedbackAnimation('#like-animation');
    			addActivities('like-'+post_type, userName, post_id, target_id, target_name );
    			
    			num=$('.like_'+post_id).html();
    			$('.like_'+post_id).html((parseInt(num)+1));
    			$('.like_control_'+post_id).attr('onclick', "unlike('"+post_id+"','"+post_type+"','"+target_id+"','"+target_name+"')");
    			$('.like_control_'+post_id).html('Unlike');
    			
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
    		}
    	});		
	}

	
	function unlike(post_id, post_type, target_id, target_name){
		$.ajax({
    		type: "GET",
    		url: serverUrl + '/app/user/' + userId + '/unlike/?post_id=' + post_id + '&fanpage_id='+ fanpageId + '&post_type=' + post_type + '&access_token=' + userAccessToken,
    		dataType: "html",
    		cache: false,
    		success: function( data ) {
				//alert("target followed")
				//alert(post_id + 'liked');
    			feedbackAnimation('#unlike-animation');
    			addActivities('unlike-'+post_type,userName, post_id, target_id, target_name);
    			
    			num=$('.like_'+post_id).html();
    			$('.like_'+post_id).html((parseInt(num)-1));
    			$('.like_control_'+post_id).attr('onclick', "like('"+post_id+"','"+post_type+"','"+target_id+"','"+target_name+"')");
    			$('.like_control_'+post_id).html('Like');
    			
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
    		}
    	});		
	}

	
	function getFancrankfeed(view){
		ui = '#fancrankfeed';
		feedLimit +=6;
		setFeed=view;
		//alert(baseAppUrl+'/fancrankfeed?fanpage_id='+ fanpageId + '&facebook_user_id=' + userId + '&access_token=' +accessToken + '&viewAs=' +view);
		$.ajax({
			type: "GET",
			url: serverUrl +'/app/app/fancrankfeed/'+ fanpageId + '?viewAs=' +view + '&limit=' + feedLimit, 
			dataType: "html",
			cache: false,
			async: false,
			success: function( data ) {
			
				$(ui).html(data);
				
			},	
			error: function( xhr, errorMessage, thrownErro ) {
				console.log(xhr.statusText, errorMessage);
			}
		});
	}
	
	  function getTopFan() {
		     
	    	$.ajax({
	    		type: "GET",
	    		url: serverUrl +'/app/app/topfan/'+ fanpageId +'?facebook_user_id=' + userId,
	    		dataType: "html",
	    		cache: false,
	    		async: true,
	    		success: function( data ) {
	    			$('#topfan').html(data);
	    			//getLatestPost();
	    		},	
	    		error: function( xhr, errorMessage, thrownErro ) {
	        		alert(url);
	    			console.log(xhr.statusText, errorMessage);
	    			
	    		}
	    	});
	    }
	
    function getLeaderboard(ui) {
     
    	$.ajax({
    		type: "GET",
    		url: serverUrl +'/app/app/leaderboard/'+ fanpageId +'?facebook_user_id=' + userId,
    		dataType: "html",
    		cache: false,
    		async: true,
    		success: function( data ) {
    			$(ui).html(data);
    			//getLatestPost();
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
        		alert(url);
    			console.log(xhr.statusText, errorMessage);
    			
    		}
    	});
    }
    
    function getMyProfile(ui) {
    	$.ajax({
    		type: "GET",
    		url:  serverUrl +'/app/app/myprofile/'+ fanpageId +'?facebook_user_id=' + userId,
    		dataType: "html",
    		cache: false,
    		async: true,
    		success: function( data ) {
    			$(ui).html(data);
    			getRecentActivities('#recent_activities');
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
    		}
    	});
    }

    function getRedeem(ui) {
    	$.ajax({
    		type: "GET",
    		url:  serverUrl +'/app/app/redeem/'+ fanpageId +'?facebook_user_id=' + userId,
    		dataType: "html",
    		cache: false,
    		async: true,
    		success: function( data ) {
    			$(ui).html(data);
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
    		}
    	});
    }	
    
    function getAwards(ui) {
    	$.ajax({
    		type: "GET",
    		url:  serverUrl +'/app/app/awards/'+ fanpageId +'?facebook_user_id=' + userId,
    		dataType: "html",
    		cache: false,
    		async: true,
    		success: function( data ) {
    			$(ui).html(data);
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
    		}
    	});
    }	


	function getRecentActivities(ui){
		$.ajax({
    		type: "GET",
    		url:  serverUrl +'/app/app/recentactivities/' + fanpageId,
    		dataType: "html",
    		cache: false,
    		async: false,
    		success: function( data ) {
    			$(ui).html(data);
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
    		}
    	});
	}

    
	function follow(target, name, ui){
		if (target != userId){
			
		
			$.ajax({
	    		type: "GET",
	    		url: serverUrl +'/app/user/'+ userId + '/follow/?subscribe_to=' + target + '&facebook_user_id=' + userId + '&fanpage_id='+ fanpageId + '&subscribe_ref_id=1',
	    		dataType: "html",
	    		cache: false,
	    		success: function( data ) {
					//alert("target followed")
	    			addActivities('follow',userName, target, target, name );
					//getUserProfile('.profile-content', target);
	    		
	    			$('.'+ui).attr('onclick',"unfollow('"+target+"','"+name+"','"+ui+"')" );
	    			$('.'+ui).html('<div class="badge badge-Following">Following</div>');
					feedbackAnimation('#follow-animation');
	    		},	
	    		error: function( xhr, errorMessage, thrownErro ) {
	    			console.log(xhr.statusText, errorMessage);
	    		}
	    	});
		}
    }	
	
	function unfollow(target, name, ui){
		$.ajax({
    		type: "GET",
    		url: serverUrl +'/app/user/'+ userId + '/unfollow/?subscribe_to=' + target + '&facebook_user_id=' + userId + '&fanpage_id='+ fanpageId + '&subscribe_ref_id=1',
    		dataType: "html",
    		cache: false,
    		success: function( data ) {
				//alert("target unfollowed")
    			addActivities('unfollow', userName, target, target, name );
				//getUserProfile('.profile-content', target);
    			$('.'+ui).attr('onclick',"follow('"+target+"','"+name+"','"+ui+"')" );
    			$('.'+ui).html('<div class="badge badge-Follow">Follow</div>');
    			feedbackAnimation('#unfollow-animation');
				
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
    		}
    	});
    }	



	function addActivities(act_type, owner_name,  event, target_id, target_name){
		$.ajax({
    		type: "GET",
    		url: serverUrl +'/app/user/'+ userId + '/addactivity/?owner_name='+owner_name +'&activity_type=' + act_type + '&event='+event+ '&fanpage_id=' + fanpageId + '&target_id=' + target_id + '&target_name=' + target_name,
    		dataType: "html",
    		cache: false,
    		success: function( data ) {
    			
    			//alert('act saved');
    			//$(ui).html(data);
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
    			alert('issue with activity save');
    		}
    	});

	}
    
	function colorChange(choice){
		$.ajax({
    		type: "GET",
    		url: serverUrl +'/app/user/'+ userId + '/color/?choice=' + choice + '&fanpage_id=' + fanpageId,
    		dataType: "html",
    		cache: false,
    		success: function( data ) {
    			window.location.reload();
				
    			//$(ui).html(data);
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
    		}
    	});


	}

	function post(target_id, target){
	
		FB.api('/'+ fanpageId+'/feed' , 'post' , { 'message': $('#post_box').val() , 'access_token' : userAccessToken},  function(response) {
			if (!response || response.error) {
				alert(response.error.message);
				} else {
				//alert('Post ID: ' + response.id);
				$('#post_box').val('');
				addActivities('post-status', userName, fanpageId, target_id, target);
			
				setFeed=0;
				getFancrankfeed(setFeed);
				}
			});

	}
	
	function commentSubmit(postid, type,  message, target_id, target, postcommentcount){
		var m = '#'+message;
	
		//alert(userAccessToken);
		FB.api('/'+ postid + '/comments' , 'post' , { 'message': $(m).val() , 'access_token' : userAccessToken},  function(response) {
			if (!response || response.error) {
				alert(response.error.message);
				} else {
				//alert('Post ID: ' + response.id);
				$(m).val('');
				addActivities('comment-'+type, userName, postid, target_id, target);
	
				comment_feed(postid, 5, postcommentcount +1, false);
				}
			});
	}
	
	function commentpopupSubmit(postid, type,  message, target_id, target){
		var m = '#'+message;
	
		//alert(userAccessToken);
		FB.api('/'+ postid + '/comments' , 'post' , { 'message': $(m).val() , 'access_token' : userAccessToken},  function(response) {
			if (!response || response.error) {
				alert(response.error.message);
				} else {
				//alert('Post ID: ' + response.id);
				$(m).val('');
				addActivities('comment-'+type, userName, postid, target_id, target);
				comment_bubble(postid, 5, false);
				
				}
			});
	}
	
	function getFollowingList(targetname, target, limit, refresh){
		ui = '.profile-content';
		refresh = typeof refresh !== 'undefined' ? refresh : true;
		$.ajax({
    		type: "GET",
    		url: serverUrl +'/app/app/getfollowing/'+ fanpageId +'?limit='+limit + '&target=' +target + '&targetname=' +targetname,
    		dataType: "html",
    		cache: false,
    		success: function( data ) {
    			
    			$(ui).html(data);
    			$('.light-box').css('display','block');
    			$('.user-profile').css('display', 'block');
    			$('.profile-content').css('height', 'auto');
    			 
    			height = $(window).scrollTop() + 20;
    			if(refresh){
	    			$('.profile-content').animate({
	    				height:'toggle',
	    				top:height+'px'
	    			   },500, function(){
	    				   
	    			});
    			}
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
    		}
    	});
	
	}
	function getFollowersList(targetname, target, limit, refresh){
		refresh = typeof refresh !== 'undefined' ? refresh : true;
		ui = '.profile-content';
		$.ajax({
    		type: "GET",
    		url: serverUrl +'/app/app/getfollowers/'+ fanpageId +'?limit='+limit + '&target=' +target + '&targetname=' +targetname,
    		dataType: "html",
    		cache: false,
    		success: function( data ) {
    			
    			$(ui).html(data);
    			$('.light-box').css('display','block');
    			$('.user-profile').css('display', 'block');
    				
    			
    			$('.profile-content').css('height', 'auto');
    			  
    			if(refresh){
	    			$('.profile-content').animate({
	    				height:'toggle',
	    				top:'20px'
	    			   },500, function(){
	    				   
	    			});
    			}
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
    		}
    	});
		
	}
	
	/*
	function getFriendsList(targetname, target, limit, refresh){
		ui = '.profile-content';
		refresh = typeof refresh !== 'undefined' ? refresh : true;
		$.ajax({
    		type: "GET",
    		url: serverUrl +'/app/app/getfriends/'+ fanpageId +'?limit='+limit + '&target=' +target + '&targetname=' + targetname,
    		dataType: "html",
    		cache: false,
    		success: function( data ) {
    			
    			$(ui).html(data);
    			$('.light-box').css('display','block');
    			$('.user-profile').css('display', 'block');
    				
    			
    			$('.profile-content').css('height', 'auto');
    			  
    			if(refresh){
	    			$('.profile-content').animate({
	    				height:'toggle',
	    				top:'20px'
	    			   },500, function(){
	    				   
	    			});
    			}
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
    		}
    	});
		
	}
	
	*/
	function resizeCommentBox(o){
		o.style.height = "1px";
	    o.style.height = (10+o.scrollHeight)+"px";
	}
	