## Synopsis

This is WordPress plugin that has been around for nearly 6 years now. It allows you to *exclude* posts that belong to certain categories of your *home page*, while they will still be reachable
from the inner sections of your site.

This is an ad-hoc solution that aims to do *one* thing, and do it *right*, with near the smallest footprint possible. So please note the following:

* The functionality of this plugin can not be limited to a given widget on the home page. Example:

Given a the category "MyCategory" from which you want to exclude posts on the **home page**, and at the same time you want to display posts of "MyCategory" in a widget that is also located **in the home page**, will not be possible.

Such a functionality, in my opinion, would be overkilling because what you are looking for is not to *exclude* everything and do one exception, instead you should *allow* everything and do one exception.

* The focus of this plugin is simplicity, performance and correctness of the code.

You can see this plugin in action the following projects:

* http://asteriskfaqs.org - Important VoIP community project.
* http://centosfaq.org
* http://slackware-es.com - Slackware Linux documentation project (Spanish)

## Motivation

I decided to maintain the code in Github in order to make it available to a bigger audience.

## Installation

1. Upload `pecf_catfilter` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Settings' and choose which categories' posts you want to _exclude_ from the Home Page

Note: the simplest way to install it is to search for it through the WordPress plugin search bar.
