

jQuery(document).ready(function($){

	/**************** Fanpage Setting Section *******************************/
	$( "#myFanpageSettingModel" ).dialog("destroy");

	$('#myFanpageSettingModel').dialog({
		autoOpen: false,
		height: 650,
		width: 400,
		modal: true,
		close: function() {
			$( this ).dialog("close");
		}
	});

	$(".model-btn").click(function (e) {
		$('#myFanpageSettingModel').dialog( "open" );
		e.preventDefault();
	});
	
	$(".setting-btn-close").click(function (e) {
		$('#myFanpageSettingModel').dialog( "close" );
		e.preventDefault();
	});

	$(".setting-btn-primary").click(function (e) {
		e.preventDefault();

		var fanpageId = $(this).attr('data-id');
		
		var enableProfileImage = $('#enableProfileImage').val();
		var topPostBy = $('#topPostBy').val();
	    if(fanpageId) {
	        $.ajax({
	            url: '/api/fanpages/' + fanpageId+ '?top_post_choice=' +topPostBy+'&profile_image_enable='+enableProfileImage,
	            type: 'change',
	            dataType: 'json',
	            error: function( res ) {
	            	alert('error');
	            },
	            success: function( data ) {
	            	if(data.code == '200') {
		            	alert(data.message + ' ' + profile_image_enable);
	            		//$('#myFanpageSettingModel').dialog( "close" );
	            	}
	            }
	        });            
	    }
		$('#myFanpageSettingModel').dialog( "close" );
	});

	/**************** User Point Modify Model Section *******************************/
	$( "#pointModifyModel" ).dialog( "destroy" );

	$('#pointModifyModel').dialog({
		autoOpen: false,
		height: 350,
		width: 400,
		modal: true,
		close: function() {
			$('#add_point').val('').removeAttr('disabled');
			$('#subtract_point').val('').removeAttr('disabled');
			$('#modifiedPointMessage').val('');
			$( this ).dialog( "close" );
		}
	});
	
	$( ".user-btn-close" ).live('click', function(){
		$('#pointModifyModel').dialog( "close" );
	});
	
	$(".user-btn-primary").click(function (e) {
		e.preventDefault();

		var userId = $('#modify_user_id').html();
		var fanpageId = $(this).attr('data-id');
		var addPoint = $('#add_point').val();
		var subtractPoint = $('#subtract_point').val();
		var pointMsg = $('#modifiedPointMessage').val();
		
	    if(fanpageId) {
	    	if(addPoint.match(/^\d+$/)) {
	    		//alert(addPoint);
		        $.ajax({
		            url: '/admin/user/addpoint?',
		            type: 'POST',
		            data: 'fanpage_id='+fanpageId+'&user_id='+userId+'&point='+addPoint+'&pointMsg='+pointMsg,
		            dataType: 'json',
		            error: function( res ) {
		            	alert('error'+res.message);
		            },
		            success: function( data ) {
		            	if(data.message == 'ok') {
			            	alert(data.message);
		            	}
		            }
		        }); 
	    	}else if(subtractPoint.match(/^\d+$/)) {
	    		//alert(subtractPoint); 
		        $.ajax({
		            url: '/admin/user/subtractpoint?',
		            type: 'POST',
		            data: 'fanpage_id='+fanpageId+'&user_id='+userId+'&point='+subtractPoint+'&pointMsg='+pointMsg,
		            dataType: 'json',
		            error: function( res ) {
		            	alert('error'+res.message);
		            },
		            success: function( data ) {
		            	if(data.message == 'ok') {
			            	alert(data.message);
		            	}
		            }
		        }); 
	    	}else {
	    		alert('invalid input');
	    	}
	    }
		$('#pointModifyModel').dialog( "close" );
	});
	
	$('#add_point').live('change', function() {
		if($(this).val().length > 0) {
			$('#subtract_point').attr('disabled', 'disabled');
		}else {
			$('#subtract_point').removeAttr('disabled');
		}
	});
	
	$('#subtract_point').live('change', function() {
		if($(this).val().length > 0) {
			$('#add_point').attr('disabled', 'disabled');
		}else {
			$('#add_point').removeAttr('disabled');
		}
	});
	
	/***********************************************/
	$('.progress-preview').each(function(){
		var el = $(this);
		function up(){
			var width = el.find('.bar').width();
			var parentWidth = el.width();
			var percent = Math.round((100*width/parentWidth));
			var plus = Math.round(Math.random() * 10);
			var newPercent = percent + plus;
			if(newPercent > 100){
				newPercent = 0;
				el.find('.bar').width("100%").html("Finish Loading 100%");
				return;
			}
			el.find('.bar').width(newPercent+"%").html('Loading '+newPercent+"%");
			setTimeout(up, 100);
		}
		up();
	});
	
	var topFanTable,fanfavoriteTable,toptalkersTable,topclickersTable,topfollowedTable;
	
	$(".mini > li > a").hover(function(e){
		e.stopPropagation();
		if(!$(this).parent().hasClass("open")) {
			$(this).find(".label").stop().animate({
				top: '-10px'
			},200, function(){
				$(this).stop().animate({top: '-6px'},100);
			});
		}
	}, function(){});
	
	/**************** Analytic Table Section *******************************/
	getTopFanTable('topfan', 30);
	

	

   // var topFanTable = $('#topPostByLike').dataTable();

   // var topFanTable = $('#topPostByComment').dataTable();
    
    /*******************************************************************/
    $( "#userprofile" ).dialog( "destroy" );
    
	$('#userprofile').dialog({
		autoOpen: false,
		height: 350,
		width: 400,
		modal: true,
		close: function() {
			$( this ).dialog( "close" );
		}
	});

	$(".model-btn-close").click(function (e) {
		$('#userprofile').dialog( "close" );
		e.preventDefault();
	});
	
	
	
	
	
	
/*
	function popover(x){
		//alert ('getting info for '+ id);
		$.ajax({
    		type: "GET",
    		url:  serverUrl +'/admin/dashboard/fanprofile/'+ fanpageId +'?facebook_user_id=' + x,
    		dataType: "html",
    		cache: false,
    		async: false,
    		success: function( data ) {
    			
    			$('#fan-profile-content').html(data);
    			$('#userprofile').dialog( "open" );
    		},	
    		error: function( xhr, errorMessage, thrownErro ) {
    			alert(xhr.statusText);
    			console.log(xhr.statusText, errorMessage);
    		}
    	});
	}
	*/

	/* Get the rows which are currently selected */
	function fnGetSelected( oTableLocal )
	{
		var aReturn = new Array();
		var aTrs = oTableLocal.fnGetNodes();
		
		for ( var i=0 ; i<aTrs.length ; i++ )
		{
			if ( $(aTrs[i]).hasClass('row_selected') )
			{
				aReturn.push( aTrs[i] );
			}
		}
		return aReturn;
	}
	
	/**************** Graph Section *******************************/
	$('#placeholder').css({'width':'400px', 'height':'200px'});

    // a null signifies separate line segments
    var options = {
		xaxis: {
			mode: "time",
			minTickSize: [1, "day"]
		},
        series: {
            lines: { show: true }
        },
        grid: {
        	hoverable: true, 
        	clickable: true,
            backgroundColor: { colors: ["#fff", "#eee"] }
        }
  	}
  	    
    $(".graph-drodown a").click(function () {

		var type;
		switch($(this).html()) {
			case 'total likes statistics': type = 'likes'; break;
			case 'daily posts statistics': type = 'posts'; break;
			case 'daily comments statistics': type = 'comments'; break;
			case 'over all': type = 'story'; break;
			default:
				return false; 
				break;
		}
		
        function onDataReceived(data) {
        	//alert(data); return false;
        	var dataset = [];
        	for(i=0; i < data.length; i++) {
        		dataset.push( [ (new Date(data[i].end_time)).getTime(), data[i].value ]);
        	}

            $.plot($("#placeholder"), [{ label: type,  data:dataset}], options);
        }

        if(fanpageId && type) {
        	//alert(fanpageId+' '+type); return false;
            $.ajax({
            	url: '/api/fanpages/' + fanpageId + '?type=' + type,
                type: 'analystic',
                dataType: 'json',
                error: function( data ) {
                	alert('error');
                },
                success: onDataReceived
            });            
        }
        
    });

    // export ajax call
    $(".export-dropdown a").click(function () {
		var type;
		switch($(this).html()) {
			case 'Top fans list': type = 'topfans'; break;
			case 'Top post': type = 'topposts'; break;
			default:
				return false; 
				break;
		}
		
        function loadAnimate() {
            $("#loadGif").text("Load.....");
        }

        if(fanpageId && type) {
        	//alert(fanpageId+' '+type); return false;
        	url = '/admin/dashboard/export/' + fanpageId + '?queryType=' + type;
        	window.location.href = url;
        }
        
        function unloadkAnimate() {
            $("#loadGif").text("Done");
        }
        
    });
    
    function showTooltip(x, y, contents) {
        $('<div id="graphTooltip">' + contents + '</div>').css( {
            position: 'absolute',
            display: 'none',
            top: y + 5,
            left: x + 15,
            border: '1px solid #fdd',
            padding: '2px',
            'background-color': '#fee',
            opacity: 0.80
        }).appendTo("body").fadeIn(200);
    }
    
    var previousPoint = null;
    
    $("#placeholder").bind("plothover", function (event, pos, item) {
		if (item) {
        	if (previousPoint != item.dataIndex) {
            	previousPoint = item.dataIndex;
                    
				$("#graphTooltip").remove();
				var x = item.datapoint[0].toFixed(2),
				y = item.datapoint[1].toFixed(2);
				var newDate = new Date();
				newDate.setTime(x);
				showTooltip(item.pageX, item.pageY, item.series.label + " of " + newDate.toUTCString() + " = " + parseInt(y));
			}
		}
		else {
			$("#graphTooltip").remove();
			previousPoint = null;            
		}
    });

    $("#placeholder").bind("plotclick", function (event, pos, item) {
        if (item) {
			plot.highlight(item.series, item.datapoint);
        }
    });

    /***************************post graph*****************************/
	$('#postStat').css({'width':'600px', 'height':'600px'});
	$('#postByLikeStat').css({'width':'600px', 'height':'500px'});
	$('#postByCommentStat').css({'width':'600px', 'height':'500px'});

	var postDataSet = jsonToDataSet(postData[0], 1);
	var postDataSet2 = jsonToDataSet(postData, 1);
	//console.log(postData[0]);
		
	var postData1 = [[1, 3], [4, 8], [7, 5], [10, 13]];
	
	var postData2 = [[1, 5], [4, 7], [7, 5], [10, 13]];
	
	var postData3 = [[1, 12], [4, 8], [7, 5], [10, 2]];
	//console.log(postDataSet);
	var barData1 = [{ label: 'Status',  data:[[1 , postData[3]['count']]]  },
	                { label: 'Link',     data:[[4, postData[0]['count']]]   },
	                { label: 'Photo',   data:[[7,  postData[1]['count']]] },	
	                { label: 'Video',  data:[ [10, postData[2]['count'] ] ]   }
	                
	
					];
	
	var barData2 = [{ label: 'Top Post By # Of Likes',  data:postData2}];
	
	var barData3 = [{ label: 'Top Post By # Of Comments',  data:postData3}];
	
    var barOptions = {
    		xaxis: {
    			min : 0,
    			max : 11,
    			ticks:[[1,'status'],[4,'link'],[7,'photo'], [10, 'video']],
    			font:{size:14},
                tickLength: 0, // hide gridlines
                color:'red',
               
             
    		},
            yaxis: {
                axisLabel: 'Value',
                font:{size:14},
         
              
           
                tickLength: 0 // hide gridlines
            },    		
            series: {
                bars: { 
                		show: true,
                		lineWidth:0,
                		fillColor: { colors: [ { opacity: 0.8 }, { opacity: 0.3 } ] },
                        barWidth:1,
                        align:'center',
                        
                },
                showNumbers:true,	
                font:"14px Verdana",
                shadowSize : 1,

            },
            
            colors: ['black', 'white'],
            grid: {
            	hoverable: true, 
            	clickable: true,
                backgroundColor: { colors: ["#fff", "#eee"] }
            },
            legend: {
            	
                labelBoxBorderColor: "black",
                labelFormatter: function(label, series) {
                	$(".legendColorBox").css({'width':'100px'});
                	return '<a href="#' + label + '" >' + label + '</a>';
                },
                position: "ne"
            }
      	};
    
    $.plot($("#postStat"), barData1, barOptions);
    
   // $.plot($("#postByLikeStat"), barData2, barOptions);
    
  // $.plot($("#postByCommentStat"), barData3, barOptions);
    /****************************Pie Graph**************************/
    $('#sexPieGraph2').css({'width':'400px', 'height':'200px'});

	var pieData =getSexPieData2(postData);
	
    $.plot($("#sexPieGraph2"), pieData,
	{
	        series: {
	            pie: {
	                show: true,
	                radius: 1,
	                tilt: 0.5,
	                label: {
	                    show: true,
	                    radius: 1,
	                    formatter: function(label, series){
	                        return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
	                    },
	                    background: { opacity: 0.8 }
	                },
	                combine: {
	                    color: '#999',
	                    threshold: 0.1
	                }
	            }
	        },
	        grid: {
	            hoverable: true,
	            clickable: true
	        },	        
	        legend: {
	            show: false
	        }
	});
    
    $("#sexPieGraph2").bind("plothover", pieHover);

    /****************************Pie Graph**************************/
    $('#sexPieGraph').css({'width':'400px', 'height':'200px'});

	var pieData = getSexPieData(sexPieData); 

    $.plot($("#sexPieGraph"), pieData,
	{
	        series: {
	            pie: {
	                show: true,
	                radius: 1,
	                tilt: 0.5,
	                label: {
	                    show: true,
	                    radius: 1,
	                    formatter: function(label, series){
	                        return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
	                    },
	                    background: { opacity: 0.8 }
	                },
	                combine: {
	                    color: '#999',
	                    threshold: 0.1
	                }
	            }
	        },
	        grid: {
	            hoverable: true,
	            clickable: true
	        },	        
	        legend: {
	            show: false
	        }
	});
    
    $("#sexPieGraph").bind("plothover", pieHover);
});


function pieHover(event, pos, obj) 
{
	if (!obj) {
		return;
	}
	var percent = parseFloat(obj.series.percent).toFixed(2);
	var number = obj.series.data[0][1];
	$("#piehover").html('<span style="padding-left: 50px; font-weight: bold; color: '+obj.series.color+'"> Total number of '+obj.series.label + ': ' +number+' ('+percent+'%)</span>');
}

function utcformat(d){
    d= new Date(d);
    var tail= 'GMT', D= [d.getUTCFullYear(), d.getUTCMonth()+1, d.getUTCDate()],
    T= [d.getUTCHours(), d.getUTCMinutes(), d.getUTCSeconds()];
    
    if(+T[0]> 12){
        T[0]-= 12;
        tail= ' pm '+tail;
    }else {
    	tail= ' am '+tail;   
    }
    
    var i= 3;
    while(i){
        --i;
        if(D[i]<10) D[i]= '0'+D[i];
        if(T[i]<10) T[i]= '0'+T[i];
    }
    return D.join('-')+' '+T.join(':')+ tail;
}

function jsonToDataSet(source) {
	var i, l, result = [], source, position=1, data; 

	for(i = 0, l = source.length; i < l; i++) { 
		data = source[i];
		
		switch(data.post_type) {
			case 'status': position = 1; break;
			case 'link': position = 4; break;
			case 'photo': position = 7; break;
			case 'video': position = 10; break;
			default : position = null;
		}
		if(position) {
			result.push([position, data.count]);
		}
	}
	return result;
}

function getSexPieData(source) {
	var data = [{ label: "male",  data: [[1 ,source.male]]},
	    		{ label: "female",  data: [[1, source.female]]}];
	return data;
}

function getSexPieData2(source) {
	var data = [{ label: 'Status',  data:[[1 , postData[3]['count']]]  },
	                { label: 'Link',     data:[[1, postData[0]['count']]]   },
	                { label: 'Photo',   data:[[1,  postData[1]['count']]] },	
	                { label: 'Video',  data:[ [1, postData[2]['count'] ] ]   }];
	
	return data;
}

$(document).on('mouseover',".dataTable tbody tr", function(event) {
	$(this).toggleClass('row_selected');
});

$(document).on('mouseover', 'a[rel=popover]', function() {
	
	if ($(this).data('isPopoverLoaded') == true) {
		return;
	}
	$(this).data('isPopoverLoaded', true).popover({
		delay : {
			show : 500,
			hide : 100
		}
	}).trigger('mouseover');
	popover($(this));
});

//modify user point popup
$(document).on('click', 'a[rel=popover]', function() {
	$('#modify_user_id').html($(this).attr('data-userid'));	
	$('#pointModifyModel').dialog( "open" );
});

function popover(x) {
	$.ajax({
		type : "GET",
		url : serverUrl + '/admin/dashboard/fanprofile/' + fanpageId
				+ '?facebook_user_id=' + $(x).attr('data-userid'),
		dataType : "html",
		cache : false,
		async : false,
		beforeSend: function(){
			$(x).attr('data-content', "<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
		},
		success : function(data) {
			$(x).attr('data-content', data);
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
		}
	});
}
function changeTimeLimit(){
	t = $("select#timelimit option:selected").val();
	destroyAll();
	//alert(t);
	getTopFanTable('fanfavorite',t);
	

}


function getTopFanTable(type, time) {
	$.ajax({
		type : "GET",
		url : serverUrl + '/admin/dashboard/fantable'+ '?id=' + fanpageId + '&type='+ type + '&time=' + time,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend:function(){	
		
					$('#topfan').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");		
				
					$('#fanfavorite').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");		
				
					$('#toptalkers').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");		
				
					$('#topclickers').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");		
				
					$('#topfollowed').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");		
		
					

		},
		success : function(data) {
	
			switch(type){
				case 'topfan':
				
					$('#topfan').html(data);
					topFanTable = $('#topfanTable').dataTable({"sDom" : "<'filter'f><'length'l>t<'info'i>", "aaSorting": [[ 2, "desc" ]]} );	
					break;
				case 'fanfavorite':
					
					$('#fanfavorite').html(data);
					fanfavoriteTable = $('#fanfavoriteTable').dataTable({"sDom" : "<'filter'f><'length'l>t<'info'i>", "aaSorting": [[ 2, "desc" ]]} );	
						
					break;
				
				case 'toptalkers':
					
					$('#toptalkers').html(data);
					toptalkersTable = $('#toptalkersTable').dataTable({"sDom" : "<'filter'f><'length'l>t<'info'i>", "aaSorting": [[ 2, "desc" ]]} );	
					break;	
					;
				case 'topclickers':
					
					$('#topclickers').html(data);
					topclickersTable = $('#topclickersTable').dataTable({"sDom" : "<'filter'f><'length'l>t<'info'i>", "aaSorting": [[ 2, "desc" ]]} );	
					break;		
				case 'topfollowed':
					
					$('#topfollowed').html(data);
					topfollowedTable = $('#topfollowedTable').dataTable({"sDom" : "<'filter'f><'length'l>t<'info'i>", "aaSorting": [[ 2, "desc" ]]} );	
					break;		
			}
		
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting the topfan table');
		}
	});
}

$(document).on('mouseover', 'a[rel=tooltip]', function() {
	if ($(this).data('isTooltipLoaded') == true) {
		return;
	}
	$(this).data('isTooltipLoaded', true).tooltip({placement:'left'}).trigger('mouseover');
});

$('#topfan-tab').live('click', function() {

	destroyAll();
	getTopFanTable('topfan', 30);
});
$('#fanfavorites-tab').live('click', function() {

	destroyAll();
	getTopFanTable('fanfavorite', 30);
});
$('#toptalkers-tab').live('click', function() {

	destroyAll();
	getTopFanTable('toptalkers', 30);
});
$('#topclicker-tab').live('click', function() {

	destroyAll();
	getTopFanTable('topclickers', 30);
});
$('#topfollowed-tab').live('click', function() {
	
	destroyAll();
	getTopFanTable('topfollowed', 30);
});

$("#fanfavoriteTable_filter").live('keyup', function () {
	fanfavoriteTable.fnFilter(this.value );
} );

function destroyAll(){
	
	try{
		topFanTable.fnDestroy();
		
	}catch(exception){
		
	}
	try{
		fanfavoriteTable.fnDestroy();
		
	}catch(exception){
		
	}
	
	try{
		toptalkersTable.fnDestroy();
		
	}catch(exception){
		
	}
	try{
		topclickersTable.fnDestroy();
		
	}catch(exception){
		
	}
	try{
		topfollowedTable.fnDestroy();
		
	}catch(exception){
		
	}

}

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

