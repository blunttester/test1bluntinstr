=== Reusable Product Description for WooCommerce ===
Contributors: mjke87
Donate link: https://paypal.me/mjjarrett
Tags: woocommerce, product, product description, attribute description, category description, attribute, product attribute, product tab, product attribute tab
Requires at least: 4.4.0
Requires PHP: 5.6
Tested up to: 5.4.1
Stable tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

 Avoid redundant product descriptions! Show category or attribute descriptions in your product main description or show them in extra tabs.

== Description ==

This plugin allows you to reuse descriptions and dynamically display them on your products. Add pieces of descriptions to your categories and product attributes, which then are added to your product description or displayed as separate tabs. This allows you to reuse any descriptions that are specific to certain attributes or categories without rewriting all information for every applicable product again and again.

The plugin creates a new field for each product category and attribute taxonomy that can be used to add and display additional information related to a specific category or attribute.

The extra information will be displayed in a separate tab by default with the product attribute taxonomy name as the tab title. The content of the tab will show all applicable attribute tab descriptions. The extra field lets you also use HTML, shortcodes and special placeholders that are replaced with product information.

= Features =

- Display WooCommerce product category and attribute information in a separate tab or in the product detail
- Control display type for each WooCommerce attribute taxonomy
- Swap between default attribute description field or use the default plugin field
- Control the tab order using priority values for each product taxonomy
- Set an alternative tab title via attribute settings
- Support for placeholders within the description (see below) that are replaced with product data

= Placeholders =

**Product properties**
The `{product:property}` placeholder can be used to replace certain parts of the text with a product property:

- `{product:name}`: get the product name
- `{product:sku}`: get the product SKU
- `{product:weight}`: get the product weight
- `{product:height}`: get the product height
- `{product:width}`: get the product width
- `{product:length}`: get the product length

Additionally, it is possible to truncate the resulting string before or after a given substring with the syntax `{product:property:string}`. For example:

- `{product:name:-}` would display the product name but cut of everything after `-`
- `{product:name::-}` would display the product name but cut of everything before `-`

If both cases the `-` character is also removed.

**Product attributes**
The `{attribute:taxonomy}` placeholder can be used to replace certain parts of the text with a product attribute. Replace the taxonomy part in the placeholder with any product attribute that you wish (slug).

For example, if you have an attribute *color*, you could use the following placeholder to insert the color of the product into the text: `{attribute:color}`. If more than one color is attached to the product, they are listed and separated by a comma.

It is therefore also possible to select only one term by using the syntax `{attribute:taxonomy:index}`. Replace the index part with a number. For example `{attribute:color:1}` will show the first color of the product.

**Product meta data**
The `{meta:key}` placeholder can be used to replace parts of the text with product meta data. Replace the key part in the placeholder with any meta data from the database.

Product meta data can also be trimmed just the like the product properties.

= Use Cases =
**Use Case 1 – Size guide**
Let's say we have a product attribute taxonomy named *Size*. For all products that are using this attribute type we wish to display a size guide on the product page, which helps the customer to find the right size. We use the plugin to specify a tab description for every size that we configured (e.g. XS, S, M, L, XL). The tab description for a size attribute could for example explain the recommended body measurements that fit this size. Finally, we create a product where we assign the size attribute and select the attribute values S, M and L. On the product page of this product we will now see a new tab named *Size* that shows the tab descriptions of the size attributes S, M and L.

**Use Case 2 – Brand information**
We might have products of different brands and for each brand we'd like to display a short description about the brand in the product description. Normally we'd have to copy paste the brand description to every product of this brand. If every something changes... well, it'd be pretty cumbersome. Now let's say we have a product attribute taxonomy named *Brand*. With this plugin we can instead add the brand description on attribute term level and automatically display it for every product with that brand assigned. We only manage the description once and display it everywhere.

**Use Case 3 – Dynamic product descriptions**
We create a new product taxonomy "Product Type" and add the reusable product descriptions there. We configure it to append the description to the main product description. We assign our product type attributes to the according products, but do not display the attribute on the product page. One product can even have more than one product types assigned to dynamically build up a product description. You could also use product categories instead.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Make sure that WooCommerce is installed and activated, otherwise the plugin won't work
2. Go to Products -> Attributes -> Add/Edit -> Use the fields provided by the plugin to configure how the attribute is being displayed
1. Go to Products -> Attributes -> Select a attribute taxonomy -> Use the *Product Tab Description* field to make use of the plugin
1. Add attributes with descriptions to products to display them in the tabs on the product page
1. Use the plugin filter and action hooks to configure the plugin as needed

== Frequently Asked Questions ==

= Why does the product tab description not show up on the product page? =

The description will only be visible if the product is associated with that specific attribute where you added your description. Every available attribute description will be wrapped in a separate paragraph by default. Also check the attribute settings and make sure you selected the correct description source and display type.

= Why does the plugin not use the default description field? =

The plugin creates a new meta field to avoid conflicts with the existing attribute descriptions, as they might already be used for other purposes. Furthermore, the default term description field does not support shortcodes and also the HTML support is inconsistent.

If you still wish to use the default attribute description instead, proceed as follows:
Navigate to Products -> Attributes -> Edit -> Description source -> Select "Term description" and save your changes.

= How can I change the tab title? =

Navigate to Products -> Attributes -> Edit -> Alternative Tab Title -> Enter a tab title or leave blank to use the default attribute title and save your changes.

= How can I translate the tab title? =

This plugin is equipped with native support for [Polylang](https://wordpress.org/plugins/polylang/) and [WPML](https://wpml.org/). The strings can be translated using the string translations functionality of the two plugins. Strings are registered witht the following domain/group: `woo-product-attribute-tab`. First, see the FAQ section *How can I change the tab title?* to learn how to set a custom attribute tab title. Then, proceed as follows to translate your tab title:

**Polylang**
1. Navigate to Languages -> String translations
2. Select `woo-product-attribute-tab` from the groups dropdown and press *Filter*
3. Translate your tab titles and save the changes

**WPML**
1. Navigate to WPML -> String Translation
2. Select `woo-product-attribute-tab` from the domains dropdown and press *Filter*
3. Translate your tab titles and save the changes

= How can I change the tab order? =

The default ordering mechanism of tabs depends on the order of the attributes you assigned to a product and is controlled by a priority number. The default WooCommerce tabs have the following priority numbers assigned:

- Description: 10
- Additional Information: 20
- Reviews: 30

The plugin will try to display the product attribute tabs between the Additional Information and Reviews tab. However, if you have many attributes assigned to a product, this does not work reliably. The product attribute position starts to count at 1. The plugin will add an offset of 20 (default behavior). For example, if you're displaying the 5th attribute in a separate tab, the calculated priority number becomes 25, in which case the new tab will be displayed at the third position of your tabs (between the Additional Information and Reviews tabs). However, your attribute is at position 11, the final calculated priority number becomes 31 and displays the tab after the Reviews tab.

If you wish to set an absolute priority for certain attributes, proceed as follows:
Navigate to Products -> Attributes -> Edit -> Tab Priority -> enter any numerical value you like (small numbers means high priority, large number means low priority) and save your changes.

= How can I hide the description of a certain attribute? =

If you wish to hide the description of a particular attribute, proceed as follows.
Navigate to Products -> Attributes -> Edit -> Display Type -> Select "Do not display" from the dropdown and save your changes.

No descriptions will be displayed for this attribute anymore.

= How can I display the description in the main tab? =

If you wish to display the term or tab description after the main description, rather than in a separate tab, then proceed as follows:
Navigate to Products -> Attributes -> Edit -> Display Type -> Select "Append to main description" from the dropdown and save your changes.

The descriptions will be appended to the main description for this attribute.

== Screenshots ==

1. The extra product tab description field in the product attribute edit screen.
2. Two product attribute descriptions displayed at once on the product detail page.
3. The extra attribute tab settings on the Attribute Edit Screen.

== Changelog ==

= 1.3.0 =
* [New Feature] Add product descriptions for categories (limited)
* [New Feature] Add new product, attribute and meta placeholders
* [Update] Rely on `wpautop` rather than custom format
* [Update] Add WordPress 5.4.0 compatibility
* [Update] Add WooCommerce 4.1.0 compatibility

= 1.2.1 =
* [Fix] Fix a warning when rendering taxonomy fields
* [Fix] Fix an error when fetching the current WooCommerce product
* [Update] Improve the readme documentation; add more use cases and feature list
* [Update] Update the WooCommerce and WordPress supported versions

= 1.2.0 =
* [New Feature] Add possibility to specify placeholders in the attribute tab description that will be replaced with dynamic values.
* [Fix] Display the product description tab if no description is available, but attribute description are defined.
* [Fix] Catch fatal error if the global product is not yet set.
* [Fix] Fix unnecessary and excessive logging if `display_type` variable is undefined.

= 1.1.3 =
* [Fix] Switch back to `do_shortcode`. Although using `the_content` works in many cases, it can break the site in certain setups. `do_shortcode` works for most cases except for the `embed` shortcode. Sorry about that.

= 1.1.2 =
* [Fix] Switch from `do_shortcode($content)` to `apply_filters('the_content', $content);` to make shortcodes work in all cases.

= 1.1.1 =
* [Fix] Ensure consistent support of shortcode and HTML for attribute descriptions. Previously didn't work when displaying contents in the main description.

= 1.1.0 =
* [New Feature] Native support for Polylang and WPML to translate attribute meta data.
* [Fix] Add more checks to avoid a fatal error if WooCommerce is not available.
* [Dev] Improved source code and filter documentation.

= 1.0.0 =
* WooCommerce 3.0 compatibility
* [New Feature] New display types for tab descriptions: display as separate tab (default and formerly the only option), append to main description (new), hide (new). You can change the display type via attribute settings in the Backend.
* [New Feature] Set a tab description source (term or tab description) via attribute settings in the Backend (old hooks still work).
* [New Feature] Set an alternative tab title via attribute settings in the Backend (old hooks still work).
* [New Feature] Set an absolute tab priority via attribute settings in the Backend (old hooks still work).

= 0.0.3 =
* Fixed the problem where tabs of empty product attribute descriptions would be displayed.
* Fixed the problem that product tab descriptions cannot be unset/deleted.

= 0.0.2 =
* Fixed the problem with the not working updating of product attribute descriptions.

= 0.0.1 =
* First stable release.

== Upgrade Notice ==

None yet.
