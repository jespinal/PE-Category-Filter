<?php

namespace PavelEspinal\WpPlugins\PECategoryFilter\Core;

use PavelEspinal\WpPlugins\PECategoryFilter\Admin\SettingsPage;
use PavelEspinal\WpPlugins\PECategoryFilter\Filters\CategoryFilter;
use PavelEspinal\WpPlugins\PECategoryFilter\Interfaces\SettingsRepositoryInterface;
use PavelEspinal\WpPlugins\PECategoryFilter\Repositories\SettingsRepository;
use PavelEspinal\WpPlugins\PECategoryFilter\WordPress\WordPressIntegration;

/**
 * Main Plugin Class
 *
 * @package PE Category Filter
 * @since 2.0.0
 */
class Plugin {

    /**
     * Plugin version
     */
    public const VERSION = '2.0.0';

    /**
     * Plugin name
     */
    public const PLUGIN_NAME = 'pe-category-filter';

    /**
     * Service container
     */
    private Container $container;

    /**
     * Constructor
     */
    public function __construct() {
        $this->container = new Container();
        $this->registerServices();
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function run(): void {
        $this->initializeWordPressIntegration();
    }

    /**
     * Register services in the container
     *
     * @return void
     */
    private function registerServices(): void {
        // Register settings repository
        $this->container->singleton(
            SettingsRepositoryInterface::class,
            SettingsRepository::class
        );

        // Register category filter
        $this->container->bind(
            CategoryFilter::class,
            function ( Container $container ) {
                $settingsRepository = $container->make( SettingsRepositoryInterface::class );
                return new CategoryFilter( $settingsRepository );
            }
        );

        // Register admin settings page
        $this->container->bind(
            SettingsPage::class,
            function ( Container $container ) {
                $settingsRepository = $container->make( SettingsRepositoryInterface::class );
                return new SettingsPage( $settingsRepository );
            }
        );

        // Register WordPress integration
        $this->container->bind(
            WordPressIntegration::class,
            function ( Container $container ) {
                return new WordPressIntegration( $container );
            }
        );
    }

    /**
     * Initialize WordPress integration
     *
     * @return void
     */
    private function initializeWordPressIntegration(): void {
        $wordPressIntegration = $this->container->make( WordPressIntegration::class );
        $wordPressIntegration->initialize();
    }

    /**
     * Get service container
     *
     * @return Container Service container
     */
    public function getContainer(): Container {
        return $this->container;
    }

    /**
     * Get plugin version
     *
     * @return string Plugin version
     */
    public function getVersion(): string {
        return self::VERSION;
    }

    /**
     * Get plugin name
     *
     * @return string Plugin name
     */
    public function getName(): string {
        return self::PLUGIN_NAME;
    }
}
