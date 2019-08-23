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
    
    private $settings = [
            'user' => 'api',
            'password' => '',
            'controller_url' => '',
            'site' => '',
            'version' => '',
        ];
    
    function __construct($settings)
    {
        $this->settings = $settings;
    }

    public function connect()
    {
        $this->connection = new \UniFi_API\Client($this->settings['user'], $this->settings['password'], $this->settings['controller_url'], $this->settings['site'], $this->settings['version']);
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