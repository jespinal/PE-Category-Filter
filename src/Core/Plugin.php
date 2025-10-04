<?php

namespace PavelEspinal\WpPlugins\PECategoryFilter\Core;

use PavelEspinal\WpPlugins\PECategoryFilter\Admin\SettingsPage;
use PavelEspinal\WpPlugins\PECategoryFilter\Filters\CategoryFilter;
use PavelEspinal\WpPlugins\PECategoryFilter\Interfaces\SettingsRepositoryInterface;
use PavelEspinal\WpPlugins\PECategoryFilter\Repositories\SettingsRepository;

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
        $this->loadTextDomain();
        $this->registerHooks();
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
    }

    /**
     * Load text domain for internationalization
     *
     * @return void
     */
    private function loadTextDomain(): void {
        add_action(
            'plugins_loaded',
            function () {
                load_plugin_textdomain(
                    'pe-category-filter',
                    false,
                    dirname( plugin_basename( PE_CATEGORY_FILTER_PLUGIN_FILE ) ) . '/languages'
                );
            }
        );
    }

    /**
     * Register WordPress hooks
     *
     * @return void
     */
    private function registerHooks(): void {
        // Register admin hooks
        add_action( 'admin_menu', array( $this, 'registerAdminHooks' ) );

        // Register public hooks
        add_action( 'pre_get_posts', array( $this, 'registerPublicHooks' ) );
    }

    /**
     * Register admin hooks
     *
     * @return void
     */
    public function registerAdminHooks(): void {
        $settingsPage = $this->container->make( SettingsPage::class );
        $settingsPage->register();
    }

    /**
     * Register public hooks
     *
     * @return void
     */
    public function registerPublicHooks(): void {
        $categoryFilter = $this->container->make( CategoryFilter::class );
        add_action( 'pre_get_posts', array( $categoryFilter, 'filterCategories' ) );
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
