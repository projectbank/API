<?php

header('Content-Type: application/json');

require_once 'client.php';

$nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid']));
$pin = str_replace(' ', '', htmlspecialchars($_POST['pin']));
$amount = str_replace(' ', '', htmlspecialchars($_POST['amount']));
if (isset($_POST['recipient'])) {
    $recipient = str_replace(' ', '', htmlspecialchars($_POST['recipient']));
}

$client = new Client($nuid, $pin);

if (isset($recipient)) {
    $response = $client->transfer($amount, $recipient);
} else {
    $response = $client->transfer($amount);
}

/* Give useful errors instead of error codes. */
if ($response >= 0) {
    echo json_encode(array('saldo' => $response));
} else if ($response == -1) {
    echo json_encode(array('error' => 'The recipient does not exist.'));
} else if ($response == -2) {
    echo json_encode(array('error' => 'You do not have enough balance.'));
}

?>
