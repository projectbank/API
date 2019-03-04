<?php

header('Content-Type: application/json');

require_once 'client.php';

$nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid']));
$pin = str_replace(' ', '', htmlspecialchars($_POST['pin']));

$client = new Client($nuid, $pin);

$response = $client->checkSaldo();

if (!$response) {
    echo json_encode(array('error' => 'Could not log in.'));
} else {
    echo json_encode(array('saldo' => $response));
}
