CREATE DATABASE IF NOT EXISTS thediscoverables;
USE thediscoverables;

DROP TABLE IF EXISTS reset_password;
DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS user_status;

CREATE TABLE IF NOT EXISTS user_status (
    user_status_id INT AUTO_INCREMENT,
    label VARCHAR(64) NOT NULL,
    is_active bit,
    created_date TIMESTAMP NOT NULL,
    PRIMARY KEY (user_status_id)
)  ENGINE=INNODB;

INSERT INTO user_status (label, is_active, created_date) VALUES ('Active', 1, now());
INSERT INTO user_status (label, is_active, created_date) VALUES ('Deactivated', 0, now());

CREATE TABLE IF NOT EXISTS `user` (
    user_id CHAR(36),
    username VARCHAR(64) NOT NULL,
    first_name VARCHAR(64),
    last_name VARCHAR(64),
    password VARCHAR(64) NOT NULL,
    email VARCHAR(255) NOT NULL,
    user_status_id INT,
    FOREIGN KEY (user_status_id) REFERENCES user_status(user_status_id),
    modified_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    modified_by_id CHAR(36) NOT NULL,
    created_date TIMESTAMP NOT NULL,
    created_by_id CHAR(36) NOT NULL,
    UNIQUE KEY username(username),
    UNIQUE KEY email(email),
    PRIMARY KEY (user_id)
)  ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS `reset_password` (
    token CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    expiration_date TIMESTAMP,
    used CHAR(0) DEFAULT NULL,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by_id CHAR(36) NOT NULL,
    UNIQUE KEY token(token),
    FOREIGN KEY (user_id) REFERENCES user(user_id)
)  ENGINE=INNODB;

DROP TABLE IF EXISTS playlist_song;
DROP TABLE IF EXISTS album;
DROP TABLE IF EXISTS playlist;
DROP TABLE IF EXISTS song;


CREATE TABLE IF NOT EXISTS playlist (
    playlist_id CHAR(36),
    title VARCHAR(255),
    description TEXT,
    modified_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    modified_by_id CHAR(36) NOT NULL,
    created_date TIMESTAMP NOT NULL,
    created_by_id CHAR(36) NOT NULL,
    UNIQUE KEY title(title),
    PRIMARY KEY (playlist_id)
)  ENGINE=INNODB;


CREATE TABLE IF NOT EXISTS song (
    song_id CHAR(36),
    title VARCHAR(255) NOT NULL,
    filename VARCHAR(255),
    description TEXT,
    duration DOUBLE NULL DEFAULT NULL,
    modified_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    modified_by_id CHAR(36) NOT NULL,
    created_date TIMESTAMP NOT NULL,
    created_by_id CHAR(36) NOT NULL,
    UNIQUE KEY title(title),
    PRIMARY KEY (song_id)
)  ENGINE=INNODB;


CREATE TABLE IF NOT EXISTS playlist_song (
    playlist_song_id CHAR(36),
    song_id CHAR(36) NOT NULL,
    playlist_id CHAR(36) NOT NULL,
    order_index INT NOT NULL DEFAULT 1,
    created_date TIMESTAMP NOT NULL,
    created_by_id CHAR(36) NOT NULL,
    FOREIGN KEY (song_id) REFERENCES song(song_id),
    FOREIGN KEY (playlist_id) REFERENCES playlist(playlist_id),
    PRIMARY KEY (playlist_song_id)
)  ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS album (
    album_id CHAR(36),
    title VARCHAR(64) NOT NULL,
    description TEXT,
    playlist_id CHAR(36),
    artwork_filename VARCHAR(255) NULL,
    publish_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    modified_by_id CHAR(36) NOT NULL,
    created_date TIMESTAMP NOT NULL,
    created_by_id CHAR(36) NOT NULL,
    FOREIGN KEY (playlist_id) REFERENCES playlist(playlist_id),
    UNIQUE KEY title(title),
    PRIMARY KEY (album_id)
)  ENGINE=INNODB;
