<?php

require_once '../includes/includes.php';

$connection = new connection();

if(isset($_GET['job']) && is_numeric($_GET['job'])){
    $latestJob = $connection->errors_getNewErrorsFromJobID($_GET['job']);
}
else{
    $latestJob = $connection->errors_getNewErrorsFromJobID();
}

$json = json_encode($latestJob);
print_r($json);

