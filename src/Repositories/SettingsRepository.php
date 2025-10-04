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
        // Try to get from cache first
        $cache_key = 'pecf_excluded_categories';
        $categories = wp_cache_get( $cache_key, 'pecf' );

        if ( false === $categories ) {
            // Record cache miss
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'PECF Cache Miss: excluded_categories' );
            }

            $categories = get_option( self::EXCLUDED_CATEGORIES_OPTION, array() );

            if ( ! is_array( $categories ) ) {
                $categories = array();
            } else {
                // Ensure all values are integers
                $categories = array_map( 'absint', $categories );
            }

            // Cache for 1 hour
            wp_cache_set( $cache_key, $categories, 'pecf', HOUR_IN_SECONDS );
        } else {
            // Record cache hit
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'PECF Cache Hit: excluded_categories' );
            }
        }

        return $categories;
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
        $sanitized = array_values( $sanitized ); // Re-index array

        $result = update_option( self::EXCLUDED_CATEGORIES_OPTION, $sanitized );

        if ( $result ) {
            // Invalidate cache when data changes
            wp_cache_delete( 'pecf_excluded_categories', 'pecf' );
        }

        return $result;
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
        // Try to get from cache first
        $cache_key = 'pecf_all_settings';
        $settings = wp_cache_get( $cache_key, 'pecf' );

        if ( false === $settings ) {
            $defaults = $this->getDefaultSettings();
            $raw_settings = get_option( self::SETTINGS_OPTION, array() );

            if ( ! is_array( $raw_settings ) ) {
                $settings = $defaults;
            } else {
                $settings = wp_parse_args( $raw_settings, $defaults );
            }

            // Cache for 1 hour
            wp_cache_set( $cache_key, $settings, 'pecf', HOUR_IN_SECONDS );
        }

        return $settings;
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

        $result = update_option( self::SETTINGS_OPTION, $settings );

        if ( $result ) {
            // Invalidate cache when data changes
            wp_cache_delete( 'pecf_all_settings', 'pecf' );
        }

        return $result;
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
