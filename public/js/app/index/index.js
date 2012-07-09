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

  //trick to indentify parent container
  if(window.location != window.parent.location) {
	  //TODO
	  //alert(window.parent.location.href);
  }else {
	  //alert(window.location.href);
	  /*
	  $('<input>').attr({
		    type: 'hidden',
		    id: 'fanpageId',
		    name: 'fanpageId',
		    value: window.location.pathname.split('/')[1],
		}).appendTo('body');
	  	FB.api('/eslyonline', function(response) {
		  alert('Your name is ' + response.id);
		});
		*/
  }
});
