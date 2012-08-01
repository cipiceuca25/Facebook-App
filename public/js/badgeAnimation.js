tog = true;
pnext = false;
images = new Array('','post_post', 'Like_comment', 'Like_photo', 'post_photo');
titles = new Array('','Made A Post', 'Liked A Comment', 'Liked A Photo', 'Posted A Photo');
count = 0;
clr = null;

exp = 0;

function loop() {
    clearTimeout(clr);
    inloop();
    //setTimeout(loop, 2500); //call 'loop()' after 2.5 seconds
}

function next(){
	moveLeft('.holder');

	
	
	exp = 0;
	loop();
	
	if ( count >= images.length){
		$('.next').css('display','none');
		$('.holder').css('display','none');
	}
}

function play(){
	$('.light-box').css('display','block');
	if (tog) {
		$('.flare').css("-webkit-animation-play-state", "paused");
		$('.flare').css("-moz-animation-play-state", "paused");
	}else{
		$('.flare').css("-webkit-animation-play-state", "running");	
		$('.flare').css("-moz-animation-play-state", "running");	
	}
	tog = !tog;

}

function moveLeft(ui){
	$(ui).animate({
		left: '-=1500px'
	   },500, function(){
		 $('.holder').css('opacity',0.1);
		 resetLeft(ui);
	});
	
}

function resetLeft(ui) {
	$(ui).css('left', 1270);

	count++;
	if (count >= images.length) {
		$('.badge_title').html('No More Notifications');
		$('.badgeAni').css('background-image',
				'none');
		$('.flare').css('background-image',
		'none');
	} else {
		$('.badgeAni').css('background-image',
				'url(/img/badges/test/' + images[count] + '.png)');
		$('.badge_title').html(titles[count]);
	}
	
	$(ui).animate({
		left : '-=1000px'
	}, 500, function() {
		
	});
	
}


function inloop() {
    $('.increasing').html(exp+= 1);
    if (exp>=50) {
    	$('.holder').animate({
		opacity: 1.0
	}, 3000, function() {
		
	});
        return;
    }
    clr = setTimeout(inloop, 30); //call 'inloop()' after 30 milliseconds
}

