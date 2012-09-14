jQuery(document).ready(function($){

    // export ajax call
    $(".level-dropdown a").live('click', function (e) {
    	e.preventDefault();
    	console.log($(this).text());
    	var text = $(this).text();
    	var level = $(this).attr('data-level');
	    var fanpageId = $(this).attr('data-id');
    	
	    if(fanpageId && level && text != $("#"+fanpageId+"_level_text").text()) {
			$.ajax({
				'url': '/api/fanpages/' + fanpageId +'?level=' + level,
				'type': 'UPGRADE',
				'success': function(xhr) {
					console.log(xhr);
					$("#"+fanpageId+"_level_text").html(text);
					$(this).closest('tr').find('.status').find('button').attr('data-level',level);
				}
			});         
	    }
    });
})