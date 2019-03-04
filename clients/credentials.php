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

$nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid']));
$pin = str_replace(' ', '', htmlspecialchars($_POST['pin']));

$client = new Client($nuid, $pin);

$response = $client->checkLogin();

echo json_encode(array('status' => $response));

?>
