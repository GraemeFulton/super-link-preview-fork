=== Super Link Preview ===
Contributors: daniele.perilli
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EZLNC7WFPTDH4
Tags: screenshot, browser, browser shot, generator, tool, shortcode, automate, screenshots, shots, web browser, window, snap, website screenshot, website preview, link preview, preview, thumbnail, embed, oembed, facebook sharer, sharing, og:image, OpenGraph, ads, shortner, unshortner
Requires at least: 3.3.0
Tested up to: 4.0.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


== Description ==

Get the most relevant image, or the appropriate embedded media player, or the page screenshot of any external link in your post (similar to what you get when you share a page on Facebook or Google+).

The *Super Link Preview* plugin supports:
 
* shortened links (for example t.co or bit.ly links)
* media content (all formats natively supported by Wordpress - http://codex.wordpress.org/Embeds)
* OpenGraph images
* page screenshots

If the external link points to a supported media, this plugin embeds the appropriate player in the page (through the Wordpress native oEmbed function - http://codex.wordpress.org/Embeds). 
If no supported media is found, the plugin embeds the og:image specified in the page’s metadata.
If the no og:image is found, the plugin parses the target external link to find the most relevant image, discarding images smaller than specified size and standard ad banner sizes.
If not a relevant image is found, the plugin takes a screenshot of the linked page (through a Wordpress public service located at http://s.wordpress.com/mshots/v1).

You can overrule the workflow and force the plugin to take the linked page’s screenshot with the *forceshot* dashcode attribute.


= Shortcode Preview =

[link-preview url="link-to-website" forceshot="false"]

= Multisite Compatibility =

The *Super Link Preview* plugin is compatibly with WordPress Multisite, just use the [Network Activate](http://codex.wordpress.org/Create_A_Network#WordPress_Plugins) feature to enable the shortcode on every site. If you only want to enable the shortcode for a specific site, activate the plugin for that site only.

== Installation ==

1. Install easily with the WordPress plugin control panel or manually download the plugin and upload the folder 'link-preview' to the '/wp-content/plugins/' directory 
2. Activate the plugin through the 'Plugins' menu in WordPress


== Changelog ==

= 1.0.1 =
Release date: December 3, 2014

* Bug Fix: Rich editor shortcode button
* Bug Fix: Removed WARNING notices when inserting new posts or broken remote images urls

= 1.0.0 =
* Initial public release to the WordPress plugin repository