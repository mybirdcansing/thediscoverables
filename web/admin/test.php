<?php
//error_reporting(E_ERROR | E_PARSE);

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASSWORD", 'PurplePeople3#');
define("DB_DATABASE", "thediscoverables");

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);

//Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 


$sql = "SELECT admin_user_id, username FROM admin_user";

$result = $conn->query($sql);

echo "<br>rows " . $result->num_rows . "<br>" ;

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "admin_user_id: " . $row["admin_user_id"]. " - username: " . $row["username"] . "<br>";
    }
} else {
    echo "0 results";
}
$conn->close();


?>