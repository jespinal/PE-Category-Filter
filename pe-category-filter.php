<?php
/**
 * @package PECF (PavelEspinal Category Filter)
 * @author Pavel Espinal
 * @version 1.4.0
 */

/*
 Plugin Name:   PE Category Filter
 Plugin URI:    https://github.com/jespinal/PE-Category-Filter
 Description:   This plugin filters the Categories that will show up in the front page of your website.<br/> This plugin attempts to be solid (using WP native methods) way to filter categories on Wordpress.
 Version:       1.4.0
 Author:        J. Pavel Espinal
 Author URI:    https://www.patreon.com/pavel_espinal
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

// No direct calls allowed
if (! function_exists('add_action')) {
    echo 'Hello from the other side :)';
    exit;
}

require_once(plugin_dir_path(__FILE__) . 'class.pe-category-filter.php');

use PavelEspinal\WP\Plugins\PECategoryFilter;

/* Creating an instance of the category filter */
$categoryFilter = new PECategoryFilter();

/* Filtering out posts of disallowed categories */
add_action('pre_get_posts', [$categoryFilter, 'filterCategories']);

/* Adding the new entry to the menu */
add_action('admin_menu', [$categoryFilter, 'generateSidebarMenuEntryAndPage']);
