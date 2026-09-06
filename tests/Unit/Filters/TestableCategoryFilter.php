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
     * Map of parent term ID => descendant term IDs, for tests.
     *
     * @var array<int, array<int>>
     */
    private array $mockDescendants = [];

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
     * Set the mock descendants map (parent ID => child IDs).
     *
     * @param array<int, array<int>> $map Descendants keyed by parent term ID.
     */
    public function setMockDescendants(array $map): void
    {
        $this->mockDescendants = $map;
    }

    /**
     * Override getCategoryDescendants() so tests can control hierarchy without
     * defining global WordPress functions.
     *
     * @param int $termId Category term ID.
     * @return array<int> Descendant term IDs.
     */
    protected function getCategoryDescendants(int $termId): array
    {
        return $this->mockDescendants[$termId] ?? [];
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
