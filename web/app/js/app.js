'use strict';

(function() {
	var app = angular.module('financeDirector', ['ngRoute', 'financeDirectorControllers', 'financeDirectorServices']);

	app.config(['$routeProvider', '$httpProvider', function($routeProvider, $httpProvider) {
		var defaultRoute = "/summary";

		$httpProvider.responseInterceptors.push('httpInterceptor');
		$routeProvider.when('/summary', {
			templateUrl: 'partials/balance-summary.html',
			controller: 'BalanceSummaryCtrl',
			controllerAs: 'summary'
		}).
		when('/login', {
			templateUrl: 'partials/login.html',
			controller: 'LoginCtrl',
			controllerAs: 'loginCtrl'
		}).
		otherwise({
			redirectTo: defaultRoute
		});
	}]);

	app.factory('httpInterceptor', ['$q', '$window', '$location', function httpInterceptor ($q, $window, $location) {
		return function (promise) {
			var success = function (response) {
				return response;
			};

			var error = function (response) {
				if (response.status === 401) {
					$location.url('/login');
				}

				return $q.reject(response);
			};

			return promise.then(success, error);
		};
	}]);
})();