=== Another Wordpress Classifieds Plugin (AWPCP) Featured Ads Premium Module ===
Contributors: awpcp.com, simplercomputing.net
Donate link: http://awpcp.com/donate
Tags: classifieds,ads,classified ads,classified,awpcp,classifieds script,classified script,classified theme
Requires at least: 3.0
Tested up to: 3.5.2
Stable tag: tags/3.0
Last Updated: 2013-Jul-1


Another Wordpress Classifieds Plugin Featured Ads allows you to generate a fee category to charge users for special visibility on your classifieds site, via a widget and via searches.
Featured ads are always listed at the top of categories, ad browsing, and search results in AWPCP.  They can also be displayed using the special Featured Ads widget, which can
be placed anywhere on your blog where widgets are allowed. 

== Description ==
Another Wordpress Classifieds Plugin Featured Ads allows you to generate a fee category to charge users for special visibility on your classifieds site, via a widget and via searches.
Featured ads are always listed at the top of categories, ad browsing, and search results in AWPCP.  They can also be displayed using the special Featured Ads widget, which can
be placed anywhere on your blog where widgets are allowed. 

This premium module requires AWPCP 3.2 or higher.

== Installation ==

Upload via your WP control panel.

1)  Download the plugin
2)  Login to your Wordpress site as an administrator
3)  Click on Plugins->Add New
4)  Under "Install Plugins" at the top, click on "Upload".
5)  Click on Browse to locate the ZIP you just downloaded.  After you find it, click OK.
6)  Now click "Install Now"
7)  When the upload is complete, click on "Activate Plugin".  

DO NOT put the files in your main AWPCP plugin directory or they will be deleted when you upgrade AWPCP!

After uploading the plugin to your site, in the WordPress admin panel go to Classifieds -> Fees

From there you can add new ad posting fees or edit existing fees. In either case you'll find a new checkbox in the fee configuration screen labeled "Featured Ad Pricing" 

Check the box if you want the fee to pertain to a feature listings.

Under Appearance -> Widgets, you'll find a new widget called AWPCP Featured Ads that lets you put featured ads in your sidebar(s).

If you need to change the appearance of the featured listings in the sidebar then edit the resources/css/frontend.css file that comes with this premium module.

If you want featured listings to appear differently from other ads when users are browsing ads, then you can add $isfeaturedclass to the div wrapper class in your layout configuration. For example, the default layout contains this tag:

<div class="$awpcpdisplayaditems">

You can alter that like this:

<div class="$awpcpdisplayaditems $isfeaturedclass">

The class name of "awpcp_featured_ad_wrapper" will be automatically inserted in place of $isfeaturedclass. And you can edit the related CSS by editing the awpcp_featured_ads.css file

== Screenshots ==

[Visit the main site for screenshots](http://www.awpcp.com/features)

== Frequently Asked Questions ==

Question: Will this plugin work with versions of WordPress earlier than 2.7?
Answer: The plugin has only been tested with version 2.7 and above. If you wish 
	to test it on earlier versions you are free to do so. If you find that it 
	works on an earlier version please consider sharing this information for 
	the benefit of others.

Question: Does your plugin have any particular requirements for PHP or MySQL versions?
Answer: Yes, you need PHP 5+ and MySQL 5+.  Earlier versions may work, 
	but are not officially supported.

Visit the support forum for [support, FAQs and docs](http://forum.awpcp.com)

Find the [faqs here](http://www.awpcp.com/category/questions-answers/)

[Main Plugin documentation](http://www.awpcp.com/about-2/)

== Changelog ==
 
 = 1.0 =
INITIAL RELEASE


