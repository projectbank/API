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

$nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid']));
$pin = str_replace(' ', '', htmlspecialchars($_POST['pin']));
$name = htmlspecialchars($_POST['name']);

if (isset($nuid)) { // There should be an NUID set
    if (isset($pin)) { // There should be a PIN set
        if (isset($name)) { // There should be a name set
            if (strlen($pin) === 4) { // The length of the PIN should be 4
                if (strlen($nuid) === 8) { // The length of the NUID should be 8
                    if (strlen($name) <= 40) { // The length of the name should be lower than 41

                        // The iban_generator.php contains the function `ibanGenerator($countryCode, $bankCode)`
                        include 'functions/iban_generator.php';

                        // Generate an IBAN
                        $iban = ibanGenerator("SU", "USSR");

                        // Generate a hashed PIN code
                        $pinHashed = md5($pin);

                        // Everything seems alright, we can create the client in the database
                        $sql = "INSERT INTO clients (nuid, iban, name, saldo, pin_attempts, pin) VALUES ('$nuid', '$iban', '$name', 0, 0, '$pinHashed')";

                        // Check whether the call succeeded
                        if ($conn->query($sql) === TRUE) {
                            // Return the generated IBAN
                            $response = array('iban' => $iban);
                        } else {
                            $response = array('error' => 'The call failed. The reason is unknown.');;
                        }

                    } else {
                        $response = array('error' => 'Name is more than 40 characters.');
                    }
                } else {
                    $response = array('error' => 'NUID is not 8 charachters.');
                }
            } else {
                $response = array('error' => 'PIN is not 4 characters.');
            }
        } else {
            $response = array('error' => 'No name entered.');
        }
    } else {
        $response = array('error' => 'No PIN entered.');
    }
} else {
    $response = array('error' => 'No NUID entered.');
}

echo json_encode($response);
