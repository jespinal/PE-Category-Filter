<?php

namespace PavelEspinal\WpPlugins\PECategoryFilter\Interfaces;

/**
 * Settings Repository Interface
 *
 * @package PE Category Filter
 * @since 2.0.0
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
     * @param array<int> $categories Array of category IDs to exclude
     * @return bool True on success, false on failure
     */
    public function setExcludedCategories( array $categories ): bool;

    /**
     * Get default settings
     *
     * @return array<string, mixed> Default settings array
     */
    public function getDefaultSettings(): array;

    /**
     * Get all settings
     *
     * @return array<string, mixed> All settings
     */
    public function getAllSettings(): array;

    /**
     * Update a specific setting
     *
     * @param string $key Setting key
     * @param mixed  $value Setting value
     * @return bool True on success, false on failure
     */
    public function updateSetting( string $key, mixed $value ): bool;

    /**
     * Get a specific setting
     *
     * @param string $key Setting key
     * @param mixed  $default Default value if setting doesn't exist
     * @return mixed Setting value or default
     */
    public function getSetting( string $key, mixed $default = null ): mixed;
}
