<?php

header('Content-Type: application/json');

include_once '../conn.php';

$nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid']));
$pin = str_replace(' ', '', htmlspecialchars($_POST['pin']));
$name = htmlspecialchars($_POST['name']);

/*
It is no accidence that this page does not use client.php.
That is because of all of the validation.
You are still free to refactor it, however.
*/

if (isset($nuid)) { 
    if (isset($pin)) {
        if (isset($name)) { 
            if (strlen($pin) === 4) {
                if (strlen($nuid) === 8) {
                    if (strlen($name) <= 40) {

                        include 'functions/iban_generator.php';

                        $iban = ibanGenerator("SU", "USSR");
                        $pinHashed = md5($pin);

                        $sql = "INSERT INTO clients (nuid, iban, name, saldo, pin_attempts, pin) VALUES ('$nuid', '$iban', '$name', 0, 0, '$pinHashed')";
                        if ($conn->query($sql) === TRUE) {
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
