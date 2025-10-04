<?php
/**
 * Test bootstrap file
 *
 * @package PE Category Filter
 */

// Define test environment
define('WP_TESTS_DIR', '/tmp/wordpress-tests-lib');
define('WP_TESTS_PHPUNIT_POLYFILLS_PATH', __DIR__ . '/../vendor/yoast/phpunit-polyfills/');

// Load WordPress test environment
if (!file_exists(WP_TESTS_DIR . '/includes/functions.php')) {
    echo "Could not find " . WP_TESTS_DIR . "/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
    exit(1);
}

require_once WP_TESTS_DIR . '/includes/functions.php';

// Load the plugin
function _manually_load_plugin() {
    require dirname(__FILE__) . '/../pe-category-filter.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment
require WP_TESTS_DIR . '/includes/bootstrap.php';
