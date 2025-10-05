<?php
/**
 * @package     PECF
 *
 * @wordpress-plugin
 Plugin Name:   PE Category Filter
 Plugin URI:    https://pavelespinal.com/wordpress-plugins-pe-category-filter/
 Description:   Modern WordPress plugin for filtering categories from home page with enterprise-grade architecture, performance optimization, and security enhancements. Features intelligent caching and comprehensive testing.
 Version:       2.0.0
 Requires at least: 6.0
 Requires PHP:  8.3
 Author:        Pavel Espinal
 Author URI:    https://pavelespinal.com/about-me/
 License:       GPL-2.0+
 License URI:   http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'PE_CATEGORY_FILTER_VERSION', '2.0.0' );
define( 'PE_CATEGORY_FILTER_PLUGIN_FILE', __FILE__ );
define( 'PE_CATEGORY_FILTER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PE_CATEGORY_FILTER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Autoloader
require_once PE_CATEGORY_FILTER_PLUGIN_DIR . 'vendor/autoload.php';

use PavelEspinal\WpPlugins\PECategoryFilter\Core\Plugin;

// Initialize plugin
$plugin = new Plugin();
$plugin->run();
