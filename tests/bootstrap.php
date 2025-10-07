<?php
/**
 * Test Bootstrap
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

// Define test constants (allow overriding via environment variables)
if (!defined('WP_TESTS_DIR')) {
    $env_wp_tests_dir = getenv('WP_TESTS_DIR');
    define('WP_TESTS_DIR', $env_wp_tests_dir ? $env_wp_tests_dir : '/tmp/wordpress-tests-lib');
}

if (!defined('WP_CORE_DIR')) {
    $env_wp_core_dir = getenv('WP_CORE_DIR');
    define('WP_CORE_DIR', $env_wp_core_dir ? $env_wp_core_dir : '/tmp/wordpress/');
}

// Load WordPress test environment
if (file_exists(WP_TESTS_DIR . '/includes/functions.php')) {
    require_once WP_TESTS_DIR . '/includes/functions.php';

    // Compute project root relative to this bootstrap file so the path works
    // regardless of the current working directory when tests are invoked.
    $project_root = realpath(__DIR__ . '/..');

    // Load the plugin when WordPress test suite triggers the `muplugins_loaded`
    // action. Requiring the plugin immediately can cause an early exit when
    // `ABSPATH` is not yet defined (the plugin guards against direct access),
    // so we defer loading until WP is bootstrapped.
    if ($project_root && file_exists($project_root . '/pe-category-filter.php') && function_exists('tests_add_filter')) {
        tests_add_filter('muplugins_loaded', function () use ($project_root) {
            require_once $project_root . '/pe-category-filter.php';
        });
    }

    // Bootstrap the WordPress testing environment which will in turn run the
    // muplugins_loaded hooks and load the plugin above.
    if (file_exists(WP_TESTS_DIR . '/includes/bootstrap.php')) {
        require_once WP_TESTS_DIR . '/includes/bootstrap.php';
    }
}

// Load test utilities
if (file_exists(__DIR__ . '/includes/test-utils.php')) {
    require_once __DIR__ . '/includes/test-utils.php';
}