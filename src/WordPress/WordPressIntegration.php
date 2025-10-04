<?php

namespace PavelEspinal\WpPlugins\PECategoryFilter\WordPress;

use Exception;
use PavelEspinal\WpPlugins\PECategoryFilter\Admin\SettingsPage;
use PavelEspinal\WpPlugins\PECategoryFilter\Core\Container;
use PavelEspinal\WpPlugins\PECategoryFilter\Filters\CategoryFilter;

/**
 * WordPress Integration Service
 *
 * Handles all WordPress-specific integrations including hooks, filters,
 * and WordPress API interactions.
 *
 * @package PE Category Filter
 * @since 2.0.0
 */
class WordPressIntegration
{
    /**
     * Service container
     */
    private Container $container;

    /**
     * Constructor
     *
     * @param Container $container Service container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Initialize WordPress integration
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->registerHooks();
        $this->registerFilters();
        $this->registerAdminHooks();
        
        // Register admin menu directly if in admin area
        if (is_admin()) {
            $this->registerAdminMenu();
        }
    }

    /**
     * Register WordPress hooks
     *
     * @return void
     */
    private function registerHooks(): void
    {
        // Plugin activation/deactivation hooks
        register_activation_hook(PE_CATEGORY_FILTER_PLUGIN_FILE, [$this, 'onActivation']);
        register_deactivation_hook(PE_CATEGORY_FILTER_PLUGIN_FILE, [$this, 'onDeactivation']);

        // WordPress init hook
        add_action('init', [$this, 'onInit']);
        
        // WordPress admin init hook
        add_action('admin_init', [$this, 'onAdminInit']);
    }

    /**
     * Register WordPress filters
     *
     * @return void
     */
    private function registerFilters(): void
    {
        // Category filtering on main query
        add_action('pre_get_posts', [$this, 'filterMainQuery']);
        
        // Add plugin action links
        add_filter('plugin_action_links_' . plugin_basename(PE_CATEGORY_FILTER_PLUGIN_FILE), [$this, 'addActionLinks']);
        
        // Add plugin row meta
        add_filter('plugin_row_meta', [$this, 'addPluginRowMeta'], 10, 2);
    }

    /**
     * Register admin-specific hooks
     *
     * @return void
     */
    private function registerAdminHooks(): void
    {
        // Admin menu and settings
        add_action('admin_menu', [$this, 'registerAdminMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
    }

    /**
     * Plugin activation handler
     *
     * @return void
     */
    public function onActivation(): void
    {
        // Set default options
        $this->setDefaultOptions();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set activation flag
        set_transient('pecf_activated', true, 30);
    }

    /**
     * Plugin deactivation handler
     *
     * @return void
     */
    public function onDeactivation(): void
    {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Clear any transients
        delete_transient('pecf_activated');
    }

    /**
     * WordPress init handler
     *
     * @return void
     */
    public function onInit(): void
    {
        // Load text domain for internationalization
        $this->loadTextDomain();
    }

    /**
     * WordPress admin init handler
     *
     * @return void
     */
    public function onAdminInit(): void
    {
        // Register settings
        $this->registerSettings();
    }

    /**
     * Filter main WordPress query
     *
     * @param \WP_Query $query WordPress query object
     * @return void
     */
    public function filterMainQuery(\WP_Query $query): void
    {
        // Only filter main query on home page
        if (!$query->is_main_query() || !$query->is_home()) {
            return;
        }

        // Get category filter service
        $categoryFilter = $this->container->make(CategoryFilter::class);
        $categoryFilter->filterCategories($query);
    }

    /**
     * Register admin menu
     *
     * @return void
     */
    public function registerAdminMenu(): void
    {
        $settingsPage = $this->container->make(SettingsPage::class);
        $settingsPage->register();
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueueAdminAssets(string $hook): void
    {
        // Only enqueue on our settings page
        if ('settings_page_pecf-settings' !== $hook) {
            return;
        }

        // Use minified assets in production
        $isDebug = defined('WP_DEBUG') && WP_DEBUG;
        $cssFile = $isDebug ? 'admin.css' : 'admin.min.css';
        $jsFile = $isDebug ? 'admin.js' : 'admin.min.js';

        // Enqueue admin styles
        wp_enqueue_style(
            'pecf-admin',
            PE_CATEGORY_FILTER_PLUGIN_URL . 'assets/css/' . $cssFile,
            [],
            PE_CATEGORY_FILTER_VERSION
        );

        // Enqueue admin scripts
        wp_enqueue_script(
            'pecf-admin',
            PE_CATEGORY_FILTER_PLUGIN_URL . 'assets/js/' . $jsFile,
            ['jquery'],
            PE_CATEGORY_FILTER_VERSION,
            true
        );
    }

    /**
     * Add plugin action links
     *
     * @param array $links Existing action links
     * @return array Modified action links
     */
    public function addActionLinks(array $links): array
    {
        $settingsLink = sprintf(
            '<a href="%s">%s</a>',
            admin_url('options-general.php?page=pecf-settings'),
            __('Settings', 'pe-category-filter')
        );

        array_unshift($links, $settingsLink);
        return $links;
    }

    /**
     * Add plugin row meta
     *
     * @param array $links Existing row meta links
     * @param string $file Plugin file
     * @return array Modified row meta links
     */
    public function addPluginRowMeta(array $links, string $file): array
    {
        if (plugin_basename(PE_CATEGORY_FILTER_PLUGIN_FILE) !== $file) {
            return $links;
        }

        $links[] = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            'https://github.com/jespinal/PE-Category-Filter',
            __('GitHub', 'pe-category-filter')
        );

        $links[] = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            'https://pavelespinal.com/about-me/',
            __('Author', 'pe-category-filter')
        );

        return $links;
    }

    /**
     * Load text domain for internationalization
     *
     * @return void
     */
    private function loadTextDomain(): void
    {
        load_plugin_textdomain(
            'pe-category-filter',
            false,
            dirname(plugin_basename(PE_CATEGORY_FILTER_PLUGIN_FILE)) . '/languages'
        );
    }

    /**
     * Register WordPress settings
     *
     * @return void
     */
    private function registerSettings(): void
    {
        register_setting(
            'pecf_settings',
            'pecf_excluded_categories',
            [
                'sanitize_callback' => [$this, 'sanitizeExcludedCategories'],
                'default' => [],
            ]
        );
    }

    /**
     * Sanitize excluded categories
     *
     * @param mixed $value Input value
     * @return array Sanitized array of category IDs
     */
    public function sanitizeExcludedCategories($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        // Limit input size to prevent abuse
        if (count($value) > 100) {
            $value = array_slice($value, 0, 100);
        }

        // Sanitize and validate category IDs
        $sanitized = array_map('absint', $value);
        
        // Additional security: validate category IDs exist and are reasonable
        $sanitized = array_filter($sanitized, function($id) {
            return $id > 0 && $id < 999999; // Reasonable limits
        });

        // Remove duplicates and re-index
        return array_values(array_unique($sanitized));
    }

    /**
     * Set default plugin options
     *
     * @return void
     */
    private function setDefaultOptions(): void
    {
        $defaults = [
            'pecf_excluded_categories' => [],
            'pecf_version' => PE_CATEGORY_FILTER_VERSION,
            'pecf_activated' => current_time('mysql'),
        ];

        foreach ($defaults as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
}
