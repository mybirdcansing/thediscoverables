const assert = require('assert')
var uuid = require('uuid');
var XMLHttpRequest = require("xmlhttprequest").XMLHttpRequest;
var http = require('http');
var userConnector = new UserConnector();
var cookie;


before(function(done){
	userConnector.authenticate("adam", "abacadae", function(data) {
		cookie = data.cookie;
		console.log(cookie);
		done();
	});
});



it('correctly gets the test admin user', (done) => {

	userConnector.getUser('00000000-0000-0000-0000-000000000000', function(user) {
		assert.equal(user.username, 'adam');
        assert.equal(user.firstName, 'Adam');
        assert.equal(user.lastName, 'Cohen');
        assert.equal(user.email, 'thediscoverables@gmail.com');
        assert.equal(user.password, '');
        done();
	});
});


it('correctly creates a new user', (done) => {
	var newUser = {
		'username': 'test-' + uuid.v4(),
		'firstName': 'Ron',
		'lastName': 'Snow',
		'email': 'test-' + uuid.v4() + '@gmail.com',
	};
	userConnector.createUser(newUser, function(id) {
		userConnector.getUser(id, function(user) {
			assert.equal(user.username, newUser.username);
	        assert.equal(user.firstName, newUser.firstName);
	        assert.equal(user.lastName, newUser.lastName);
	        assert.equal(user.email, newUser.email);
	        assert.equal(user.password, '');
	        done();
		});
	});
	
});



function UserConnector() {
 	//todo: move this into a config file
 	this.handlerUrl = 'http://localhost/lib/handlers/user/index.php';
	this.getUser = function(id, callback) {
		
		var request = new XMLHttpRequest();
		request.open('GET', this.handlerUrl + '?id=' + id, true);
		request.onload = function () {
	      	var user = JSON.parse(request.responseText);

	        callback(user);
		}
		request.send();
	};

	this.createUser = function(user, callback) {
		var request = new XMLHttpRequest();
		request.open('POST', this.handlerUrl, true);
		request.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
		request.onload = function () {
	      	var json = JSON.parse(request.responseText);
	        callback(json.userId);
		}
		request.send(JSON.stringify(user));
	};

	this.authenticate = function(username, password, callback) {

		var handler = "http://localhost/lib/handlers/authenticate.php";

		var request = new XMLHttpRequest();
		request.open('POST', handler, true);
		var params = encodeURI("username=" + username + "&password=" + password);
		request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		request.setRequestHeader("Content-Length", Buffer.byteLength(params, 'utf8'));

		request.onload = function () {
	      	var data = JSON.parse(request.responseText);
	        callback(data);
		}
		request.send(params);
	}
}
