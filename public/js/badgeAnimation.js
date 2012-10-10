tog = true;
pnext = false;

count = 0;
clr = null;

exp = 0;

function loop() {
    clearTimeout(clr);
    inloop();
    //setTimeout(loop, 2500); //call 'loop()' after 2.5 seconds
}

function next(){
	if ( count > images.length){
		$('.next').css('display','none');
		$('#holder').css('display','none');
		close();
	}else{
		moveLeft('#holder');
		exp = 0;
		
		
		
		$('.outof').html(experience[count]);
		loop();
	}
	
}

function play(){
	count = 0;
	exp = 0;
	$('.light-box').css('display','block');

	$('.flare').css("-webkit-animation-play-state", "running");	
	$('.flare').css("-moz-animation-play-state", "running");	
	$('.badgeAni').css('background-image',
				'url('+images[count]+')');
	$('.badge_title').html(titles[count]);
	$('.outof').html(experience[count]);
	loop();
}

function moveLeft(ui){
	
	$(ui).css('opacity',0.1);
	
	resetLeft(ui);

	
}

function resetLeft(ui) {
	//$(ui).css('left',1310);
	count++;
	if (count >= images.length) {
		$('.badge_title').html('No More Notifications');
		$('.badgeAni').css('background-image',
				'none');
		$('.flare').css('background-image',
		'none');
		$('#holder').css('opacity','1');
		$('.next').remove();
		$('.exp_count').remove();
	} else {
		setTimeout($(ui).removeClass('holder-animate').animate({'nothing':null}, 1, function () {
			$(ui).addClass('holder-animate');
			}), 500);
		$('.badgeAni').css('background-image',
				'url('+images[count]+')');
		$('.badge_title').html(titles[count]);
	}
	
	
}


function inloop() {
    
    if (exp>=experience[count]) {
    	$('#holder').animate({
		opacity: 1.0
	}, 500, function() {
		
	});
        return;
    }else{
    	$('.increasing').html(exp+= 1);
    	 clr = setTimeout(inloop, 30); //call 'inloop()' after 30 milliseconds
    }
   
}

