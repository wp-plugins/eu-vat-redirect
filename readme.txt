=== EU VAT Redirect ===
Contributors: andrewpaulbowden
Tags: vatmoss, EUvat, European Union, VAT, tax, geo location, payment processors, redirect, redirection, IP
Donate link: http://andrewbowden.me.uk/donate
Requires at least: 3.5
Tested up to: 4.3
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows you to set buy links differently for visitors from the EU and outside the EU, for VAT purposes.

== Description ==
EU VAT Redirect allows sellers of digital products to send users with an IP address in the EU to one payment provider, and those with an IP address outside the EU to another.

From 2015 sellers of digital products to customers in the EU are required to charge VAT on the product's purchase price, with the VAT rate being determined by the location of the customer, and report it regularly.  Sellers may avoid VAT registration by using a reseller / 3rd party intermediary to process the sale on their behalf and handle VAT calculation and reporting; however, this means that VAT is applied on all EU sales including sales to customers in the seller's own country, which may otherwise have been exempt from VAT being added. In addition   each sale through the 3rd party reseller may be subject to higher transaction fees than payment  processors which do not handle VAT reporting. This is obviously detrimental if only a small percentage of the sales are to customers in the EU.

This plugin enables the seller to sell the same product through both a reseller that applies VAT and VAT reporting, and a payment processor which does not, and redirect customers to buy through the most appropriate 3rd party.

The plugin is used in place of the URL in "buy now" links.  When the user selects the "buy now" button, the user's IP address is checked and EU VAT Redirect determines whether a user's IP address is in the EU or outside it.  Users with an IP address in the EU can be sent to a reseller/intermediary that confirms and identifies the customer's country of residence, adds VAT and handles the VAT calculation, whilst those with an IP address outside the EU can be sent to a cheaper payment processor, which does not add VAT.

Customers with an IP address outside the EU can be further checked by: 

a) using this plugin to add an optional "Confirm my location" page which asks customers to confirm that their country of residence is one that is subject to VAT or not 

and/or

b) using a payment processor that blocks sales of countries subject to VAT and only allows through buyers in countries which are not subject. This is a feature which some processors offer and which would be set up in the payment processor settings. 

Features include:

* Ability to configure multiple products, each with their own purchase links.

* Treat UK as VAT free.  Business in the UK who are below the VAT threshold are not required to charge VAT to UK buyers on digital purchases.  Those UK business to which this applies to can select this option, and send all UK users to the cheaper payment processor (i.e. treat them as non-EU buyers).

* "Confirm my location" page - when enabled, an interstitial ('gateway') page is shown to any user EU VAT Redirect determines has an IP address outside of the EU.  The page asks the user to confirm if they are in the EU or not by selecting from a choice of two links, before directing them to the correct payment processor.

* Ability to set custom URLs for "buy now" page, and the "Confirm my Location" page.

This product includes GeoLite data created by MaxMind, available from http://www.maxmind.com.

With thanks to Chris Tingay for sparking this idea.

== Installation ==

Basic installation is as follows:

1) Install and activate the plugin in the normal way.  When activated, EU VAT Redirect will create two new pages called "EU VAT Redirect" and "Confirm Your Location".  

2) Go to 'Settings' then 'EU VAT Redirect' to setup the plugin.  For each product, you will need to provide an ID and you will need the URLs for the two different payment processors you wish to use, for example, the buy button link to PayPal or FastSpring (these are just examples).  You can also set other options on this page.

3) In your "Buy Now" links, change the URL to the Wordpress shortcode shown on the Settings page.  When published, this will provide a link to a page (called 'location-detect' by default) where the user's IP address is checked, and the user is then redirected to the right payment processor.

And that's it.  

== Frequently Asked Questions ==

= Q. Can I set up multiple products? = 

As of version 1.1, you can.

= Q. How reliable is the location detection? =

EU VAT Redirect uses the GeoLite database, created by MaxMind (http://www.maxmind.com), which they state is 99.8% accurate.  No location detection will ever be 100% accurate, however GeoLite offers a very high level of accuracy.

Note that the EU VAT Redirect plugin redirects users based on IP address and then the 3rd party reseller is responsible for furthering confirming their exact country of residence.

= Q. Will the country detection slow down my website? =

The country detection is only done when the user clicks on your buy link.  To ensure the detection is performed as quickly as possible, the database is included in the plugin.  No external websites are called in order to do the country detection.

= Q. Can I put the buy link anywhere on my site? =

The buy link is a shortcode, so can be placed anywhere in a content field / body of a page.  You can find the shortcode for the products you have set up, listed on the settings page.

= Q. What happens if the plugin can't determine the country the user is in? =

The plugin will err on the side of caution, and assume the customer is in the EU.  This will allow the payment processor to determine the user's location using the additional information they have access to, such as billing addresses and bank details.

= Q. How often is the GeoLite database updated? =

The latest version of the database is automatically downloaded when you activate the plugin.  After that, the plugin will update the database every 31 days.

= Q. How can I tell when the GeoLite database was last updated? =

The date the database was last downloaded is shown at the bottom of the EU VAT Redirect plugin's settings screen in Wordpress.

= Q. Can I customise the "Confirm Location" gateway page? =

Yes, absolutely.  This page is generated using the normal Page template in Wordpress, and you can change everything on it.  The two links are generated by shortcodes - [euvat_non_eu_url] and [euvat_eu_url].  You can customise the link text for each of these shortcodes, by using the text attribute - e.g. [euvat_non_eu_url text="Your link text for non-EU users"], and [euvat_non_eu_url text="Your link text for EU users"]

= Q. Could I use this plugin to block visitors in the EU from buying my product? =

Users with an IP address in the EU can be blocked from buying. Instead of sending them to a reseller, you will send them to an explanatory page instead.  To do so, create a page in Wordpress that you wish to show visitors with an IP address in the EU, explaining why they are unable to buy your product.  Then place the URL of the page in the settings page in the box for EU customers.  Note that if you do this, visitors with an IP address in the EU will only see a message after they have followed your buy link.

Note that if buyer whose country of residence is within the EU is using an IP address in a non-EU country, this will direct them to the non-VAT payment processor. You can add further steps as explained above to add further checks or restrictions if you feel this alone is not adequate.

= Q. How can I test this plugin with a range of different countries? =

There are two ways.  Firstly, you can simulate how the plugin behaves for a particular country.  To do this, you need an IP address for a domain registered in a particular country.  You can find an IP address for any website using an online service like http://ip-lookup.net/domain.php, which will convert a website domain name into an IP address for you.  In the plugin examples folder is a list of IP addresses for each EU country, which we used for our own testing.  

Once you have an IP address, you can use it directly on the Country Detect page, by calling it in this way: http://name-of-your-site.com/location-detect?ip=[ipaddress]&product=[product id], where [ipaddress] is an IP address for a particular website, and [product id] is your product ID.

Alternatively, you can use a commercial service like WonderProxy which allows you to test your website across multiple countries.

= Q. Does the country detection support IPV6? =

Yes.

= Q. Are there any restrictions on using this plugin? =

No.  You are free to use this plugin, without charge, on any many websites and for as many products as you wish.  There is a donate button below if you wish to support the developer.

= Q. Can you add a new feature to do X? =

We're always interested in finding out what people would find useful for this plugin, and we will review all feature requests.

= Q. Does this plugin guarantee my compliance with EU VAT? =

Nothing can be guaranteed. You will have to make your own decision as to whether you feel this method is appropriate and demonstrates a reasonable effort on your part to direct taxable sales to the most appropriate payment processor.  We recommend that you document your redirection procedure for your records and the date you started to use it.  Use of this plugin is at entirely your own risk.  

= Q. What if the user's country of residence is in a non-VAT country but they are using an IP address in a VAT country? =

They will be directed to the reseller. It is then for the reseller to identify and resolve the discrepancy. 

= Q. Does this plugin store any customer data e.g. which IP address was directed where? =

No, the plugin doesn't store any customer data.

= Q. Does the plugin store data about which IP address or customer clicked to confirm that their country of residence is outside of the EU and not subject to VAT? =

No. It is just a gateway page which restricts access to the payment processor unless they click through. It does not store any data. You could note in your procedure document that the customer is required to click through this gateway page in order to buy the product without VAT.

= Q. On the confirmation page, could a customer using an IP address in a non-EU country but whose country of residence is within the EU just lie and click to confirm they are not subject to VAT? =

Yes however:

You may wish to use a payment processor to handle your non-VAT sales that can block buyers in EU countries and turn on these settings as an extra level of checking.

You may wish to set your prices in your reseller account to be inclusive of VAT, so that all customers pay the same price regardless of which route they go through, so there would be no incentive for the customer to pretend.

= Q. How is this plugin tested? =

We tested the plugin using a range of IP addresses from EU and non-EU countries.  

= Q. I click "Add a product" but nothing happens.  What's wrong? =

Are you using Multisite, and the Wordpress MU Domain Mapping plugin?  If so, a flaw in this plugin is the likely cause.  The problem (documented at http://davoscript.com/blog/desarrollo/wordpress-mu-domain-mapping-plugin-and-multisite-mu-plugins/) affects many plugins.   This is, unfortunately, outside our control.

== Screenshots ==

1. Flow diagram showing how EU VAT Redirect works.
2. Example of the default "Confirm Location" page
3. Example of customised "Confirm Location" page text and links 

== Changelog ==

= 1.2.0 =

* Added automatic downloading of latest GeoIP databases on plugin activation
* Added scheduled job which then downloads the latest GeoIP databases every 31 days.
* Fixes several minor bugs during plugin activation

= 1.1.1 =

* Confirmed compatability with Wordpress 4.1

= 1.1.0 =

* Adds support for multiple products
* Fixes issue where uninstall script did not work on single site Wordpress installls.

= 1.0.4 =

* Fixes issue where settings page was not being seen for some users.

= 1.0.3 = 

* Fixing formatting errors in readme.txt

= 1.0.1 =

* Updates to readme.txt to improve clarity.

= 1.0 =

* Initial release

== Upgrade Notice ==

= 1.2.0 =

* Now automatically downloads the latest GeoIP database when the plugin is activated, and then every 31 days.  This update will help ensure your site is always using the most up to date GeoIP database.

= 1.1.0 =

* Adds support for multiple products - note that this is a big change.  It is advised that you take note of your previous settings before upgrade as a precaution.
* Fixes issue where uninstall script did not work on single site Wordpress installls.

= 1.0.4 =

* Fixes issue where settings page was not being seen for some users.

= 1.0.3 = 

* Fixing formatting errors in readme.txt

= 1.0.1 =

Improvement to documentation.

