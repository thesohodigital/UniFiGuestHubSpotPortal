# UniFi Guest HubSpot Portal

A simple guest portal for Ubiquiti UniFi which integrates with HubSpot for authentication and authorisation of WiFi guests.

Optionally, a list of HubSpot lead statuses can be specified and only guests with one of these statuses will be granted access. If the list is empty, the email address of the guest must simply exist in HubSpot for the guest to be granted access.

If the email address if not recognised, the guest is prompted to register and if they choose to do so, a new contact is created in HubSpot before WiFi access is granted.

![Login flow](https://thesohodigital.com/images/external/hubspot-unifi-flow.png)

## Prerequisites
1. Webserver with PHP and SQLite3 installed

## Installation Instructions

### 1. Get the code
Download a copy of the project and upload to your webserver, or clone the repository directly from your web server:

`git clone https://github.com/thesohodigital/UniFiGuestHubSpotPortal`

### 2. Configure the web server
URL rewriting is required for the portal to work.

If you use Apache, a .htaccess file is included which will perform the rewrites so long as mod_rewrite is enabled

If you use Nginx, add the following line to the server block in the Nginx config file:

`rewrite guest\/s\/[a-zA-Z0-9]+/(.*)$ /$1 break;`

### 3. Setup SQLite database directory
By default, the portal creates a guests.db database file in the directory /etc/wifilogin 

`sudo mkdir /etc/wifiportal/`
`sudo chown www-data:www-data /etc/wifilogin/`
`sudo chmod 644 /etc/wifilogin`

### 4. Setup UniFi Controller
Create a new admin user with a long and secure password.

In the Guest Portal settings, enter the URL or IP address of the server hosting your portal.

### 5. Create a HubSpot form and API Key

- Create a new form in HubSpot (choose Forms from the Marketing menu)
  - Choose Regular form, then Next
  - Choose Blank template, then Start
  - Add the fields First name and Last name, so you have those two and Email which is there by default
  - Click publish
- Make a note of the form GUID - [see here](https://knowledge.hubspot.com/articles/kcs_article/forms/find-your-form-guid)
- Make a note of the portal ID [see here](https://knowledge.hubspot.com/articles/kcs_article/account/manage-multiple-hubspot-accounts)
- Create a HubSpot API key [see here](https://knowledge.hubspot.com/articles/kcs_article/integrations/how-do-i-get-my-hubspot-api-key)

### 6. Edit config.php

Open config.php in a text editor, read the settings descriptions and input your values.
