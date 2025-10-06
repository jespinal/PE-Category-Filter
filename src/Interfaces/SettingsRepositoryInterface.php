<?php
/**
 * Settings Repository Interface
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

namespace PavelEspinal\WpPlugins\PECategoryFilter\Interfaces;

/**
 * Settings Repository Interface
 *
 * Defines the contract for settings data access.
 */
interface SettingsRepositoryInterface {
	/**
	 * Get excluded categories
	 *
	 * @return array<int> Array of category IDs to exclude
	 */
	public function getExcludedCategories(): array;

	/**
	 * Set excluded categories
	 *
	 * @param array<int> $categories Array of category IDs to exclude.
	 * @return bool True on success, false on failure
	 */
	public function setExcludedCategories( array $categories ): bool;

	/**
	 * Get all plugin settings
	 *
	 * @return array<string, mixed> All plugin settings
	 */
	public function getAllSettings(): array;

	/**
	 * Update a specific setting
	 *
	 * @param string $key Setting key.
	 * @param mixed  $value Setting value.
	 * @return bool True on success, false on failure
	 */
	public function updateSetting( string $key, mixed $value ): bool;
}
