<?php

namespace PavelEspinal\WpPlugins\PECategoryFilter\Tests\Unit\Filters;

use PavelEspinal\WpPlugins\PECategoryFilter\Filters\CategoryFilter;
use PavelEspinal\WpPlugins\PECategoryFilter\Interfaces\SettingsRepositoryInterface;
use PavelEspinal\WpPlugins\PECategoryFilter\Tests\Unit\Filters\TestableCategoryFilter;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Category Filter Test
 *
 * @package PE Category Filter
 * @since 2.0.0
 */
class CategoryFilterTest extends TestCase
{
    /**
     * Settings repository mock
     */
    private MockObject|SettingsRepositoryInterface $settingsRepository;

    /**
     * Category filter instance
     */
    private CategoryFilter $categoryFilter;

    /**
     * Set up test
     */
    protected function setUp(): void
    {
        $this->settingsRepository = $this->createMock(SettingsRepositoryInterface::class);
        $this->categoryFilter = new TestableCategoryFilter($this->settingsRepository);
    }

    /**
     * Test filter categories with excluded categories
     */
    public function testFilterCategoriesWithExcludedCategories(): void
    {
        $excludedCategories = [1, 2, 3];
        
        // Mock settings repository
        $this->settingsRepository
            ->expects($this->once())
            ->method('getExcludedCategories')
            ->willReturn($excludedCategories);

        // Set mock admin state
        $this->categoryFilter->setMockIsAdmin(false);

        // Create mock WP_Query (main query, home page)
        $query = $this->createMockWPQuery(true, true);
        
        // Expect set method to be called with category__not_in
        $query->expects($this->once())
            ->method('set')
            ->with('category__not_in', $excludedCategories);

        $this->categoryFilter->filterCategories($query);
    }

    /**
     * Test filter categories with no excluded categories
     */
    public function testFilterCategoriesWithNoExcludedCategories(): void
    {
        // Mock settings repository to return empty array
        $this->settingsRepository
            ->expects($this->once())
            ->method('getExcludedCategories')
            ->willReturn([]);

        // Set mock admin state
        $this->categoryFilter->setMockIsAdmin(false);

        // Create mock WP_Query (main query, home page)
        $query = $this->createMockWPQuery(true, true);
        
        // Expect set method NOT to be called
        $query->expects($this->never())
            ->method('set');

        $this->categoryFilter->filterCategories($query);
    }

    /**
     * Test filter categories with non-home query
     */
    public function testFilterCategoriesWithNonHomeQuery(): void
    {
        // Set mock admin state
        $this->categoryFilter->setMockIsAdmin(false);

        // Create mock WP_Query (main query, not home page)
        $query = $this->createMockWPQuery(true, false);
        
        // Expect getExcludedCategories NOT to be called
        $this->settingsRepository
            ->expects($this->never())
            ->method('getExcludedCategories');

        $this->categoryFilter->filterCategories($query);
    }

    /**
     * Test filter categories with admin query
     */
    public function testFilterCategoriesWithAdminQuery(): void
    {
        // Set mock admin state
        $this->categoryFilter->setMockIsAdmin(true);

        // Create mock WP_Query (main query, home page, but admin)
        $query = $this->createMockWPQuery(true, true);
        
        // Expect getExcludedCategories NOT to be called
        $this->settingsRepository
            ->expects($this->never())
            ->method('getExcludedCategories');

        $this->categoryFilter->filterCategories($query);
    }

    /**
     * Test get excluded categories
     */
    public function testGetExcludedCategories(): void
    {
        $excludedCategories = [1, 2, 3];
        
        // Mock settings repository
        $this->settingsRepository
            ->expects($this->once())
            ->method('getExcludedCategories')
            ->willReturn($excludedCategories);

        $result = $this->categoryFilter->getExcludedCategories();
        
        $this->assertEquals($excludedCategories, $result);
    }

    /**
     * Test is category excluded
     */
    public function testIsCategoryExcluded(): void
    {
        $excludedCategories = [1, 2, 3];
        
        // Mock settings repository
        $this->settingsRepository
            ->expects($this->exactly(2))
            ->method('getExcludedCategories')
            ->willReturn($excludedCategories);

        // Test excluded category
        $this->assertTrue($this->categoryFilter->isCategoryExcluded(1));
        
        // Test non-excluded category
        $this->assertFalse($this->categoryFilter->isCategoryExcluded(4));
    }

    /**
     * Test is category excluded with empty excluded categories
     */
    public function testIsCategoryExcludedWithEmptyExcluded(): void
    {
        // Mock settings repository to return empty array
        $this->settingsRepository
            ->expects($this->once())
            ->method('getExcludedCategories')
            ->willReturn([]);

        $this->assertFalse($this->categoryFilter->isCategoryExcluded(1));
    }

    /**
     * Create mock WP_Query
     *
     * @param bool $isHome Whether query is home
     * @param bool $isAdmin Whether query is admin
     * @return MockObject Mock WP_Query
     */
    private function createMockWPQuery(bool $isMainQuery, bool $isHome): MockObject
    {
        $query = $this->createMock(\WP_Query::class);
        
        $query->method('is_main_query')
            ->willReturn($isMainQuery);
            
        $query->method('is_home')
            ->willReturn($isHome);
            
        return $query;
    }
}
