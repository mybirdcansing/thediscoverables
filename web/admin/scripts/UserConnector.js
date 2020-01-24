// Use this to conect to the server for user related requests
class UserConnector extends ConnectorBase {
      constructor() {
            super('user');
      }

      authenticate(input, callback, errorCallback) {
            $.ajax({
            url: this.handlerBase + '/authenticate.php',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            cache: false,
            success: function (data, textStatus, jqXHR) {
                  callback(data, textStatus, jqXHR);
            },
            error: function(data, textStatus, errorThrown) {
                  errorCallback(data.responseJSON, textStatus, errorThrown);
                },
            data: JSON.stringify(input)
        });
      };

      logout(callback, errorCallback) {
            $.ajax({
                  url: this.handlerBase + '/logout.php',
                  type: 'POST',
                  dataType: 'json',
                  contentType: 'application/json',
                  cache: false,
                  success: function (data, textStatus, jqXHR) {
                        callback(data, textStatus, jqXHR);
                  },
                  error: function(data, textStatus, errorThrown) {
                        errorCallback(data.responseJSON, textStatus, errorThrown);
                      }
              });
      };

      requestPasswordReset(user, callback, errorCallback) {
            $.ajax({
                  url: this.handlerBase + '/requestpasswordreset.php',
                  type: 'POST',
                  dataType: 'json',
                  contentType: 'application/json',
                  cache: false,
                  success: function (data, textStatus, jqXHR) {
                        callback(data, textStatus, jqXHR);
                  },
                  error: function(data, textStatus, errorThrown) {
                        errorCallback(data.responseJSON, textStatus, errorThrown);
                      },
                      data: JSON.stringify(user)
              });
      };

      resetPassword(data, callback, errorCallback) {
            // 00000000-0000-0000-0000-000000000000 is just a blank guid to allow the path format
            // to be correct for the server. This should be changed
            $.ajax({
                  url: this.handlerUrl + '00000000-0000-0000-0000-000000000000/password',
                  type: 'POST',
                  dataType: 'json',
                  contentType: 'application/json',
                  cache: false,
                  success: function (data, textStatus, jqXHR) {
                        callback(data, textStatus, jqXHR);
                  },
                  error: function(data, textStatus, errorThrown) {
                        errorCallback(data.responseJSON, textStatus, errorThrown);
                      },
                      data: JSON.stringify(data)
              });
      };
}