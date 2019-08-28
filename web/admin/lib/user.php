<?php 
class User 
{ 

    private $id;
    private $username;
    private $firstName;
    private $lastName;
    private $email;


    function __construct($id, $username, $email, $firstName, $lastName) 
    { 
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    } 


    public function setId(User $id){
        $this->id = $id;
    }

    public function setUsername(User $username){
        $this->username = $username;
    }

    public function setFirstName(User $firstName){
        $this->firstName = $firstName;
    }

    public function setLastName(User $lastName){
        $this->lastName = $lastName;
    }

    public function setEmail(User $email){
        $this->email = $email;
    }
} 