<?php
// Include additional PHP scripts

include 'confirmation_url.php';

// Set the content type header to JSON
header("Content-Type: application/json");

// Get the raw POST data from the STK callback response
$stkCallbackResponse = file_get_contents('php://input');

// Specify the path to the log file
$logFile = "Mpesastkresponse.json";

// Open the log file in append mode
$log = fopen($logFile, "a");

// Write the raw JSON data to the log file
fwrite($log, $stkCallbackResponse . PHP_EOL);

// Close the log file
fclose($log);
