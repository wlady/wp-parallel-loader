=== WordPress Parallel Loader Plugin ===
Author: Vladimir Zabara
Tags: parallel loader
Requires at least: 3.9
Tested up to: 3.9.2
Stable tag: 1.0.1



WP Parallel Loader plugin.


== Description ==
The WP Parallel Loader plugin reduces page loading time by substituting the original resource URLs with configured hostnames to parallelize downloads across them. [Read more](http://gtmetrix.com/parallelize-downloads-across-hostnames.html)

= Substitution is turned off if: =

* URL starts with double slashes. E.g:

  * &lt;script src&#061;"**//libs/angular.min.js**"&gt;&lt;/script&gt;

* URL contains template variable. E.g:

  * &lt;script src&#061;"**{$domain}**/libs/angular.min.js"&gt;&lt;/script&gt;
  * &lt;script src&#061;"**${domain}**/libs/angular.min.js"&gt;&lt;/script&gt;
  * &lt;script src&#061;"**{{domain}}**/libs/angular.min.js"&gt;&lt;/script&gt;

* External URL is in FQDN format.


== Installation ==


1. Upload `wp-parallel-loader.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure hostnames to be used and type of processed resources



== Screenshots ==

1. Settings Screen
2. Waterfall diagram of a page without domain sharding
3. Waterfall diagram with resources sharded across 3 domains


== Changelog ==

= 1.0.1 =
* Add plugins repository domain for updates and change updates behavior

= 1.0.0 =
* Plugin first version completed
