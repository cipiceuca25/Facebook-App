jQuery(document).ready(function($) {
	
	var badgeTable = $('#badgeTable').dataTable({
		"bProcessing": false,
		"bServerSide": true,
		"bPaginate": false,
		"bFilter": false,
		"bInfo": false,
		"bLengthChange": false,
		"aoColumns": [
		              { "mData": "id",  "bVisible": false },
		              { "mData": "name" },
		              { "mData": "description" },
		              { "mData": "weight" },
		              { "mData": "picture" },
		              { 
		            	  "mData": null,
		            	  "bSearchable": false,
	                      "bSortable": false,
	                      "fnRender": function (oObj)                              
	                       {
	                            return "<a class='remove-badge' data-id= '"+ oObj.aData['id']+"' href='#'><i class='icon-remove'></i></a>";
	                      }
		              }
		          ],
		"sAjaxSource": "/admin/badge?fanpage_id=" +fanpageId
	});
	
	if ($(".wizard").length > 0) {
		$(".wizard").formwizard(
				{
					formPluginEnabled : true,
					validationEnabled : true,
					focusFirstInput : false,
					validationOptions : {
						highlight : function(label) {
							$(label).closest('.control-group')
									.addClass('error');
						},
						success : function(label) {
							label.addClass('valid').closest(
									'.control-group').addClass(
									'success');
						}
					},
					formOptions : {
						success : function(data) {
							if(data['message'] == 'ok'){
								location.reload();
							}
						},
						beforeSubmit : function(data) {
							//var formData = $.param(data);
							//alert(formData); return false;
						},
						dataType : 'json',
						resetForm : true
					}
				});
	}
	
	$('.remove-badge').live('click', function(e){
		e.preventDefault();
		var badgeId = $(this).attr('data-id');
		
		$.ajax({
    		type: "GET",
    		url:  '/admin/badge/delete?fanpage_id='+ fanpageId +'&badge_id=' + badgeId,
    		cache: false,
    		async: false,
    		success: function( data ) {
    			if(data == 1) {
    				location.reload();
    			}
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			alert(xhr.statusText);
    			console.log(xhr.statusText, errorMessage);
    		}
    	});
	});

});
