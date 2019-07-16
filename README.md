
<p align="center">
  <img src="https://www.paazl.com/app/themes/paazl-2018/assets/dist/images/logo.5b3f9aac.svg" width="300"/>
</p>
<h1 align="center">Paazl for Magento 2.x</h2>


<h2>About Paazl</h2>
Paazl is a multi-carrier platform that also offers a superior delivery experience. The Paazl platform offers functionality for generating shipping labels, sending pro-active track and trace updates, and managing returns. It combines this with this with an attractive, customizable, delivery option display whose shopper friendliness helps maximize webshop conversion.

<h2>Installation using Composer</h2>
Magento 2 uses Composer to manage the module package and the library. Composer is a dependency manager for PHP. Composer declares the libraries your project depends on and installs them for you.
Check whether your server has Composer installed by running the following command;
   
   ```
   composer –v
   ``` 
   
If your server doesn’t have Composer installed, the Composer [Getting Started](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx) page explains how to install it.


## Step 1. Run ssh console ##
Run your SSH Console to connect to your Magento 2 store.
## Step 2. Go to root ##
Go to the root directory of your Magento 2 store.
## Step 3. Install ##
Execute the following commands in your root directory:
   ```
composer require paazl/magento2-checkout-widget
   ```
## Step 4. Cache and Deploy ##
Activate the extension and run setup by executing the following commands in root in the order shown:
   ```
   php bin/magento module:enable Paazl_CheckoutWidget
   php bin/magento setup:upgrade
  ```
If Magento 2 is running in production modus, run compiltation and deploy static content:
   ```
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
   ```
