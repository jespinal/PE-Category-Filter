<?php
/**
 * @package PECF (PavelEspinal Category Filter)
 * @author Pavel Espinal
 * @version 1.2
 */
/*
 Plugin Name:   PE Category Filter
 Plugin URI:    http://pavelespinal.com/resume/downloads/
 Description:   This plugin filters the Categories that will show up in the front page of your website.<br/> This plugin attempts to be a well written (using WP native methods) way to filter categories on Wordpress.
 Version:       1.2
 Author:        J. Pavel Espinal
 Author URI:    http://pavelespinal.com/
 License:       GPL2

    Copyright 2017  J. Pavel Espinal  (email : jose@pavelespinal.com)

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

/* [1] Start */
/* Taking out posts of disallowed categories */
add_action( 'pre_get_posts' , 'pecf_categ_excluder' );

/**
 * PE Categories Excluder
 * 
 * This method receives the $wp_query object as a param and applies the filter
 * depending on the section of the blog that we are.
 *
 * @param object $input 
 */
function pecf_categ_excluder($input) {
    if( is_home() ) {
        $categories = get_option('pecf_cat-list');
        
        if($categories != false) {
            $input->set('category__not_in', $categories);
        }
    }
}
/* [1] End */


/* [2] Start */
/* Adding the new entry to the menu */
add_action('admin_menu','pecf_categ_menu');

/**
 * PECF Category Menu
 */
function pecf_categ_menu() {
    add_options_page('PE Category Filter','PECF Plugin','manage_options','pecf_categ-menu','pecf_cb_menu_options');
}

/**
 * PECF Menu Options
 */
function pecf_cb_menu_options() {
    if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

    include_once 'pecf_menu_form.php';
}

/* Initializating the form body */
add_action('admin_init','pecf_admin_init');

/**
 * Form body settings
 */
function pecf_admin_init() {
    add_settings_section('pecf_general','PE Category Filter', 'pecf_cb_section_html', 'pecf_categ-menu');
    add_settings_field('pecf_cat-list', 'Categories', 'pecf_cb_catlist_html', 'pecf_categ-menu', 'pecf_general');
    register_setting('pecf_option-group', 'pecf_cat-list', 'pecf_cb_cat_sanitize');
}
/* [2] End */


function pecf_cb_catlist_html() {
    
    $options = get_option('pecf_cat-list');
    
    $args = array( 'taxonomy' => 'category', 'hide_empty' => 0);
    $cat_list = get_categories($args);
    
    if(is_array($options)) {
        foreach($cat_list as $c):
            ?>
            <input type="checkbox" <?=(in_array($c->cat_ID, $options))? 'checked="checked"': '' ?> name="pecf_cat-list[]" value="<?=$c->cat_ID?>"/> <?=$c->name?><br/>
           <?php
        endforeach;
    } else {
        foreach($cat_list as $c):
            ?>
            <input type="checkbox" name="pecf_cat-list[]" value="<?=$c->cat_ID?>"/> <?=$c->name?><br/>
           <?php
        endforeach;
    }
}

function pecf_cb_section_html() {
    echo '<p>Select the categories that you want to <strong>exclude</strong> from the index.</p>';
}

/**
 * In a future, further filtering might be placed here.
 */
function pecf_cb_cat_sanitize($param) {
    return $param;
}