<?php

/**
 * Basic application settings are defined here
 *
 * Category         Setting             Description
 * -------------    -----------------   ------------------------------------------------------------------
 * hubspot          api_key             your HubSpot API key
 * unifi            user                an admin username for your UniFi Controller
 * unifi            password            password for the UniFi Controller user
 * unifi            controller_url      the URL of your controller, note this MUST start with https://
 * unifi            site                your site name, which can be found in the controller URL
 * unifi            version             the version of your controller
 * guest_session    duration            number of minutes to authorise the guest for
 * guest_session    max_devices         max number of devices a guest may connect (oldest devices deauthorised if limit exceeded)
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
            'duration' => '20',  
            'max_devices' => '2',
        ],
        'database' => [
            'location' => '/var/www/crossfit-portal/database/guests.db',
        ]
    ];
    
?>