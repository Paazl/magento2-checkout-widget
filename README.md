
<p align="center">
  <img src="https://avatars2.githubusercontent.com/u/11311339?s=460&v=4" width="200"/>
</p>
<h1 align="center">Paazl for Magento 2.x</h2>


<h2>About Paazl</h2>

Paazl enables international brands and retailers to create the ultimate last mile experience.                                            

Our platform connects you to delivery methods worldwide. From any type of home delivery to thousands of carrier collection points. Paazl even helps you with your stores to achieve a truly, omnichannel approach.

Use our extensions to power customer experience in your webshop. Paazl personalizes delivery methods, displaying these seamlessly and intuitively in your checkout. And with clear information on when to expect delivery we help maximizing conversion.

Connect your warehouse to our platform to automate the shipping process. We facilitate shipping labels of broad range of local, national and global e-commerce carriers. We offer a flexible solution and speed up your go-to-market.  

Our track & trace allows you to engage customers with your own look & feel. We help you make delivery a really branded experience, building trust with pro-active communication. So your customers will keep coming back for more.

<h2>The Paazl Magento2 extension</h2>

The new Paazl Magento2 extension is all about the delivery experience of consumers. It comes with the brand new Paazl Checkout Widget that helps you display any type delivery method in your Magento checkout. Think of any type of home delivery (e.g. same-day, next-day, nominated-day) and thousands of carrier pickup points. With our checkout widget we even support click & collect @your store ([See more here](https://vimeo.com/362771849/)).  

The typical Magento2 customer for this extension has international ambitions, high volume delivery and processes its shipments via an ERP/OMS/WMS. Paazl shipping capabilities from the backend of Magento itself are therefore limited. See also our [Wiki](https://github.com/Paazl/magento2-checkout-widget/wiki) for the differences with our Magento1 extension

Note that the Paazl Magento2 extension offers the basics for integrating Paazl in your checkout. Always make sure to align with other extensions and/or existing customisation.

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
