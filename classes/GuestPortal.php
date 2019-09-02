<?php

/**
 * GuestPortal Object
 *
 * The controlling/run class for the application which handles form input and
 * directs the authentication, authorisation, login or registration of the Guest.
 */

namespace TSD\UniFiGuestHubSpotPortal;

use Twig;
use Medoo;

class GuestPortal
{ 
    /* Details of the guest's access request */
    private $formData = [
            'id' => '',         /* Guest's MAC address */
            'ap' => '',         /* Access point's MAC address */
            't' => '',          /* Timestamp of request */
            'url' => '',        /* URL the client requested (often that of the vendor eg. https://captive.apple.com) */
            'ssid' => '' ,      /* SSID of the network the guest has connected to */
            'email' => '',      /* Guest's email address */
            'site' => '',       /* UniFi site the guest is connecting to */
            'csrf' => '',       /* CSRF token */
            'page' => '',       /* Used internally to determine page requested eg. login or register */
            'firstname' => '',  /* Guest's first name */
            'lastname' => '',   /* Guests's last name */
            'consent' => '',    /* Whether or not guest has given consent for marketing comms */
        ];

    private $Twig;
    private $Guest;
    private $UniFiController;
    
    private $debugMessages = array();
    
    function __construct()
    {   
        $loader = new \Twig\Loader\FilesystemLoader('./templates/');

        $this->Twig = new \Twig\Environment($loader, array(
                'cache' => false,
                'debug' => true,
            ));     

        $this->loadRequestVariables();
        
        $this->Guest = new Guest($this->formData['csrf']);
        $this->UniFiController = new UniFiController();
        
        $this->run();
    }
    
    /**
     * Main routine for the application
     *
     */  
    
    public function run()
    {
        /* Populate the UniFi site for which access is requested */
        $this->setUniFiSite();
        
        if(! isset($_POST['page']))
        {
            /**
             *  These three variables should be present in the request string when the Unifi
             *  Controller redirects the guest. If they're not present then something is wrong
             *  and we'll display a 'something went wrong' error. If they are all present, then
             *  we will show the login page because everything looks normal.
             */
            if (! $this->validateMandatory(array('id', 'ap', 'ssid', 'site'), $this->formData))
            {
                $this->debugMessages[] = "Mandaory values not passed in URL from Unifi.";
                $this->renderLogin(Settings::$lang['generic']);
            }
            else
            {
                $this->renderLogin();
            }
        }
        elseif($this->formData['csrf'] == "" || ! $this->Guest->validateCsrf($this->formData['csrf']))
        {
            /* Show an error if the CSRF value is different to what's expected */
            $this->debugMessages[] = "CSRF token match failure.";
            $this->renderLogin(Settings::$lang['generic']);
        }
        elseif(! $this->validateMandatory(array('id', 'ap', 'site'), $this->formData))
        {
            /** 
             *  Even if the email address or registration is valid, we can't authorise the guest without these
             *  three mandatory fields so stop here if one or more isn't complete.
             */
            $this->debugMessages[] = "Mandatory values client MAC, access point MAC and UniFi site ID not passed in form submission.";
            $this->renderLogin(Settings::$lang['generic']);
        }
        elseif(! filter_var($this->formData['email'], FILTER_VALIDATE_EMAIL) || strlen($this->formData['email']) > 255)
        {
            /* If the email address is not in a valid format, show an error message */
            $this->debugMessages[] = "Invalid email submitted.";
            $this->renderLogin(Settings::$lang['email_format']);
        }
        elseif(! filter_var($this->formData['id'], FILTER_VALIDATE_MAC))
        {
            /* We're checking the MAC address looks OK here, if not show a generic error message */
            $this->debugMessages[] = "Invalid client MAC address.";
            $this->renderLogin(Settings::$lang['generic']);
        }
        else
        {
            $this->Guest->email = $this->formData['email'];
            $this->Guest->accessPoint = $this->formData['ap'];
            $this->Guest->mac = $this->formData['id'];
            
            $this->Guest->authenticate();
            
            if ($this->formData['page'] == 'register')
            {
                $this->registerPage();
            }
            else
            {
                $this->loginPage();
            }
        }
    }

    /**
     * Processes the login page and inputs.
     *
     */
     
    private function loginPage()
    {        
        if (! $this->Guest->authenticate())
        {
            /**
             * If we can't authenticate the guest (ie. we don't recognise their email) then
             * redirect them to the registration page.
             */
            
            $this->renderRegister();
            return;
        }
        
        if ($this->Guest->authorise())
        {
            /*
                If the guest is a valid HubSpot contact, then try to authorise them
                and show them a success page.
            */
            
            $this->UniFiController->connect();
             
            if($this->UniFiController->authoriseGuest($this->Guest->mac, Settings::$session['duration'], $this->Guest->accessPoint))
            {
                
                $excessDevices = $this->Guest->save();
                
                foreach($excessDevices as $m)
                {
                    $this->UniFiController->unAuthoriseGuest($m);
                }
                
                $this->renderSuccess();
            }
            else
            { 
                $this->renderLogin(Settings::$lang['generic']);
            }
        }
        else
        {
            /* Show an  error if the member is not recognised */
            $this->registerPage();
        }
    }
 
     /**
     * Processes the registration page and user inputs.
     *
     */
     
    private function registerPage()
    { 
        if($this->formData['firstname'] == "" || $this->formData['lastname'] == "")
        {
            $this->renderRegister();
        }
        elseif(strlen($this->formData['firstname']) > 30 || preg_match("/^[a-zA-Z]+$/", $this->formData['firstname']) === 0 )
        {
            $this->renderRegister("Please enter a valid first name.");
        }
        elseif(strlen($this->formData['lastname']) > 30 || preg_match("/^[a-zA-Z]+$/", $this->formData['lastname']) === 0)
        {
            $this->renderRegister("Please enter a valid last name.");
        }
        elseif($this->formData['consent'] != "on")
        {
            $this->renderRegister("Please agree to receive marketing communications from us to gain WiFi access.");
        }
        else
        {
            if(! $this->Guest->authenticated)
            {
                $this->Guest->firstName = $this->formData['firstname'];
                $this->Guest->lastName = $this->formData['lastname'];
                $this->Guest->create();
            }

            $this->Guest->save();
            
            $this->renderSuccess();
        }
    }   
    

    /**
     * Displays the success page, shown to the user after a successful login.
     */
     
    private function renderSuccess()
    {
        echo $this->Twig->render("success.twig", ['redirect' => Settings::$portal['redirect_url'],
                                                  'lang' => Settings::$lang,
                                                 ]);        
    }
    
    /**
     * Displays the initial login page.
     */
     
    private function renderLogin($msg="")
    {
        $this->formData['csrf'] = $this->Guest->csrf;
        
        echo $this->Twig->render("login.twig", ['values' => $this->formData,
                                                'loginMsg' => $msg,
                                                'lang' => Settings::$lang,
                                               ]);
                                                     
    }

    /**
     * Displays the registriation page
     *
     */    
    
    private function renderRegister($msg="")
    {
        $this->formData['csrf'] = $this->Guest->csrf;
        
        echo $this->Twig->render("register.twig", ['values' => $this->formData,
                                                   'loginMsg' => $msg,
                                                   'lang' => Settings::$lang,
                                                  ]);
    }    

    /**
     * Checks whether or not an array of keys exists in another array
     *
     * Returns true if all keys exist with at least a value of length
     * greater than 1 character, or false otherwise.
     */
     
    private function validateMandatory($mandatoryVars, $checkArray)
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
    
    /**
     * Loads all GET and POST variables into a single array
     *
     */  
    
    private function loadRequestVariables()
    {
        $requestVars = array_merge($_GET, $_POST);
                             
        foreach ($requestVars as $k => $v)
        {
            if(key_exists($k, $this->formData))
            {
                $this->formData[ $k ] = trim($v);
                
                if (! in_array($k, ['firstname', 'lastname']))
                {
                    $this->formData[ $k ] = strtolower($this->formData[ $k ]);
                }
            }
        }
    }
    
    /**
     * Sets the UniF site to be used
     *
     * Returns true if successfully set.
     */
    
    private function setUniFiSite()
    {
        /**
         * If the site is already set, then it has been defined in the settings
         * file so we will use that. If not, then try to find it via a couple
         * of methods.
         */
        
        if(Settings::$unifi['site'] == "")
        {
            /**
             * If the site was passed through the login form then use that
             * or if not, try and detect the site from the URL.
             */
             
            if($this->formData['site'] == "")
            {
                
                $this->formData['site'] = $this->detectSiteFromUrl();
            }
            
            Settings::$unifi['site'] = $this->formData['site'];
        }
        
        if(Settings::$unifi['site'] == "")
        {
            $this->debugMessage[] = "Site is not specified in settings and it could not be automatically detected.";
            return false;
        }
        else
        {
            return true;
        }
    }
   
    /**
     * Attempts to get UniFi site name from the URL
     *
     */   
    
    private function detectSiteFromUrl()
    {
        $matches = array();
        $site = "";
        
        preg_match('/\/guest\/s\/([a-zA-Z0-9]+)\/.*/', $_SERVER['REQUEST_URI'], $matches);
        
        if(isset($matches[1]))
        {
            $site = $matches[1];
        }
        
        return $site;
    }

}

?>