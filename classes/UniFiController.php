<?php

/**
 * UniFiHelper Class
 *
 * A helper class to tidily connect to a UniFi Controller
 * and authorise guests using a PHP API.
 */

namespace TSD\UniFiGuestHubSpotPortal;

use UniFi_API;

class UniFiController
{
    private $connection;
    
    function __construct()
    {

    }

    public function connect()
    {
        $this->connection = new \UniFi_API\Client(Settings::$unifi['user'], Settings::$unifi['password'], Settings::$unifi['controller_url'], Settings::$unifi['site'], Settings::$unifi['version']);
        $this->connection->login();
    }
    
    public function authoriseGuest($mac, $duration, $ap)
    {
        $authResult = $this->connection->authorize_guest($mac, $duration, null, null, null, $ap);
        
        if ($authResult == "1")
        {
            return true;
        }
        
        return false;
    }

    public function unAuthoriseGuest($mac)
    {
        $this->connection->unauthorize_guest($mac);
    }
}

?>