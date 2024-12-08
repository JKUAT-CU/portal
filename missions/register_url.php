<?php
// INCLUDE ACCESS TOKEN FILE
include 'accessToken.php';
include 'securitycredential.php';

$registerurl = 'https://api.safaricom.co.ke/mpesa/c2b/v2/registerurl';
$BusinessShortCode = '921961';
$confirmationUrl = 'https://mission.jkuatcu.org/confirmation_url.php';
$validationUrl = 'https://mission.jkuatcu.org/validation_url.php';

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $registerurl);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $access_token
));

$data = array(
    'ShortCode' => $BusinessShortCode,
    'ResponseType' => 'Completed',
    'ConfirmationURL' => $confirmationUrl,
    'ValidationURL' => $validationUrl,
);

$data_string = json_encode($data);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

try {
    $curl_response = curl_exec($curl);
    
    // Check for cURL errors
    if ($curl_response === false) {
        throw new Exception(curl_error($curl));
    }
    
    // Log successful response
    file_put_contents('curl_logs.txt', 'Success: ' . $curl_response . PHP_EOL, FILE_APPEND);
    
    echo $curl_response;
} catch (Exception $e) {
    // Log error message
    file_put_contents('curl_logs.txt', 'Error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    
    // Output error message
    echo 'Error: ' . $e->getMessage();
} finally {
    // Close cURL resource
    curl_close($curl);
}
?>
