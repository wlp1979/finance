'use strict';

(function(){
	var services = angular.module('financeDirectorServices', ['ngCookies']);

	services.factory('AuthService', ['$http', '$cookieStore', function($http, $cookieStore) {
		var cookieName = 'AuthService.authenticated';
		return {
			authenticated : null,
			login : function(email, password) {
				var self = this;
				return $http.post('/api/user/login', {email: email, password: password}).
				success(function(data) {
					self.setAuthenticated(true);
				}).
				error(function(data) {
					self.setAuthenticated(false);
				});
			},
			logout: function() {
				var self = this;
				return $http.post('/api/user/logout').success(function(data){
					self.setAuthenticated(false);
				});
			},
			isAuthenticated: function() {
				var self = this;
				if(self.authenticated === null) {
					self.authenticated = $cookieStore.get(cookieName);
				}

				return self.authenticated;
			},
			setAuthenticated: function(authenticated) {
				this.authenticated = authenticated;
				$cookieStore.put(cookieName, authenticated);
			}
		};
	}]);
})();