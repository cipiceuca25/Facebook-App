var images; 
var titles;
var experience;

jQuery(document).ready(function($){
	color1 = $('.toolbar li').css("color");
	color2 = $('.toolbar li').css("background-color");
	//alert(color1 + color2);
	isLoginNotification();
	setInterval(isLoginNotification, 150000);
	
	
});

$('.notification').live('click', function(){
	getBadgeNotification();

	
	$('.notification').css('background-color',color2);
	$('.notification a').css('color',color1);
	$('.notification').css('opacity','1');
	$('.notification a').attr('data-original-title','You have no new Notifications');
	//$('#badge-notification-count').html('0');
	$(this).stop();
});

function isLoginNotification() {
	$.ajax({
		type : "GET",
		url : serverUrl + '/app/user/' +userId +'/notification?fanpage_id=' + fanpageId,
		dataType : "json",
		cache : false,
		async : true,
		beforeSend: function(){
		},
		success : function(data) {
			console.log(data['message']);
			console.log(data);
			if(data['message'] == "ok") {
				setupBadges(data);
				$('#badge-notification-count').html(data['count']);
				
				$('.notification a').attr('data-original-title','You have ' + data['count'] + ' new Notifications');
				$('.notification').css('background-color','#56A556');
				$('.notification a').css('color',color2);
				$('.notification').effect("pulsate", { times:3 }, 300);
				
				//$('.notification').animate({'background-color':color2 , 'color': color1});
			}
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
	for (i=0; i<badges['count']; i++ ){
		
		images[i] = badges['notification']['newBadgeCount'][i]['picture'];
		titles[i] = badges['notification']['newBadgeCount'][i]['name'];
		experience[i] = badges['notification']['newBadgeCount'][i]['quantity'];
	} 
	
	console.log(images);
	console.log(titles);
	console.log(experience);
}

function setviewedbadges(){
	

}
