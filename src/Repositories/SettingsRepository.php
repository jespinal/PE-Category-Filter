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

		if ( $result ) {
			// Clear cache.
			wp_cache_delete( 'pecf_excluded_categories', self::CACHE_GROUP );
		}

		return $result;
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
			$settings = array(
				'excluded_categories' => $this->getExcludedCategories(),
			);

			wp_cache_set( $cache_key, $settings, self::CACHE_GROUP, self::CACHE_EXPIRATION );
		}

		return $settings;
	}

	/**
	 * Update a specific setting
	 *
	 * @param string $key Setting key.
	 * @param mixed  $value Setting value.
	 * @return bool True on success, false on failure
	 */
	public function updateSetting( string $key, mixed $value ): bool {
		switch ( $key ) {
			case 'excluded_categories':
				if ( ! is_array( $value ) ) {
					return false;
				}
				return $this->setExcludedCategories( $value );

			default:
				return false;
		}
	}
}
