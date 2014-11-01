'use strict';
// file upload https://github.com/danialfarid/angular-file-upload

(function() {
	var controllers = angular.module('financeDirectorControllers', ['xeditable']);

	controllers.controller('BalanceSummaryCtrl', ['$http', function($http) {
		var self = this;
		self.total = {};
		self.expenses = [];
		$http.get('/api/allocation/summary/').success(function(data){
			self.total = data.total;
			self.expenses = data.expenses;
		});
	}]);

	controllers.controller('TransactionListCtrl', ['Transaction', 'Expense', '$filter', function(Transaction, Expense, $filter) {
		var self = this;
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
			self.transactions = Transaction.query(this.transactionFilter);
		};

		this.incrementPage = function(delta) {
			if( (delta < 0 && this.hasPreviousPage()) || (delta > 0 && this.hasNextPage()) ) {
				this.transactionFilter.page += delta;
				this.refresh(false);
			}
		};

		this.hasPreviousPage = function() {
			return this.transactionFilter.page > 1;
		};

		this.hasNextPage = function() {
			return this.transactions.length == this.transactionFilter.pageSize;
		};

		this.saveTransaction = function(data, index) {
			var transaction = this.transactions[index];
			transaction.date = data.date;
			transaction.checkNum = data.checkNum;
			transaction.description = data.description;
			transaction.amount = data.amount;
			transaction.expenseId = data.expenseId;
			transaction.$save();
		};

		// this.addTransaction = function() {
		// 	this.inserted = new Transaction({
		// 		id: null,
		// 		date: $filter('date')(new Date(), 'yyyy-MM-dd'),
		// 		checkNum: null,
		// 		description: null,
		// 		amount: 0,
		// 		expenseId : this.expenses[0].id
		// 	});

		// 	this.transactions.push(this.inserted);
		// }

		this.showExpense = function(transaction) {
			if(transaction.expenseId && this.expenses.length) {
				var selected = $filter('filter')(this.expenses, { id: transaction.expenseId }, true);
				return selected.length ? selected[0].name : 'unknown';
			} else {
				return 'unknown';
			}
		};

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

	controllers.run(function(editableOptions, editableThemes) {
		editableThemes.bs3.inputClass = 'input-sm';
		editableThemes.bs3.buttonsClass = 'btn-xs';
		editableOptions.theme = 'bs3';
	});

})();