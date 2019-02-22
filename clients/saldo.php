<?php

header('Content-Type: application/json');

include_once '../conn.php';

/*
To sanitize the input, we:

1. strip the whitespace
2. replace all html characters

After we have done that, we will check if all of the requirements are met.
This will be done for each specific case, so we can return specific error codes.
 */

$nuid = str_replace(' ', '', htmlspecialchars($_GET['nuid']));
$pin = str_replace(' ', '', htmlspecialchars($_GET['pin']));

if (isset($nuid)) { // There should be an NUID set
    if (isset($pin)) { // There should be a PIN set
        if (strlen($pin) === 4) { // The length of the PIN should be 4
            if (strlen($nuid) === 8) { // The length of the NUID should be 8

                // Hash the PIN to match the database
                $pinHashed = md5($pin);

                // Everything seems alright, we can look up the saldo of the user
                $sql = "SELECT saldo, pin FROM clients WHERE nuid = '$nuid'";
                $result = $conn->query($sql);
                $obj = $result->fetch_object();

                if ($obj->pin == $pinHashed) {
                    // Check if there was something found
                    if ($obj->saldo != null) {
                        $response = array('saldo' => $obj->saldo); // Return the saldo
                    } else {
                        $response = array('error' => 'Nothing found.');
                    }
                } else {
                    // update attempts = attempts + 1
                    $response = array('error' => 'Wrong PIN.');
                }

            } else {
                $response = array('error' => 'NUID is not 8 charachters.');
            }
        } else {
            $response = array('error' => 'PIN is not 4 characters.');
        }
    } else {
        $response = array('error' => 'No PIN entered.');
    }
} else {
    $response = array('error' => 'No NUID entered.');
}

echo json_encode($response);
