jQuery(document).ready(function($){
 /************ SLIDING PANELS *************/
  
    $(".flip").toggle(function() {

      var panel = $(this).parent().prev('.panel');
      var count = $(panel).find('.border').length

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
      var count = $(panel).find('.border').length

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

  if(window.location != window.parent.location) {
  }
  

});
