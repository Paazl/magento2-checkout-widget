
<p align="center">
  <img src="https://www.paazl.com/app/themes/paazl-2018/assets/dist/images/logo.5b3f9aac.svg" width="300"/>
</p>
<h1 align="center">Paazl for Magento 2.x</h2>


<h2>About Paazl</h2>
Paazl enables international brands and retailers to create the ultimate last mile experience.


Paazl connects you to delivery methods worldwide. From any type of home delivery to thousands of carrier collection points. We even help you with your stores to achieve a truly, omnichannel approach.

Our extensions power customer experience in your webshop. Paazl determines the right delivery methods, displaying these seamlessly and intuitively in your checkout. Clear information on when to expect delivery maximizes conversion.

Paazl track & trace allows you to engage customers with your own look & feel. We help you make delivery a really branded experience, building trust with pro-active communication. So your customers will keep coming back for more.

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
