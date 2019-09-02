<?php

namespace TSD\UniFiGuestHubSpotPortal;

require_once('./config.php');

class Settings
{
    public static $hubspot;
    public static $unifi;
    public static $session;
    public static $portal;
    public static $lang;
    
    public function load($settings)
    {        
        Settings::$hubspot = $settings['hubspot'];
        Settings::$unifi = $settings['unifi'];
        Settings::$session = $settings['session'];
        Settings::$portal = $settings['portal'];
        Settings::$lang = $settings['lang'];
    }
}
?>