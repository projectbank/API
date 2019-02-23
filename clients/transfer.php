<?php

header('Content-Type: application/json');

require_once 'client.php';

/*
To sanitize the input, we:

1. strip the whitespace
2. replace all html characters

After we have done that, we will check if all of the requirements are met.
This will be done for each specific case, so we can return specific error codes.
 */

$nuid = str_replace(' ', '', htmlspecialchars($_GET['nuid']));
$pin = str_replace(' ', '', htmlspecialchars($_GET['pin']));
$amount = str_replace(' ', '', htmlspecialchars($_GET['amount']));
$recipient = str_replace(' ', '', htmlspecialchars($_GET['recipient']));

$client = new Client($nuid, $pin);

$response = $client->transfer($amount, $recipient);

echo json_encode(array('saldo' => $response));

?>