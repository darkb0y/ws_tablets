<?php
$ch = curl_init('');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
//curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/html',
    'Content-Length: ' . strlen($ch))
);

$result = curl_exec($ch);
var_dump($result);
//print_r(json_decode($result, true));
?>