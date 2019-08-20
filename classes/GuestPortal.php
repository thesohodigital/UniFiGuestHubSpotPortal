<?php

namespace TSD\UniFiGuestHubSpotPortal;

use Twig;
use Medoo\Medoo;

class GuestPortal
{
	private $settings = array(
		'hubspot_api_key' => '',
		'unifi_user' => 'api',
		'unifi_password' => '',
		'unifi_controller_url' => 'https://192.168.1.221:8443',
		'unifi_site' => '',
		'unifi_version' => '5.10.25',
		'unifi_session_mins' => '',
	);
	
	/* Instance of the Twig PHP template engine */
	private $Twig;
	
	/* Templated messages shown to the user, defined here for easy translation */
	private $strings =
		array(
			'error' => array(
				'email_invalid' => "Sorry, we didn't recognise that email address. Please try again or ask for help at reception.",
				'email_format' => "Please enter a valid email address.", 
				'generic' => "Sorry, something went wrong. Please try again shortly."
			)
		);
		
	/* Details of the guest's access request */
	private $formData =
		array(
			'id' => '',			// Client's MAC address
			 'ap' => '',		// Access point's MAC address
			 't' => '',			// Timestamp of request
			 'url' => '',		// URL the client requested (often that of the vendor eg. https://captive.apple.com)
			 'ssid' => '' ,		// SSID of the network the guest has connected to
			 'email' => ''		// 
		);		
	
	function __construct($settings)
	{
		$this->settings = $settings;
		
		$loader = new \Twig\Loader\FilesystemLoader('./templates/');

		$this->Twig = new \Twig\Environment($loader, array(
				'cache' => false,
				'debug' => true,
			));		
		
		$this->run();
	}
	
	function run()
	{
		// Populate any GET data array, we expect all of these to be completed
							 
		foreach ($_GET as $k => $v)
		{
			if(key_exists($k, $this->formData))
			{
				$this->formData[ $k ] = strtolower(trim($v));
			}
		}
		
		// Populate any POST data
						  
		foreach ($_POST as $k => $v)
		{
			if(key_exists($k, $this->formData))
			{
				$this->formData[ $k ] = strtolower(trim($v));
			}
		}

		if(! isset($_POST['email']))
		{
			/* 
				These three variables should be present in the request string when the Unifi
				Controller redirects the guest. If they're not present then something is wrong
				and we'll display a 'something went wrong' error. If they are all present, then
				we will show the login page because everything looks normal.
			*/
			if (! $this->validateMandatory(array('id', 'ap', 'ssid'), $this->formData))
			{
				$this->renderLogin($this->strings['error']['generic']);
			}
			else
			{
				$this->renderLogin();
				
			}
		}
		elseif(! $this->validateMandatory(array('id', 'ap', 'email'), $this->formData))
		{
			/* 
				Even if the email address is valid, we can't authorise the guest without these
				three mandatory fields so stop here if one or more isn't complete.
			*/

			$this->renderLogin($this->strings['error']['generic']);
		}
		elseif(! filter_var($this->formData['email'], FILTER_VALIDATE_EMAIL) && strlen($this->formData['email']) > 255)
		{
			/* If the email address is not in a valid format, show an error message */
			$this->renderLogin($this->strings['error']['email_format']);
		}
		elseif(! filter_var($this->formData['id'], FILTER_VALIDATE_MAC))
		{
			/* We're checking the MAC address looks OK here, if not show a generic error message */
			$this->renderLogin($this->strings['error']['generic']);
		}
		else
		{
			$Contact = new HubSpotContact($this->formData['email'], $this->settings['hubspot_api_key']);
			
			if ($Contact->isAuthorised)
			{
				/*
					If the guest is a valid HubSpot contact, then try to authorise them
					and show them a success page.
				*/
				
				try
				{		
					$Unifi = new UniFiHelper($this->settings);
					
					if($Unifi->authoriseGuest($this->formData['id'], 1, $this->formData['ap']))
					{
						$this->recordMac($Unifi, $this->formData['email'], $this->formData['id']);
						$this->renderSuccess();
					}
					else
					{
						throw new Exception();
					}
				}
				catch(Exception $e)
				{
					/*
						Something probably went wrong with the UniFi API connection, we want to gracefully
						exit and show an error to the guest.
					*/
					$this->renderLogin($this->strings['error']['generic']);
				}
			}
			else
			{
				/* Show an  error if the member is not recognised */
				$this->renderLogin($this->strings['error']['email_invalid']);
				
			}
		}

	}

	// FUNCTION
	// Shows a successful message and redirects to CrossFit website
	function renderSuccess()
	{
		echo $this->Twig->render("success.twig", array('redirect' => true));		
	}

	// FUNCTION
	// Shows the login page and, optionally, an error message
	function renderLogin($msg="")
	{
		echo $this->Twig->render("login.twig", array('values' => $this->formData,
													 'loginMsg' => $msg,));
	}

	// FUNCTION
	// Updates database

	function recordMac(&$Unifi, $email, $mac)
	{
			$db = new \Medoo\Medoo([
				'database_type' => 'sqlite',
				'database_file' => './database/guests.db'
			]);
			
			$db->create('GuestMacAddresses', [
				'email' => [
					'VARCHAR(255)',
					'NOT NULL',
					'PRIMARY KEY'
				],
				'mac' => [
					'VARCHAR(17)'
				]
			]);
			
			$user = $db->select('GuestMacAddresses', 'mac', ['email' => $email]);
			
			if(isset($user[0]) && $user[0]['mac'] != $mac)
			{
				$db->update('GuestMacAddresses', ['mac' => $mac], ['email' => $email]);
				$Unifi->unAuthoriseGuest($user[0]['mac']);
			}
			else
			{
				$db->insert('GuestMacAddresses', ['mac' => $mac, 'email' => $email]);		
			}
	}

	// FUNCTION
	// Validates that array keys exist and have length more than 0 
	function validateMandatory($mandatoryVars, $checkArray)
	{
		foreach ($mandatoryVars as $k)
		{
			if (strlen($checkArray[ $k ]) < 1)
			{
				return false;
			}
		}
		
		return true;
	}

}

?>