'use strict';

(function(console) {
	var controllers = angular.module('financeDirectorControllers', []);

	controllers.controller('BalanceSummaryCtrl', ['$http', function($http) {
		var self = this;
		self.total = {};
		self.expenses = [];
		$http.get('/api/allocation/summary/').success(function(data){
			self.total = data.total;
			self.expenses = data.expenses;
		});
	}]);

	controllers.controller('TransactionListCtrl', ['Transaction', 'Expense', function(Transaction, Expense) {
		this.transactionFilter = {
			page: 1,
			pageSize: 25
		};

		this.transactions = [];

		this.expenses = Expense.query();

		this.refresh = function(startOver) {
			if(startOver) {
				this.transactionFilter.page = 1;
			}

			this.transactions = Transaction.query(this.transactionFilter);
		}

		this.incrementPage = function(delta) {
			if( (delta < 0 && this.hasPreviousPage()) || (delta > 0 && this.hasNextPage()) ) {
				this.transactionFilter.page += delta;
				this.refresh(false);
			}
		}

		this.hasPreviousPage = function() {
			return this.transactionFilter.page > 1;
		}

		this.hasNextPage = function() {
			return this.transactions.length == this.transactionFilter.pageSize;
		}

		this.refresh(true);
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