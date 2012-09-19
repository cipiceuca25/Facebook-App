jQuery(document).ready(function($){

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
	var topFanTable = $('#topFanTable').dataTable();

    var topPostTable = $('#topPostByLike').dataTable();

    var topCommentTable = $('#topPostByComment').dataTable();
    
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
	
	$(".dataTable tbody tr").live('click', function(event) {

		var aData = topFanTable.fnGetData( this );
		var iId = aData[0];
		
		//alert(iId);
		$(this).toggleClass('row_selected');
		if(iId) {
			event.preventDefault();
			popover(iId);
		}
	});
	
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
	$('#postStat').css({'width':'600px', 'height':'400px'});
	$('#postByLikeStat').css({'width':'600px', 'height':'400px'});
	$('#postByCommentStat').css({'width':'600px', 'height':'400px'});

	var postDataSet = jsonToDataSet(postData, 1);
	
	//alert(postDataSet);
		
	var postData1 = [[1, 3], [4, 8], [7, 5], [10, 13]];
	
	var postData2 = [[1, 5], [4, 7], [7, 5], [10, 13]];
	
	var postData3 = [[1, 12], [4, 8], [7, 5], [10, 2]];

	var barData1 = [{ label: 'Post Overall Stat',  data:postDataSet}];
	
	var barData2 = [{ label: 'Top Post By # Of Likes',  data:postData2}];
	
	var barData3 = [{ label: 'Top Post By # Of Comments',  data:postData3}];
	
    var barOptions = {
    		xaxis: {
    			min : 0,
    			max : 12,
    			ticks:[[1,'status'],[4,'link'],[7,'photo'], [10, 'video']],
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelPadding: 25,
                axisLabel: 'type',
                tickLength: 0 // hide gridlines
    		},
            yaxis: {
                axisLabel: 'Value',
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
                axisLabelPadding: 5,
                tickLength: 0 // hide gridlines
            },    		
            series: {
                bars: { 
                		show: true
                },
                showNumbers: true,	
                shadowSize : 1
            },
            grid: {
            	hoverable: true, 
            	clickable: true,
                backgroundColor: { colors: ["#fff", "#eee"] }
            },
            legend: {
                labelBoxBorderColor: "none",
                labelFormatter: function(label, series) {
                	$(".legendColorBox").css({'width':'100px'});
                	return '<a href="#' + label + '">' + label + '</a>';
                },
                position: "ne"
            }
      	};
    
    $.plot($("#postStat"), barData1, barOptions);
    
    //$.plot($("#postByLikeStat"), barData2, barOptions);
    
   //$.plot($("#postByCommentStat"), barData3, barOptions);
    
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

function getData() {
	$.ajax({
		type: "GET",
		url: '/collectors/facebook/view',
		dataType: "json",
		cache: false,
		success: function( data ) {
			try{
				//var data = jQuery.parseJSON(data);
				alert(data);
			}	
			catch(e){
				return false;
			}
		},	
		error: function( xhr, errorMessage, thrownErro ) {
			alert(errorMessage);
			console.log(xhr.statusText, errorMessage);
		}
	});
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
