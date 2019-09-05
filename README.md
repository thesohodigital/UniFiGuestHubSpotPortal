UniFi Guest HubSpot Portal
============================
A simple guest portal for Ubiquiti UniFi which integrates with HubSpot for authentication and authorisation of WiFi guests.

![alt text](https://thesohodigital.com/images/external/portal-1.png)

Prerequisites
1. Webserver with PHP and SQLite3 installed

#Installation Instructions

1. Get the code
Download a copy of the project and upload to your webserver, or clone the repository directly from your web server:

git clone xxxx

2. Configure the web server
URL rewriting is required for the portal to work.

If you use Apache, a .htaccess file is included which will perform the rewrites so long as mod_rewrite is enabled

If you use Nginx, add the following line to the server block in the Nginx config file:

rewrite guest\/s\/[a-zA-Z0-9]+/(.*)$ /$1 break;

SQLite Database
By default, the portal stores a guests.db database file in the directory /etc/wifiportal 

sudo mkdir /etc/wifiportal/
sudo chown www-data:www-data /etc/wifiportal/
sudo chmod 

Data

UniFi Controller Setup

# Screenshots

