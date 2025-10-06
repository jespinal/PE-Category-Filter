<?php

namespace PavelEspinal\WpPlugins\PECategoryFilter\Tests\Unit\Filters;

use PavelEspinal\WpPlugins\PECategoryFilter\Filters\CategoryFilter;
use PavelEspinal\WpPlugins\PECategoryFilter\Interfaces\SettingsRepositoryInterface;
use WP_Query;

/**
 * Testable version of CategoryFilter that allows mocking of WordPress functions
 */
class TestableCategoryFilter extends CategoryFilter
{
    private bool $mockIsAdmin = false;

    /**
     * Set the mock admin state
     *
     * @param bool $isAdmin Whether to mock as admin
     */
    public function setMockIsAdmin(bool $isAdmin): void
    {
        $this->mockIsAdmin = $isAdmin;
    }

    /**
     * Override shouldFilter to use mock admin state
     *
     * @param WP_Query $query WordPress query object
     * @return bool True if query should be filtered
     */
    protected function shouldFilter(WP_Query $query): bool
    {
        // Only filter main query on home page and not in admin
        return $query->is_main_query() && $query->is_home() && ! $this->mockIsAdmin;
    }
}
