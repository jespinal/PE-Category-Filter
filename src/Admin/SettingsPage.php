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

        // Add nonce field for CSRF protection
        add_action( 'admin_init', array( $this, 'addNonceField' ) );
    }

    /**
     * Add nonce field for CSRF protection
     *
     * @return void
     */
    public function addNonceField(): void {
        add_settings_field(
            'pecf_nonce',
            '',
            array( $this, 'renderNonceField' ),
            self::PAGE_SLUG,
            'pecf_main_section'
        );
    }

    /**
     * Render nonce field
     *
     * @return void
     */
    public function renderNonceField(): void {
        wp_nonce_field( 'pecf_save_settings', 'pecf_nonce' );
    }

    /**
     * Sanitize categories input
     *
     * @param mixed $value Input value
     * @return array<int> Sanitized array of category IDs
     */
    public function sanitizeCategories( $value ): array {
        // Verify nonce for security
        if ( ! isset( $_POST['pecf_nonce'] ) || ! wp_verify_nonce( $_POST['pecf_nonce'], 'pecf_save_settings' ) ) {
            wp_die( esc_html__( 'Security check failed. Please try again.', 'pe-category-filter' ) );
        }

        if ( ! is_array( $value ) ) {
            return array();
        }

        // Limit input size to prevent abuse
        if ( count( $value ) > 100 ) {
            $value = array_slice( $value, 0, 100 );
        }

        // Sanitize and validate category IDs
        $sanitized = array_map( 'absint', $value );
        
        // Additional security: validate category IDs are reasonable
        $sanitized = array_filter( $sanitized, function( $id ) {
            return $id > 0 && $id < 999999; // Reasonable limits
        });

        // Remove duplicates and re-index
        return array_values( array_unique( $sanitized ) );
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
