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
			$query->set( 'category__not_in', $excludedCategories );
		}
	}

	/**
	 * Determine whether the given query should be filtered.
	 *
	 * Made protected so tests can override this behavior when needed.
	 *
	 * @param \WP_Query $query The WordPress query object.
	 * @return bool True if the query should be filtered
	 */
	protected function shouldFilter( \WP_Query $query ): bool {
		// Only filter main queries on the home page.
		if ( ! $query->is_main_query() ) {
			return false;
		}

		// Don't filter admin queries.
		if ( is_admin() ) {
			return false;
		}

		// Only filter home page queries.
		if ( ! $query->is_home() ) {
			return false;
		}

		return true;
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
