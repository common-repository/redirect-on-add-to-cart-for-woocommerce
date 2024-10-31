=== Redirect on Add To Cart For WooCommerce ===
Contributors: wpxqw
Tags: buynow, buy now, direct checkout, quick checkout, one click checkout, skip cart, redirect on add to cart, add to cart redirect, redirect to checkout, redirect to page, redirect to url, woocommerce, add to cart, ajax add to cart
Requires at least: 5.0
Tested up to: 6.3
Requires PHP: 5.6
Stable tag: 1.3.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lets you redirect the user to a page on your website or internal/external URL on add-to-cart button.

== Description ==
Redirect on Add To Cart For WooCommerce plugin lets you redirect the user to a page on your website or internal/external URL on add-to-cart button.

=== Some use cases ===

1. The default WooCommerce checkout flow expects user to navigate from shop/single-product page to cart page to checkout page. This navigation results in a slow and cumbersome checkout experience for the user and often makes the user leave your site without any purchase (a.k.a. abandoned cart). For these reason, you want to provide a faster checkout experience to your users by automatically redirecting them to the checkout page after the product is added to cart.
2. You want user to fill out a form or read terms & conditions before/after the product is added to cart. You want to achieve it by redirecting the user to a purpose-built page on your website after add-to-cart button is clicked.
3. You want to use an external checkout service - may be, you own multiple stores and want to redirect the user to the other store or you are an affiliate and want to earn commission on variable/grouped products by redirecting the user to external website.

=== Features ===

- For all products
  - Enable redirection on add-to-cart button
- Redirection
  - Redirect to a page on your website
  - Redirect to any URL - an internal or external URL
  - Works when WooCommer setting "Enable AJAX add to cart buttons on archives" is enabled.
  - Works when WooCommerce setting "Redirect to the cart page after successful addition" is enabled.

=== Premium Features ===
- Product level settings
    - Override global settings at product level or just configure at product level
- Set custom label for add-to-cart button
- Skip addition to cart (helpful when you redirect the user to an external URL)
- Hide "product has been added to cart message" (helpful when you redirect the user to checkout page)
- Compatible with Multisite
- Compatible with Block Theme, WooCommerce Cart and Checkout Blocks
- Compatible with Dokan - a Multivendor Marketplace plugin powered by WooCommerce

=== Video Preview (Pro version) ===
[youtube https://www.youtube.com/watch?v=--aM4G5NMqM]


[Live Demo](http://cartplus.42web.io/shop) | [Upgrade to PRO](https://codecanyon.net/item/woocommerce-redirect-to-page-or-url-on-add-to-cart-direct-checkout-or-skip-cart/36215584) | [Documentation](https://wpxqw.github.io/mwca)

== Frequently Asked Questions ==

= What if settings of a Grouped product are different from the settings of its child product(s)? =

Grouped product settings will be used.

= Does settings of a Variable product apply to all of its variation? =

Yes.

== Screenshots ==
1. Global Level Settings
3. Global Level Setting (Pro)
4. Product Level Setting (Pro)

== Changelog ==
= 1.3.2 =
* updated readme.txt

= 1.3.1 =
* updated readme.txt

= 1.3.0 =
* Added compatability for Block theme
* Added compatability for Cart Cross-Sells Products Block
* Added compatability for ProductButon Block
* Tested with PHP 8.2.5, WordPress 6.5.4, WooCommerce 8.8.5
* Declared dynamic properties in class to avoid deprecation notice (dynamic properties are deprecated in PHP 8.2) 

= 1.2.2 =
* updated readme.txt

= 1.2.1 =
* updated readme.txt

= 1.2.0 =
* code refactored

= 1.1.0 =
* updated readme.txt

= 1.0.0 =
* Initial Release
