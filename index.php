<?php


/**
 * UniFi Guest Portal with HubSpot integration
 *
 * A basic guest WiFi portal which authorises guests based on their
 * HubSpot lead status.
 */

namespace TSD\UniFiGuestHubSpotPortal;

ini_set('display_errors', 1);

require_once('./vendor/autoload.php');
require_once('./classes/Guest.php');
require_once('./classes/GuestDatabase.php');
require_once('./classes/UniFiController.php');
require_once('./classes/GuestPortal.php');
require_once('./settings.php');
    
new GuestPortal($settings);


?>
