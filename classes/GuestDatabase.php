<?php

namespace TSD\UniFiGuestHubSpotPortal;

use PDO;

class GuestDatabase
{
    private $databaseFile = '/var/www/crossfit-portal/database/guests.db';
    private $pdo = null;
    
    public function __construct()
    {
        if ($this->pdo == null)
        {
            $this->pdo = new PDO("sqlite:".$this->databaseFile);
            $this->createSchema();
        }
    }
    
    public function getGuestMac($email)
    {
        $query = "SELECT mac from GuestMacAddresses WHERE email=?;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['mac'];
    }
    
    public function insertGuest($email, $mac)
    {
        $query = "INSERT INTO GuestMacAddresses (email, mac) VALUES(:email,:mac);";
        return $this->pdo->prepare($query)->execute(['email' => $email, 'mac' =>$mac]);
    }
    
    public function updateGuestMac($email, $mac)
    {
        $query = "UPDATE GuestMacAddresses SET mac=:mac WHERE email=:email";
        return $this->pdo->prepare($query)->execute(['email' => $email, 'mac' =>$mac]);
    }
    
    private function createSchema()
    {
        $prep = $this->pdo->query('CREATE TABLE IF NOT EXISTS 
            GuestMacAddresses(email VARCHAR(255) NOT NULL PRIMARY KEY, mac VARCHAR(17));');
            
        $prep->execute();
    }
}

?>