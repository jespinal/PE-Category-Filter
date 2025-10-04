<?php

namespace PavelEspinal\WpPlugins\PECategoryFilter\Admin;

use PavelEspinal\WpPlugins\PECategoryFilter\Interfaces\SettingsRepositoryInterface;

/**
 * Admin Settings Page
 *
 * @package PE Category Filter
 * @since 2.0.0
 */
class SettingsPage {

    /**
     * Settings repository instance
     */
    private SettingsRepositoryInterface $settingsRepository;

    /**
     * Page slug
     */
    private const PAGE_SLUG = 'pecf-settings';

    /**
     * Constructor
     *
     * @param SettingsRepositoryInterface $settingsRepository Settings repository
     */
    public function __construct( SettingsRepositoryInterface $settingsRepository ) {
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * Register admin menu and settings
     *
     * @return void
     */
    public function register(): void {
        add_action( 'admin_menu', array( $this, 'addAdminMenu' ) );
        add_action( 'admin_init', array( $this, 'registerSettings' ) );
    }

    /**
     * Add admin menu
     *
     * @return void
     */
    public function addAdminMenu(): void {
        add_options_page(
            __( 'PE Category Filter Settings', 'pe-category-filter' ),
            __( 'PECF Plugin', 'pe-category-filter' ),
            'manage_options',
            self::PAGE_SLUG,
            array( $this, 'renderSettingsPage' )
        );
    }

    /**
     * Register WordPress settings
     *
     * @return void
     */
    public function registerSettings(): void {
        register_setting(
            'pecf_settings',
            'pecf_excluded_categories',
            array(
                'sanitize_callback' => array( $this, 'sanitizeCategories' ),
                'default'           => array(),
            )
        );
    }

    /**
     * Sanitize categories input
     *
     * @param mixed $value Input value
     * @return array<int> Sanitized array of category IDs
     */
    public function sanitizeCategories( $value ): array {
        if ( ! is_array( $value ) ) {
            return array();
        }

        // Sanitize and validate category IDs
        $sanitized = array_map( 'absint', $value );
        return array_filter( $sanitized, fn( $id ) => $id > 0 );
    }

    /**
     * Render settings page
     *
     * @return void
     */
    public function renderSettingsPage(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'pe-category-filter' ) );
        }

        $categories         = get_categories( array( 'hide_empty' => false ) );
        $excludedCategories = $this->settingsRepository->getExcludedCategories();

        include PE_CATEGORY_FILTER_PLUGIN_DIR . 'src/Admin/views/settings-page.php';
    }

    /**
     * Get page URL
     *
     * @return string Admin page URL
     */
    public function getPageUrl(): string {
        return admin_url( 'options-general.php?page=' . self::PAGE_SLUG );
    }
}
