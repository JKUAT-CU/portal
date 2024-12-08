<?php
header("Content-Type: application/json");
$QueueTimeOutURLCallbackResponse = file_get_contents('php://input');
$logFile = "ResultURL.json";
$log = fopen($logFile, "w");
fwrite($log, $QueueTimeOutURLCallbackResponse);
fclose($log);