<?php
/**
 * Category Filter Service
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

namespace PavelEspinal\WpPlugins\PECategoryFilter\Filters;

use PavelEspinal\WpPlugins\PECategoryFilter\Interfaces\SettingsRepositoryInterface;

/**
 * Category Filter Service
 *
 * Handles the core business logic for filtering categories from the home page.
 */
class CategoryFilter {
    /**
     * Settings repository
     *
     * @var SettingsRepositoryInterface
     */
    private SettingsRepositoryInterface $settingsRepository;

    /**
     * Constructor
     *
     * @param SettingsRepositoryInterface $settingsRepository Settings repository
     */
    public function __construct(SettingsRepositoryInterface $settingsRepository) {
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * Filter categories from the query
     *
     * @param \WP_Query $query The WordPress query object
     * @return void
     */
    public function filterCategories(\WP_Query $query): void {
        if (!$this->shouldFilter($query)) {
            return;
        }

        $excludedCategories = $this->settingsRepository->getExcludedCategories();
        
        if (!empty($excludedCategories)) {
            $query->set('category__not_in', $excludedCategories);
        }
    }

    /**
     * Check if the query should be filtered
     *
     * @param \WP_Query $query The WordPress query object
     * @return bool True if the query should be filtered
     */
    private function shouldFilter(\WP_Query $query): bool {
        // Only filter main queries on the home page
        if (!$query->is_main_query()) {
            return false;
        }

        // Don't filter admin queries
        if ($query->is_admin()) {
            return false;
        }

        // Only filter home page queries
        if (!$query->is_home()) {
            return false;
        }

        return true;
    }
}