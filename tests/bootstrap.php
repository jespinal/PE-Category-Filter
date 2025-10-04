<?php
/**
 * PE Category Filter Test Suite Bootstrap
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

// Define test environment
define('WP_TESTS_DIR', '/tmp/wordpress-tests-lib');
define('WP_TESTS_PHPUNIT_POLYFILLS_PATH', __DIR__ . '/../vendor/yoast/phpunit-polyfills/');

// Load WordPress test environment
if (!file_exists(WP_TESTS_DIR . '/includes/includes/functions.php')) {
    echo 'Could not find ' . WP_TESTS_DIR . '/includes/includes/functions.php, have you run tests/bin/install-wp-tests-local.sh ?' . PHP_EOL;
    exit(1);
}

require_once WP_TESTS_DIR . '/includes/includes/functions.php';

// Load the plugin
function _manually_load_plugin() {
    require __DIR__ . '/../pe-category-filter.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment
require WP_TESTS_DIR . '/includes/includes/bootstrap.php';