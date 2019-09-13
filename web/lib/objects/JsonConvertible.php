<?php
declare(strict_types=1);

abstract class JsonConvertible
{
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