<?php
/**
 * Test Bootstrap
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

// Define test constants
if (!defined('WP_TESTS_DIR')) {
    define('WP_TESTS_DIR', '/tmp/wordpress-tests-lib');
}

if (!defined('WP_CORE_DIR')) {
    define('WP_CORE_DIR', '/tmp/wordpress/');
}

// Load WordPress test environment
if (file_exists(WP_TESTS_DIR . '/includes/functions.php')) {
    require_once WP_TESTS_DIR . '/includes/functions.php';
}

// Load plugin
if (file_exists(dirname(__DIR__) . '/pe-category-filter.php')) {
    require_once dirname(__DIR__) . '/pe-category-filter.php';
}

// Load test utilities
if (file_exists(__DIR__ . '/includes/test-utils.php')) {
    require_once __DIR__ . '/includes/test-utils.php';
}