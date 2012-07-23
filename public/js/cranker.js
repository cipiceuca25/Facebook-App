
	$(document).ready(function() {
		
		FB.api(fanpageId , function(response){
			 if (!response || response.error) {
			 	
		 	 }else{
			 	 	try{
			 	 		var x = -1*(parseInt(response.cover.offset_y) + 50);
			 	 	}catch(err){
						var x = 0;
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
	tfb = true;
	ffb = true;
	ttb = true;
	tcb = true;

	$('a[title]').live("mouseover", function(){
		$(this).tooltip({ placement: 'left'});	
	});
	
	$('#top-fans-btn').live('click', function() {
		
		if ((tfb)==true){
		$('#top-fans-btn').text('- Close');
		}else{
			$('#top-fans-btn').text("+ More");
		}
		tfb = !tfb;
	})
	
	$('#fan-favorite-btn').live( 'click', function() {
		if ((ffb)==true){
		$('#fan-favorite-btn').text('- Close');
		}else{
			$('#fan-favorite-btn').text("+ More");
		}
		ffb = !ffb;
	})
	
	$('#top-talker-btn').live( 'click', function() {
		if ((ttb)==true){
		$('#top-talker-btn').text('- Close');
		}else{
			$('#top-talker-btn').text("+ More");
		}
		ttb = !ttb;
	})
	
	$('#top-clicker-btn').live( 'click', function() {
		if ((tcb)==true){
		$('#top-clicker-btn').text('- Close');
		}else{
			$('#top-clicker-btn').text("+ More");
		}
		tcb = !tcb;
	})

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
		if($('#top-fans').hasClass('active')){
			getTopfans('#top-fans');
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
	})

	$('#fan-favorite a').click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	})
	
	$('#profile a').click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	})
	
	$('#achievements a').click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	})
	
	$('#newsfeed-tab').live('click',function(){
		feedLimit = 0;
		getNewsfeed('#news-feed');
		
		$('#top-fans').html('');
		$('#profile').html('');
		$('#achievements').html('');
		//alert('getting top posts');
	});
	
	
	$('#topfans-tab').live('click',function(){
		getTopfans('#top-fans');
		
		$('#profile').html('');
		$('#achievements').html('');
		$('#newsfeed').html('');
		//$('.bubble').html('');
		
	});
	
	$('#profile-tab').live('click', function(){
	
		 getMyProfile('#profile');
		 $('#top-fans').html('');
		 $('#achievements').html('');
		 $('#newsfeed').html('');

	});
	
	$('#achievements-tab').live('click', function(){
		getAwards('#achievements') 
		$('#top-fans').html('');
		$('#profile').html('');
		$('#newsfeed').html('');
		});

	
	
	function userProfile(user){
		getUserProfile('.profile-content', user);
		
		$('.light-box').css('display','block');
		$('.user-profile').css('display', 'block');
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
		
		$('.profile-content').css('height', 'auto');
		  
		$('.profile-content').animate({
			height:'toggle',
			top:'20px'
		   },500, function(){
			   
		});
	
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
			$('.profile-content').css('height', 'auto');
			//$('.profile-content').css('height', '1100px');
			$('.profile-content').animate({
				height:'toggle',
				top:'20px'
			   },500, function(){
				   
			});
		}
	}

	
	function comment_feed(post_id, limiter, total, toggle){
		ui = '#post_' + post_id;
		//alert(ui);
		getFeedComment( ui, post_id,limiter, total, toggle);

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


	function getFeedComment(ui, post_id, limiter, total, toggle){
		$.ajax({
			type: "GET",
			url: serverUrl +'/app/app/fancrankfeedcomment/'+ fanpageId + '?post_id=' +post_id + '&limit=' + limiter + '&total=' +total + '&access_token=' + accessToken,

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
			url: serverUrl +'/app/app/comment/'+ fanpageId + '?post_id=' +post_id + '&limit=' + limiter +'&access_token=' + accessToken,

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


	$('#moreComments').live('click',function(){
		FB.api( $('#moreComments').attr('postid')+'/comments?limit=10&offset=' + $('#moreComments').attr('loaded') ,  function(response){
	    	   if (!response || response.error) {
	    		    alert('Error occured');
	    		  } else {
	    		    //alert(response.from.id);
		    		
	    			var limiter=10;
	    			try{
						var limit = response.data.length;
	    			}catch(err){
						var limit = 0;
	    			}

						//	alert(limit);
					if(limiter < limit){
						limit = limiter;
					}
					//alert(limit);
					//alert(limiter);
					for (i=0;i<limit; i++){	
						//alert(i);
						d = new Date(Date.parse(response.data[i].created_time));
						//alert(d);
						//alert(html);
						$('#post-list').append(
							'<li class="grey-light"><div class="photo"><a href=""><img class="face" width="30" height="30" src="https://graph.facebook.com/' 
							+ response.data[i].from.id+ '/picture"></a></div><div class="post"><div class="name"><a href="">' 
							+ response.data[i].from.name + '</a></div>' + response.data[i].message + 
							'</div><div class="social">' + '<a href="#" class="likes">Like</a>  Posted on <span class="date"> '
							+ d.toDateString() + '</span></div></li><br/>' 
							);			
					}

					var x = parseInt($('#moreComments').attr('loaded')) + 10;
					$('#moreComment').removeAttr('loaded');
					$('#moreComments').attr('loaded', x );
					//alert(response.data.length);
					//alert(limiter);


					try{
						var limit = response.data.length;
	    			}catch(err){
						var limit = 0;
	    			}
					if (limiter < limit){
						$('#moreComments').html('More +');
						$('#moreComments').attr('postid', post_id );						
					}else{
						$('#moreComments').html('');
					}
	    		  }
	 	});
	});	
	
	
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

	function like(post_id, post_type, target_id, target_name){
		$.ajax({
    		type: "GET",
    		url: serverUrl + '/app/user/' + userId + '/likes/?post_id=' + post_id + '&fanpage_id='+ fanpageId + '&post_type=' + post_type + '&access_token=' + userAccessToken,
    		dataType: "html",
    		cache: false,
    		success: function( data ) {
				//alert("target followed")
				//alert(post_id + 'liked');
    			alert('liked');
    			addActivities('like-'+post_type, post_id, target_id, target_name );
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
    			alert('unliked');
    			addActivities('unlike-'+post_type, post_id, target_id, target_name);
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
    		}
    	});		
	}

	
	function getFancrankfeed(view){
		ui = '#fancrankfeed';
		feedLimit +=10;
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
	
    function getTopfans(ui) {
     
    	$.ajax({
    		type: "GET",
    		url: serverUrl +'/app/app/topfans/'+ fanpageId +'?facebook_user_id=' + userId,
    		dataType: "html",
    		cache: false,
    		async: true,
    		success: function( data ) {
    			$(ui).html(data);
    			getLatestPost();
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

    
	function follow(target, name){
		$.ajax({
    		type: "GET",
    		url: serverUrl +'/app/user/'+ userId + '/follow/?subscribe_to=' + target + '&facebook_user_id=' + userId + '&fanpage_id='+ fanpageId + '&subscribe_ref_id=1',
    		dataType: "html",
    		cache: false,
    		success: function( data ) {
				//alert("target followed")
    			addActivities('follow', target, target, name );
				getUserProfile('.profile-content', target);
    			
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
    		}
    	});
    }	
	
	function unfollow(target, name){
		$.ajax({
    		type: "GET",
    		url: serverUrl +'/app/user/'+ userId + '/unfollow/?subscribe_to=' + target + '&facebook_user_id=' + userId + '&fanpage_id='+ fanpageId + '&subscribe_ref_id=1',
    		dataType: "html",
    		cache: false,
    		success: function( data ) {
				//alert("target unfollowed")
    			addActivities('unfollow', target, target, name );
				getUserProfile('.profile-content', target);
    		
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
    		}
    	});
    }	



	function addActivities(act_type, event, target_id, target_name){
		$.ajax({
    		type: "GET",
    		url: serverUrl +'/app/user/'+ userId + '/addactivity/?activity_type=' + act_type + '&event='+event+ '&fanpage_id=' + fanpageId + '&target_id=' + target_id + '&target_name=' + target_name,
    		dataType: "html",
    		cache: false,
    		success: function( data ) {
    			
    			alert('act saved');
    			//$(ui).html(data);
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			console.log(xhr.statusText, errorMessage);
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
    			window.location.reload()
				
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
				addActivities('post',fanpageId, target_id, target);
				}
			});

	}
	
	function commentSubmit(postid, message, target_id, target){
		var m = '#'+message;
	
		//alert(userAccessToken);
		FB.api('/'+ postid + '/comments' , 'post' , { 'message': $(m).val() , 'access_token' : userAccessToken},  function(response) {
			if (!response || response.error) {
				alert(response.error.message);
				} else {
				//alert('Post ID: ' + response.id);
				$(m).val('');
				addActivities('post-comment',postid, target_id, target);
				}
			});
	}
	
	function resizeCommentBox(o){
		o.style.height = "1px";
	    o.style.height = (10+o.scrollHeight)+"px";
	}
	
