<?php
/**
 * Settings Repository
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

namespace PavelEspinal\WpPlugins\PECategoryFilter\Repositories;

use PavelEspinal\WpPlugins\PECategoryFilter\Interfaces\SettingsRepositoryInterface;

/**
 * Settings Repository
 *
 * Handles data access for plugin settings with intelligent caching.
 */
class SettingsRepository implements SettingsRepositoryInterface {
	/**
	 * Excluded categories option name
	 */
	private const EXCLUDED_CATEGORIES_OPTION = 'pecf_excluded_categories';


	/**
	 * Cache group for WordPress object cache
	 */
	private const CACHE_GROUP = 'pecf';

	/**
	 * Cache expiration time (1 hour)
	 */
	private const CACHE_EXPIRATION = HOUR_IN_SECONDS;

	/**
	 * Get excluded categories
	 *
	 * @return array<int> Array of category IDs to exclude
	 */
	public function getExcludedCategories(): array {
		$cache_key  = 'pecf_excluded_categories';
		$categories = wp_cache_get( $cache_key, self::CACHE_GROUP );

		if ( false === $categories ) {
			$categories = get_option( self::EXCLUDED_CATEGORIES_OPTION, array() );

			if ( ! is_array( $categories ) ) {
				$categories = array();
			} else {
				$categories = array_map( 'absint', $categories );
			}

			wp_cache_set( $cache_key, $categories, self::CACHE_GROUP, self::CACHE_EXPIRATION );
		}

		return $categories;
	}

	/**
	 * Set excluded categories
	 *
	 * @param array<int> $categories Array of category IDs to exclude.
	 * @return bool True on success, false on failure
	 */
	public function setExcludedCategories( array $categories ): bool {
		// Sanitize and validate categories.
		$sanitized = array_map( 'absint', $categories );
		$sanitized = array_values( array_unique( array_filter( $sanitized, fn( $id ) => $id > 0 ) ) );

		$result = update_option( self::EXCLUDED_CATEGORIES_OPTION, $sanitized );

		// Some WP implementations return false from update_option when the
		// value didn't change (or for other benign reasons). To make this
		// method resilient in both the lightweight test-shim environment and
		// the full WP test suite, consider the operation successful if the
		// option now contains the expected sanitized value.
		if ( false === $result ) {
			$stored = get_option( self::EXCLUDED_CATEGORIES_OPTION, null );
			if ( is_array( $stored ) ) {
				$stored = array_map( 'absint', $stored );
				$stored = array_values( array_unique( array_filter( $stored, fn( $id ) => $id > 0 ) ) );
				if ( $stored === $sanitized ) {
					$result = true;
				}
			}
		}

		if ( $result ) {
			// Clear cache.
			wp_cache_delete( 'pecf_excluded_categories', self::CACHE_GROUP );
		}

		return (bool) $result;
	}

	/**
	 * Get all plugin settings
	 *
	 * @return array<string, mixed> All plugin settings
	 */
	public function getAllSettings(): array {
		$cache_key = 'pecf_all_settings';
		$settings  = wp_cache_get( $cache_key, self::CACHE_GROUP );

		if ( false === $settings ) {
			// If a stored settings array exists in the options table, prefer
			// that. This mirrors the behavior tests expect when the
			// `pecf_settings` option is set.
			$stored = get_option( 'pecf_settings', false );

			if ( false !== $stored && is_array( $stored ) ) {
				$settings = $stored;
			} else {
				// No stored settings array — build a settings array that includes
				// the default metadata as well as current excluded categories.
				$defaults                        = $this->getDefaultSettings();
				$defaults['excluded_categories'] = $this->getExcludedCategories();
				$settings                        = $defaults;
			}

			wp_cache_set( $cache_key, $settings, self::CACHE_GROUP, self::CACHE_EXPIRATION );
		}

		return $settings;
	}

	/**
	 * Get default settings for the plugin.
	 *
	 * @return array<string, mixed>
	 */
	public function getDefaultSettings(): array {
		return array(
			'excluded_categories' => array(),
			'version'             => '2.0.0',
			'last_updated'        => gmdate( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Get a single setting value with an optional fallback.
	 *
	 * @param string $key      Setting key to retrieve.
	 * @param mixed  $fallback Fallback value to return if key is not set.
	 * @return mixed
	 */
	public function getSetting( string $key, mixed $fallback = null ): mixed {
		$settings = get_option( 'pecf_settings', false );
		if ( false === $settings || ! is_array( $settings ) ) {
			$defaults = $this->getDefaultSettings();
			return $defaults[ $key ] ?? $fallback;
		}

		return $settings[ $key ] ?? $fallback;
	}

	/**
	 * Update a specific setting.
	 *
	 * @param string $key   Setting key.
	 * @param mixed  $value Setting value.
	 * @return bool True on success, false on failure.
	 */
	public function updateSetting( string $key, mixed $value ): bool {
		// Load existing settings array (or defaults) and update key.
		$settings = get_option( 'pecf_settings', false );
		if ( false === $settings || ! is_array( $settings ) ) {
			$settings = $this->getDefaultSettings();
		}

		// Special handling for excluded_categories to sanitize.
		if ( 'excluded_categories' === $key ) {
			if ( ! is_array( $value ) ) {
				return false;
			}
			$value = array_map( 'absint', $value );
			$value = array_values( array_unique( array_filter( $value, fn( $id ) => $id > 0 ) ) );
		}

		$settings[ $key ] = $value;

		$result = update_option( 'pecf_settings', $settings );

		if ( $result ) {
			// Clear caches so subsequent reads pick up the new values.
			wp_cache_delete( 'pecf_all_settings', self::CACHE_GROUP );
			wp_cache_delete( 'pecf_excluded_categories', self::CACHE_GROUP );
		}

		return (bool) $result;
	}
}
