<?php
// error codes and messages
define("USERNAME_TAKEN_CODE", 1000);
define("USERNAME_TAKEN_MESSAGE", "Username (%s) is taken. Please try another.");
define("USERNAME_BLANK_CODE", 1008);
define("USERNAME_BLANK_MESSAGE", "Please enter a username.");
define("USERNAME_LONG_CODE", 1009);
define("USERNAME_LONG_MESSAGE", "The username must be 64 characters or less.");
define("USERNAME_SHORT_CODE", 1010);
define("USERNAME_SHORT_MESSAGE", "The username must be at least 4 characters.");
define("USERNAME_INVALID_CODE", 1011);
define("USERNAME_INVALID_MESSAGE", "The username may not contain invalid characters (&#x27; \ &#x60; | ; &#x22; &#x3C; &#x3E; \).");

define("EMAIL_TAKEN_CODE", 1001);
define("EMAIL_TAKEN_MESSAGE", "Email address (%s) is taken. Please use another.");
define("EMAIL_INVALID_CODE", 1002);
define("EMAIL_INVALID_MESSAGE", "Please enter a valid email address.");
define("EMAIL_BLANK_CODE", 1003);
define("EMAIL_BLANK_MESSAGE", "Please enter an email address.");

define("PASSWORD_BLANK_CODE", 1004);
define("PASSWORD_BLANK_MESSAGE", "Please enter a password.");
define("PASSWORD_LONG_CODE", 1005);
define("PASSWORD_LONG_MESSAGE", "The password must be 64 characters or less.");
define("PASSWORD_SHORT_CODE", 1006);
define("PASSWORD_SHORT_MESSAGE", "The password must be at least 6 characters.");
define("PASSWORD_INVALID_CODE", 1007);
define("PASSWORD_INVALID_MESSAGE", "The password may not contain invalid characters (&#x27; \ &#x60; | ; @ &#x22; &#x3C; &#x3E; \).");

define("FIRSTNAME_LONG_CODE", 1012);
define("FIRSTNAME_LONG_MESSAGE", "The first name must be 64 characters or less.");
define("FIRSTNAME_INVALID_CODE", 1013);
define("FIRSTNAME_INVALID_MESSAGE", "The first name may not contain invalid characters (&#x27; \ &#x60; | ; @ &#x22; &#x3C; &#x3E; \).");

define("LASTNAME_LONG_CODE", 1014);
define("LASTNAME_LONG_MESSAGE", "The last name must be 64 characters or less.");
define("LASTNAME_INVALID_CODE", 1015);
define("LASTNAME_INVALID_MESSAGE", "The last name may not contain invalid characters (&#x27; \ &#x60; | ; @ &#x22; &#x3C; &#x3E; \).");

define("AUTH_FAILED_CODE", 1016);
define("AUTH_FAILED_MESSAGE", "Authentication failed, please try again.");

define("USER_NOT_FOUND_CODE", 1017);
define("USER_NOT_FOUND_MESSAGE", "User not found.");

define("PASSWORD_TOKEN_NOT_FOUND_CODE", 1018);
define("PASSWORD_TOKEN_NOT_FOUND_MESSAGE", "Password token not found or already used.");

define("ENTER_EMAIL_OR_USERNAME_CODE", 1019);
define("ENTER_EMAIL_OR_USERNAME_MESSAGE", "Please enter an email or a username.");

define("TITLE_BLANK_CODE", 1020);
define("TITLE_BLANK_MESSAGE", "Please enter a title.");
define("TITLE_LONG_CODE", 1021);
define("TITLE_LONG_MESSAGE", "The title must be 255 characters or less.");
define("TITLE_INVALID_CODE", 1022);
define("TITLE_INVALID_MESSAGE", "The title may not contain invalid characters (&#x27; \ &#x60; | ; @ &#x22; &#x3C; &#x3E; \).");

define("TITLE_TAKEN_CODE", 1023);
define("TITLE_TAKEN_MESSAGE", "The title (%s) is taken. Please use another.");

define("PLAYLIST_NOT_FOUND_CODE", 1023);
define("PLAYLIST_NOT_FOUND_MESSAGE", "Playlist not found.");

define("SONG_NOT_FOUND_CODE", 1024);
define("SONG_NOT_FOUND_MESSAGE", "Song not found.");

define("ACTIVE_USER_STATUS_ID", 1);
define("INACTIVE_USER_STATUS_ID", 2);

define("CREATE_ACTION", "CREATE");
define("UPDATE_ACTION", "UPDATE");
define("DELETE_ACTION", "DELETE");
define("GET_ACTION", "GET");
define("UPDATE_PASSWORD_ACTION", "UPDATE_PASSWORD");
define("ADD_TO_PLAYLIST_ACTION", "ADD_TO_PLAYLIST_ACTION");
define("REMOVE_FROM_PLAYLIST_ACTION", "REMOVE_FROM_PLAYLIST_ACTION");


// global methods
function GUID()
{
    if (function_exists('com_create_guid') === true) {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}