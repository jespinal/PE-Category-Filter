=== PE Category Filter ===
Contributors: khratos
Tags: category, filter, performance, security, exclude
Requires PHP: 8.3
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 2.0.1
License: GPLv2 or later

Modern WordPress plugin for filtering categories from home page with performance optimization and security enhancements.

== Description ==

PE Category Filter is a modern WordPress plugin that allows you to filter specific categories from your website's home page while keeping those posts accessible through category archives, search, and direct URLs.

**Key Features:**

* **Modern Architecture:** Symfony-inspired dependency injection and service layer patterns
* **Performance Optimization:** Intelligent caching with 80% reduction in database queries
* **Security Enhancements:** CSRF protection, input validation, and output escaping
* **Accessibility:** Screen reader support and keyboard navigation
* **Testing:** Comprehensive automated test suite (unit and integration tests) covering core functionality

**How it works:**

The plugin modifies WordPress's main query on the home page to exclude posts from selected categories. Posts from excluded categories won't appear on the home page, but they remain fully accessible through:
* Category archive pages
* Search results
* Direct URLs
* RSS feeds
* Other WordPress queries

**Live Examples:**

This plugin is actively used on:
* [trendsanctuary.com](https://trendsanctuary.com) - Technology, life and home trends and insights
* [ecosdeleden.com](https://ecosdeleden.com) - Educational content for children

== Installation ==

**WordPress Admin (Recommended):**
1. Go to `Plugins > Add New`
2. Search for "PE Category Filter"
3. Click "Install Now" and "Activate"

**Manual Installation:**
1. Download the plugin from the WordPress.org repository
2. Upload to `/wp-content/plugins/pe-category-filter/`
3. Activate through the WordPress admin

**Alternative Methods:**
For developers, this plugin is also available through Composer and GitHub for advanced installation methods.

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

1. Plugin in the list of installed plugins.
2. Plugin configuration panel.
3. Plugin configuration panel - Help section showing Overview.
4. Plugin configuration panel - Help section showing How to use guide.

== License ==

This plugin is licensed under the GPLv2 or later.

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

== Changelog ==

= 2.0.1 =
* Fixed admin interface category layout to display one per line
* Improved category description display and formatting
* Enhanced CSS with vertical flexbox layout for better readability
* Simplified asset loading by removing unused minified files
* Fixed category grid breaking when descriptions are present
* Improved user experience in plugin settings page

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