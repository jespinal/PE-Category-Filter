<?php

namespace PavelEspinal\WpPlugins\PECategoryFilter\Repositories;

use PavelEspinal\WpPlugins\PECategoryFilter\Interfaces\SettingsRepositoryInterface;

/**
 * Settings Repository Implementation
 *
 * @package PE Category Filter
 * @since 2.0.0
 */
class SettingsRepository implements SettingsRepositoryInterface {

    /**
     * WordPress option name for excluded categories
     */
    private const EXCLUDED_CATEGORIES_OPTION = 'pecf_excluded_categories';

    /**
     * WordPress option name for plugin settings
     */
    private const SETTINGS_OPTION = 'pecf_settings';

    /**
     * Get excluded categories
     *
     * @return array<int> Array of category IDs to exclude
     */
    public function getExcludedCategories(): array {
        $categories = get_option( self::EXCLUDED_CATEGORIES_OPTION, array() );

        if ( ! is_array( $categories ) ) {
            return array();
        }

        // Ensure all values are integers
        return array_map( 'absint', $categories );
    }

    /**
     * Set excluded categories
     *
     * @param array<int> $categories Array of category IDs to exclude
     * @return bool True on success, false on failure
     */
    public function setExcludedCategories( array $categories ): bool {
        // Sanitize input - ensure all values are positive integers
        $sanitized = array_map( 'absint', $categories );
        $sanitized = array_filter( $sanitized, fn( $id ) => $id > 0 );

        return update_option( self::EXCLUDED_CATEGORIES_OPTION, $sanitized );
    }

    /**
     * Get default settings
     *
     * @return array<string, mixed> Default settings array
     */
    public function getDefaultSettings(): array {
        return array(
            'excluded_categories' => array(),
            'version'             => '2.0.0',
            'last_updated'        => current_time( 'mysql' ),
        );
    }

    /**
     * Get all settings
     *
     * @return array<string, mixed> All settings
     */
    public function getAllSettings(): array {
        $defaults = $this->getDefaultSettings();
        $settings = get_option( self::SETTINGS_OPTION, array() );

        if ( ! is_array( $settings ) ) {
            return $defaults;
        }

        return wp_parse_args( $settings, $defaults );
    }

    /**
     * Update a specific setting
     *
     * @param string $key Setting key
     * @param mixed  $value Setting value
     * @return bool True on success, false on failure
     */
    public function updateSetting( string $key, mixed $value ): bool {
        $settings         = $this->getAllSettings();
        $settings[ $key ] = $value;

        return update_option( self::SETTINGS_OPTION, $settings );
    }

    /**
     * Get a specific setting
     *
     * @param string $key Setting key
     * @param mixed  $default Default value if setting doesn't exist
     * @return mixed Setting value or default
     */
    public function getSetting( string $key, mixed $default = null ): mixed {
        $settings = $this->getAllSettings();

        return $settings[ $key ] ?? $default;
    }
}
