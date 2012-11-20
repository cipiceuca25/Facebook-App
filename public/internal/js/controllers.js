'use strict';

/* Controllers */
function IndexCtrl($scope, $routeParams) {
	console.log('index controller called');
}

function HomeCtrl($scope, $http) {
	
	console.log($scope);

	$(function(){
		console.log('log begin...');
		jQuery(".app-info").hide();
		//toggle the componenet with class msg_body
		$(".heading").click(function() {
			$(this).next(".app-info").slideToggle(500);
		});
	});
	
    var updateData = function (data) {
    	$scope.fanpageCount = data.fanpages;
    	//console.log(data.fanpages);
    	$scope.fanCount = data.fans;
    	$scope.insight = data.basicInsight;
    }
    
	$http({method: 'GET', url: '/fc/index/appinfo'}).
	success(function(data, status) {
	    //console.log(data);
	    if (status === 200) {
	    	//console.log(data);
	    	updateData(data); 
	    }
	}).
	error(function(data, status) {
	    $scope.data = data || "Request failed";
	    $scope.status = status;
	    //console.log($scope.data);
	});
	
	
}

// fanpage controller
function FanpageCtrl($scope, $http) {
	console.log('fanpage ctrl called');
    var updateData = function (data) {
    	$scope.fanpages = data;
    }
    
	$http({method: 'GET', url: '/fc/index/fanpage'}).
	success(function(data, status) {
	    console.log(data);
	    if (status === 200) {
	    	//console.log(data);
	    	updateData(data); 
	    }
	}).
	error(function(data, status) {
	    $scope.data = data || "Request failed";
	    $scope.status = status;
	    //console.log($scope.data);
	});
}

// cron controller
function CronCtrl($scope, $http) {

}

// point controller
function PointCtrl($scope, $http) {

}

// page admin activity controller
function ActivityCtrl($scope, $http) {

}

// facebook user controller
function FacebookUserCtrl($scope, $http) {

}

// rss controller
function RssCtrl($scope, $http) {
	console.log('Rss');
    var updateFeed = function (data) {
    	$scope.feeds = data;
    }
    
	$scope.getFeed = function() {
		$http({method: 'GET', url: '/fc/rss/fanpage/'+$scope.fanpageId}).
		success(function(data, status) {
		    console.log(data);
		    if (status === 200) {
		    	//console.log(data);
		    	updateFeed(data); 
		    }
		}).
		error(function(data, status) {
		    $scope.data = data || "Request failed";
		    $scope.status = status;
		    //console.log($scope.data);
		});
	}
}