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

        $this->settingsRepository->method('getFilterScope')->willReturn('blog_index');
        
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
        $this->settingsRepository->method('getFilterScope')->willReturn('blog_index');

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
        $this->settingsRepository->method('getFilterScope')->willReturn('blog_index');

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
     * Test filter applies on a static front page when scope is 'front_page'
     */
    public function testFilterAppliesOnStaticFrontPageWhenScopeIsFrontPage(): void
    {
        $excluded = [5, 6];

        $this->settingsRepository->method('getFilterScope')->willReturn('front_page');
        $this->settingsRepository
            ->expects($this->once())
            ->method('getExcludedCategories')
            ->willReturn($excluded);

        $this->categoryFilter->setMockIsAdmin(false);

        // Static front page: main query, not is_home, but is_front_page.
        $query = $this->createScopedQuery(true, false, true);
        $query->expects($this->once())
            ->method('set')
            ->with('category__not_in', $excluded);

        $this->categoryFilter->filterCategories($query);
    }

    /**
     * Test filter skips a static front page when scope is 'blog_index'
     */
    public function testFilterSkipsStaticFrontPageWhenScopeIsBlogIndex(): void
    {
        $this->settingsRepository->method('getFilterScope')->willReturn('blog_index');
        $this->settingsRepository
            ->expects($this->never())
            ->method('getExcludedCategories');

        $this->categoryFilter->setMockIsAdmin(false);

        $query = $this->createScopedQuery(true, false, true);
        $query->expects($this->never())->method('set');

        $this->categoryFilter->filterCategories($query);
    }

    /**
     * Test filter applies to a secondary post query when scope is 'secondary'
     */
    public function testFilterAppliesToSecondaryPostQueryWhenScopeIsSecondary(): void
    {
        $excluded = [7];

        $this->settingsRepository->method('getFilterScope')->willReturn('secondary');
        $this->settingsRepository
            ->expects($this->once())
            ->method('getExcludedCategories')
            ->willReturn($excluded);

        $this->categoryFilter->setMockIsAdmin(false);

        // Secondary query (e.g. theme homepage section): not main, targets posts.
        $query = $this->createScopedQuery(false, false, false, 'post');
        $query->expects($this->once())
            ->method('set')
            ->with('category__not_in', $excluded);

        $this->categoryFilter->filterCategories($query);
    }

    /**
     * Test filter skips a secondary query when scope is 'blog_index'
     */
    public function testFilterSkipsSecondaryQueryWhenScopeIsBlogIndex(): void
    {
        $this->settingsRepository->method('getFilterScope')->willReturn('blog_index');
        $this->settingsRepository
            ->expects($this->never())
            ->method('getExcludedCategories');

        $this->categoryFilter->setMockIsAdmin(false);

        $query = $this->createScopedQuery(false, false, false, 'post');
        $query->expects($this->never())->method('set');

        $this->categoryFilter->filterCategories($query);
    }

    /**
     * Test filter skips a non-post secondary query even when scope is 'secondary'
     */
    public function testFilterSkipsNonPostSecondaryQueryWhenScopeIsSecondary(): void
    {
        $this->settingsRepository->method('getFilterScope')->willReturn('secondary');
        $this->settingsRepository
            ->expects($this->never())
            ->method('getExcludedCategories');

        $this->categoryFilter->setMockIsAdmin(false);

        // Secondary query for a non-post type (e.g. a page/CPT) must be skipped.
        $query = $this->createScopedQuery(false, false, false, 'page');
        $query->expects($this->never())->method('set');

        $this->categoryFilter->filterCategories($query);
    }

    /**
     * Create a fully-stubbed mock WP_Query for scope tests.
     *
     * @param bool   $isMainQuery  Whether the query is the main query.
     * @param bool   $isHome       Whether the query is the blog posts index.
     * @param bool   $isFrontPage  Whether the query is the front page.
     * @param string $postType     Post type the query targets.
     * @return MockObject Mock WP_Query
     */
    private function createScopedQuery(
        bool $isMainQuery,
        bool $isHome,
        bool $isFrontPage,
        string $postType = 'post'
    ): MockObject {
        $query = $this->createMock(\WP_Query::class);
        $query->method('is_main_query')->willReturn($isMainQuery);
        $query->method('is_home')->willReturn($isHome);
        $query->method('is_front_page')->willReturn($isFrontPage);
        $query->method('get')->with('post_type')->willReturn($postType);

        return $query;
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
