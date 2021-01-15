<?php

/**
 * this page is used by data.php to provide a json response for all errors
 */
require_once 'includes/includes.php';

$connection = new connection();

$result = $connection->errors_getAllErrorsFromMostRecentJob();

$json = json_encode($result);

print($json);