'use strict';

(function(console) {
	var controllers = angular.module('financeDirectorControllers', []);

	controllers.controller('BalanceSummaryCtrl', ['$http', function($http) {
		var self = this;
		self.total = {};
		self.expenses = [];
		$http.get('/api/allocation/summary/format/json/').success(function(data){
			self.total = data.total;
			self.expenses = data.expenses;
		});
	}]);

	controllers.controller('LoginCtrl', ['AuthService', '$location', function(AuthService, $location) {
		var self = this;
		self.user = {
			email: "",
			password: ""
		};

		self.failed = false;

		this.login = function() {
			AuthService.
			login(self.user.email, self.user.password).
			success(function(data){
				self.failed = false;
				$location.path('/');
			}).
			error(function(data) {
				self.failed = true;
			});
			//show an error when it fails.
		}
	}]);

	controllers.controller('FinanceNavCtrl', ['AuthService','$location', function(AuthService, $location) {
		this.isActive = function(route) {
			return $location.path() === route;
		}

		this.logout = function() {
			AuthService.
			logout().
			success(function(data){
				$location.path('/');
			});
		}

		this.showNav = function() {
			return AuthService.isAuthenticated();
		};
	}]);

})(console);