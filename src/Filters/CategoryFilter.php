<?php
/**
 * Category Filter Service
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

namespace PavelEspinal\WpPlugins\PECategoryFilter\Filters;

use PavelEspinal\WpPlugins\PECategoryFilter\Core\Constants;
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
	 * @param SettingsRepositoryInterface $settingsRepository Settings repository.
	 */
	public function __construct( SettingsRepositoryInterface $settingsRepository ) {
		$this->settingsRepository = $settingsRepository;
	}

	/**
	 * Filter categories from the query
	 *
	 * @param \WP_Query $query The WordPress query object.
	 * @return void
	 */
	public function filterCategories( \WP_Query $query ): void {
		if ( ! $this->shouldFilter( $query ) ) {
			return;
		}

		$excludedCategories = $this->settingsRepository->getExcludedCategories();

		if ( ! empty( $excludedCategories ) ) {
			$query->set( 'category__not_in', $this->expandWithChildren( $excludedCategories ) );
		}
	}

	/**
	 * Determine whether the given query should be filtered.
	 *
	 * Honors the configured filter scope:
	 * - 'blog_index' : the main query on the blog posts index (is_home()).
	 * - 'front_page' : the above plus the main query on a static front page.
	 * - 'secondary'  : any front-end query that targets posts (main or
	 *                  secondary), which also covers theme homepage sections.
	 *
	 * @param \WP_Query $query The WordPress query object.
	 * @return bool True if the query should be filtered
	 */
	protected function shouldFilter( \WP_Query $query ): bool {
		// Never filter admin queries.
		if ( $this->isAdmin() ) {
			return false;
		}

		$scope = $this->settingsRepository->getFilterScope();

		// 'secondary' also covers secondary front-end post queries, such as
		// theme homepage sections and recent-posts blocks.
		if ( Constants::SCOPE_SECONDARY === $scope ) {
			return $this->targetsPosts( $query );
		}

		// The remaining scopes only affect the main query.
		if ( ! $query->is_main_query() ) {
			return false;
		}

		// The blog posts index is in scope for every scope value.
		if ( $query->is_home() ) {
			return true;
		}

		// 'front_page' additionally covers a static front page.
		if ( Constants::SCOPE_FRONT_PAGE === $scope && $query->is_front_page() ) {
			return true;
		}

		return false;
	}

	/**
	 * Whether the current request is in the WordPress admin.
	 *
	 * Wrapped so tests can override it without defining global functions.
	 *
	 * @return bool
	 */
	protected function isAdmin(): bool {
		return is_admin();
	}

	/**
	 * Whether the query targets the 'post' post type.
	 *
	 * @param \WP_Query $query The WordPress query object.
	 * @return bool
	 */
	private function targetsPosts( \WP_Query $query ): bool {
		$postType = $query->get( 'post_type' );

		if ( empty( $postType ) ) {
			// An empty post_type defaults to 'post' for standard queries.
			return true;
		}

		if ( is_array( $postType ) ) {
			return in_array( 'post', $postType, true );
		}

		return 'post' === $postType;
	}

	/**
	 * Expand a list of category IDs to also include their descendant categories.
	 *
	 * WordPress' `category__not_in` does not consider hierarchy, so excluding a
	 * parent category would otherwise leave posts filed only under its children
	 * visible. This expands each excluded parent to include all descendants.
	 *
	 * @param array<int> $categoryIds Category IDs selected for exclusion.
	 * @return array<int> The IDs plus all their descendant category IDs.
	 */
	private function expandWithChildren( array $categoryIds ): array {
		$expanded = $categoryIds;

		foreach ( $categoryIds as $categoryId ) {
			foreach ( $this->getCategoryDescendants( (int) $categoryId ) as $childId ) {
				$expanded[] = (int) $childId;
			}
		}

		return array_values( array_unique( array_map( 'absint', $expanded ) ) );
	}

	/**
	 * Get the descendant category IDs for a given category.
	 *
	 * Wrapped so tests can override it without defining global functions.
	 *
	 * @param int $termId Category term ID.
	 * @return array<int> Descendant category term IDs.
	 */
	protected function getCategoryDescendants( int $termId ): array {
		$children = get_term_children( $termId, 'category' );

		return is_array( $children ) ? $children : array();
	}

	/**
	 * Convenience wrapper to expose excluded categories for tests.
	 *
	 * @return array<int>
	 */
	public function getExcludedCategories(): array {
		return $this->settingsRepository->getExcludedCategories();
	}

	/**
	 * Check whether a category id is excluded.
	 *
	 * @param int $categoryId Category ID to check.
	 * @return bool True if excluded.
	 */
	public function isCategoryExcluded( int $categoryId ): bool {
		$excluded = $this->getExcludedCategories();
		return in_array( $categoryId, $excluded, true );
	}
}
