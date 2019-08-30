<?php
require_once __dir__ . '/data_access.php';
require_once __dir__ . '/../objects/user.php';

class UserData
{ 
	
    function __construct() 
    {
    } 

	public function getAuthenticatedUser($username, $password) {
		
		$sql = <<<EOT
		  	SELECT 
				au.admin_user_id,
				au.username,
				au.first_name,
				au.last_name,
				au.email
		  	FROM admin_user au 
		  	WHERE au.admin_user_status_id = 1 
		  	AND au.username = ? 
		  	AND au.password = ?
EOT;

		$conn = DataAccess::getConnection();
		try { 
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("ss", $username, $password);
			$stmt->execute();
			$result = $stmt->get_result();
			if ($result->num_rows == 1) {
			    $row = $result->fetch_assoc();
			    $user = new User();
			    $user->setUsername($row["username"]);
			    $user->setId($row["admin_user_id"]);
			    $user->setEmail($row["email"]);
			    $user->setFirstName($row["first_name"]);
			    $user->setLastName($row["last_name"]);
			    return $user;
			} else {
				return 0;
			}
		} finally {
		  	$conn->close();
		}
	}
} 