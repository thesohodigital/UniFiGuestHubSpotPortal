<?php

namespace TSD\UniFiGuestHubSpotPortal;

use PDO;

class GuestDatabase
{
    private $databaseFile = '/var/www/crossfit-portal/database/guests.db';
    private $pdo = null;
    
    public function __construct()
    {
        
    }
    
    public function connect()
    {
        if ($this->pdo == null)
        {
            $this->pdo = new PDO("sqlite:".Settings::$database['file']);
            $this->createSchema();
        }
    }
    
    public function getGuestDevices($email)
    {
        $query = "SELECT rowid, mac from GuestDevices WHERE email=? ORDER BY last_seen desc;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$email]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
        
    public function countGuestDevices ($email)
    {
        $query = "SELECT COUNT(*) FROM GuestDevices WHERE email=:email";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['email' => $email]);
        
        return $stmt->fetchColumn();
    }
    
    public function deleteExcessDevices($rowIds)
    {
        $in  = str_repeat('?,', count($rowIds) - 1) . '?';
        $query = "DELETE FROM GuestDevices WHERE rowid IN ($in)";
        $stmt = $this->pdo->prepare($query);
        
        return $stmt->execute($rowIds);       
    }
    
    public function updateMacLastSeen($email, $mac, $last_seen)
    {
        $query = "UPDATE GuestDevices SET last_seen=:last_seen WHERE email=:email and mac=:mac";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['email' => $email, 'mac' => $mac, 'last_seen' => $last_seen]);
        
        return $stmt->rowCount();
    } 
  
    
    public function insertGuestMac($email, $mac, $last_seen="")
    {
        $last_seen = ($last_seen === "") ? time() : $last_seen;
        $query = "INSERT INTO GuestDevices (email, mac, last_seen) VALUES(:email,:mac,:time);";
        
        return $this->pdo->prepare($query)->execute(['email' => $email, 'mac' =>$mac, 'time' => $last_seen]);
    }
    
    public function updateGuestMac($email, $mac)
    {
        $query = "UPDATE GuestDevices SET mac=:mac WHERE email=:email";
        
        return $this->pdo->prepare($query)->execute(['email' => $email, 'mac' =>$mac]);
    }
    
    private function createSchema()
    {
        $prep = $this->pdo->query('CREATE TABLE IF NOT EXISTS 
            GuestDevices(email VARCHAR(255) NOT NULL , mac VARCHAR(17) NOT NULL, last_seen INT);')->execute();        

        $prep = $this->pdo->query('CREATE INDEX IF NOT EXISTS email_index ON GuestDevices(email)')->execute();
        $prep = $this->pdo->query('CREATE INDEX IF NOT EXISTS mac_index ON GuestDevices(mac)')->execute();   
        $prep = $this->pdo->query('CREATE INDEX IF NOT EXISTS last_seen_index ON GuestDevices(last_seen)')->execute();
    }
}

?>