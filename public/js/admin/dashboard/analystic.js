jQuery(document).ready(function($){

	$('#placeholder').css({'width':'800px', 'height':'400px'});

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
