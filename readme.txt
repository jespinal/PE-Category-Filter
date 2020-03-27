=== PE Category Filter ===
Contributors: khratos
Tags: category, filter, category filter, exclude post, home page, exclude from home, pecf
Donate link: https://www.patreon.com/pavel_espinal
Requires PHP: 5.6
Requires at least: 3.0
Tested up to: 5.3.2
Stable tag: 1.4.0

This plugin allows you to exclude posts that belong to certain categories from your home page.

== Description ==

This plugin allows you to **exclude** posts that belong to certain categories from your **home page**, while still being reachable from the inner sections of your site.

This is an ad-hoc solution that aims to do **one** thing, and do it **right**, with the smallest footprint possible (hopefully). So please note the following:

* The functionality of this plugin can not be limited to a given widget on the home page. For example:

Given a the category "MyCategory" from which you want to exclude posts on the **home page**, and at the same time you want to display posts of the same "MyCategory" in a widget that is also located **in the home page**, will not be possible.

Such a functionality, in my opinion, would be overkilling and does not represent the approach taken by this plugin.

* The focus of this plugin is simplicity, performance and correctness of the code.

You can see this plugin in action the following projects:

* http://asteriskfaqs.org - Important VoIP community project.
* http://centosfaq.org
* http://slackware-es.com - Spanish version of the Slackware Linux project's website (Spanish)

== Installation ==

1. Upload `pe-category-filter` directory to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Settings' and choose which categories' posts you want to _exclude_ from the Home Page

If you manage your WordPress installation using composer, follow this instructions:

1. Add the following in the `requirements` section of your `composer.json` file:

```
  "require": {
    "wpackagist-plugin/pe-category-filter":"^1.3"
  },
```

2. Add the following to the `repositories` section of your `composer.json` file:

```
  "repositories" : [
      {
          "type":"composer",
          "url":"https://wpackagist.org"
      }
  ]
```

3. Run `composer install`

A third installation method if you are using composer is to add the Github:

1. Add the following in the `requirements` section of your `composer.json` file:

```
  "require": {
      "pavel_espinal/pe-category-filter":"^1.4.0"
  },
```

2. Add the following to the `repositories` section of your `composer.json` file:

```
  "repositories" : [
      {
          "type":"git",
          "url":"https://github.com/jespinal/PE-Category-Filter.git"
      }
  ]
```

3. Run `composer install`

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

= 1.3 =
* Global code assessment to ensure compatibility with latest WP versions.
* Adding the GitHub README.md file (this plugin is now also hosted on github).
* Corrections to "readme" file/documentation.

= 1.4.0 =
* Global code refactoring in order to use classes, namespaces and to ensure compatibility with latest WordPress and PHP versions.
* Included a package definition using a `composer.json` file in order to allow installation from **Github**.
* Included a `phpcs.xml` configuration file for `phpcs`.
* Switched to semantic versioning system for releases.

== Upgrade Notice ==

= 1.4.0 =
Global code assessment for compatibility with latest versions of WordPress and recent versions of PHP. Note: requires plugin reactivation.
