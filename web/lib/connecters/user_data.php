<?php
require_once __dir__ . '/../objects/user.php';
require_once __dir__ . '/../objects/users.php';

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
            // $result = $statement->fetch(\PDO::FETCH_ASSOC);
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
            INSERT INTO person 
                (firstname, lastname, firstparent_id, secondparent_id)
            VALUES
                (:firstname, :lastname, :firstparent_id, :secondparent_id);
        ";

        try {
            $statement = $this->dbConnection->prepare($statement);
            $statement->execute(array(
                'firstname' => $input['firstname'],
                'lastname'  => $input['lastname'],
                'firstparent_id' => $input['firstparent_id'] ?? null,
                'secondparent_id' => $input['secondparent_id'] ?? null,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function update($id, Array $input)
    {
        $statement = "
            UPDATE person
            SET 
                firstname = :firstname,
                lastname  = :lastname,
                firstparent_id = :firstparent_id,
                secondparent_id = :secondparent_id
            WHERE id = :id;
        ";

        try {
            $statement = $this->dbConnection->prepare($statement);
            $statement->execute(array(
                'id' => (int) $id,
                'firstname' => $input['firstname'],
                'lastname'  => $input['lastname'],
                'firstparent_id' => $input['firstparent_id'] ?? null,
                'secondparent_id' => $input['secondparent_id'] ?? null,
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
				au.email
		  	FROM admin_user au 
		  	WHERE au.admin_user_status_id = 1 
		  	AND au.username = ? 
		  	AND au.password = ?;
		";

		// $conn = (new DataAccess())->getConnection();
		try { 
			$stmt = $this->dbConnection->prepare($sql);
			$stmt->bind_param("ss", $username, $password);
			$stmt->execute();
			$result = $stmt->get_result();
			if ($result->num_rows == 1) {
			    $row = $result->fetch_assoc();
			    $user = $this->getUserFromRow($row);
			    return $user;
			} else {
				return 0;
			}
		} finally {
		  	// $this->dbConnection->close();
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