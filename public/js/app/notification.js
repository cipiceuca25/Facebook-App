var images = new Array(); 
var titles = new Array(); 
var experience= new Array(); 
var badgeIds= new Array(); 
var points;
var lastViewNotification;
var badgeCount = 0;
var pointCount = 0;

jQuery(document).ready(function($){
	color1 = $('.toolbar li').css("color");
	color2 = $('.toolbar li').css("background-color");
	//alert(color1 + color2);
	isLoginNotification();
	
	setInterval(isLoginNotification, 150000);
	
});

$('.notification').live('click', function(){

	if(pointCount + badgeCount > 0 ){
		getBadgeNotification();
		
		setviewedbadges();
		
		$('.notification').css('background-color',color2);
		$('.notification a').css('color',color1);
		$('.notification').css('opacity','1');
		$('.notification a').attr('data-original-title','You have no new Notifications');
		$('#badge-notification-count').html('0');
		$(this).stop();
		$('.notification a').addClass('noclick');
		badgeIds = new Array();
		
		//alert(date);
	}
});

function pointlogNotification() {
	console.log(lastViewNotification);
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/user/' +userId +'/pointlognotification?fanpage_id='+ fanpageId +'&time=' + lastViewNotification ,
		dataType : "json",
		cache : false,
		async : false,

		success : function(data) {
			points = data;
			pointCount = points.length;
			if (pointCount + badgeCount > 0){
				$('.notification a').removeClass('noclick');
				$('#badge-notification-count').html(pointCount + badgeCount);
				$('.notification a').attr('data-original-title','You have ' + (badgeCount + pointCount) + ' new Notifications');
				$('.notification').css('background-color','#56A556');
				$('.notification a').css('color',color2);
				$('.notification').effect("pulsate", { times:3 }, 300);
			}
			date = new Date();
			lastViewNotification = date.getUTCFullYear()+'-'+(date.getUTCMonth() + 1)+'-'+date.getUTCDate() +' '+ (date.getUTCHours()) + ':' + (date.getUTCMinutes()) + ':' + date.getUTCSeconds(); 
			 
			console.log(lastViewNotification);
		
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}


function isLoginNotification() {
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/user/' +userId +'/notification?fanpage_id=' + fanpageId,
		dataType : "json",
		cache : false,
		async : false,
		beforeSend: function(){
			pointlogNotification();
		},
		success : function(data) {
			//console.log(data['message']);
			//console.log(data);
			if(data['message'] == "ok") {
				
				setupBadges(data);
				
				badgeCount =  data['count'];
			}
		
			if (pointCount + badgeCount > 0){
				$('.notification a').removeClass('noclick');
				$('#badge-notification-count').html(pointCount + badgeCount);
				$('.notification a').attr('data-original-title','You have ' + (badgeCount + pointCount) + ' new Notifications');
				$('.notification').css('background-color','#56A556');
				$('.notification a').css('color',color2);
				$('.notification').effect("pulsate", { times:3 }, 300);
			}
				//$('.notification').animate({'background-color':color2 , 'color': color1});
			
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}
/*
 * var images = new Array('','post_post', 'Like_comment', 'Like_photo', 'post_photo');
var titles = new Array('','Made A Post', 'Liked A Comment', 'Liked A Photo', 'Posted A Photo');
 * 
 */
function setupBadges(badges){
	images = new Array(badges['count']);
	titles = new Array(badges['count']);
	experience = new Array(badges['count']);
	badgeIds = new Array(badges['count']);
	for (i=0; i<badges['count']; i++ ){
		
		images[i] = badges['notification']['newBadgeCount'][i]['picture'];
		titles[i] = badges['notification']['newBadgeCount'][i]['name'];
		experience[i] = badges['notification']['newBadgeCount'][i]['quantity'];
		badgeIds[i] = badges['notification']['newBadgeCount'][i]['id'];
	} 
	/*
	console.log(images);
	console.log(titles);
	console.log(experience);
	*/
}

function setviewedbadges(){
	if (badgeCount > 0){
		$.ajax({
			type : "GET",
			url : serverUrl + '/app/user/' +userId +'/setviewedbadges?fanpage_id=' + fanpageId,
			dataType : "json",
			cache : false,
			async : false,
			success : function(data) {
				
				console.log('badges toggled')
			},
			error : function(xhr, errorMessage, thrownErro) {
				console.log(xhr.statusText, errorMessage);
			}
		});
	}
}


