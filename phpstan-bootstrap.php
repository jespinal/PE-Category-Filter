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
    define('PE_CATEGORY_FILTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('PE_CATEGORY_FILTER_PLUGIN_URL')) {
    define('PE_CATEGORY_FILTER_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// WordPress constants that might be needed
if (!defined('HOUR_IN_SECONDS')) {
    define('HOUR_IN_SECONDS', 3600);
}
