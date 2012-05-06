
$(document).ready(function(){
    
  /************ SLIDING PANELS *************/
  
    $(".flip").toggle(function() {

      var panel = $(this).parent().prev('.panel');
      var count = $(panel).find('.border').length;

      if($(panel).hasClass('down')) {
        $(panel).animate({height: 80 * count});
        $(this).text("+ More");
        $(panel).removeClass('down').addClass('up');
      } else if(count > 1) {
        $(panel).animate({height: 80});
        $(this).text("- Close");
        $(panel).addClass('down').removeClass('up');
      }
    
    }, function() {
      var panel = $(this).parent().prev('.panel');
      var count = $(panel).find('.border').length;

      if($(panel).hasClass('down')) {
        $(panel).animate({height: 80 * count});
        $(this).text("+ More");
        $(panel).removeClass('down').addClass('up');
      } else if(count > 1){
        $(panel).animate({height: 80});
        $(this).text("- Close");
        $(panel).removeClass('up').addClass('down');
      }
    
    });
  
  /************ TOOL TIP *************/
  
  $('a[title]').tooltip({
    placement: 'left'
  });
  
});



var paragraphs_added = 0;
window.fbAsyncInit = function() {
  FB.init({
    appId : '272147032839990', //Your facebook APP here
    cookie : true, // enable cookies to allow the server to access the session
  });
}

window.onload = function() {
  FB.Canvas.setAutoGrow(91);
  setTimeout('autoGrowTimer();', 1000); //This is for demonstration
};

function autoGrowTimer() { //Can be removed.
  paragraphs_added++; //Increase the count
  autoGrowDiv = document.getElementById('autoGrow');
  //autoGrowDiv.innerHTML = autoGrowDiv.innerHTML + "<p>This is making the iframe taller.</p>";
  //Stop after 50 paragraphs aded
  if (paragraphs_added > 50) return;

  setTimeout('autoGrowTimer();', 1000);
}