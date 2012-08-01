
$(document).ready(function(){
    
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
  
  /**************** Color Setting *************/
  $(".color-config a").click(function () {

		var fanpageTheme;
		switch($(this).html()) {
			case 'Grey theme': fanpageTheme = 1; break;
			case 'Pink lady theme': fanpageTheme = 2; break;
			case 'Classic theme': fanpageTheme = 3; break;
			case 'Orange theme': fanpageTheme = 4; break;
			case 'Ocean theme': fanpageTheme = 5; break;
			default:
				return false; 
				break;
		}
		
	    var fanpageId = $(this).attr('data-id');

	    if(fanpageId && fanpageTheme) {
	        $.ajax({
	            url: '/api/fanpages/' + fanpageId + '?fanpageTheme=' + fanpageTheme,
	            type: 'settheme',
	            dataType: 'json',
	            error: function( res ) {
	            	alert('error');
	            },
	            success: function( data ) {
	            	if(data.code == '200') {
		            	//alert(data.message);
		            	window.location.reload();
	            	}
	            }
	        });            
	    }
	    
	});

  /**************** Fanpage Setting Popup *************/
	$( "myFanpageSettingModel" ).dialog( "destroy" );

	$('#myFanpageSettingModel').dialog({
		autoOpen: false,
		height: 350,
		width: 400,
		modal: true,
		close: function() {
			
		}
	});

	$(".model-btn").click(function (e) {
		$('#myFanpageSettingModel').dialog( "open" );
		e.preventDefault();
	});
	
	$(".model-btn-close").click(function (e) {
		$('#myFanpageSettingModel').dialog( "close" );
		e.preventDefault();
	});

	$(".btn-primary").click(function (e) {
		var fanpageId = $(this).attr('data-id');
		
		$('#myFanpageSettingModel').dialog( "close" );
		e.preventDefault();
	    if(fanpageId) {
	        $.ajax({
	            url: '/api/fanpages/' + fanpageId + ',
	            type: 'change',
	            dataType: 'json',
	            error: function( res ) {
	            	alert('error');
	            },
	            success: function( data ) {
	            	if(data.code == '200') {
		            	//alert(data.message);
		            	window.location.reload();
	            	}
	            }
	        });            
	    }
	});
});


function fileUpload(form, action_url, div_id) {
    // Create the iframe...
    var iframe = document.createElement("iframe");
    iframe.setAttribute("id", "upload_iframe");
    iframe.setAttribute("name", "upload_iframe");
    iframe.setAttribute("style", "width: 100; height: 50; border: 1; display:none");
 
    // Add to document...
    form.parentNode.appendChild(iframe);
    window.frames['upload_iframe'].name = "upload_iframe";
 
    iframeId = document.getElementById("upload_iframe");
 
    // Add event...
    var eventHandler = function () {
 
            if (iframeId.detachEvent) iframeId.detachEvent("onload", eventHandler);
            else iframeId.removeEventListener("load", eventHandler, false);
 
            // Message from server...
            if (iframeId.contentDocument) {
                content = iframeId.contentDocument.body.innerHTML;
            } else if (iframeId.contentWindow) {
                content = iframeId.contentWindow.document.body.innerHTML;
            } else if (iframeId.document) {
                content = iframeId.document.body.innerHTML;
            }
 
            document.getElementById(div_id).innerHTML = content;
 
            // Del the iframe...
            setTimeout('iframeId.parentNode.removeChild(iframeId)', 250);
        }
 
    if (iframeId.addEventListener) iframeId.addEventListener("load", eventHandler, true);
    if (iframeId.attachEvent) iframeId.attachEvent("onload", eventHandler);
 
    // Set properties of form...
    form.setAttribute("target", "upload_iframe");
    form.setAttribute("action", action_url);
    form.setAttribute("method", "post");
    form.setAttribute("enctype", "multipart/form-data");
    form.setAttribute("encoding", "multipart/form-data");
 
    // Submit the form...
    form.submit();
 
    document.getElementById(div_id).innerHTML = "Uploading...";
}
