<?php
declare(strict_types=1);

class User {

    public $id;
    public $username;
    public $firstName;
    public $lastName;
    public $email;
    public $password;
    public $statusId;

    function __construct() {
    }
    
    public function expose() {
        return get_object_vars($this);
    }


    static function fromJson($json)
    {
       $result = new static();
       $objJson = json_decode($json);
       $class = new \ReflectionClass($result);
       $publicProps = $class->getProperties(\ReflectionProperty::IS_PUBLIC);
       foreach ($publicProps as $prop) {
            $propName = $prop->name;
            if (isset($objJson->$propName)) {
                $prop->setValue($result, $objJson->$propName);
            }
            else {
                $prop->setValue($result, null);
            }
       }
       return $result;
    }

   function toJson()
   {
      return json_encode($this);
   }
}