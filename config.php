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
 * hubspot          lead_status         Optiona: guest must have a lead status in this list to be granted access
 * hubspot          portal              The ID of the HubSpot portal (see README)
 * hubspot          form                The form ID of the hubspot form (see README)
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
            'database' => '/etc/wifilogin/guests.db',
            'redirect_url' => '',
        ],
        'hubspot' => [
            'api_key' => '',
            'lead_status' => [
                // 'EXAMPLE_STATUS',
            ],
            'portal' => '',
            'form' => '',
        ],
        'unifi' => [
            'user' => '',
            'password' => '',
            'controller_url' => '',
            'site' => '',
            'version' => '5.10.25',
        ],
        'session' => [
            'duration' => 60*24,  
            'max_devices' => '2',
        ],
        'lang' => [
            'privacy_policy' => 'Privacy Policy',
            'page_title' =>     'Guest WiFi Login',
            'enter_email' =>    'Enter your email address for free WiFi access',
            'login' =>          'Login',
            'first_name' =>     'First name',
            'last_name' =>      'Last name',
            'register' =>       'It looks like we don\'t know you! Please register your details to connect to our WiFi.',
            'hello' =>          'Hello!',
            'marketing' =>      'I agree to receive marketing communications.',
            'submit' =>         'Submit',
            'email_invalid' =>  'Sorry, we didn\'t recognise that email address. Please try again or ask for help at reception.',
            'email_format' =>   'Please enter a valid email address.',
            'generic' =>        'Sorry, something went wrong. Please try again shortly.',
        ]
    ];
    
?>
