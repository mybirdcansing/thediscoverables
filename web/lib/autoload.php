<?php
    require_once __dir__ . '/../../vendor/autoload.php';
    
    spl_autoload_register(function ($className) {
            //class directories
        $directorys = array(
            'connecters/',
            'objects/',
            '.'
        );
        
        echo "\n\nAutoloaded:\n";

        foreach($directorys as $directory)
        {
            $f = $directory . $className . '.php';
            if(file_exists($f))
            {
                echo "- $f\n";
                require_once($f);
                // only require the class once, 
                // so quit after to save effort 
                // (if you got more, then name them something else)
                return;
            }
        }
    });