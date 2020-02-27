
<p align="center">
  <img src="https://avatars2.githubusercontent.com/u/11311339?s=460&v=4" width="200"/>
</p>
<h1 align="center">Paazl for Magento 2.x</h2>


<h2>About Paazl</h2>

Paazl is a carrier management and delivery experience platform for global e-commerce.  Our mission is to help brands and retailers offer the best delivery everywhere.

Our extensions enable a seamless and intuitive display of delivery options in the check-out of your webshop. They support a wide range of home delivery options, carrier collection points and click & collect via your local stores. The extensions also provides timeframe availability, nominated day selection and ETA mechanisms. Powerful algorithms calculate the right delivery for every customer.

Our warehousing solutions take care of the shipping labels, carrier manifesting, electronic customs documentation and personalized track & trace notifications. Your customer service team can tap directly into our systems for real time monitoring.

In a nutshell, Paazl provides the tools to scale e-commerce delivery globally. Our carrier management and delivery experience platform increases carrier flexibility, go-to-market speed and customer loyalty, lowering shipping costs as well as driving long-term revenue

<h2>The Paazl Magento2 extension</h2>

The new Paazl Magento2 extension is all about the delivery experience of consumers. It comes with the brand new Paazl Checkout Widget that helps you display any type delivery method in your Magento checkout ([See more here](https://vimeo.com/362771849/)). The typical Magento2 customer for this extension has international ambitions, high volume delivery and processes its shipments via an ERP/OMS/WMS. See also our [Wiki](https://github.com/Paazl/magento2-checkout-widget/wiki) for the differences with our Magento1 extension.

Note that the Paazl Magento2 extension offers all the basics for integrating Paazl in your checkout. Always make sure to align with other extensions and/or existing customisation.

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