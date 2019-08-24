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
    public $devices = array();
    public $isAuthorised = false;
    
    private $db;
    private $profile = false;
    private $validLeadStatus = array("FOUNDING_MEMBER");
    
    function __construct()
    {
        $this->db = new GuestDatabase();
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

        $this->profile = json_decode($read);
        
        $this->db->connect();
        
        if($this->authorise())
        {
            
        }
        
        return true;
    }

    
    /**
     * Saves a Guest to a local database
     *
     * A key/value pair is saved to a local database, the Guest's email
     * address and MAC address.
     *
     * Returns deleted MAC addresses if guest has more deivces than is 
     * allowed in the settings.
     */
    
    public function save()
    {
        $this->db->connect();

        
        /** 
         * Try to update time last seen for this mac in the database, if not
         * not successful (ie. 0 rows updated) then insert a new device.
         */
        
        $updateCount = $this->db->updateMacLastSeen($this->email, $this->device, time());

        if($updateCount < 1)
        {
            $this->db->insertGuestMac($this->email, $this->device);
        }
        
        /**
         * Here we check how many devices the user has and delete devices from the database,
         * starting with the oldest first, to bring the total down to the max number defined
         * in settings.
         */
        
        $this->devices = $this->db->getGuestDevices($this->email);
        $deviceCount = count($this->devices);

        $deletedMacs = array();
        $deletedRowIds = array();      
        
        if($deviceCount > Settings::$session['max_devices'])
        {
            while($deviceCount > Settings::$session['max_devices'])
            { 
                $deletedMacs[] = $this->devices[ $deviceCount-1 ]['mac'];
                $deletedRowIds[] = $this->devices[ $deviceCount-1 ]['rowid'];
                array_pop($this->devices);
                $deviceCount = $deviceCount-1;
            }
            
            $this->db->deleteExcessDevices($deletedRowIds);
        }
        
        return $deletedMacs;
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
        return $baseUrl . $request . "/?property=hs_lead_status&propertyMode=value_only&hapikey=" . Settings::$hubspot['api_key'];
    }
    
    private function findDevice($mac)
    {
        foreach ($this->devices as $k => $v)
        {
            if ($v['mac'] = $mac)
            {
                return $k;
            }
        }
        
        return false;
    }

}

?>