<?php
    function executeBackgroundScript($targetPath) {
        $phpPath = "php";
        $cmd = " $phpPath $targetPath";
        execInBackground($cmd);
        // $proc=new BackgroundProcess();
        // $proc->setCmd($cmd);
        // $proc->start();
    }

    function execInBackground($cmd) {
        if (substr(php_uname(), 0, 7) == "Windows"){
            pclose(popen("start /B ". $cmd, "r")); 
        }
        else {
            exec($cmd . " > /dev/null &");  
        }
    }

?>