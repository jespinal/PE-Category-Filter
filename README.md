# PE Category Filter

**Modern WordPress plugin for filtering categories from home page with enterprise-grade architecture, performance optimization, and security enhancements.**

[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## 🚀 **Features**

- **Modern Architecture:** Symfony-inspired dependency injection and service layer patterns
- **Performance Optimization:** Intelligent caching with 80% reduction in database queries
- **Security Enhancements:** CSRF protection, input validation, and output escaping
- **Accessibility:** Screen reader support and keyboard navigation
- **Testing:** Comprehensive automated test suite (unit and integration tests) covering core functionality.

## 📋 **Requirements**

- **WordPress:** 6.0 or higher
- **PHP:** 8.3 or higher  
- **MySQL:** 5.7 or higher (or MariaDB 10.3+)

## 🛠️ **Installation**

### **WordPress Admin (Recommended)**
1. Go to `Plugins > Add New`
2. Search for "PE Category Filter"
3. Click "Install Now" and "Activate"

### **Manual Installation**
1. Download the latest release from [GitHub](https://github.com/jespinal/PE-Category-Filter/releases)
2. Upload to `/wp-content/plugins/pe-category-filter/`
3. Activate through the WordPress admin

### **Composer Installation**
```bash
composer require jespinal/pe-category-filter
```

### **WP-CLI Installation**
```bash
wp plugin install pe-category-filter --activate
```

## ⚙️ **Configuration**

1. Go to `Settings > PECF Plugin` in WordPress admin
2. Select categories you want to exclude from the home page
3. Click "Save Changes"
4. Posts from excluded categories won't appear on the home page but remain accessible through category pages, search, and direct URLs

## 🏗️ **Architecture**

This plugin uses modern PHP patterns inspired by Symfony:

- **Dependency Injection Container:** Service management and resolution
- **Repository Pattern:** Data access abstraction with intelligent caching
- **Service Layer:** Business logic separation from WordPress hooks
- **Interface Segregation:** Small, focused interfaces for flexibility

## 🧪 **Development**

### **Prerequisites**
- WordPress 6.0+
- PHP 8.3+
- MySQL 5.7+
- Composer

### **Setup**
```bash
git clone https://github.com/jespinal/PE-Category-Filter.git
cd PE-Category-Filter
composer install
```

### **Testing (local)
**

The project includes both unit and integration tests. Integration tests use the WordPress PHPUnit test suite and require a local WordPress test environment (database + test suite).

Prerequisites
- PHP 8.3+, Composer, and a local MySQL instance

Quick steps (example)

1. Install dependencies (if not already):

```bash
composer install
```

2. Install the WordPress test suite and create a test database. The repository includes helper scripts in `tests/bin/`:

- Use the interactive/local script when developing with an existing WP install:

```bash
./tests/bin/install-wp-tests-local.sh
# (This script is intended for local setups and will prompt or use the hardcoded values in the script.)
```

- Or run the general installer that downloads the WP test suite, configures `WP_TESTS_DIR` and creates the DB:

```bash
./tests/bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]
# Example:
./tests/bin/install-wp-tests.sh wordpress_test root '' 127.0.0.1 latest
```

Both installers populate the test bootstrap (the project uses `tests/bootstrap.php`) and set up the WordPress test framework (default paths: `WP_TESTS_DIR=/tmp/wordpress-tests-lib`, `WP_CORE_DIR=/tmp/wordpress/`).

3. Run the tests:

```bash
composer test          # runs phpunit
composer test:coverage # generate coverage HTML in ./coverage
```

Or run the binary directly:

```bash
vendor/bin/phpunit --testdox
```

Notes & troubleshooting
- The integration tests require a functioning WordPress test suite and a MySQL user that can create the test database.
- If tests output nothing or fail to bootstrap, check `tests/bootstrap.php` and ensure the `WP_TESTS_DIR` and `WP_CORE_DIR` paths are correct and accessible.
- For CI, you can run the `tests/bin/install-wp-tests.sh` script in your runner, or use established WordPress testing Actions that prepare the test environment.

### Running tests & generating coverage (note)

If you want to generate code coverage reports, a coverage driver must be available to PHPUnit. That means one of the following must be available for the CLI PHP:

- Xdebug (recommended for local dev) — enable it and set `XDEBUG_MODE=coverage` when running PHPUnit, or enable `xdebug.mode=coverage` in your `php.ini` for CLI.
- phpdbg (shipped with PHP) — run PHPUnit through phpdbg (example below).
- PCOV — an alternative coverage extension.

Examples:

```bash
# With Xdebug enabled (preferred)
XDEBUG_MODE=coverage composer run test:coverage

# Or explicitly with phpdbg (if available and built for your PHP):
/usr/bin/phpdbg -qrr ./vendor/bin/phpunit --configuration phpunit.xml --coverage-html coverage
```

Common gotchas:

- If PHPUnit prints "No code coverage driver available" then either Xdebug/PCOV is not installed/enabled for CLI or phpdbg is not the correct binary for your PHP.
- If Xdebug is installed but you still see a warning, ensure `XDEBUG_MODE=coverage` is set or `xdebug.mode=coverage` is enabled for the CLI SAPI.
- Coverage generation can be slower; use it for local analysis or CI but avoid running it for every quick dev test.

### **Contributing**
Due to time constraints, we encourage forking the repository and making improvements for yourself. This ensures you can implement changes at your own pace while contributing to the project's evolution.

## 📚 **Documentation**

Documentation is planned; for now please consult the codebase and inline comments for guidance.

## 🌐 **Live Examples**

This plugin is actively used on:

- **[pavelespinal.com](https://pavelespinal.com)** - Personal website and blog
- **[slackware-es.com](https://slackware-es.com)** - Spanish Slackware Linux community
- **[trendsanctuary.com](https://trendsanctuary.com)** - Technology, life and home trends and insights
- **[ecosdeleden.com](https://ecosdeleden.com)** - Educational content for children
- **[dietapaleo.com](https://dietapaleo.com)** - Paleo diet and lifestyle content

## 📖 **Background**

Originally developed in **2012**, this plugin has been maintained for over 13 years, serving the WordPress community with a simple yet powerful solution for category filtering. The v2.0.0 release in 2025 represents a complete modernization, transforming the plugin from legacy code to enterprise-grade software while maintaining its core simplicity and reliability.

## 📄 **License**

This plugin is licensed under the GPL v2 or later.

## 🤝 **Support**

- **WordPress.org:** [Plugin Support Forum](https://wordpress.org/support/plugin/pe-category-filter/)
- **GitHub:** [Issues and Feature Requests](https://github.com/jespinal/PE-Category-Filter/issues)