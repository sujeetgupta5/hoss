// var qs = document.querySelectorAll;

angular.module('project', ['ngRoute'])

.constant('VALID_USERNAME', /^[a-zA-Z0-9_]*$/)

.factory('List', function($http) {
	var service = {
		list:  [
			{name:'gee', age: 25}, 
			{name:'yury', age:25}
		]

	};
	return service;
})

.factory('User', function($http, $q) {
	console.log(123);
	var service = {

		islogin: window.islogin,

		apikey: window.apikey,

		username: '',

		signup: function(username, password, callback){
			$http({
				method: 'post',
				data: $.param({username: username, password: password}),
				url: siteUrl + 'REST/authanticate/signup',
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			})
			.success(callback)
			.error(function(){});
		},
		
		resetpass: function(username, callback){
			$http({
				method: 'post',
				data: $.param({username: username}),
				url: siteUrl + 'REST/authanticate/resetpass',
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			})
			.success(callback)
			.error(function(){});
		},

		login: function(username, password, callback){
			$http({
				method: 'post',
				data: $.param({username: username, password: password}),
				url: siteUrl + 'REST/authanticate/login',
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			})
			.success(callback)
			.error(function(){});

			// .post(siteUrl + '/REST/user/login', {username: username, password: password})
		},

		logout: function(callback){
			$http.get(siteUrl + 'REST/authanticate/logout')
				.success(function(){
					service.islogin = false;
					callback();
				})
				.error(function(){

				});
		},
		
		passupdate: function(password, id, callback){
			/*var data = {
				id: password.id,
				name: password.password
			};*/
			$http({
				method: 'post',
				data: $.param({id: id, password: password}),
				url: siteUrl + 'REST/authanticate/passupdate',
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			})
			.success(callback)
			.error(function(){});
		},
		
		get: function(callback,id){
			if(service.initialized){
				callback();
				return;
			}
			$http({
				method: 'POST',
				url: siteUrl + 'REST/authanticate/getid',
				headers: {'Content-Type': 'application/x-www-form-urlencoded'},
				data: $.param({id: id}),
			})
			.success(callback)
			.error(function(){});
		},

		checkAuth: function(callback){
			var d = $q.defer();

			$http.get(siteUrl + 'REST/authanticate/login')
				.success(function(data){
					if(data.status === 'true'){
						service.islogin = true;
						callback();
						d.resolve();
					}else{
						service.islogin = false;
					}
				})
				.error(function(){
					d.reject();
				});

		}

	};
	return service;
})

.config(function($routeProvider){
	$routeProvider
	.when('/', {
		controller: 'indexController',
		templateUrl: 'application/views/login.html'
	})
	.when('/reset-password', {
		controller: 'resetController',
		templateUrl: 'application/views/reset-password.html'
	})
	.when('/reset/:id', {
		controller: 'resetPassController',
		templateUrl: 'application/views/resetpass.html'
	})
	/*.when('/signup', {
		controller: 'signupController',
		templateUrl: 'asset/signup.html'
	})
	.when('/reset-password', {
		controller: 'resetController',
		templateUrl: 'asset/reset-password.html'
	})
	.when('/reset/:id', {
		controller: 'resetPassController',
		templateUrl: 'asset/resetpass.html'
	})
	.when('/dashboard',{
		controller: 'dashboardController',
		templateUrl: 'asset/dashboard.php'
	})
	.when('/users',{
		controller: 'usersController',
		templateUrl: 'asset/users/users.php'
	})
	.when('/add',{
		controller: 'usersController',
		templateUrl: 'asset/users/add.php'
	})	
	.when('/edit/:id',{
		controller: 'usersEditController',
		templateUrl: 'asset/users/edit.php'
	})
	.when('/jcr',{
		controller: 'jcrController',
		templateUrl: 'asset/jcr/list.php'
	})
	.when('/workflow',{
		controller: 'workflowController',
		templateUrl: 'asset/workflow/list.php'
	})
	//Not Used
	.when('/timezone',{
		controller: 'timezoneController',
		templateUrl: 'asset/timezone/timezone.html'
	})*/
	/*.when('/add',{
		controller: 'timezoneController',
		templateUrl: 'asset/timezone/add.html'
	})	
	.when('/edit/:id',{
		controller: 'timezoneEditController',
		templateUrl: 'asset/timezone/edit.html'
	})*/
	.otherwise({
		redirectTo: '/'
	});
})


.controller('indexController', function($scope, List, $location, User, VALID_USERNAME){
	/*setTimeout(function(){
		$(".off-canvas-submenu").hide();
		$(".off-canvas-submenu-call").click(function() {
			$(this).parent().next(".off-canvas-submenu").slideToggle('fast');
		});
		$(document).foundation();
	}, 500);*/
	
	$scope.errors = [];
	//$scope.inputName  = '';

	$scope.list = List.list;
	
	if(User.islogin){
		//$location.path('dashboard');
		window.location = 'dashboard';
	}

	$scope.goToSignUp = function() {
		$location.path('/signup');
	};
	
	$scope.goToResetPass = function() {
		$location.path('/reset-password');
	};

	$scope.goToEdit = function(id){
		$location.path('/reset/' + id);
	};
	
	$scope.login = function() {
		this.errors = [];
		if(_validate.apply(this, null)){
			User.login(this.username, this.password, _loginCallback);
		}
	};

	$scope.inputFocus = function(){
		this.errors = [];
	};

	var _validate = function(){
		var username = $('#username').val(), error, valid = false,
			password = $('#password').val();

		if (!username || !password) {
			this.errors.push('Invalid username or password!');
		}
		if (username.length > 20 || password.length > 20) {
			this.errors.push('Username or password should not exceed 20 character');
		}

		this.username = username;
		this.password = password;
		return !this.errors.length;
	};

	var _loginCallback = function(data){
		if(data.status == 'fail') {
			$scope.errors.push('Wrong username or password!');
		} else {
			User.islogin = true;
			User.apikey = data.apikey;
			console.log('apikey is', data.apikey);
			User.username = this.username;
			$location.path('/expense');
		}
	};
	$scope.addPerson = function(){
		console.log(this === $scope);
		console.log($scope);
		this.list.push({name: this.inputName, age: 25});
	}
	
})

.controller('resetPassController', function($scope, User, $location, $http, $routeParams, VALID_USERNAME){
	$scope.errors = [];

	$scope.passupdate = function(){
		$location.path('/reset/' + id);
	};
	
	$scope.goToResetPass = function() {
		$location.path('/reset-password');
	};
	
	/*var _getCallback = function(data){
		if(data.error === 'failed') {
			$scope.errors.push('Your reset password link expired, Please reset again.');
			$('#hidethis').hide();
			$('#hidebtn').hide();
		} else {
			$('#hidethis').show();
			$('#hidebtn').show();
			$scope.errors.push('Please enter new password.');
			$scope.id = data.status;
		}
	};*/
	
	//var pathname = window.location.pathname.split("#");
	//var filename = pathname[pathname.length-1];
	var type = window.location.hash.substr(1);
	var pathname = type.split("/");
	var uID = pathname[pathname.length-1];
	$scope.id = uID;
	//User.get(_getCallback,uID);

	//$scope.username = '';
	$scope.password = '';

	$scope.passupdate = function(id){
		this.errors = [];
		if(_validate.apply(this, null)){
			//User.passupdate(this.password, _signupCallback);
			User.passupdate(this.password, this.id, _loginCallback);
			
			/*User.passupdate(this.password,id).success(function(data) {
				alert(data);
			}).error(function(data){
				//TBC
				alert(data.error)
			});*/
		}
	};
	
	$scope.addPerson = function(){
		console.log(this === $scope);
		console.log($scope);
		this.list.push({password: this.inputName, age: 25});
	};

	$scope.inputFocus = function(){
		this.errors = [];
	};

	var _signupCallback = function(data){
		console.log(data);
		if(data.error === 'duplicate') {
			$scope.errors.push('Username exists, please choose another username');
		} else {
			//User.password = this.password;
			//User.apikey = data.apikey;
			//console.log('apikey is', User.apikey);
			$location.path('/');
		}
	};
	//sign up form validation
	var _validate = function(){
		var password = $('#password').val().trim(), error, valid = false,
			password2 = $('#password2').val().trim();

		if (!password || !password2) {
			this.errors.push('Required fields not empty.');
		} else if (password !== password2) {
			this.errors.push('Confirm password not match.');
		} /*else if (password.length > 20) {
			this.errors.push('Username or password should not exceed 20 chars.');
		}*/

		this.password = password;
		return !this.errors.length;
	};
	
	var _loginCallback = function(data){
		if(data.status == 'fail') {
			$scope.errors.push('Something wrong please try again!');
		} else {
			/*User.islogin = true;
			User.apikey = data.apikey;
			console.log('apikey is', data.apikey);
			User.username = this.username;*/
			$location.path('/');
		}
	};
	$scope.addPerson = function(){
		console.log(this === $scope);
		console.log($scope);
		this.list.push({name: this.inputName, age: 25});
	}
	
})

.controller('resetController', function($scope, User, $location, $http, VALID_USERNAME){
		
	$scope.errors = [];

	$scope.username = '';

	$scope.resetpass = function(){
		this.errors = [];
		if(_validate.apply(this, null)){
			User.resetpass(this.username, _resetCallback);
		}
	};

	$scope.inputFocus = function(){
		this.errors = [];
	};

	var _resetCallback = function(data){
		console.log(data);
		if(data.error === 'failed') {
			$scope.errors.push('Reset password link already send  to your email address.');
		} else if(data.error === 'duplicate') {
			$scope.errors.push('Username is invalid, please try again.');
		} else {
			//User.islogin = true;
			//User.username = this.username;
			//User.apikey = data.apikey;
			//console.log('apikey is', User.apikey);
			//$location.path('/');
			//$scope.errors.push('Please check your email address for reset new password.');
			$scope.errors.push(data.status);
		}
	};
	//sign up form validation
	var _validate = function(){
		var username = $('#username').val().trim(), error, valid = false;
		
		/*var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
		if(emailPattern.test(document.getElementById('email').value)==false)
		{
			this.errors.push('Please enter valid email address.');
		}*/
		if (!username) {
			this.errors.push('Please enter valid username.');
		}
		this.username = username;
		return !this.errors.length;
	};
								
})

.controller('signupController', function($scope, User, $location, $http, VALID_USERNAME){
	$scope.errors = [];

	$scope.signup = function(){
		$location.path('/detail/1');
	};
	
	$scope.goToSignIn = function() {
		$location.path('/');
	};
	
	$scope.username = '';
	$scope.password = '';

	$scope.signup = function(){
		this.errors = [];
		if(_validate.apply(this, null)){

			User.signup(this.username, this.password, _signupCallback);
		}
	};
	
	$scope.addPerson = function(){
		console.log(this === $scope);
		console.log($scope);
		this.list.push({name: this.inputName, age: 25});
	};

	$scope.inputFocus = function(){
		this.errors = [];
	};

	var _signupCallback = function(data){
		console.log(data);
		if(data.error === 'duplicate') {
			$scope.errors.push('Username exists, please choose another username');
		} else {
			User.islogin = true;
			User.username = this.username;
			User.apikey = data.apikey;
			console.log('apikey is', User.apikey);
			$location.path('/');
		}
	};
	//sign up form validation
	var _validate = function(){
		var username = $('#username').val().trim(), error, valid = false,
			password = $('#password').val().trim(),
			password2 = $('#password2').val().trim();

		if (!username || !password || !password2) {
			this.errors.push('Required fields not empty.');
		} else if (password !== password2) {
			this.errors.push('Confirm password not match.');
		} else if (username.length > 20 || password.length > 20) {
			this.errors.push('Username or password should not exceed 20 chars.');
		} else if (!VALID_USERNAME.test(username)) {
			this.errors.push('Username should be only consists of letters, numbers or "_"');
		}
		this.username = username;
		this.password = password;
		return !this.errors.length;
	};
})


.controller('listController', function($scope, List){
	console.log('list is');
	console.log(List);

	$scope.myname = 'gee';
	$scope.age = '25';
	//$scope.inputName  = '';

	$scope.list = List.list;

	$scope.getCount = function(){
		console.log('in getting count');
		return this.list.length;
	};

	$scope.getName = function(){
		console.log('in getting name');
		return '123';
	}

	$scope.addPerson = function(){
		console.log(this === $scope);
		console.log($scope);
		this.list.push({name: this.inputName, age: 25});
	}
})


.controller('testController', function($scope, $routeParams){
	$scope.name = 'test controller';

	$scope.getCount = function(){
		return $scope.name.length;
	}
});
