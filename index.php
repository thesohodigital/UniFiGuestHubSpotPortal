<?php


/**
 * UniFi Guest Portal with HubSpot integration
 *
 * A basic guest WiFi portal which authorises guests based on their
 * HubSpot lead status.
 */

namespace TSD\UniFiGuestHubSpotPortal;

require_once('./vendor/autoload.php');
require_once('./classes/HubSpotAPI.php');
require_once('./classes/GuestDatabase.php');
require_once('./classes/UniFiHelper.php');
require_once('./classes/GuestPortal.php');

/**
 * Basic application settings are defined here
 *
 * hubspot          api_key             your HubSpot API key
 * unifi            user                an admin username for your UniFi Controller
 * unifi            password            password for the UniFi Controller user
 * unifi            controller_url      the URL of your controller, note this MSUT start with https://
 * unifi            site                your site name, which can be found in the controller URL
 * unifi            version             the version of your controller
 * guest_session    duration            number of minutes to authorise the guest for
 */

$settings = [
        'hubspot' => [
            'api_key' => ''
        ],
        'unifi' => [
            'user' => 'api',
            'password' => '',
            'controller_url' => 'https://192.168.1.221:8443',
            'site' => '',
            'version' => '5.10.25',
        ],
        'guest_session' => [
            'duration' => '',    
        ]
    ];
    
new GuestPortal($settings);


?>
