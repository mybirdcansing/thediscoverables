<?php
    require '../../vendor/autoload.php';
    
    spl_autoload_register(function ($className) {
            //class directories
        $directorys = array(
            'connecters/',
            'objects/',
            'handlers/user/',
            ''
        );
        
        echo "\n\nAutoloaded:\n";
        //for each directory
        foreach($directorys as $directory)
        {

            //see if the file exsists
            $f = $directory . $className . '.php';
            if(file_exists($f))
            {
                echo "- $f\n";
                require_once($f);
                //only require the class once, so quit after to save effort (if you got more, then name them something else 
                return;
            }
        }
    });