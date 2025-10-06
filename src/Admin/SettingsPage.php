<?php
/**
 * Admin Settings Page
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

namespace PavelEspinal\WpPlugins\PECategoryFilter\Admin;

use PavelEspinal\WpPlugins\PECategoryFilter\Interfaces\SettingsRepositoryInterface;

/**
 * Admin Settings Page
 *
 * Handles the WordPress admin interface for plugin settings.
 */
class SettingsPage {
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
	 * Add admin menu
	 *
	 * @return void
	 */
	public function addAdminMenu(): void {
		$hook = add_options_page(
			__( 'PE Category Filter Settings', 'pe-category-filter' ),
			__( 'PECF Plugin', 'pe-category-filter' ),
			'manage_options',
			'pecf-settings',
			array( $this, 'renderSettingsPage' )
		);

		// Add help tab.
		add_action( "load-{$hook}", array( $this, 'addHelpTab' ) );
	}

	/**
	 * Register plugin settings
	 *
	 * @return void
	 */
	public function registerSettings(): void {
		register_setting(
			'pecf_settings',
			'pecf_excluded_categories',
			array(
				'sanitize_callback' => array( $this, 'sanitizeCategories' ),
				'default'           => array(),
			)
		);

		add_settings_section(
			'pecf_main_section',
			__( 'Category Filter Settings', 'pe-category-filter' ),
			array( $this, 'renderSectionDescription' ),
			'pecf_settings'
		);

		// Note: Categories field is rendered directly in the template
		// to avoid duplication with do_settings_sections().
	}

	/**
	 * Render settings page
	 *
	 * @return void
	 */
	public function renderSettingsPage(): void {
		$categories         = get_categories( array( 'hide_empty' => false ) );
		$excludedCategories = $this->settingsRepository->getExcludedCategories();

		include PE_CATEGORY_FILTER_PLUGIN_DIR . 'src/Admin/views/settings-page.php';
	}

	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function renderSectionDescription(): void {
		echo '<p>' . esc_html__( 'Configure which categories should be excluded from the home page.', 'pe-category-filter' ) . '</p>';
	}


	/**
	 * Sanitize categories input
	 *
	 * @param mixed $value Input value.
	 * @return array<int> Sanitized categories array
	 */
	public function sanitizeCategories( $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		// WordPress settings_fields() already handles nonce verification
		// No additional nonce check needed here.

		// Limit to 100 categories to prevent abuse.
		if ( count( $value ) > 100 ) {
			$value = array_slice( $value, 0, 100 );
		}

		// Sanitize and validate.
		$sanitized = array_map( 'absint', $value );
		return array_values( array_unique( array_filter( $sanitized, fn( $id ) => $id > 0 && $id < 999999 ) ) );
	}

	/**
	 * Add help tab to settings page
	 *
	 * @return void
	 */
	public function addHelpTab(): void {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		$screen->add_help_tab(
			array(
				'id'      => 'pecf-overview',
				'title'   => __( 'Overview', 'pe-category-filter' ),
				'content' => '<p>' . __( 'PE Category Filter allows you to exclude specific categories from your home page while keeping those posts accessible through category archives, search, and direct URLs.', 'pe-category-filter' ) . '</p>',
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'pecf-usage',
				'title'   => __( 'How to Use', 'pe-category-filter' ),
				'content' => '<p>' . __( 'Simply select the categories you want to exclude from the home page and click "Save Changes". Posts from excluded categories will not appear on the home page but remain accessible elsewhere.', 'pe-category-filter' ) . '</p>',
			)
		);

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'pe-category-filter' ) . '</strong></p>' .
			'<p><a href="https://github.com/jespinal/PE-Category-Filter/issues" target="_blank">' . __( 'Report a bug', 'pe-category-filter' ) . '</a></p>' .
			'<p><a href="https://pavelespinal.com/wordpress-plugins-pe-category-filter/" target="_blank">' . __( 'Plugin Homepage', 'pe-category-filter' ) . '</a></p>'
		);
	}
}
