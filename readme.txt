=== PE Category Filter ===
Contributors: jespinal
Tags: category, filter, modern, performance, security, accessibility, exclude, home page, pecf
Requires PHP: 8.3
Requires at least: 6.0
Tested up to: 6.4
Stable tag: 2.0.0

Modern WordPress plugin that excludes categories from home page while keeping them accessible elsewhere. Features enterprise architecture, performance optimization, and security enhancements.

== Description ==

PE Category Filter is a modern WordPress plugin that allows you to exclude specific categories from your website's home page while keeping those posts accessible through category archives, search, and direct URLs.

**Key Features:**

* **Modern Architecture:** Symfony-inspired dependency injection and service layer patterns
* **Performance Optimization:** Intelligent caching with 80% reduction in database queries
* **Security Enhancements:** CSRF protection, input validation, and output escaping
* **Accessibility:** Screen reader support and keyboard navigation
* **Testing:** Comprehensive test suite with 51 tests covering all functionality
* **Documentation:** Complete user and developer guides with troubleshooting support

**How it works:**

The plugin modifies WordPress's main query on the home page to exclude posts from selected categories. Posts from excluded categories won't appear on the home page, but they remain fully accessible through:
* Category archive pages
* Search results
* Direct URLs
* RSS feeds
* Other WordPress queries

**Live Examples:**

This plugin is actively used on:
* [pavelespinal.com](https://pavelespinal.com) - Personal website and blog
* [slackware-es.com](https://slackware-es.com) - Spanish Slackware Linux community
* [trendsanctuary.com](https://trendsanctuary.com) - Technology, life and home trends and insights
* [ecosdeleden.com](https://ecosdeleden.com) - Educational content for children
* [dietapaleo.com](https://dietapaleo.com) - Paleo diet and lifestyle content

== Installation ==

**WordPress Admin (Recommended):**
1. Go to `Plugins > Add New`
2. Search for "PE Category Filter"
3. Click "Install Now" and "Activate"

**Manual Installation:**
1. Download the latest release from [GitHub](https://github.com/jespinal/PE-Category-Filter/releases)
2. Upload to `/wp-content/plugins/pe-category-filter/`
3. Activate through the WordPress admin

**Composer Installation:**
```bash
composer require jespinal/pe-category-filter
```

**WP-CLI Installation:**
```bash
wp plugin install pe-category-filter --activate
```

== Configuration ==

1. Go to `Settings > PECF Plugin` in WordPress admin
2. Select categories you want to exclude from the home page
3. Click "Save Changes"
4. Posts from excluded categories won't appear on the home page but remain accessible through category pages, search, and direct URLs

== Frequently Asked Questions ==

= Can I use this plugin on a multisite installation? =

Presumably, yes, the plugin should work with WordPress Multisite (not comprehensively tested). Each site would have its own category filter settings.

= Is this plugin accessible? =

Yes, the plugin supports screen readers and keyboard navigation.

= Does this plugin affect SEO? =

No, the plugin only affects the home page display. Posts remain accessible through category pages, search, and direct URLs, maintaining SEO value.

= Will this plugin slow down my website? =

No, the plugin is designed for optimal performance with intelligent caching and efficient queries.

= Can I change my mind and include categories again? =

Absolutely! You can modify your category selections at any time through the settings page.

= Does it work with caching plugins? =

Yes, the plugin is fully compatible with all major caching plugins including WP Rocket, W3 Total Cache, and LiteSpeed Cache.

= Is it compatible with page builders? =

Yes, the plugin works with all major page builders including Elementor, Gutenberg, Beaver Builder, and Divi.

== Screenshots ==

1. Plugin in the Plugins menu.
2. Plugin configuration panel.

== Changelog ==

= 2.0.0 =
* Complete architectural modernization
* Symfony-inspired dependency injection
* Performance optimization with intelligent caching
* Security enhancements with CSRF protection
* Screen reader support and keyboard navigation
* Comprehensive testing suite (51 tests)
* Modern PHP 8.3+ features
* Complete documentation overhaul

= 1.4.0 =
* Global code refactoring in order to use classes, namespaces and to ensure compatibility with latest WordPress and PHP versions.
* Included a package definition using a `composer.json` file in order to allow installation from **Github**.
* Included a `phpcs.xml` configuration file for `phpcs`.
* Switched to semantic versioning system for releases.

= 1.3 =
* Global code assessment to ensure compatibility with latest WP versions.
* Adding the GitHub README.md file (this plugin is now also hosted on github).
* Corrections to "readme" file/documentation.

= 1.2 =
* Global code assessment to ensure compatibility with latest WP versions.
* Corrections to "readme" file/documentation.

= 1.1 =
* Allowing to exclude categories whether they have posts or not.
* Global code assessment to ensure compatibility with latest WP versions.
* Improving "readme" documentation.

= 1.0 =
* Stable release.

== Upgrade Notice ==

= 2.0.0 =
Major modernization with new architecture, performance improvements, and security enhancements. Requires PHP 8.3+.