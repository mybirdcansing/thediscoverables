<?php
require_once __dir__ . '/../objects/User.php';
require_once __dir__ . '/../objects/DuplicateUsernameException.php';
require_once __dir__ . '/../objects/DuplicateEmailException.php';

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
        } catch (\mysqli_sql_exception $e) {
            error_log($e->getMessage());
            throw $e;
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

    public function getByEmail($email, $user_status_id = 1)
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
            WHERE email = ? AND $user_status_id = ?;
        ";

        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("si", $email, $user_status_id);
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
    public function insert(User $user, User $administrator)
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
                    modified_by_id,
                    created_date,
                    created_by_id
                )
			VALUES (?, ?, ?, ?, ?, ?, 1, now(), ?, now(), ?);
        ";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $hashedPassword = password_hash($user->password, PASSWORD_DEFAULT);
            $userId = GUID();
            $stmt->bind_param("ssssssss",
                $userId,
				$user->username,
				$user->email,
				$user->firstName,
				$user->lastName,
				$hashedPassword,
                $administrator->id,
                $administrator->id
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
        }
    }

    public function update(User $user, User $administrator)
    {
        $sql = "
            UPDATE user
               SET
                    username = ?,
                    email = ?,
                    first_name = ?,
                    last_name  = ?,
                    user_status_id = ?,
                    modified_date = now(),
                    modified_by_id = ?
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
                $user->statusId,
                $administrator->id,
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
        }
    }

    public function markPasswordTokenUsed($token)
    {
        $sql = "
            UPDATE reset_password
            SET used = ''
            WHERE token = ?;
        ";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows;
        } catch (\mysqli_sql_exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function updatePassword($id, $password, User $administrator)
    {
        $sql = "
            UPDATE user
               SET
                    password = ?,
                    modified_by_id = ?
                WHERE user_id = ?;
        ";
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("sss", $hashedPassword, $administrator->id, $id);
            $stmt->execute();
            $stmt->store_result();
            return $id;
        } catch (\mysqli_sql_exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function insertPasswordResetToken(User $user, User $administrator)
    {
        $sql = "
            INSERT INTO 
                reset_password (
                    token,
                    user_id,
                    expiration_date,
                    created_date,
                    created_by_id
                )
            VALUES (?, ?, DATE_ADD(now(), INTERVAL 2 DAY), now(), ?);
        ";
        try {
            $token = GUID();
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("sss", $token, $user->id, $administrator->id);
            $stmt->execute();
            $stmt->store_result();
            return $token;
        } catch (\mysqli_sql_exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function deletePasswordResetToken($token)
    {
        $sql = "
            DELETE FROM reset_password
            WHERE token = ?;
        ";
        try {
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows;
        } catch (\mysqli_sql_exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function deletePasswordResetTokens($userId)
    {
        $sql = "DELETE FROM reset_password WHERE user_id = ?;";
        try {
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $stmt->store_result();
            $rowsEffected = $stmt->num_rows;
            return $rowsEffected;
        } catch (\mysqli_sql_exception $e) {
            error_log($e->getMessage());
            throw $e;
        }    
    }


    public function getPasswordResetTokens($userId)
    {

        function tokenObj($token, $expirationDate)
        {
            return new class($token, $expirationDate) {
                public $token;
                public $expirationDate;
                public function __construct($token, $expirationDate)
                {
                    $this->token = $token;
                    $this->expirationDate = $expirationDate;
                }
            };
        }

        $sql = "
            SELECT 
                token, 
                expiration_date
            FROM reset_password 
            WHERE used IS NULL 
            AND user_id = ? 
            AND expiration_date > now() 
            ORDER BY expiration_date asc;
        ";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("s", $userId);
            
            $stmt->execute();            
            $result = $stmt->get_result();
            $tokens = [];
            while ($row = $result->fetch_assoc()) {
                $tokens[] = tokenObj($row["token"], $row["expiration_date"]);
            }
            return $tokens;
        } catch (\mysqli_sql_exception $e) {
            error_log($e->getMessage());
            throw $e;
        }    
    }

    public function getPasswordResetTokenInfo($token)
    {

        function tokenInfo($token, $expirationDate, $userId)
        {
            return new class($token, $expirationDate, $userId) {
                public $token;
                public $expirationDate;
                public $userId;

                public function __construct($token, $expirationDate, $userId)
                {
                    $this->token = $token;
                    $this->expirationDate = $expirationDate;
                    $this->userId = $userId;
                }
            };
        }

        $sql = "
            SELECT 
                token, 
                expiration_date,
                user_id
            FROM reset_password 
            WHERE token = ? 
            AND used IS NULL
            AND expiration_date > now();
        ";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                return tokenInfo(
                    $row["token"], 
                    $row["expiration_date"], 
                    $row["user_id"]
                );
            }
            return false;
        } catch (\mysqli_sql_exception $e) {
            error_log($e->getMessage());
            throw $e;
        }    
    }

    public function delete($id)
    {
        $this->deletePasswordResetTokens($id);

        $sql = "DELETE FROM user WHERE user_id = ?;";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $stmt->store_result();
            $rowsEffected = $stmt->num_rows;
            return $rowsEffected;
        } catch (\mysqli_sql_exception $e) {
            error_log($e->getMessage());
            throw $e;
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
		  	AND (username = ? OR email = ?);
		";

		$stmt = $this->dbConnection->prepare($sql);
		$stmt->bind_param("ss", $username, $username);
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
