jQuery(document).ready(function($){

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
  	    
    $(".dropdown-menu a").click(function () {

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
                bars: { show: true },
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
    
    $.plot($("#postByLikeStat"), barData2, barOptions);
    
    $.plot($("#postByCommentStat"), barData3, barOptions);
    
    
});

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

function jsonToDataSet(source, xaxisOffsetPos) {
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

