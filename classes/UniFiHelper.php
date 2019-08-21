<?php

/**
 * UniFiHelper Class
 *
 * A helper class to tidily connect to a UniFi Controller
 * and authorise guests using a PHP API.
 */

namespace TSD\UniFiGuestHubSpotPortal;

use UniFi_API;

class UniFiHelper
{
    public $connection;
    
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
        $this->connect();
    }

    private function connect()
    {
        $this->connection = new \UniFi_API\Client($this->settings['user'], $this->settings['password'], $this->settings['controller_url'], $this->settings['site'], $this->settings['version']);
        //$this->connection->set_debug(true);
        $this->connection->login();
        //var_dump($this->connection);
    }
    
    function authoriseGuest($mac, $duration, $ap)
    {
        $authResult = $this->connection->authorize_guest($mac, $duration, null, null, null, $ap);
        //var_dump($authResult);
        
        if ($authResult == "1")
        {
            return true;
        }
        
        return false;
    }

    function unAuthoriseGuest($mac)
    {
        $this->connection->unauthorize_guest($mac);
    }
}

?>