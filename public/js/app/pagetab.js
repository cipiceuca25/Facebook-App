$(document).ready(function() {

var tabs = $( "#myTabs" ).tabs({
	load: function(event, ui) {
		$(ui.panel).delegate('a', 'click', function(event) {
				$(ui.panel).load(this.href);
				event.preventDefault();
			});
		}
	});

	/*
	$('#myTabs a').click(function () {
		$(this).attr('id', 'current');
	});
	*/
	$('#myTabs').bind('tabsselect', function(event, ui) {
		$('#myTabs a').removeAttr('id');
		$(ui.tab).attr('id', 'currentPageTab').css({'outline': 'none'});
		
		//var url = $.data(ui.tab, 'load.tabs');
		//alert(ui.panel.id);
		switch (ui.tab.href.split('#')[1]) {
			case 'newsfeed': getNewsfeed(ui); break; 
			case 'topfans': getTopfans(ui); break;
			case 'myprofile': getMyProfile(ui); break;
			case 'awards': getAwards(ui); break;
			default: break;	
		}
	});
});


function getNewsfeed(ui) {
	$.ajax({
		type: "GET",
		url: baseAppUrl+'newsfeed',
		dataType: "html",
		cache: false,
		success: function( data ) {
			$(ui.panel).show(data);
			//$(ui.panel).html(data);
		},	
		error: function( xhr, errorMessage, thrownErro ) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}

function getTopfans(ui) {
	$.ajax({
		type: "GET",
		url: baseAppUrl+'topfans',
		dataType: "html",
		cache: false,
		success: function( data ) {
			$(ui.panel).html(data);
		},	
		error: function( xhr, errorMessage, thrownErro ) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}

function getMyProfile(ui) {
	$.ajax({
		type: "GET",
		url: baseAppUrl+'myprofile',
		dataType: "html",
		cache: false,
		success: function( data ) {
			$(ui.panel).html(data);
		},	
		error: function( xhr, errorMessage, thrownErro ) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}

function getAwards(ui) {
	$.ajax({
		type: "GET",
		url: baseAppUrl+'awards',
		dataType: "html",
		cache: false,
		success: function( data ) {
			$(ui.panel).html(data);
		},	
		error: function( xhr, errorMessage, thrownErro ) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}

function hoverBackgroundEffect (target) {
	var width = $(target).width();
    var height = $(target).height();
    var offset = $(target).offset();
    //alert('width'+width+' height'+height+' offset'+offset.left);
    bgscroll(target, 'h', 0);
    //Calls the scrolling function repeatedly
    var init = setInterval("bgscroll()", 70);  
}

function bgscroll(target, direction, defaultPostion, offset){
    // 1 pixel row at a time
	defaultPostion -= 1;
    // move the background with backgrond-position css properties
    $(target).css("backgroundPosition", (direction == 'h') ? defaultPostion+"px 0" : "0 " + defaultPostion+"px");
}
