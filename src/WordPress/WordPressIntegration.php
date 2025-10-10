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
		add_filter( 'plugin_row_meta', array( $this, 'addPluginRowMeta' ), 10, 2 );
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

		// Always use non-minified files for easier debugging and maintenance
		$cssFile = 'admin.css';
		$jsFile  = 'admin.js';

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

	/**
	 * Admin init handler used by tests. Exposed publicly so integration
	 * tests can call it directly. Keep minimal — the SettingsPage handles
	 * the heavy lifting.
	 *
	 * @return void
	 */
	public function onAdminInit(): void {
		// Delegate to SettingsPage registerSettings.
		$settingsPage = $this->container->make( SettingsPage::class );
		$settingsPage->registerSettings();
	}

	/**
	 * Sanitize excluded categories input. Tests call this directly to
	 * verify sanitization behavior.
	 *
	 * @param mixed $input Raw input to sanitize.
	 * @return array<int> Sanitized array of IDs.
	 */
	public function sanitizeExcludedCategories( $input ): array {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$sanitized = array_map( 'absint', $input );
		$sanitized = array_filter( $sanitized, fn( $id ) => $id > 0 );
		return array_values( array_unique( $sanitized ) );
	}

	/**
	 * Add action links to the plugin row. Minimal implementation used in
	 * tests to verify the link output contains expected substrings.
	 *
	 * @param string[] $existing Existing row links.
	 * @return string[] Modified list.
	 */
	public function addActionLinks( array $existing ): array {
		$settingsUrl  = admin_url( 'options-general.php?page=pecf-settings' );
		$settingsHtml = '<a href="' . esc_url( $settingsUrl ) . '">Settings</a>';
		array_unshift( $existing, $settingsHtml );
		return $existing;
	}

	/**
	 * Add plugin row meta links (GitHub/Author).
	 *
	 * Purpose: Provide the small set of row-meta links shown under the
	 * plugin on the Plugins screen (for example GitHub and Author links).
	 *
	 * Contract:
	 * - Inputs: $existing (array) of existing row-meta links, $pluginFile
	 *   plugin basename for the current plugin row.
	 * - Behavior: If $pluginFile matches this plugin's basename we append
	 *   GitHub and Author links and return the array. Otherwise, return
	 *   $existing unchanged.
	 *
	 * Notes:
	 * - This method is intentionally minimal to make tests deterministic.
	 * - Links are static; if links become dynamic in future they should be
	 *   escaped and/or translated where appropriate.
	 *
	 * @param string[] $existing   Existing row-meta links.
	 * @param string   $pluginFile Plugin basename provided by WordPress.
	 * @return string[] Modified row-meta links.
	 */
	public function addPluginRowMeta( array $existing, string $pluginFile ): array {
		// Only add our links for the plugin file that matches this plugin.
		if ( plugin_basename( PE_CATEGORY_FILTER_PLUGIN_FILE ) !== $pluginFile ) {
			return $existing;
		}

		$new_links = array(
			'plugin_site' => sprintf(
				'<a href="%s" target="_blank">%s</a>',
				'https://pavelespinal.com/wordpress-plugins-pe-category-filter/',
				__( 'Visit plugin site', 'pe-category-filter' )
			),
			'support'     => sprintf(
				'<a href="%s" target="_blank">%s</a>',
				'https://github.com/jespinal/PE-Category-Filter',
				__( 'GitHub', 'pe-category-filter' )
			),
		);

		return array_merge( $existing, $new_links );
	}
}
