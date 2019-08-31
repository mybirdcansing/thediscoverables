<?php
require_once __dir__ . '/../objects/user.php';

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
				au.admin_user_id,
				au.username,
				au.first_name,
				au.last_name,
				au.email
		  	FROM 
		  		admin_user au 
		  	WHERE au.admin_user_status_id = 1;
        ";

        try {
			$statement = $this->dbConnection->query($statement);
            $users = [];
			while ($row = $statement->fetch_assoc()) {
			    $users[] = $this->getUserFromRow($row);
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
				au.admin_user_id,
				au.username,
				au.first_name,
				au.last_name,
				au.email
		  	FROM 
		  		admin_user au 
		  	WHERE au.admin_user_status_id = 1 AND au.admin_user_id = ?;
        ";

		$stmt = $this->dbConnection->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows == 1) {
		    $row = $result->fetch_assoc();
		    $user = $this->getUserFromRow($row);
		    return $user;
		} else {
			return 0;
		}   
    }

    public function insert(Array $input)
    {
        $statement = "
			INSERT INTO 
				admin_user (username, first_name, last_name, password, email, admin_user_status_id, modified_date, created_date)
			VALUES (:username, :firstname, :lastname, :password, :email, 1, now(), now());
        ";
        try {
            $statement = $this->dbConnection->prepare($statement);
            $statement->execute(array(
            	'username' => $input['username'],
                'firstname' => $input['firstname'] ?? null,
                'lastname'  => $input['lastname'] ?? null,
                'password' => $hashed_password = password_hash($input['password'], PASSWORD_DEFAULT),
                'email' => $input['email']
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function update($id, Array $input)
    {
        $statement = "
            UPDATE admin_user
            SET 
                username = :username,
                email = :email,
                firstname = :firstname,
                lastname  = :lastname,
                password = :password,
                admin_user_status_id = :admin_user_status_id,
                modified_date = now()
            WHERE id = :id;
        ";
        $username = $input['username'];
        if (intval($input['admin_user_status_id']) === DISABLED_USER_STATUS_ID) {
        	$username = $username . "_DISABLED_" . (microtime(true) * 10000000);
        }
        try {
            $statement = $this->dbConnection->prepare($statement);
            $statement->execute(array(
                'id' => (int) $id,
                'username' => $input['username'],
                'firstname' => $input['firstname'],
                'lastname'  => $input['lastname'],
                'password' => $hashed_password = password_hash($input['password'], PASSWORD_DEFAULT),
                'email' => $input['email'],
                'admin_user_status_id' => $input['admin_user_status_id'] || 1
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function delete($id)
    {
        $statement = "
            DELETE FROM person
            WHERE id = :id;
        ";

        try {
            $statement = $this->dbConnection->prepare($statement);
            $statement->execute(array('id' => $id));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

	public function getAuthenticatedUser($username, $password) {
		$sql = "
		  	SELECT 
				au.admin_user_id,
				au.username,
				au.first_name,
				au.last_name,
				au.email,
				au.password
		  	FROM admin_user au 
		  	WHERE au.admin_user_status_id = 1 
		  	AND au.username = ?;
		";

		$stmt = $this->dbConnection->prepare($sql);
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows == 1) {
		    $row = $result->fetch_assoc();
		    $user = $this->getUserFromRow($row);
		    return password_verify($password, $row["password"]) ? $user : 0;
		} else {
			return 0;
		}
	}

	private function getUserFromRow($row) {
	    $user = new User();
	    $user->setUsername($row["username"]);
	    $user->setId($row["admin_user_id"]);
	    $user->setEmail($row["email"]);
	    $user->setFirstName($row["first_name"]);
	    $user->setLastName($row["last_name"]);
	    return $user;
	}
} 