# The Discoverables Website
http://thediscoverables.com

## Prerequiseties

* Git
* A web server
* MySql 8 or higher
* Php 7.2 or higher
* Composer (Php dependency management)
* npm (node package management)

#### If you plan to use Docker, the Dockerfile in the `thediscoverables/docker_env` folder can be used

> thediscoverables/docker_env/Dockerfile 
[This setup guide might help](https://medium.com/better-programming/php-how-to-run-your-entire-development-environment-in-docker-containers-on-macos-787784e94f9a).
You will need to make sure the `vendor` and `config` folders are maintained as siblings to the `web` folder in your Docker setup.

## Php configuration
Settings in `php-config.ini`
```ini
post_max_size = 32M
file_uploads = On
upload_max_filesize = 32M
max_file_uploads = 20
```

## Mysql configuration
[mysqld]
default-authentication-plugin=mysql_native_password

## Get ready

### Clone the repository to your environment

```bash
git clone https://github.com/mybirdcansing/thediscoverables.git
```

### Go into the `thediscoverables` repository and run update on composer
```bash
cd thediscoverables 
composer update
```
Note: you can ignore this error message.

```bash
[RuntimeException]
Directory name must not be empty.
```

### Go into the `web` folder and run update on npm
```bash
cd web 
npm update
```

### Build the client side code
```bash
cd web 
npm run build
```

__The root directoy of the website must be the `web` folder of the `thediscoverables` repository.__

### Database Setup

Create a mysql user
```sql
-- replace [password] with a password of your choice
CREATE USER 'thediscoverables'@'%' IDENTIFIED BY '[password]';
GRANT ALL PRIVILEGES ON thediscoverables.* TO 'thediscoverables'@'%';
ALTER USER 'thediscoverables'@'%' IDENTIFIED WITH mysql_native_password
BY '[password]'; 
```

Run `thediscoverables/sql/scheme.sql` file on your mysql database


## Create config for the website
Go to `thediscoverables/config` and make the config.json file
```bash
cp config.json.template config.json
```
Then update the config.json file

```javascript
{
    "dataAccess" : {
        // if you're using Docker, use the name of the mysql docker container for the host
        "DB_HOST": "[your host]", 
        "DB_USER": "thediscoverables",
        "DB_PASSWORD": "[password]",
        "DB_DATABASE": "thediscoverables"
    },
    "security" : {
        // this is used to protect the authenticaiton cookie
        "SECRET_WORD": "[something secret]", 
        "LOGIN_COOKIE_NAME": "login"
    },
    // configuration for PHPMailer https://github.com/PHPMailer/PHPMailer
    "email": {
        "USER": "youremail@email.com",
        "PASSWORD": "[password]",
        "FROM_ADDRESS": "youremail@email.com",
        "FROM_NAME": "The Discoverables Support"
    }
}
```

## Create a user for the admin tool

#### First create the config file
Go to `thediscoverables/adminuser` and make the config.json file
```bash
cp config.json.template config.json
```
Then update the config.json file

```javascript
{
    "dataAccess" : {
        "DB_HOST": "0.0.0.0",
        "DB_USER": "thediscoverables",
        "DB_PASSWORD": "[password]",
        "DB_DATABASE": "thediscoverables"
    },
    // this will be used to make the first admin tool user
    "user" : {
        "username": "admin",
        "firstName": "Admin",
        "lastName": "User",
        "email": "[email]",
        "password" : "[password]"
    }
}
```
Now run the script

You might have to change the first line of the script to point to your php path. 
If you need to find it, you can run
```bash
which php
```

Make the script executable
```bash
chmod +x ./adminuser.php
```

Run the script
```bash
./adminuser.php
```

# Web server

If you don't want to use port 80 for your web server, you must update the devserver's proxy settings in `web/webpack.dev.js` to inclue the port you're using.

```javascript
devServer: {
    proxy: {
        '/lib/handlers': 'http://[::1]:[your port]',
        '/artwork': 'http://[::1]:[your port]',
        '/audio': 'http://[::1]:[your port]',
    }
}
```

If you don't have a web server setup for developemnet, you can use the one that ships with php.
https://www.php.net/manual/en/features.commandline.webserver.php

Exampele:
```bash
cd web
php -S 127.0.0.1:8000
```

# Testing

## Php tests

#### First create the config file
Go to `thediscoverables/php_tests` and make the config.json file
```bash
cp config.json.template config.json
```
Then update the config.json file

```javascript
{
    "dataAccess" : {
        "DB_HOST": "0.0.0.0",
        "DB_USER": "thediscoverables",
        "DB_PASSWORD": "[password]",
        "DB_DATABASE": "thediscoverables"
    },

    "test" : {
        // domain of the website (these are integration tests)
        "DOMAIN": "localhost", // us your website runs on a port other than 80, add it here.
        // credentials for theadmin tool, 
        // this can be the same user that you created with the `adminuser.php` script
        "TEST_USERNAME": "admin",
        "TEST_PASSWORD": "[password]",
        "TEST_EMAIL" : "[email]"
    }
}
```

Go to the `thediscoverables/web/lib` abd then run the tests

```bash
phpunit --bootstrap autoload.php ../../php_tests/
```

## Javascript tests

Go to `thediscoverables/web/test` and run
```bash
npm run test --inspect
```

## Develop

To run the website in development mode, go to `thediscoverables/web` and run

```bash
npm start
```


Enjoy!