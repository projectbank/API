<?php

class Client
{
    private $nuid, $pin, $conn;

    public function __construct($nuid, $pin)
    {
        $this->nuid = $nuid;
        $this->pin = md5($pin);

        include_once '../conn.php';
        $this->conn = $conn;
    }

    private function validateCredentials(): boolean
    {
        $response = false;

        if (isset($this->nuid)) {
            if (isset($this->pin)) {
                if (strlen($this->pin) === 4) {
                    if (strlen($this->nuid) === 8) {
                        $response = true;
                    }
                }
            }
        }
        return $response;
    }

    public function checkLogin(): int
    {

        if ($this->validateCredentials()) {
          $sql = "SELECT saldo, pin, pin_attempts FROM clients WHERE nuid = '$this->nuid'";

          $result = $this->conn->query($sql);
          $obj = $result->fetch_object();

          if ($obj->pin_attempts < 3) {
              if ($obj->pin == $this->pin) {
                  $sql = "UPDATE clients SET pin_attempts = 0 WHERE nuid='$this->nuid'";
                  $this->conn->query($sql);
                  $response = 0; // The credentials are OK
              } else {
                  $sql = "UPDATE clients SET pin_attempts = pin_attempts + 1 WHERE nuid='$this->nuid'";
                  $this->conn->query($sql);
                  $response = 1; // The credentials are wrong
              }
          } else {
              $response = 2; // The user has entered the pin wrong 3 times
          }
        } else {
            $response = 3; // The validation failed
        }

        return $response;
    }

    public function checkSaldo()
    {
        if ($this->checkLogin() == 0) {
            $sql = "SELECT saldo FROM clients WHERE nuid = '$this->nuid'";
            $result = $this->conn->query($sql);
            $obj = $result->fetch_object();

            $response = $obj->saldo;
        } else {
            $response = null;
        }
        return $response;
    }

    public function transfer($amount, $recipient = "00000000000000"): int
    {

        $saldo = $this->checkSaldo();
        if ($amount <= $saldo) {
            /* Select the recipient */
            $sql = "SELECT iban FROM clients WHERE iban = '$recipient'";
            $result = $this->conn->query($sql);
            $obj = $result->fetch_object();

            if ($obj != null) {
                $newSaldo = $saldo - $amount;

                /* Set the balance of the sender */
                $sql = "UPDATE clients SET saldo = '$newSaldo' WHERE nuid = '$this->nuid'";
                $this->conn->query($sql);

                /* Select the IBAN of the sender */
                $sql = "SELECT iban FROM clients WHERE nuid = '$this->nuid'";
                $result = $this->conn->query($sql);
                $obj = $result->fetch_object();

                /* Insert the transaction */
                $sql = "INSERT INTO transactions (iban_sender, iban_recipient, amount) VALUES ('$obj->iban', '$recipient', '$amount')";
                $this->conn->query($sql);
                $response = $newSaldo;

                /* Set the balance of the recipient */
                $sql = "UPDATE clients SET saldo = saldo + '$amount' WHERE iban = '$recipient'";
                $this->conn->query($sql);
            } else {
                $response = -1;
            }
        } else {
            $response = -2;
        }
        return $response;
    }
}
