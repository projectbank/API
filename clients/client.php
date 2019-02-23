<?php

class Client
{
    /* Details that every client should have */
    private $nuid, $pin, $conn;

    /* Set the default details */
    public function __construct($nuid, $pin)
    {
        /* Set the NUID and PIN of the client globally */
        $this->nuid = $nuid;
        /* Hash the pin to match the database */
        $this->pin = md5($pin);

        /* Include connection details */
        include_once '../conn.php';
        $this->conn = $conn;
    }

    private function validateCredentials(): boolean
    {
        /* The response is false on default */
        $response = false;

        if (isset($this->nuid)) { // There should be an NUID set
            if (isset($this->pin)) { // There should be a PIN set
                if (strlen($this->pin) === 4) { // The length of the PIN should be 4
                    if (strlen($this->nuid) === 8) { // The length of the NUID should be 8
                        /* Hurray! The credentials are at least the right format (we hope) */
                        $response = true;
                    }
                }
            }
        }
        return $response;
    }

    public function checkLogin(): int
    {
        /* This function can return 3 different integers:
        0. The user credentials are OK
        1. The user credentials are wrong
        2. The user has entered his PIN wrong 3 times
         */

        /* Create a query to select all of the necessary data from the database */
        $sql = "SELECT saldo, pin, pin_attempts FROM clients WHERE nuid = '$this->nuid'";

        /* Store the result of that query in an object */
        $result = $this->conn->query($sql);
        $obj = $result->fetch_object();

        if ($obj->pin_attempts < 3) {
            if ($obj->pin == $this->pin) {
                /* Reset the amount of login attempts */
                $sql = "UPDATE clients SET pin_attempts = 0 WHERE nuid='$this->nuid'";
                $this->conn->query($sql);
                $response = 0;
            } else {
                /* Update the amount of login attempts */
                $sql = "UPDATE clients SET pin_attempts = pin_attempts + 1 WHERE nuid='$this->nuid'";
                $this->conn->query($sql);
                $response = 1;
            }
        } else {
            $response = 2;
        }

        return $response;
    }

    public function checkSaldo()
    {
        if ($this->checkLogin() == 0) {
            /* Create a query to select all of the necessary data from the database */
            $sql = "SELECT saldo FROM clients WHERE nuid = '$this->nuid'";

            /* Store the result of that query in an object */
            $result = $this->conn->query($sql);
            $obj = $result->fetch_object();

            $response = $obj->saldo;
        } else {
            /* It is the responsibility of the bank to check whether the credentials are correct */
            $response = null;
        }

        return $response;
    }
}
