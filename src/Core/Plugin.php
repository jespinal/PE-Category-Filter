<?php
/**
 * Main Plugin Class
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

namespace PavelEspinal\WpPlugins\PECategoryFilter\Core;

use PavelEspinal\WpPlugins\PECategoryFilter\WordPress\WordPressIntegration;
use PavelEspinal\WpPlugins\PECategoryFilter\Interfaces\SettingsRepositoryInterface;
use PavelEspinal\WpPlugins\PECategoryFilter\Filters\CategoryFilter;
use PavelEspinal\WpPlugins\PECategoryFilter\Admin\SettingsPage;
use PavelEspinal\WpPlugins\PECategoryFilter\Repositories\SettingsRepository;

/**
 * Main Plugin Class
 *
 * Initializes the plugin and sets up the dependency injection container.
 */
class Plugin {
	/**
	 * Dependency injection container
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * WordPress integration service
	 *
	 * @var WordPressIntegration
	 */
	private WordPressIntegration $wordPressIntegration;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->container = new Container();
		$this->registerServices();
	}

	/**
	 * Run the plugin
	 *
	 * @return void
	 */
	public function run(): void {
		$this->wordPressIntegration = $this->container->make( WordPressIntegration::class );
		$this->wordPressIntegration->initialize();
	}

	/**
	 * Register services in the container
	 *
	 * @return void
	 */
	private function registerServices(): void {
		// Register WordPress integration as singleton.
		$this->container->singleton(
			WordPressIntegration::class,
			function ( $container ) {
				return new WordPressIntegration( $container );
			}
		);

		// Register repositories.
		$this->container->singleton(
			SettingsRepositoryInterface::class,
			function () {
				return new SettingsRepository();
			}
		);

		// Register filters as transient (not singletons) so callers receive
		// a new instance each time. Tests expect CategoryFilter to be
		// non-singleton.
		$this->container->bind(
			CategoryFilter::class,
			function ( $container ) {
				return new CategoryFilter(
					$container->make( SettingsRepositoryInterface::class )
				);
			}
		);

		// Register admin services.
		$this->container->singleton(
			SettingsPage::class,
			function ( $container ) {
				return new SettingsPage(
					$container->make( SettingsRepositoryInterface::class )
				);
			}
		);
	}

	/**
	 * Get the container instance
	 *
	 * @return Container
	 */
	public function getContainer(): Container {
		return $this->container;
	}

	/**
	 * Get plugin version used in tests and assets
	 *
	 * @return string
	 */
	public function getVersion(): string {
		return defined( 'PE_CATEGORY_FILTER_VERSION' ) ? PE_CATEGORY_FILTER_VERSION : '2.0.0';
	}

	/**
	 * Get plugin slug/name used by tests
	 *
	 * @return string
	 */
	public function getName(): string {
		return 'pe-category-filter';
	}
}
