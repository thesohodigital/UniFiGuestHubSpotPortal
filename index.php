<?php

/***********************************
 * UniFi Guest Portal with HubSpot integration
 *
 *
 *
 *
************************************/

require_once('./classes/HubSpotAPI.php');
require_once('./classes/UnifiAPI.php');
require_once('./classes/GuestPortal.php');
require_once('./vendor/autoload.php');



$params = array(
	'hubspot_api_key' => '',		// Your HubSpot API key
	'unifi_user' => '',				// UniFi Controller admin account username
	'unifi_password' => '',			// UniFi Controller admin account password
	'unifi_controller_url' => '',	// UnnFi Controller URL (note: MUST start with https://)
	'unifi_site' => '',				// UniFi Controller site ID (found in the URL)
	'unifi_version' => '5',			// Version of your UniFi Controller
);
	
new GuestPortal($params);


?>
