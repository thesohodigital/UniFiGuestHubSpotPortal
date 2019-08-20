<?php

/**
 * UniFi Guest Portal with HubSpot integration
 *
 * A basic guest WiFi portal which authorises guests based on their
 * HubSpot lead status.
 */

namespace TSD\UniFiGuestHubSpotPortal;

use UniFi_API\Client;

class UniFiHelper
{
	public $connection;
	
	private $settings =
		array(
			'unifi_user' => 'api',
			'unifi_password' => '',
			'unifi_controller_url' => 'https://192.168.1.221:8443',
			'unifi_site' => '',
			'unifi_version' => '5.10.25',
		);
	
	function __construct($settings)
	{
		$this->settings = $settings;
		$this->connect();
	}

	private function connect()
	{
		$this->connection = new UniFi_API\Client($this->settings['unifi_user'], $this->settings['unifi_password'], $this->settings['unifi_controller_url'], $this->settings['unifi_site'], $this->settings['unifi_version']);
		
		$this->connection->login();	
	}
	
	function authoriseGuest($mac, $duration, $ap)
	{
		$authResult = $this->connection->authorize_guest($mac, $duration, null, null, null, $ap);
		
		
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