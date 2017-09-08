=== PE Category Filter ===
Contributors: khratos
Tags: category, filter, category filter, exclude post, home page, exclude from home
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=U8W8GKD6KM2G8
Requires at least: 3.0
Tested up to: 4.7.5
Stable tag: 1.2

This plugin allows you to exclude posts that belong to certain categories from your home page.

== Description ==

This plugin allows you to **exclude** posts that belong to certain categories from your **home page**, while they will still be reachable
from the inner sections of your site.

This is an ad-hoc solution that aims to do **one** thing, and do it **right** with near the smallest footprint possible. So please note the following:

* The functionality of this plugin can not be limited to a given widget on the home page. Example:

Given a the category "MyCategory" from which you want to exclude posts on the **home page**, and at the same time you want to display posts of "MyCategory" in a widget that is also located **in the home page**, will not be possible.

Such a functionality, in my opinion, would be overkilling because what you are looking for is not to *exclude* everything and do one exception, instead you should *allow* everything and do one exception.

* The focus of this plugin is simplicity, performance and correctness of the code.

You can see this plugin in action the following projects:

* http://asteriskfaqs.org - Important VoIP community project.
* http://centosfaq.org
* http://slackware-es.com - Slackware Linux documentation project (Spanish)


== Installation ==

1. Upload `pecf_catfilter` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Settings' and choose which categories' posts you want to _exclude_ from the Home Page

== Frequently Asked Questions ==

1. I'm using the plugin and while it works as expected, I would like it to allow content from a filtered category to be displayed by
some other plugin. Is that possible?

No. At least not for now. Read the description for more details.

== Screenshots ==

1. Plugin in the Plugins menu.
2. Plugin configuration panel.

== Changelog ==

= 1.0 =
* Stable release.

= 1.1 =
* Allowing to exclude categories whether they have posts or not.
* Global code assessment to ensure compatibility with latest WP versions.
* Improving "readme" documentation.

= 1.2 =
* Global code assessment to ensure compatibility with latest WP versions.
* Corrections to "readme" file/documentation.