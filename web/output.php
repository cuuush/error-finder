<?php

require_once 'includes/includes.php';

$connection = new connection();

$output = $connection->errors_getOutput();

foreach($output as $row){
    $rowtext = '<p><b>' . $row['time'] . '</b>: ' . $row['log'] . '</p>';
    print($rowtext);
}

// time log endpoint_id