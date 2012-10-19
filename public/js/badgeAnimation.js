tog = true;
pnext = false;
badgesDone = false;
count = 0;
clr = null;

exp = 0;

function inloop() {

	if (experience.length>0){
		
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

}
function loop() {
	
    clearTimeout(clr);
    inloop();
   
    //setTimeout(loop, 2500); //call 'loop()' after 2.5 seconds
}

function next(){

	if ((count > images.length + points.length)){
		console.log(images.length + points.length);
	}else{
		moveLeft('#holder');
		exp = 0;
		if(experience.length > 0){
			$('.outof').html(experience[count]);
		}
		loop();
	}
	
}

function play(){
	count = 0;
	exp = 0;

	$('.light-box').css('display','block');

	$('.flare').css("-webkit-animation-play-state", "running");	
	$('.flare').css("-moz-animation-play-state", "running");
	if(images.length > 0){
		$('.badgeAni').css('background-image',
					'url('+images[count]+')');
		$('.badge_title').html(titles[count]);
		$('.outof').html(experience[count]);
		loop();
	}else{
		
		next();
	}
	
	
}

function moveLeft(ui){

	$(ui).css('opacity',0.1);
	
	resetLeft(ui);

	
}

function resetLeft(ui) {
	//$(ui).css('left',1310);

	count++;

	if (count >= images.length) {
		if(!badgesDone){
			count= 0;
		}
		
		$('.exp_count').remove();
		$('.badgeAni').css('background', 'none');
		$('#holder').css('opacity','1');

		if (count < points.length  ){
			badgesDone = true;
			//???
			
			$('#holder').replaceWith($('#holder').clone(true))
	        
	        /*
			setTimeout($('#holder').removeClass("holder-animate").animate({"nothing":null}, 1, function() {
				$('#holder').addClass("holder-animate");
				
				}), 500);*/

			if (points[count]['sum'] > 9){
				$('.badgeAni').css('left', '125px');
			}else if(points[count]['sum'] > 99){
				
				$('.badgeAni').css('left', '80px');
			}else{
				$('.badgeAni').css('left', '196px');
			}
			
			$('.badgeAni').html(((points[count]['sum'] > 0)? '+'+points[count]['sum']:points[count]['sum'])+
								'<span style="font-size:14px">Points</span>' );
			
			date= new Date(points[count]['created_time']);
			//console.log(date);
			$('.badge_title').html(date.toLocaleDateString());
			
			
			//$('.badge_title').remove();
			//$('.profile-content').removeAttr('style');
			//$('.profile-content').css('display','block');
			//$('#holder').prepend(parsePointsDisplay());
		}else{
			
			$('.badgeAni').remove();
			$('.flare').remove();
			$('.badge_title').css('margin-top','200px');
			$('.badge_title').css('margin-bottom','200px');
			$('.badge_title').css('position','absolute');
			$('.badge_title').html('No More Notifications');
			$('.next').remove();
			pointCount = 0;
			points = new Array();
			badgeCount = 0;
		}
		
	} else {
		$('#holder').replaceWith($('#holder').clone(true))
                
		$('.badgeAni').css('background-image',
				'url('+images[count]+')');
		$('.badge_title').html(titles[count]);
	}
	
	
}




