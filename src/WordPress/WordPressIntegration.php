<?php
/**
 * WordPress Integration Service
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

namespace PavelEspinal\WpPlugins\PECategoryFilter\WordPress;

use WP_Query;
use PavelEspinal\WpPlugins\PECategoryFilter\Core\Container;
use PavelEspinal\WpPlugins\PECategoryFilter\Filters\CategoryFilter;
use PavelEspinal\WpPlugins\PECategoryFilter\Admin\SettingsPage;

/**
 * WordPress Integration Service
 *
 * Handles all WordPress-specific functionality including hooks, filters,
 * and admin interface integration.
 */
class WordPressIntegration {
	/**
	 * Dependency injection container
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * Constructor
	 *
	 * @param Container $container Dependency injection container.
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * Initialize WordPress integration
	 *
	 * @return void
	 */
	public function initialize(): void {
		$this->registerHooks();
		$this->registerFilters();
		$this->registerAdminHooks();
	}

	/**
	 * Register WordPress hooks
	 *
	 * @return void
	 */
	private function registerHooks(): void {
		register_activation_hook( PE_CATEGORY_FILTER_PLUGIN_FILE, array( $this, 'onActivation' ) );
		register_deactivation_hook( PE_CATEGORY_FILTER_PLUGIN_FILE, array( $this, 'onDeactivation' ) );
		add_action( 'init', array( $this, 'onInit' ) );
	}

	/**
	 * Register WordPress filters
	 *
	 * @return void
	 */
	private function registerFilters(): void {
		add_action( 'pre_get_posts', array( $this, 'filterMainQuery' ) );
	}

	/**
	 * Register admin hooks
	 *
	 * @return void
	 */
	private function registerAdminHooks(): void {
		add_action( 'admin_menu', array( $this, 'addAdminMenu' ) );
		add_action( 'admin_init', array( $this, 'registerSettings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminAssets' ) );
	}

	/**
	 * Filter the main query
	 *
	 * @param WP_Query $query The WordPress query object.
	 * @return void
	 */
	public function filterMainQuery( WP_Query $query ): void {
		if ( ! $query->is_main_query() || is_admin() || ! $query->is_home() ) {
			return;
		}

		$categoryFilter = $this->container->make( CategoryFilter::class );
		$categoryFilter->filterCategories( $query );
	}

	/**
	 * Add admin menu
	 *
	 * @return void
	 */
	public function addAdminMenu(): void {
		$settingsPage = $this->container->make( SettingsPage::class );
		$settingsPage->addAdminMenu();
	}

	/**
	 * Register plugin settings
	 *
	 * @return void
	 */
	public function registerSettings(): void {
		$settingsPage = $this->container->make( SettingsPage::class );
		$settingsPage->registerSettings();
	}

	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueueAdminAssets( string $hook ): void {
		if ( 'settings_page_pecf-settings' !== $hook ) {
			return;
		}

		$isDebug = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$cssFile = $isDebug ? 'admin.css' : 'admin.min.css';
		$jsFile  = $isDebug ? 'admin.js' : 'admin.min.js';

		wp_enqueue_style(
			'pecf-admin',
			PE_CATEGORY_FILTER_PLUGIN_URL . 'assets/css/' . $cssFile,
			array(),
			PE_CATEGORY_FILTER_VERSION
		);

		wp_enqueue_script(
			'pecf-admin',
			PE_CATEGORY_FILTER_PLUGIN_URL . 'assets/js/' . $jsFile,
			array( 'jquery' ),
			PE_CATEGORY_FILTER_VERSION,
			true
		);
	}

	/**
	 * Plugin activation handler
	 *
	 * @return void
	 */
	public function onActivation(): void {
		// Set default options.
		add_option( 'pecf_excluded_categories', array() );

		// Clear any existing caches.
		wp_cache_flush();
	}

	/**
	 * Plugin deactivation handler
	 *
	 * @return void
	 */
	public function onDeactivation(): void {
		// Clear caches.
		wp_cache_flush();
	}

	/**
	 * WordPress init handler
	 *
	 * @return void
	 */
	public function onInit(): void {
		// Load text domain for internationalization.
		load_plugin_textdomain(
			'pe-category-filter',
			false,
			dirname( plugin_basename( PE_CATEGORY_FILTER_PLUGIN_FILE ) ) . '/languages'
		);
	}
}
