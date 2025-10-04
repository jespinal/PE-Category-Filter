<?php
/**
 * @package PECF (PavelEspinal Category Filter)
 * @author Pavel Espinal
 * @version 2.0.0
 */

/*
Plugin Name:   PE Category Filter
Plugin URI:    https://github.com/jespinal/PE-Category-Filter
Description:   This plugin filters the Categories that will show up in the front page of your website.<br/> This plugin attempts to be solid (using WP native methods) way to filter categories on WordPress.
Version:       2.0.0
Author:        J. Pavel Espinal
Author URI:    https://pavelespinal.com/about-me/
License:       GPL2

Copyright 2020  J. Pavel Espinal  (email : jose@pavelespinal.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
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
