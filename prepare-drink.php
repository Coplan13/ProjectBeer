<?php
define('PREP_TIME', 5);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $drinkId = $_REQUEST['drinkId'];
    $session_id = $_REQUEST['session_id'];
    sleep(PREP_TIME);
    // ... preparation logic ...
    //http_response_code(200);

    $data = array('drinkId' => $drinkId,'session_id' => $session_id);
    $curlHandle = curl_init('http://localhost:8888/exampleWithCurl.php/remove');
    curl_setopt($curlHandle, CURLOPT_TIMEOUT_MS, 1);
    curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);

    $curlResponse = curl_exec($curlHandle);
    curl_close($curlHandle);

}