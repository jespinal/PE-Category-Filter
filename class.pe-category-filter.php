<?php

namespace PavelEspinal\WP\Plugins;

/**
 * PE Category Filter
 */
class PECategoryFilter
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'initializeAdminSettings']);
    }

    /**
     * Initialize admin section and settings
     */
    public function initializeAdminSettings()
    {
        add_settings_section(
            $id       = 'pecf_general',
            $title    = 'PE Category Filter',
            $callback = [$this, 'generateMainSectionHtml'],
            $page     = 'pecf-category-filter-page'
        );

        add_settings_field(
            $id       = 'pecf_cat-list',
            $title    = 'Categories',
            $callback = [$this, 'generateListOfCategoriesHtml'],
            $page     = 'pecf-category-filter-page',
            $section  = 'pecf_general'
        );

        register_setting(
            $option_group = 'pecf_option-group',
            $option_name  = 'pecf_cat-list',
            $args         = [
                'sanitize_callback' => [$this, 'sanitizeInput']
            ]
        );
    }

    /**
     * PE Categories Excluder
     *
     * This method receives the $wp_query object as an argument and applies the filter
     * depending on the section of the blog that we are.
     *
     * @param WP_Query $input
     */
    public function filterCategories($input)
    {
        if (is_home()) {
            $categories = get_option('pecf_cat-list');

            if ($categories != false) {
                $input->set('category__not_in', $categories);
            }
        }
    }

    /**
     * PECF Sidebar Menu Entry and Page
     */
    public function generateSidebarMenuEntryAndPage()
    {
        add_options_page(
            $page_title = 'PE Category Filter',
            $menu_title = 'PECF Plugin',
            $capability = 'manage_options',
            $menu_slug  = 'pecf-category-filter-page',
            $function   = function () {
                if (! current_user_can('manage_options')) {
                    wp_die(__('You do not have sufficient permissions to access this page.'));
                }

                include_once 'pecf_menu_form.php';
            }
        );
    }

    /**
     * PECF Main Section HTML callback
     *
     * This method outputs the HTML that's displayed on the main section of the plugin.
     * At the moment, this is the only section we have.
     *
     * @return void
     */
    public function generateMainSectionHtml()
    {
        echo '<p>Select the categories which you want to <strong>exclude</strong> from the home page.</p>';
    }

    /**
     * PECF List Of Categories HTML callback
     *
     * This function returns the HTML content of all categories, names, IDs, etc.
     * The form tag and other attributes are added by pecf_menuentry_formoptions_callback
     *
     * @return void
     */
    public function generateListOfCategoriesHtml()
    {
        $options = get_option('pecf_cat-list');

        $filters = [
            'taxonomy'   => 'category',
            'hide_empty' => 0
        ];

        $category_list = get_categories($filters);

        if (is_array($options)) {
            foreach ($category_list as $c) {
                $checked_str = (in_array($c->cat_ID, $options)) ? 'checked' : '';
                $catID       = $c->cat_ID;
                $input       = "<input type='checkbox' name='pecf_cat-list[]' value='$catID' id='pecf_cat_$catID' data-pecf-category-id='$catID' $checked_str/>";
                $input      .= "<label for='pecf_cat_$catID'>{$c->name}</label></br>";

                echo $input;
            }
        } else {
            foreach ($category_list as $c) {
                $catID  = $c->cat_ID;
                $input  = "<input type='checkbox' name='pecf_cat-list[]' value='$catID' id='pecf_cat_$catID' data-pecf-category-id='$catID'/>";
                $input .= "<label for='pecf_cat_$catID'>{$c->name}</label></br>";

                echo $input;
            }
        }
    }

    /**
     * In a future, further filtering might be placed here.
     */
    public function sanitizeInput($param)
    {
        return $param;
    }
}
