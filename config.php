<?php

/**
 * Basic application settings are defined here
 *
 * Category         Setting             Description
 * -------------    -----------------   ------------------------------------------------------------------
 * portal           debug               Set to true in order to see error messages which help with troubleshooting
 * portal           database            Full path to a directory outside of web root which the web server can write to
 * portal           redirect_url        If set, portal will redirect to this URL after guest has authenticated
 * hubspot          api_key             Your HubSpot API key
 * unifi            user                An admin username for your UniFi Controller
 * unifi            password            Password for the UniFi Controller user
 * unifi            controller_url      The URL of your controller, note this MUST start with https://
 * unifi            site                Your site name, which can be found in the controller URL
 * unifi            version             The version of your controller
 * session          duration            Number of minutes to authorise the guest for
 * session          max_devices         Max number of devices a guest may connect (oldest devices deauthorised if limit exceeded)
 */

$settings = [
        'portal' => [
            'debug' => false,
            'database' => '',
            'redirect_url' => 'https://google.com'
        ],
        'hubspot' => [
            'api_key' => '',
        ],
        'unifi' => [
            'user' => '',
            'password' => '',
            'controller_url' => '',
            'site' => '',
            'version' => '',
        ],
        'session' => [
            'duration' => '60',  
            'max_devices' => '2',
        ],
    ];
    
?>