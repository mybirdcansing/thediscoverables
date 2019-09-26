<?php
require_once __dir__ . '/../objects/User.php';

class UserData
{ 
	private $dbConnection = null;

    function __construct($dbConnection) 
    {
        $this->dbConnection = $dbConnection;
    }

	public function findAll($activeUsers = true)
    {
        $sql = "
            SELECT 
				user_id,
				username,
				first_name,
				last_name,
				email,
                user_status_id
		  	FROM 
		  		user";
        if ($activeUsers) {
            $sql .= ' WHERE user_status_id = 1';
        } 
        $sql .= ';';

        try {
			$stmt = $this->dbConnection->query($sql);
            $users = [];
			while ($row = $stmt->fetch_assoc()) {
			    $users[] = $this->_rowToUser($row);
			}
            return $users;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function find($id)
    {
        $sql = "
            SELECT 
				user_id,
				username,
				first_name,
				last_name,
				email,
                user_status_id
		  	FROM 
		  		user
		  	WHERE user_id = ?;
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


    public function getByUsername($username, $user_status_id = 1)
    {
        $sql = "
            SELECT 
                user_id,
                username,
                first_name,
                last_name,
                email,
                user_status_id
            FROM 
                user
            WHERE username = ? AND $user_status_id = ?;
        ";

        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("si", $username, $user_status_id);
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

    public function insert(User $user)
    {

        $sql = "
			INSERT INTO 
				user (
                    user_id, 
					username, 
					email,
					first_name, 
					last_name, 
					password, 
					user_status_id, 
                    modified_date, 
                    created_date
                )
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
        } catch (mysqli_sql_exception $e) {
            $mysqliErrorMessage = $e->getMessage();
            if (strpos($mysqliErrorMessage, 'username') !== false) {
                throw new DuplicateUsernameException(
                    sprintf(USERNAME_TAKEN_MESSAGE, $user->username),
                    USERNAME_TAKEN_CODE);
            } elseif (strpos($mysqliErrorMessage, 'email') !== false) {
                throw new DuplicateEmailException(
                    sprintf(EMAIL_TAKEN_MESSAGE, $user->email),
                    EMAIL_TAKEN_CODE);
            } else {
                error_log($e->getMessage());
                throw $e;
            }
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function update($user)
    {
        $sql = "
            UPDATE user
               SET
                    username = ?,
                    email = ?,
                    first_name = ?,
                    last_name  = ?,
                    password = ?,
                    user_status_id = ?,
                    modified_date = now()
                WHERE user_id = ?;
        ";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $hashedPassword = password_hash($user->password, PASSWORD_DEFAULT);
            $stmt->bind_param("sssssss", 
                                $user->username,
                                $user->email,
                                $user->firstName,
                                $user->lastName,
                                $hashedPassword,
                                $user->statusId,
                                $user->id
                            );
            $stmt->execute();
            $stmt->store_result();
            return $user->id;
        } catch (mysqli_sql_exception $e) {
             $mysqliErrorMessage = $e->getMessage();
            if (strpos($mysqliErrorMessage, 'username') !== false) {
                throw new DuplicateUsernameException(
                    sprintf(USERNAME_TAKEN_MESSAGE, $user->username),
                    USERNAME_TAKEN_CODE);
            } elseif (strpos($mysqliErrorMessage, 'email') !== false) {
                throw new DuplicateEmailException(
                    sprintf(EMAIL_TAKEN_MESSAGE, $user->email),
                    EMAIL_TAKEN_CODE);
            } else {
                error_log($e->getMessage());
                throw $e;
            }
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function delete($id)
    {
        $sql = "
            DELETE FROM user
            WHERE user_id = ?;
        ";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $stmt->store_result();
            $rowsEffected = $stmt->num_rows;
            return $rowsEffected;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

	public function getAuthenticatedUser($username, $password)
    {
		$sql = "
		  	SELECT 
				user_id,
				username,
				first_name,
				last_name,
				email,
				password,
                user_status_id
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

	private function _rowToUser($row)
    {
	    $user = new User();
	    $user->username = $row["username"];
	    $user->id = $row["user_id"];
	    $user->email = $row["email"];
	    $user->firstName = $row["first_name"];
	    $user->lastName = $row["last_name"];
        $user->statusId = $row["user_status_id"];
	    return $user;
	}
}

class DuplicateUsernameException extends Exception{}
class DuplicateEmailException extends Exception{}