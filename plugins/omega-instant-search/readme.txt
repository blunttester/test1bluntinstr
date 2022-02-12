=== WooCommerce Search by Omega ===
Contributors: omegacommerce
Tags:  search, search results, fast search, faceted search, woocommerce product search
Requires at least: 4.1
Tested up to: 5.7.1
Stable tag: 2.0.9
License: MIT
License URI: https://opensource.org/licenses/MIT

WooCommerce Search by Omega. Instant search results. SaaS. No additional software. FREE plan available.

== Description ==

<a href="https://demo-search-woo.omegacommerce.com/omega-search/?q=woo">**Check our search demo!**</a>

Omega Instant Search can turn your customers search experience into pleasure and bring you a bunch of orders! It is smart and quality search in returning the right products and increasing your store sales.
= Main features: =

1. **High Searching speed**: customers get quality search and see relevant search results in a split second.
1. **Autocomplete search**: Search-As-You-Type function in action! Completes customer search query automatically after entering first few symbols, effectively saving shoppers time!
1. **Automatic typos / misspellings correction while searching in a store**: search amends typos on-the-go! No more empty search results due to customers’ misspellings: customers get relevant search results for every search query made.
1. **Singular/Plural/Morphology search**
1. **Search Layered Navigation Filters**: Configuration is very flexible. You can easily configure layered navigation filters according to your own requirements.
1. **Support of different themes and design customization opportunities**: Our search out-of-box supports the most themes. That’s why right after installation it’ll naturally fit into your store design. In case you need to customize something, there is an opportunity to edit CSS and even templates.
1. **Supports WPML**
1. **Fast Installation and Low Server Load**: Thanks to all operations are handled on our servers, it’s easy to install our plugin. You don’t need to install any additional software like Sphinx or Solr. The additional perk: our plugin doesn’t load your server with heavy indexing and searching tasks. Most probably, your store will work faster thanks to this!
1. **High Indexing speed**: You don’t need to wait for days while the scheduled database reindex will be launched! We index your store products almost in real-time! So you can make changes, add new items and in a few seconds your modifyings will be available in your customers search.

= About pricing =

Our plugin use Omega Search SaaS Service. We have a FREE plan and paid plans. Please, check <a href="https://omegacommerce.com/search#plans">our pricing plans</a>.

= Advantages over other search solutions =

* You don't need to install heavy search engines like Sphinx, Solr or Elasticsearch. You don't need to do complex search configuration and pay your hosting provider for help. You don't need to have a dedicated server to run search engine.
* Our search does not consume resources of your server. We will not overload your server. All search queries are performed on our servers and final results are rendered in the browser by javascript.
* Search is very fast. It returns results in few milliseconds.
* Search can work with huge number of products, posts, documents, etc.
* Our search solution has huge number of useful features, which are very important for your customers. For example, filters in the layered navigation allows to find necessary items in very intuitive way.

= More Information =

<a href="https://demo-search-woo.omegacommerce.com/omega-search/?q=woo#q=woo">**Check our demo!**</a>

Visit the <a href="https://omegacommerce.com/search">Omega Commerce website</a> for documentation and support.
Or contact us at support@omegacommerce.com and we’ll gladly try to help you!


== Installation ==

= From your WordPress dashboard =

1. Visit 'Plugins > Add New'
2. Search for 'Omega Instant Search for WooCommerce'
3. Activate 'Omega Instant Search for WooCommerce' from your Plugins page.

= From WordPress.org =

1. Download 'Omega Instant Search for WooCommerce'.
2. Upload the 'omegacommerce-search-woo' directory to your '/wp-content/plugins/' directory, using your favorite method (ftp, sftp, scp, etc...)
3. Activate 'Omega Instant Search for WooCommerce' from your Plugins page.

= Once Activated =

1. Open WordPress Admin panel. Open Omega Search menu.
2. Login or Register in our service.
3. Visit 'Omega Search > Settings' menu. Click a button 'Run Data Synchronization' to sync data.
4. Open store frontend and start typing in the search box. You should see a dropdown list of search autocomplete.

= Data synchronization =

There are several options to peform a data synchronization:

1. Default. Data is synchronized when you add/update/remove item using wordpress admin panel.
2. Syncronisation by wordpress cron. To enable this option, open Omega Search > Settings, enable "Run Data Synchronization By CRON". Make sure that you have setup a cronjob for wordpress. Plugin will synchronize only added/updated/removed items.
3. Syncronisation using external script. You can run data synchronization manually using the command: "php  [path to your wordpress]/wp-content/plugins/omegacommerce-search-woo/shell/sync.php". Or you can add this script to the crontab. By default script will synchronize only added/updated/removed items. So it will run fast.

== Screenshots ==

1. Search Results.
2. Search Results with Custom Filters.
3. Search Dashboard.
4. Settings of Search Autocomplete dropdown list.

== Frequently Asked Questions ==

= Can I use my existing WordPress theme? =

Yes, sure. Omega Instant Search works out-of-the-box with nearly every WordPress theme.

= I experience some difficulties with setting up a service. What should I do next? =

Just let us know your issues at support@omegacommerce.com and we’ll gladly help you to resolve them!

== Changelog ==

= 2.0.8 =
* Support of WMPL for products

= 2.0.7 =
* Solved possible issue with taxes
* Solved possible issue with URLs of categories

= 2.0.5 =
* Solved possible issue with incorrect prices calculation for products with taxes. Update requires a full data reindex.

= 2.0.4 =
* Solved possible error which may happen on plugin activation

= 2.0.3 =
* Solved several minor issues of REST API

= 2.0.0 =
* Switch to Wordpress and WooCommerce API. Plugin will not work on localhost anymore.

= 1.6.3 =
* Support of Wordpress 5.x and WooCommerce 3.5.x
* Improved manual reindexing shell script
* Improved Data Reindexing interface with possibility of custom reindexing
* Added possibility to use custom decimal format for prices
* Added possibility to skip loading Omega Instant Search on certain pages (compatibility fix)
* Fixed price display for Groupable Products
* Fixed incorrect Product Tags filtering

= 1.6.2 =
* Solved possible issue with incorrect database migration, which can lead to high load of server
* Solved possible issue with errors on checkout page

= 1.6.0 =
* Major improvements of code.

= 1.5.3 =
* Added a button "Clean all search indexes"

= 1.5.2 =
* Solved issue with data synchronization after fresh installation.

= 1.5.0 =
* Major improvements of data synchronization system.

= 1.4.16 =
* Fix of possible browser caching of redirect in admin panel. Will affect only version 1.4.15.

= 1.4.15 =
* Added an ability to set max number of requests during data synchronization. Useful in some rare cases.

