<?php
/**
 * PHPStan Bootstrap File
 * 
 * This file provides constants and functions for PHPStan static analysis
 * without affecting the main plugin runtime.
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

// Define plugin constants for PHPStan analysis
if (!defined('PE_CATEGORY_FILTER_VERSION')) {
    define('PE_CATEGORY_FILTER_VERSION', '2.0.0');
}

if (!defined('PE_CATEGORY_FILTER_PLUGIN_FILE')) {
    define('PE_CATEGORY_FILTER_PLUGIN_FILE', __FILE__);
}

if (!defined('PE_CATEGORY_FILTER_PLUGIN_DIR')) {
    // Avoid calling plugin_dir_path() which requires WordPress functions.
    // Use the directory of this bootstrap file so PHPStan can run without WP.
    define('PE_CATEGORY_FILTER_PLUGIN_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
}

if (!defined('PE_CATEGORY_FILTER_PLUGIN_URL')) {
    // Provide a simple placeholder URL for static analysis purposes.
    define('PE_CATEGORY_FILTER_PLUGIN_URL', 'https://example.org/pe-category-filter/');
}

// WordPress constants that might be needed
if (!defined('HOUR_IN_SECONDS')) {
    define('HOUR_IN_SECONDS', 3600);
}
