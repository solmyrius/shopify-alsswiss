## Shopify AlsSwiss integration

Scripts to automate integration between Shopify shop and Als-Swiss warehouse.

Google Sheets is used as source of data for receipts

## Insallation

To install Shopify and Google Sheets libs:

`composer install`

File composer.json will be used to install dependencies

To install SFTP:

`apt install php-ssh2`

## Setup

1. Install scripts on the server and create database

2. Create/install Shopify app for your shop

3. Create Google Sheet to enter receipts data and create Google Service account to allow access ti that sheet: https://cloud.google.com/iam/docs/creating-managing-service-accounts

## Environment varibles:

$_ENV['shopify_shop'] = ID of your Shopify shop
$_ENV['shopify_api_key'] = Shopify app API Key
$_ENV['shopify_api_secret_key'] = Shopify app Secret Key
$_ENV['shopify_api_secret_token'] = Shopify app Secret Token

$_ENV['mysql_user'] = DB user
$_ENV['mysql_host'] = DB host
$_ENV['mysql_pass'] = DB password
$_ENV['mysql_dbname'] = Database name

$_ENV['google_cred_path'] = Path to file where Google Service account credentials are stored
$_ENV['google_sheet_id'] = Sheet ID

$_ENV['sftp_host'] = SFTP host for Als Swiss
$_ENV['sftp_user'] = SFTP user
$_ENV['sftp_pass'] = SFTP password

$_ENV['alsswiss_order'] = Als Swiss order ID
$_ENV['alsswiss_receipt'] = Als Swiss receipt ID
$_ENV['alsswiss_product'] = Als Swiss product ID

## How to use

Enter new receipts into your google sheet with respect to products SKU

Data about products and orders with these SKU's will be automatically pulled from Shopify

CSV files according to Als-Swiss requirements will be created automatically and uploaded via SFTP to als-swiss servers