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
require_once('./classes/UniFiHelper.php');
require_once('./classes/GuestPortal.php');


$params = array(
	'hubspot_api_key' => '',			// Your HubSpot API key
	'unifi_user' => '',					// UniFi Controller admin account username
	'unifi_password' => '',				// UniFi Controller admin account password
	'unifi_controller_url' => '',		// UnnFi Controller URL (note: MUST start with https://)
	'unifi_site' => '',					// UniFi Controller site ID (found in the URL)
	'unifi_version' => '5',				// Version of your UniFi Controller
	'unifi_session_mins' => 60*24*14,	// Number of minutes guest session should last for	
);
	
new GuestPortal($params);


?>
