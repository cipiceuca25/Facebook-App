
	$(document).ready(function() {
		
		FB.api(fanpageId , function(response){
			 if (!response || response.error) {
			 	
		 	 }else{
		 		 var x = 0;
		 		 try{
		 			 y = response.cover.offset_y;
		 		 }catch(err){
		 			 y = 0;
		 		 }
		 		 
		 		 if (y > 35){
				 x = -1*(parseInt(y) + 50);
		 	 	 }else{
		 	 		 x = 0;
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
	tfb = false;
	ffb = true;
	ttb = true;
	tcb = true;
	tfdb = true;
	

	
	

	
	
	

	$(document).on('mouseover', 'a[rel=popover]', function () {
		popover($(this));
		
		if ( $(this).data('isPopoverLoaded') == true ) { return; }
		  $(this).data('isPopoverLoaded', true).popover({delay: { show: 300, hide: 100 } , placement: $(this).attr('data-placement')}).trigger('mouseover');
		
		
		  
	});
	
	
	
	
	
	function popover(x){
		//alert ('getting info for '+ id);
		$.ajax({
    		type: "GET",
    		url:  serverUrl +'/app/app/popoverprofile/'+ fanpageId +'?facebook_user_id=' + $(x).attr('data-userid'),
    		dataType: "html",
    		cache: false,
    		async: false,
    		success: function( data ) {
    			$(x).attr('data-content', data);
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
    		}
    	});
	}
	
	
	$(document).on('mouseover', 'a[rel=tooltip]', function () {
		  if ( $(this).data('isTooltipLoaded') == true ) { return; }
		  $(this).data('isTooltipLoaded', true).tooltip({
				'placement' : 'left'   
			}).trigger('mouseover');
		});
	/*
	$('a[rel=tooltip]').on("mouseover",function(){
		$(this).tooltip({
			'placement' : 'left'   
		});	
	});*/

	
	 
	$('.badge-Following').live("mouseover", function(){
		$(this).text('Unfollow');

	});
	$('.badge-Following').live("mouseleave", function(){
		$(this).text('Following');
	});
	
	$('.badge-Follower').live("mouseover", function(){
		$(this).text('Follow');
	});
	$('.badge-Follower').live("mouseleave", function(){
		$(this).text('Follower');
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

		//if($('#news-feed').hasClass('active')){
			
			//getNewsfeed('#news-feed');
			//getFancrankfeed('all');getTopPost();
			//alert("top class reload");
		//}
		//if($('#leaderboard').hasClass('active')){
			//getLeaderboard('#leaderboard');
			//getLatestPost();
			//alert("top class reload");
		//}
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

	$('#post_post a').click(function (e) {
		  e.preventDefault();
		  $(this).tab('show');
	});
	
	$('#post_photo a').click(function (e) {
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
		
		
		$('.social.comment.'+post_id).css('display', 'none');
		
	
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
	
	function comment_feed2(post_id, type, limiter, total, toggle){
		ui = '#postn_' + post_id;
		//alert(ui);
		getFeedComment( ui, post_id, type, limiter, total, toggle);
		
		$('.social.commentn.'+post_id).css('display', 'none');

		
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
    			
    			if (post_type == 'comment'){
    				if ((num== null)||(num==0)){
        				$('.like_'+post_id).html('1');
        				//alert('.social.like.'+post_id);
        				
        			}else{
        				$('.like_'+post_id).html((parseInt(num)+1));
        				
        			}
    			}else{
    			if ((num== null)||(num==0)){
    				$('.like_'+post_id).html('1 person');
    				//alert('.social.like.'+post_id);
    				
    			}else{
    				$('.like_'+post_id).html((parseInt(num)+1) + ' people');
    				
    			}
    			}
    			$('.social.like.'+post_id).css('display', 'block');
    			
    			
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
    			if (post_type == 'comment'){
    				if (num == 2){
        				$('.like_'+post_id).html('1');
        			}else{
        				$('.like_'+post_id).html((parseInt(num)-1));
        			}
    			}else{
    			if (num == 2){
    				$('.like_'+post_id).html('1 person');
    			}else{
    				$('.like_'+post_id).html((parseInt(num)-1) + ' people');
    			}
    			}
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
    			getMiniFollowingList(userName,userId);
    			getMiniFollowersList(userName, userId);
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


    function getRelation(target, ui){
    	
    	$.ajax({
    		type: "GET",
    		url: serverUrl +'/app/user/'+ userId + '/relation/?target_id=' + target + '&fanpage_id=' + fanpageId,
    		dataType: "html",
    		cache: false,
    		success: function( data ) {
    			$('.'+ui).html('<div class="badge badge-'+data+'">'+data+'</div>');
			
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
	    			getRelation(target, ui);
	    			//alert(relation);
	    			$('.'+ui).attr('onclick',"unfollow('"+target+"','"+name+"','"+ui+"')" );
	    			//$('.'+ui).html('<div class="badge badge-'+relation+'">'+relation+'</div>');
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
    			getRelation(target, ui);
    		
    			$('.'+ui).attr('onclick',"follow('"+target+"','"+name+"','"+ui+"')" );
    			//$('.'+ui).html('<div class="badge badge-'+relation+'">'+relation+'</div>');
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

				num=$('.comment_'+postid).html();

    				$('.comment_'+postid).html((parseInt(num)+1));
    				
    			
    			
    				
				comment_feed(postid, type, num+1, num+1,  false);
				
				
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
				num=$('.comment_'+postid).html();
    			$('.comment_'+postid).html((parseInt(num)+1));
				comment_bubble(postid, num+1, false);
				
				}
			});
	}
	function getFollowingList(targetname, target, limit, refresh){
		ui = '.profile-content';
		refresh = typeof refresh !== 'undefined' ? refresh : true;
		$.ajax({
    		type: "GET",
    		url: serverUrl +'/app/app/getfollowing/'+ fanpageId +'?limit='+limit + '&target=' +target + '&targetname=' +targetname + '&mini=0',
    		dataType: "html",
    		cache: false,
    		success: function( data ) {
    			
    			$(ui).html(data);
    			$('.light-box').css('display','block');
    			$('.user-profile').css('display', 'block');
    			$('.profile-content').css('height', 'auto');
    			 
    			FB.Canvas.getPageInfo(
    				    function(info) {
    				    	$('.user-profile').css('top', info.scrollTop - 100);
    				    }
    				);
    			
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
	function getFollowersList(targetname, target, limit, refresh){
		refresh = typeof refresh !== 'undefined' ? refresh : true;
		ui = '.profile-content';
		$.ajax({
    		type: "GET",
    		url: serverUrl +'/app/app/getfollowers/'+ fanpageId +'?limit='+limit + '&target=' +target + '&targetname=' +targetname + '&mini=0',
    		dataType: "html",
    		cache: false,
    		success: function( data ) {
    			
    			$(ui).html(data);
    			$('.light-box').css('display','block');
    			$('.user-profile').css('display', 'block');
    			$('.profile-content').css('height', 'auto');
    			
    			FB.Canvas.getPageInfo(
    				    function(info) {
    				    	$('.user-profile').css('top', info.scrollTop - 100);
    				    }
    				);
    			
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
	function getMiniFollowingList(targetname, target){
		ui = '#followinglist';

		$.ajax({
    		type: "GET",
    		url: serverUrl +'/app/app/getfollowing/'+ fanpageId +'?limit=5' + '&target=' +target + '&targetname=' +targetname + '&mini=1',
    		dataType: "html",
    		cache: false,
    		async:false,
    		success: function( data ) {
    			
    			$(ui).html(data);
    	
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
    		}
    	});
	
	}
	function getMiniFollowersList(targetname, target){

		ui = '#followerslist';
		$.ajax({
    		type: "GET",
    		url: serverUrl +'/app/app/getfollowers/'+ fanpageId +'?limit=5' + '&target=' +target + '&targetname=' +targetname + '&mini=1',
    		dataType: "html",
    		cache: false,
    		async:false,
    		success: function( data ) {
    			
    			$(ui).html(data);
    	
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

	
	function upload(form, action_url, div_id, target_id, target){
		fileUpload(form, action_url, div_id);
		addActivities('post-photo', userName, fanpageId, target_id, target);
		setFeed=0;
		getFancrankfeed(setFeed);
	}
	
	function fileUpload(form, action_url, div_id) {
	    
		
		// Create the iframe...
	    var iframe = document.createElement("iframe");
	    iframe.setAttribute("id", "upload_iframe");
	    iframe.setAttribute("name", "upload_iframe");
	    iframe.setAttribute("style", "width: 100; height: 50; border: 1; display:block");
	 
	    // Add to document...
	    form.parentNode.appendChild(iframe);
	    window.frames['upload_iframe'].name = "upload_iframe";
	 
	    iframeId = document.getElementById("upload_iframe");
	 
	    // Add event...
	    var eventHandler = function () {
	 
	            if (iframeId.detachEvent) iframeId.detachEvent("onload", eventHandler);
	            else iframeId.removeEventListener("load", eventHandler, false);
	 
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
	 
	    if (iframeId.addEventListener) iframeId.addEventListener("load", eventHandler, true);
	    if (iframeId.attachEvent) iframeId.attachEvent("onload", eventHandler);
	 
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
	
	
	