<?php
/**
 * Main Plugin Class
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

namespace PavelEspinal\WpPlugins\PECategoryFilter\Core;

use PavelEspinal\WpPlugins\PECategoryFilter\WordPress\WordPressIntegration;

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
			\PavelEspinal\WpPlugins\PECategoryFilter\Interfaces\SettingsRepositoryInterface::class,
			function () {
				return new \PavelEspinal\WpPlugins\PECategoryFilter\Repositories\SettingsRepository();
			}
		);

		// Register filters.
		$this->container->singleton(
			\PavelEspinal\WpPlugins\PECategoryFilter\Filters\CategoryFilter::class,
			function ( $container ) {
				return new \PavelEspinal\WpPlugins\PECategoryFilter\Filters\CategoryFilter(
					$container->make( \PavelEspinal\WpPlugins\PECategoryFilter\Interfaces\SettingsRepositoryInterface::class )
				);
			}
		);

		// Register admin services.
		$this->container->singleton(
			\PavelEspinal\WpPlugins\PECategoryFilter\Admin\SettingsPage::class,
			function ( $container ) {
				return new \PavelEspinal\WpPlugins\PECategoryFilter\Admin\SettingsPage(
					$container->make( \PavelEspinal\WpPlugins\PECategoryFilter\Interfaces\SettingsRepositoryInterface::class )
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
}
