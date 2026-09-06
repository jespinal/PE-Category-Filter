<?php

namespace PavelEspinal\WpPlugins\PECategoryFilter\Tests\Unit\Filters;

use PavelEspinal\WpPlugins\PECategoryFilter\Filters\CategoryFilter;

/**
 * Testable version of CategoryFilter that allows mocking of WordPress functions.
 *
 * Only the admin check is overridden; the real shouldFilter() logic (including
 * the filter scope) is exercised by the tests.
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
     * Override isAdmin() so tests can control admin state without defining
     * global WordPress functions.
     *
     * @return bool
     */
    protected function isAdmin(): bool
    {
        return $this->mockIsAdmin;
    }
}
