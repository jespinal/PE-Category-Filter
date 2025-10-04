<?php

namespace PavelEspinal\WpPlugins\PECategoryFilter\Filters;

use PavelEspinal\WpPlugins\PECategoryFilter\Interfaces\SettingsRepositoryInterface;
use WP_Query;

/**
 * Category Filter for WordPress queries
 *
 * @package PE Category Filter
 * @since 2.0.0
 */
class CategoryFilter
{
    /**
     * Settings repository instance
     */
    private SettingsRepositoryInterface $settingsRepository;

    /**
     * Constructor
     *
     * @param SettingsRepositoryInterface $settingsRepository Settings repository
     */
    public function __construct(SettingsRepositoryInterface $settingsRepository)
    {
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * Filter categories from WordPress query
     *
     * @param WP_Query $query WordPress query object
     * @return void
     */
    public function filterCategories(WP_Query $query): void
    {
        if (!$this->shouldFilter($query)) {
            return;
        }

        $excludedCategories = $this->settingsRepository->getExcludedCategories();
        
        if (empty($excludedCategories)) {
            return;
        }

        $query->set('category__not_in', $excludedCategories);
    }

    /**
     * Check if the query should be filtered
     *
     * @param WP_Query $query WordPress query object
     * @return bool True if query should be filtered
     */
    private function shouldFilter(WP_Query $query): bool
    {
        // Only filter on home page and not in admin
        return $query->is_home() && !$query->is_admin();
    }

    /**
     * Get excluded categories for debugging
     *
     * @return array<int> Array of excluded category IDs
     */
    public function getExcludedCategories(): array
    {
        return $this->settingsRepository->getExcludedCategories();
    }

    /**
     * Check if a specific category is excluded
     *
     * @param int $categoryId Category ID to check
     * @return bool True if category is excluded
     */
    public function isCategoryExcluded(int $categoryId): bool
    {
        $excludedCategories = $this->settingsRepository->getExcludedCategories();
        return in_array($categoryId, $excludedCategories, true);
    }
}
