'use strict';

/* App Module */

angular.module('fc-internal', ['MyHtmlModule']).config(
		[ '$routeProvider', function($routeProvider) {
			$routeProvider.when('/index', {
				templateUrl : '/',
				controller : IndexCtrl
			}).when('/fanpage', {
				templateUrl : '/internal/partials/fanpage_list.html',
				controller : FanpageCtrl
			}).when('/point', {
					templateUrl : '/fc/index/showpointlog',
					controller : PointCtrl
			}).when('/cron', {
				templateUrl : '/fc/index/showcronlog',
				controller : CronCtrl				
			}).when('/activity', {
				templateUrl : '/fc/index/showactivities',
				controller : ActivityCtrl				
			}).when('/facebookuser', {
				templateUrl : '/fc/index/facebookuser',
				controller : FacebookUserCtrl				
			}).when('/rss', {
				templateUrl : '/fc/rss',
				controller : RssCtrl				
			}).				
			when('/home', {
				templateUrl : '/internal/partials/home.html',
				controller : HomeCtrl
			}).	otherwise({
				redirectTo : '/home'
			});
		} ]);
