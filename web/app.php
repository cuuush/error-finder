<?php

require_once 'includes/includes.php';

global $EFPythonFile;
global $EFPythonRuntime;
global $osEnvironment;

if(isset($_POST['action'])) {

    $action = $_POST['action'];

    $connection = new connection();

    if ($action == 'start'){
        $file = $EFPythonFile;


        if(! file_exists($file)) {
            die('nofile');
        }


        switch ($osEnvironment) {
            case "WINDOWS":
                $test = "\"$EFPythonRuntime\" \"$file\"";
                shell_exec("\"$EFPythonRuntime\" $file");
                break;
            case "LINUX":
                shell_exec("python3 $file > /dev/null 2>&1 &");
                break;

        }

        print('started');
    }

    else if ($action == 'stop'){
        $job = $connection->errors_getMostRecentJob();
        $connection->errors_killJob($job['job_id']);
        print('stopped');
    }
    else if ($action == 'status'){
        $job = $connection->errors_getMostRecentJob();

        if($job['active'] == 1){
            print("running");
        }
        elseif($job['killed'] == 1){
            print("killed");
        }
        elseif($job['finished'] == 1){
            print("finished");
        }

    }
    else if ($action == 'percent'){

        $job = $connection->errors_getMostRecentJob();
        $current = $job['current'];
        $total = $job['total'];


        if(is_null($job))
            print('nopython');
        else if($current == $total){
            print('done');
        }
        else {
            $current = $job['current'];
            $total = $job['total'];

            $percent = round((intval($current) / intval($total)), 2) * 100;
            print($percent);
        }
    }
}


?>