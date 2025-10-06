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
- **Testing:** Comprehensive test suite with 51 tests covering all functionality
- **Documentation:** Complete user and developer guides with troubleshooting support

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
- Node.js (for asset building)

### **Setup**
```bash
git clone https://github.com/jespinal/PE-Category-Filter.git
cd PE-Category-Filter
composer install
npm install
npm run build
```

### **Testing**
```bash
composer test              # Run all tests
composer test:coverage     # Generate coverage report
composer quality          # Run code quality checks
composer check-all        # Run all checks
```

### **Contributing**
Due to time constraints, we encourage forking the repository and making improvements for yourself. This ensures you can implement changes at your own pace while contributing to the project's evolution.

## 📚 **Documentation**

- **[Installation Guide](docs/user-guide/installation.md)** - Complete setup instructions
- **[Configuration Guide](docs/user-guide/configuration.md)** - Plugin configuration
- **[FAQ](docs/user-guide/faq.md)** - Frequently asked questions
- **[Troubleshooting](docs/user-guide/troubleshooting.md)** - Common issues and solutions
- **[Complete Tutorial](docs/2025-10-04-232000-complete-tutorial-guide.md)** - Full modernization journey

## 🌐 **Live Examples**

This plugin is actively used on:

- **[pavelespinal.com](https://pavelespinal.com)** - Personal website and blog
- **[slackware-es.com](https://slackware-es.com)** - Spanish Slackware Linux community
- **[trendsanctuary.com](https://trendsanctuary.com)** - Technology, life and home trends and insights
- **[ecosdeleden.com](https://ecosdeleden.com)** - Educational content for children
- **[dietapaleo.com](https://dietapaleo.com)** - Paleo diet and lifestyle content

## 📖 **Background**

Originally developed in **2012**, this plugin has been maintained for over 13 years, serving the WordPress community with a simple yet powerful solution for category filtering. The v2.0.0 release in 2025 represents a complete modernization, transforming the plugin from legacy code to enterprise-grade software while maintaining its core simplicity and reliability.

## 🔧 **Technical Details**

- **Modern PHP 8.3+ Features:** Type hints, namespaces, and modern syntax
- **WordPress Standards:** Full compliance with WordPress coding standards
- **Performance:** Intelligent caching reduces database queries by 80%
- **Security:** CSRF protection, input validation, and output escaping
- **Accessibility:** Screen reader support and keyboard navigation
- **Testing:** 51 comprehensive tests with real WordPress environment

## 📄 **License**

This plugin is licensed under the GPL v2 or later.

## 🤝 **Support**

- **WordPress.org:** [Plugin Support Forum](https://wordpress.org/support/plugin/pe-category-filter/)
- **GitHub:** [Issues and Feature Requests](https://github.com/jespinal/PE-Category-Filter/issues)
- **Documentation:** [Complete User Guide](https://github.com/jespinal/PE-Category-Filter/blob/master/docs/user-guide/)