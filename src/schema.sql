CREATE DATABASE IF NOT EXISTS thediscoverables;
USE thediscoverables;

DROP TABLE IF EXISTS admin_user;
DROP TABLE IF EXISTS admin_user_status;

CREATE TABLE IF NOT EXISTS admin_user_status (
    admin_user_status_id INT AUTO_INCREMENT,
    label VARCHAR(64) NOT NULL,
    is_active bit,
    created_date TIMESTAMP NOT NULL,
    PRIMARY KEY (admin_user_status_id)
)  ENGINE=INNODB;

INSERT INTO admin_user_status (label, is_active, created_date) VALUES ('Active', 1, now());
INSERT INTO admin_user_status (label, is_active, created_date) VALUES ('Deactivated', 0, now());

CREATE TABLE IF NOT EXISTS admin_user (
    admin_user_id INT AUTO_INCREMENT,
    username VARCHAR(64) NOT NULL,
    first_name VARCHAR(64),
    last_name VARCHAR(64),
    password VARCHAR(64) NOT NULL,
    email VARCHAR(255) NOT NULL,
    admin_user_status_id INT,
    FOREIGN KEY (admin_user_status_id) REFERENCES admin_user_status(admin_user_status_id),
    modified_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_date TIMESTAMP NOT NULL,
    UNIQUE KEY username(username),
    PRIMARY KEY (admin_user_id)
)  ENGINE=INNODB;

INSERT INTO admin_user (username, first_name, last_name, password, email, admin_user_status_id, modified_date, created_date) 
VALUES ('adam', 'Adam', 'Cohen', 'abacadae', 'thediscoverables@gmail.com', 1, now(), now());


DROP TABLE IF EXISTS playlist_song;
DROP TABLE IF EXISTS playlist;
DROP TABLE IF EXISTS song;

CREATE TABLE IF NOT EXISTS playlist (
    playlist_id INT AUTO_INCREMENT,
    title VARCHAR(64) NOT NULL,
    description TEXT,
    modified_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_date TIMESTAMP NOT NULL,
    UNIQUE KEY title(title),
    PRIMARY KEY (playlist_id)
)  ENGINE=INNODB;


CREATE TABLE IF NOT EXISTS song (
    song_id INT AUTO_INCREMENT,
    title VARCHAR(64) NOT NULL,
    filename VARCHAR(2083) NOT NULL,
    description TEXT,
    modified_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_date TIMESTAMP NOT NULL,
    UNIQUE KEY title(title),
    PRIMARY KEY (song_id)
)  ENGINE=INNODB;


CREATE TABLE IF NOT EXISTS playlist_song (
    playlist_song_id INT AUTO_INCREMENT,
    song_id INT NOT NULL,
    playlist_id INT NOT NULL,
    created_date TIMESTAMP NOT NULL,
    FOREIGN KEY (song_id) REFERENCES song(song_id),
    FOREIGN KEY (playlist_id) REFERENCES playlist(playlist_id),
    PRIMARY KEY (playlist_song_id)
)  ENGINE=INNODB;

