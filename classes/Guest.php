<?php

/**
 * Guest Object Class
 *
 * When the Guest object has been created and with a MAC address and an email address set,
 * provides functions to authenticate and authroise the guest using HubSpot Contact
 * information.
 */

namespace TSD\UniFiGuestHubSpotPortal;

class Guest
{
    public $email = "";
    public $mac = "";
    public $firstName = "";
    public $lastName = "";
    public $isAuthorised = false;
    public $authenticated = false;
    public $csrf;
    
    private $previousCsrf = "";
    private $devices = array();    
    private $db;
    private $profile = false;
    
    function __construct()
    {
        $this->db = new GuestDatabase();
        $this->startSession();
    }
    
    private function startSession()
    {
        session_start();
        
        if(isset($_SESSION['csrf']))
        {
            $this->previousCsrf = $_SESSION['csrf'];
        }
        
        $this->csrf = bin2hex(random_bytes(32)); 
        $_SESSION['csrf'] = $this->csrf;        
    }
    
    private function endSession()
    {
        session_destroy();
    }
    
    /**
     * Create a contact in HubSpot
     *
     * Creates a contact in HubSpot by submitting a form, which must been
     * defined in HubSpot.
     *
     * Returns true on success, false if not.
     */
    
    public function create()
    {
        $endpoint = 'https://forms.hubspot.com/uploads/form/v2/'. Settings::$hubspot['portal'] .'/'. Settings::$hubspot['form'];
        
        $str_post = "firstname=". urlencode($this->firstName)
                    ."&lastname=". urlencode($this->lastName)
                    ."&email=". urlencode($this->email)
                    ."&hs_context=". urlencode(json_encode(array('pageName' => 'Guest WiFi Registration')));
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str_post);
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        
        if($code != 204)
        {
            return false;
        }
        
        curl_close($ch);
        
        return true;
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
        $curl = curl_init();
        
        $ch =  curl_init('https://api.hubapi.com/contacts/v1/contact/email/'. $this->email .'/profile/?property=hs_lead_status&propertyMode=value_only&hapikey='. Settings::$hubspot['api_key']);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        
        $result = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        
        curl_close($ch);
        
        if($code != 200)
        {
            return false;
        }
        
        $this->profile = json_decode($result);
        $this->authenticated = true;

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
        
        $updateCount = $this->db->updateMacLastSeen($this->email, $this->mac, time());

        if($updateCount < 1)
        {
            $this->db->insertGuestMac($this->email, $this->mac);
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
        
        $this->endSession();
        
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

    public function authorise()
    {        
        if($this->profile === false)
        {
            return false;
        }   
 
        if (count(Settings::$hubspot['lead_status']) > 0)
        {
            if(isset($this->profile->properties->hs_lead_status)
               && in_array($this->profile->properties->hs_lead_status->value, Settings::$hubspot['lead_status']))
            {
                $this->isAuthorised = true;
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return true;
        }
    }

    /**
     * Detetmines if a MAC address exists in a guest's device list.
     *
     * Returns true if a contact was found, or false if not.
     * Note: this return value does NOT indicate if the contact is authorised,
     * use $this->isAuthorised for that.
     */
    
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


    /**
     * Detetmines if a guest's CSRF token is valid
     *
     * Returns true if valid or false if not.
     */    
    public function validateCsrf($formToken)
    {  
        if($this->previousCsrf != "" && hash_equals($this->previousCsrf, $formToken))
        {
            return true;
        }
        
        return false;
    }

}

?>
