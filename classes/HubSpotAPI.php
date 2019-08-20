<?php

class HubSpotContact
{
	public $isAuthorised = false;
	
	private $validLeadStatus = array("FOUNDING_MEMBER");
	private $checkEmail = "";
	
	private $apiKey = "";
	
	function __construct($email, $apiKey)
	{
		$this->apiKey = $apiKey;
		$this->checkEmail = $email;
		
		$this->authenticateUser();
	}

	private function authenticateUser()
	{
		$contact = $this->getHubSpotContact($this->checkEmail);
		
		if($contact === false)
		{
			return false;
		}	
		
		if(! isset($contact->properties->hs_lead_status))
		{
			return false;
		}

		if(in_array($contact->properties->hs_lead_status->value, $this->validLeadStatus))
		{
			$this->isAuthorised = true;
			return true;
		}
		
		return false;
	}

	private function getHubSpotContact($email)
	{
		$read = @file_get_contents($this->createUrl("contacts/v1/contact/email/" . $email . "/profile"));
		
		if ($read === false)
		{
			return false;
		}
		else
		{
			return json_decode($read);
		}
	}
	
	private function createUrl($request)
	{
		$baseUrl = "https://api.hubapi.com/";
		
		return $baseUrl . $request . "/?property=hs_lead_status&propertyMode=value_only&hapikey=" . $this->apiKey;
	}

}

?>