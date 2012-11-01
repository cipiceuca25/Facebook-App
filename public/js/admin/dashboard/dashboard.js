var defaultMax = 10;
var defaultMin = 0;
var defaultStep = 1;
    
jQuery(document).ready(function($){
	loadHome();
});	

function getHomeFans(time){
	tick = 'day'
	 switch(time){
	 	case 'today':
	 		tick = 'hours';
	 		break;
	 	default :
	 		tick = 'day';
	 }
		//console.log(tick);
	 options = {		xaxis: {
			mode: "time",
			font:{size:12},
			minTickSize: [1, tick]
			},
		yaxis:{
			font:{size:12},
			minTickSize: [1]
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
	if(fanpageId) {
	    	//alert(fanpageId+' '+type); return false;
			$.ajax({
				url: '/admin/fanpage/graphhomefans/'+fanpageId + '?time='+time,
			    type: 'get',
			    dataType: 'json',
			    error: function( data ) {
			    	alert('error');
			    },
			    beforeSend : function() {
			    	$('#placeholder').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
					
					$('#placeholder2').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
					//destroyAll();
				},
			    success: function (data){
			    	//console.log(data);
			    	var dataset = [];
			    	var dataset2 = [];
			    	var crankdataset = [];
			    	var crankdataset2 = [];
			    	
			    	//console.log(first_time_use);
			    	for(i=0; i < data.length; i++) {
			    		if ( ((new Date(data[i].date)).getTime()) >= (new Date(first_time_use).getTime())){
			    			crankdataset.push( [ (new Date(data[i].date)).getTime(), (data[i].like)]);
			    			crankdataset2.push( [ (new Date(data[i].date)).getTime(), (data[i].total)]);
							if (crankdataset.length == 1){
								dataset.push( [ (new Date(data[i].date)).getTime(), (data[i].like)]);
				    		
							}
							if (crankdataset2.length == 1){
				    			dataset2.push( [ (new Date(data[i].date)).getTime(), (data[i].total)]);
							}
							
							
							
						}else{
			    			dataset.push( [ (new Date(data[i].date)).getTime(), (data[i].like)]);
			    			dataset2.push( [ (new Date(data[i].date)).getTime(), (data[i].total)]);
						}
			    	}
			    	
			    	if (crankdataset.length == 1){
			    		var date = new Date(crankdataset[0][0]);
			    		date.setDate(date.getDate()-1);
			    		crankdataset.push([date.getTime(), 0]);
			    	}
			    	if (crankdataset2.length == 1){
			    		var date = new Date(crankdataset2[0][0]);
			    		date.setDate(date.getDate()-1);
			    		crankdataset2.push([date.getTime(), 0]);
			    	}
			    	
			    	if (crankdataset.length == 0){
			    		var date = new Date();
			    		crankdataset.push([date.getTime(), 0]);
			    	
			    		date.setDate(date.getDate()-1);
			    		crankdataset.push([date.getTime(), 0]);
			    	}
			    	if (crankdataset2.length == 0){
			    		var date = new Date();
			   
			    		crankdataset2.push([date.getTime(), 0]);
			    
			    		date.setDate(date.getDate()-1);
			    		crankdataset2.push([date.getTime(), 0]);
			    	}
			    	//console.log(dateset2);
			        $.plot($('#placeholder'), [ {label: 'Started Using FanCrank', data:crankdataset },{ label: 'Fan Interaction',  data:dataset} ], options);
			        $.plot($('#placeholder2'), [{label: 'Started Using FanCrank', data:crankdataset2 }, { label: 'Fan Interaction Total',  data:dataset2} ], options);
			        $('#graphtitle').html('New Fans');
			        $('#graphtitle2').html('Total New Fans');
			    }
			});  
	}
}

function getHomeActivedFans(time){
	tick = 'day'
	 switch(time){
	 	case 'today':
	 		tick = 'hours';
	 		break;
	 	default :
	 		tick = 'day';
	 }
		//console.log(tick);
	 options = {		xaxis: {
			mode: "time",
			font:{size:12},
			minTickSize: [1, tick]
			},
		yaxis:{
			font:{size:12},
			minTickSize: [1]
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
	if(fanpageId) {
	    	//alert(fanpageId+' '+type); return false;
			$.ajax({
				url: '/admin/fanpage/graphhomeactivefans/'+fanpageId + '?time='+time,
			    type: 'get',
			    dataType: 'json',
			    error: function( data ) {
			    	alert('error');
			    },
			    beforeSend : function() {
			    	$('#placeholder').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
					
					$('#placeholder2').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
					//destroyAll();
				},
			    success: function (data){
			    	//console.log(data);
			    	var dataset = [];
			    	var dataset2 = [];
			    
			    	
			    	//console.log(first_time_use);
			    	for(i=0; i < data.length; i++) {
			    		
			    		dataset.push( [ (new Date(data[i].first_login_time)).getTime(), (data[i].active)]);
			    		dataset2.push( [ (new Date(data[i].first_login_time)).getTime(), (data[i].total)]);
						
			    	}
			    	
			    	if (dataset.length == 1){
			    		console.log(dataset);
			    		var date = new Date(dataset[0][0]);
			    		date.setDate(date.getDate()-1);
			    		dataset.push([date.getTime(), 0]);
			    	}
			    	if (dataset2.length == 1){
			    		var date = new Date(dataset2[0][0]);
			    		date.setDate(date.getDate()-1);
			    		dataset2.push([date.getTime(), 0]);
			    	}
			    	
			    	
			    	if (dataset.length == 0){
			    		var date = new Date();
			    		dataset.push([date.getTime(), 0]);
			    	
			    		date.setDate(date.getDate()-1);
			    		dataset.push([date.getTime(), 0]);
			    	}
			    	if (dataset2.length == 0){
			    		var date = new Date();
			   
			    		dataset2.push([date.getTime(), 0]);
			    
			    		date.setDate(date.getDate()-1);
			    		dataset2.push([date.getTime(), 0]);
			    	}
			    	
			    	//console.log(dateset2);
			        $.plot($('#placeholder'), [ { label: 'Fan Interaction',  data:dataset} ], options);
			        $.plot($('#placeholder2'), [{ label: 'Fan Interaction Total',  data:dataset2} ], options);
			        $('#graphtitle').html('Active Fans');
			        $('#graphtitle2').html('Total Active Fans');
			    }
			});  
	}
}


function getHomePageLikes(time){

	options = {		xaxis: {
						mode: "time",
						font:{size:12},
						minTickSize: [1, "day"]
						},
					yaxis:{
						font:{size:12},
						
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


	   	if(fanpageId) {
	    	//alert(fanpageId+' '+type); return false;
			$.ajax({
				url: '/admin/fanpage/graphlikes/'+fanpageId+"?time="+time,
			    type: 'get',
			    dataType: 'json',
			    error: function( data ) {
			    	alert('error');
			    },
			    beforeSend : function() {
			    	$('#placeholder').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
					
					$('#placeholder2').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
					//destroyAll();
				},
			    success: function (data){
			    	//console.log(data);
			    	var dataset = [];
			    	var dataset2 = [];
			    	
			    	for(i=0; i < data[0].length; i++) {
			    		
			    		dataset.push( [ (new Date(data[0][i].end_time)).getTime(), (data[0][i].value)]);
			    		dataset2.push( [ (new Date(data[1][i].end_time)).getTime(), (data[1][i].value)]);
			    	}
			    	
			    	$.plot($('#placeholder'), [{ label: 'Facebook Likes',  data:dataset2} ], options);
			        $.plot($('#placeholder2'), [{ label: 'Facebook Likes',  data:dataset} ], options);
			        $('#graphtitle').html('Facebook Likes');
			        $('#graphtitle2').html('Total Facebook Likes');
			    }
			});            
	   	}

}

function getHomeFacebookInteractions(time){
	tick = 'day'
	 switch(time){
	 	case 'today':
	 		tick = 'hours';
	 		break;
	 	default :
	 		tick = 'day';
	 }
		//console.log(tick);
	 options = {		xaxis: {
			mode: "time",
			font:{size:12},
			minTickSize: [1, tick]
			},
		yaxis:{
			font:{size:12},
			minTickSize: [1]
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
	if(fanpageId) {
	    	//alert(fanpageId+' '+type); return false;
			$.ajax({
				url: '/admin/fanpage/graphhomefacebookinteractions/'+fanpageId + '?time='+time,
			    type: 'get',
			    dataType: 'json',
			    error: function( data ) {
			    	alert('error');
			    },
			    beforeSend : function() {
			    	$('#placeholder').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
					
					$('#placeholder2').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
					//destroyAll();
				},
			    success: function (data){
			    	//console.log(data);
			    	var alldataset = [];
			    	var postsdataset = [];
			    	var commentsdataset = [];
			    	var likesdataset = [];
			    	var alldataset2 = [];
			    	var postsdataset2 = [];
			    	var commentsdataset2 = [];
			    	var likesdataset2 = [];
			    	
			    	//console.log(first_time_use);
			    	for(i=0; i < data.length; i++) {
			    		
			    			alldataset.push( [ (new Date(data[i].created_time)).getTime(), (data[i].all)]);
			    			postsdataset.push( [ (new Date(data[i].created_time)).getTime(), (data[i].posts)]);
			    			commentsdataset.push( [ (new Date(data[i].created_time)).getTime(), (data[i].comments)]);
			    			likesdataset.push( [ (new Date(data[i].created_time)).getTime(), (data[i].likes)]);
			    			
			    			alldataset2.push( [ (new Date(data[i].created_time)).getTime(), (data[i].total_all)]);
			    			postsdataset2.push( [ (new Date(data[i].created_time)).getTime(), (data[i].total_posts)]);
			    			commentsdataset2.push( [ (new Date(data[i].created_time)).getTime(), (data[i].total_comments)]);
			    			likesdataset2.push( [ (new Date(data[i].created_time)).getTime(), (data[i].total_likes)]);
			    	}
			    	
			    	//console.log(dateset2);
			        $.plot($('#placeholder'), [{ label: 'Posts',  data:postsdataset},
			                                    {label: 'Comments', data:commentsdataset },
			                                    {label: 'Likes', data:likesdataset }, 
			                                    {label: 'All', data:alldataset }], options);
			        $.plot($('#placeholder2'), [{ label: 'Posts',  data:postsdataset2},
			                                    {label: 'Comments', data:commentsdataset2 },
			                                    {label: 'Likes', data:likesdataset2 }, 
			                                    {label: 'All', data:alldataset2 }], options);
			        //$.plot($('#placeholder2'), [{label: 'Started Using FanCrank', data:crankdataset2 }, { label: 'Fan Interaction Total',  data:dataset2} ], options);
			        $('#graphtitle').html('Interactions');
			        $('#graphtitle2').html('Total Interactions');
			    }
			});  
	}
}



function getInteractionsFanCrank(){
	options = {		xaxis: {
		mode: "time",
		font:{size:12},
		minTickSize: [1, "day"]
		},
		yaxis:{
			font:{size:12},
			
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
	if(fanpageId) {
		//alert(fanpageId+' '+type); return false;
		$.ajax({
			url: '/admin/fanpage/fancrankinteractionsgraph/'+fanpageId,
		    type: 'get',
		    dataType: 'json',

		    error: function( data ) {
		    	alert('error');
		    },
		    success: function (data){
		    	//console.log(data);
		    	
		    	var dataset = [];
		    	var dataset2 = [];
		    	for(i=0; i < data.length; i++) {
		    		dataset.push( [ (new Date(data[i].date)).getTime(), data[i].interactions ]);
		    		dataset2.push( [ (new Date(data[i].date)).getTime(), data[i].total ]);
		    	} 
		
		        $.plot($('#placeholder'), [{ label: 'Interactions Via Fancrank',  data:dataset}], options);
		        $.plot($('#placeholder2'), [{ label: 'Interactions Via Fancrank Total',  data:dataset2}], options);
		    }
		});            
	}

	
}

function getHomePointsAwarded(ui){

	options = {		xaxis: {
						mode: "time",
						font:{size:12},
						minTickSize: [1, "day"]
						},
					yaxis:{
						font:{size:12},
						
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


	if(fanpageId) {
		//alert(fanpageId+' '+type); return false;
		$.ajax({
			url: '/admin/fanpage/graphpoints/'+fanpageId,
		    type: 'get',
		    dataType: 'json',

		    error: function( data ) {
		    	alert('error');
		    },
		    success: function (data){
		    	//console.log(data);
		    	
		    	var dataset = [];
		    	for(i=0; i < data.length; i++) {
		    		dataset.push( [ (new Date(data[i].hours)).getTime(), data[i].sum ]);
		    	} 
		
		        $.plot($(ui), [{ label: 'Points',  data:dataset}], options);
		    }
		});            
	}

}




function showTooltip(x, y, contents) {
    $('<div id="graphTooltip">' + contents + '</div>').css( {
        position: 'absolute',
        display: 'none',
        top: y + 5,
        width:'200px',
        left: x - 200,
        border: '1px solid #ade4ff',
        padding: '2px',
        'background-color': '#ade4ff',
        opacity: 0.80
    }).appendTo("body").fadeIn(200);
}

function loadBadge(){
	
	$.ajax({
		type : "GET",
		url : serverUrl + '/admin/dashboard/badge?id=' + fanpageId,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend : function() {
			$('#badges').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
			//destroyAll();
		},
		success : function(data) {
			$('#badges').html(data);
			mostAwardBadges = $('#MostAwardTable').dataTable({"sDom" : "t", "aaSorting": [[ 3, "desc" ]]} );
			recentBadges = $('#RecentlyAwardedTable').dataTable({"sDom" : "t", "aaSorting": [[ 3, "desc" ]]} );
			mostBadges = $('#mostBadgesTable').dataTable({"sDom" : "t", "aaSorting": [[ 1, "desc" ]]} );
			allBadges = $('#allBadgesTable').dataTable({"sDom" : "t", "aaSorting": [[ 1, "desc" ]]} );
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting the mini followers list');
		}
	});
	
}

function loadHome(){
	
	$.ajax({
		type : "GET",
		url : serverUrl + '/admin/dashboard/home?id=' + fanpageId,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend : function() {
			$('#home').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
			//destroyAll();
		},
		success : function(data) {
			$('#home').html(data);
			getHomePageLikes("#placeholder");
			//loadGraph("#placeholder2", 'Points');
			$('#placeholder').css({'width':'100%', 'height':'300px'});
			$('#placeholder2').css({'width':'100%', 'height':'300px'});
			$('#placeholder').resize();
			$('#placeholder2').resize();
			//topFanTable = $('#topfanTable').dataTable({"sDom" : "t", "aaSorting": [[ 2, "desc" ]]} );
			topPostByLike= $('#topPostByLike').dataTable({"sDom" : "t", "aaSorting": [[ 3, "desc" ]]});
			
			//topFanTable = $('#topfanTable').dataTable({"sDom" : "<'filter'f><'length'l>t<'info'i>", "aaSorting": [[ 2, "desc" ]]} );
			//getTopFanTable('topfan', 30);
			/**************** Fanpage Setting Section *******************************/
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
						showTooltip(item.pageX, item.pageY, parseInt(y) + " " + item.series.label + " as of " + newDate.toLocaleString());
					}
				} else {
					$("#graphTooltip").remove();
					previousPoint = null;            
				}
			
			});
			
		
			$("#placeholder2").bind("plothover", function (event, pos, item) {
				if (item) {
					if (previousPoint != item.dataIndex) {
				    	previousPoint = item.dataIndex;
				            
						$("#graphTooltip").remove();
						var x = item.datapoint[0].toFixed(2),
						y = item.datapoint[1].toFixed(2);
						var newDate = new Date();
						newDate.setTime(x);
						showTooltip(item.pageX, item.pageY, parseInt(y) + " " + item.series.label + " as of " + newDate.toLocaleString());
					}
				} else {
					$("#graphTooltip").remove();
					previousPoint = null;            
				}
			
			});
			
			

		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting the mini followers list');
		}
	});
	
}

function loadFacebookInsights(){
	
	$.ajax({
		type : "GET",
		url : serverUrl + '/admin/dashboard/facebookinsights?id=' + fanpageId,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend : function() {
			$('#facebook').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
			//destroyAll();
		},
		success : function(data) {
			$('#facebook').html(data);
			
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting the mini followers list');
		}
	});
	
}

function loadUsers(){
	
	$.ajax({
		type : "GET",
		url : serverUrl + '/admin/dashboard/users?id=' + fanpageId,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend : function() {
			$('#users').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
			//destroyAll();
		},
		success : function(data) {
			$('#users').html(data);
			userTable = $('#userTable').dataTable({"sDom" : "t", "aaSorting": [[ 2, "desc" ]]} );
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting the mini followers list');
		}
	});
	
}

function loadDashboard(){
	
	$.ajax({
		type : "GET",
		url : serverUrl + '/admin/dashboard/stats?id=' + fanpageId,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend : function() {
			$('#dashboard').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
			//destroyAll();
		},
		success : function(data) {
			$('#dashboard').html(data);
	
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting the mini followers list');
		}
	});
	
}

function loadSettings(){
	
	$.ajax({
		type : "GET",
		url : serverUrl + '/admin/dashboard/settings?id=' + fanpageId,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend : function() {
			$('#settings').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
			//destroyAll();
		},
		success : function(data) {
			$('#settings').html(data);			
			
			$('#point_like_normal').spinner({
				min: defaultMin,
				max: defaultMax,
				step: defaultStep
			});
			
			$('#point_comment_normal').spinner({
				min: defaultMin,
				max: defaultMax,
				step: defaultStep
			});
			
			$('#point_post_normal').spinner({
				min: -10,
				max: defaultMax,
				step: defaultStep
			});
			
			$('#point_like_admin').spinner({
				min: defaultMin,
				max: defaultMax,
				step: defaultStep
			});
			
			$('#point_comment_admin').spinner({
				min: defaultMin,
				max: defaultMax,
				step: defaultStep
			});
			
			$('#point_bonus_duration').spinner({
				min: 0,
				max: 180,
				step: 1
			});
			
			$('#point_virginity').spinner({
				min: defaultMin,
				max: defaultMax,
				step: defaultStep
			});
			
			$('#point_comment_limit').spinner({
				min: defaultMin,
				max: defaultMax,
				step: defaultStep
			});	
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting settings');
		}
	});
	
}



function loadPoints(){
	
	$.ajax({
		type : "GET",
		url : serverUrl + '/admin/dashboard/points?id=' + fanpageId,
		dataType : "html",
		cache : false,
		async : true,
		beforeSend : function() {
			$('#points').html("<div style='text-align:center; padding:40px 0 40px 0'><img src='/img/ajax-loader.gif' /></div>");
			//destroyAll();
		},
		success : function(data) {
			
			loadGraph("#placeholder3", 'Points');
			$('#placeholder3').css({'width':'100%', 'height':'150px'});
			$('#placeholder3').resize();
		
		},
		error : function(xhr, errorMessage, thrownErro) {
			console.log(xhr.statusText, errorMessage);
			console.log('error getting point settings');
		}
	});	
}

$('#badges-tab').live('click', function() {
	loadBadge();
});

$('#points-tab').live('click', function() {
	loadPoints();
});

$('#users-tab').live('click', function() {
	loadUsers();
});

$('#settings-tab').live('click', function() {
	loadSettings();
});

$('#dashboard-tab').live('click', function() {
	loadDashboard();
});

$('#home-tab').live('click', function() {
	loadHome();
});

$('#facebook-tab').live('click', function() {
	loadFacebookInsights();
});





function destroyAll(){
	
	$('#home').html('');
	$('#dashboard').html('');
	$('#settings').html('');
	$('#users').html('');
	$('#badges').html('');
	$('#points').html('');

	try{
		topFanTable.fnDestroy();
	}catch(exception){
	}
	try{
		userTable.fnDestroy();
	}catch(exception){
	}
	try{
		mostAwardBadges.fnDestroy();
	}catch(exception){
	}
	try{
		recentBadges.fnDestroy();
	}catch(exception){
	}
	try{
		mostBadges.fnDestroy();
	}catch(exception){
	}
	try{
		allBadges.fnDestroy();
	}catch(exception){
	}
	try{
		topPostByLike.fnDestroy();
	}catch(exception){
	}

}
/*	
	$( "#myFanpageSettingMsodel" ).dialog("destroy");

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
		var pointLikeNormal = $('#point_like_normal').val();
		var pointCommentNormal = $('#point_comment_normal').val();
		var pointPostNormal = $('#point_post_normal').val();
		var pointLikeAdmin = $('#point_like_admin').val();
		var pointCommentAdmin = $('#point_comment_admin').val();
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

	//User Point Modify Model Section 
	$( "#pointModifyModel" ).dialog( "destroy" );

	$('#pointModifyModel').dialog({
		autoOpen: false,
		height: 350,
		width: 400,
		modal: true,
		close: function() {
			$('#adjust_point').val('');
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
		var adjustPoint = $('#adjust_point').val();
		var pointMsg = $('#modifiedPointMessage').val();
		
	    if(fanpageId) {
	    	if($("#add_radio").attr('checked')) {
	    		//alert(addPoint);
		        $.ajax({
		            url: '/admin/user/addpoint?',
		            type: 'POST',
		            data: 'fanpage_id='+fanpageId+'&user_id='+userId+'&point='+adjustPoint+'&pointMsg='+pointMsg,
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
	    	}else if($("#subtract_radio").attr('checked')) {
	    		//alert(subtractPoint); 
		        $.ajax({
		            url: '/admin/user/subtractpoint?',
		            type: 'POST',
		            data: 'fanpage_id='+fanpageId+'&user_id='+userId+'&point='+adjustPoint+'&pointMsg='+pointMsg,
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
	
	///////////////////////////////////////////////////////
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
	
	///Analytic Table Section 
	//getTopFanTable('topfan', 30);
	

	

   // var topFanTable = $('#topPostByLike').dataTable();

   // var topFanTable = $('#topPostByComment').dataTable();
    
 ////////////////////////////////////////////////////
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
	
	
	
	
	
	


	// Get the rows which are currently selected 
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
	
	///Graph Section 
	
    // a null signifies separate line segments
   
   
    
    
    
    $(".graph-dropdown a").click(function () {

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
                type: 'dashboard',
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
    
  
    
 
    
//post graph
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
//pie graph
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

//pie graph
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
*/
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