<?php
require_once __dir__ . '/../objects/User.php';

class UserData
{ 
	private $dbConnection = null;

    function __construct($dbConnection) 
    {
        $this->dbConnection = $dbConnection;
    }

	public function findAll()
    {
        $statement = "
            SELECT 
				user_id,
				username,
				first_name,
				last_name,
				email
		  	FROM 
		  		user
		  	WHERE user_status_id = 1;
        ";

        try {
			$statement = $this->dbConnection->query($statement);
            $users = [];
			while ($row = $statement->fetch_assoc()) {
			    $users[] = $this->_rowToUser($row);
			}
            return $users;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function find($id) {
        $sql = "
            SELECT 
				user_id,
				username,
				first_name,
				last_name,
				email
		  	FROM 
		  		user
		  	WHERE user_status_id = 1 AND user_id = ?;
        ";

		$stmt = $this->dbConnection->prepare($sql);
		$stmt->bind_param("s", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows == 1) {
		    $row = $result->fetch_assoc();
		    $user = $this->_rowToUser($row);
		    return $user;
		} else {
			return 0;
		}   
    }

    public function insert(User $user) {

        $sql = "
			INSERT INTO 
				user (
                    user_id, 
					username, 
					email,
					first_name, 
					last_name, 
					password, 
					user_status_id, modified_date, created_date)
			VALUES (?, ?, ?, ?, ?, ?, 1, now(), now());
        ";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $hashedPassword = password_hash($user->password, PASSWORD_DEFAULT);
            $userId = GUID();
            $stmt->bind_param("ssssss",
                                $userId,
            					$user->username,
								$user->email,
								$user->firstName,
								$user->lastName,
								$hashedPassword
                            );
            $stmt->execute();
            $stmt->store_result();
            return $userId;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        } 
    }

    public function update($id, Array $input) {

        $sql = "
            UPDATE user
               SET
                    username = ?,
                    email = ?,
                    first_name = ?,
                    last_name  = ?,
                    password = ?,
                    modified_date = now()
                WHERE user_id = ?;
        ";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $hashedPassword = password_hash($user->password, PASSWORD_DEFAULT);
            $stmt->bind_param("ssssss", 
                                $user->username,
                                $user->email,
                                $user->firstName,
                                $user->lastName,
                                $hashedPassword,
                                $user->id
                            );
            $stmt->execute();
            $stmt->store_result();
            return $user->id;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function delete($id) {
        $sql = "
            DELETE FROM user
            WHERE user_id = ?;
        ";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("s", $user->id);
            return $stmt->rowCount;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

	public function getAuthenticatedUser($username, $password) {
		$sql = "
		  	SELECT 
				user_id,
				username,
				first_name,
				last_name,
				email,
				password
		  	FROM user 
		  	WHERE user_status_id = 1 
		  	AND username = ?;
		";

		$stmt = $this->dbConnection->prepare($sql);
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows == 1) {
		    $row = $result->fetch_assoc();
            if (password_verify($password, $row["password"])) {
                $user = $this->_rowToUser($row);
                return $user;
            }
		}
		return 0;
	}

	private function _rowToUser($row) {
	    $user = new User();
	    $user->username = $row["username"];
	    $user->id = $row["user_id"];
	    $user->email = $row["email"];
	    $user->firstName = $row["first_name"];
	    $user->lastName = $row["last_name"];
	    return $user;
	}
} 