'use strict';

/* Filters */

angular.module('MyHtmlModule', []).filter('htmlDecode', function() {
	return function(input) {
		return (typeof input === 'undefined') ? '' : $('<div/>').html(input).text();
	}
});