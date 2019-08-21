<?php

/**
 * Guest Objet Class
 *
 * When the Guest object has been created and with a MAC address and an email address set,
 * provides functions to authenticate and authroise the guest using HubSpot Contact
 * information.
 */

namespace TSD\UniFiGuestHubSpotPortal;

class Guest
{
    public $email;
    public $mac;
    public $isAuthorised = false;
    public $apiKey;
    
    private $profile = false;
    private $validLeadStatus = array("FOUNDING_MEMBER");
    
    function __construct()
    {

    }
    
    /**
     * Authenticate a Guest
     *
     * Authenticates a guest against HubSpot and if they exist, fetches
     * their profile data. Also determines if the guest is authorised.
     *
     * Returns true if authenticated, false if not.
     */
     
    public function authenticate()
    {
        $read = @file_get_contents($this->createApiUrl("contacts/v1/contact/email/" . $this->email . "/profile"));
        
        if ($read === false)
        {
            return false;
        }
        else
        {
            $this->profile = json_decode($read);
            $this->authorise();
            return true;
        }
    }
    
    /**
     * Saves a Guest to a local database
     *
     * A key/value pair is saved to a local database, the Guest's email
     * address and MAC address.
     *
     * Returns the old MAC address if found, otherwise false if a new
     * Guest was created.
     */
    
    public function save()
    {
        $db = new GuestDatabase;
        
        $oldMac = $db->getGuestMac($this->email);
        
        if( ! is_null($oldMac) && $oldMac != $this->mac)
        {
            $db->updateGuestMac($this->email, $this->mac);
            return $oldMac;
        }
        else
        {
            $db->insertGuest($this->email, $this->mac);   
            return false;
        }
    }
    
    /**
     * Determines Guest authorisation for WiFi use
     *
     * If the Guest has a valid HubSpot lead status, then the 
     * Guest is authorised to use the WiFi network.
     *
     * Returns true if authorised, false if not.
     */

    private function authorise()
    {        
        if($this->profile === false)
        {
            return false;
        }    
        
        if(! isset($this->profile->properties->hs_lead_status))
        {
            return false;
        }

        if(in_array($this->profile->properties->hs_lead_status->value, $this->validLeadStatus))
        {
            $this->isAuthorised = true;
            return true;
        }
        
        return false;
    }

    /**
     *
     *
     * Returns true if a contact was found, or false if not.
     * Note: this return value does NOT indicate if the contact is authorised,
     * use $this->isAuthorised for that.
     */

    
    private function createApiUrl($request)
    {
        $baseUrl = "https://api.hubapi.com/";
        return $baseUrl . $request . "/?property=hs_lead_status&propertyMode=value_only&hapikey=" . $this->apiKey;
    }

}

?>