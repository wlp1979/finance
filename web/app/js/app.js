'use strict';

(function() {
	var app = angular.module('financeDirector', ['ngRoute', 'financeDirectorControllers', 'financeDirectorServices']);

	app.factory('httpInterceptor', ['$q', '$window', '$location', function httpInterceptor ($q, $window, $location) {
		return {
			"responseError": function (response) {
				if (response.status === 401) {
					$location.url('/login');
				}

				return $q.reject(response);
			}
		};
	}]);

	app.config(['$routeProvider', '$httpProvider', function($routeProvider, $httpProvider) {
		var defaultRoute = "/summary";

		$httpProvider.interceptors.push('httpInterceptor');
		$routeProvider.when('/summary', {
			templateUrl: 'partials/balance-summary.html',
			controller: 'BalanceSummaryCtrl',
			controllerAs: 'summaryCtrl'
		}).
		when('/transactions', {
			templateUrl: 'partials/transactions.html',
			controller: 'TransactionListCtrl',
			controllerAs: 'transCtrl'
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
})();