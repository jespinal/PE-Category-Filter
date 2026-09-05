<?php
/**
 * Plugin constants - Single source of truth for version and configuration
 *
 * @package PECF
 * @since 2.0.0
 */

namespace PavelEspinal\WpPlugins\PECategoryFilter\Core;

/**
 * Plugin constants class
 *
 * Centralized location for all plugin constants, especially version management.
 * This is the single source of truth for the plugin version.
 *
 * @since 2.0.0
 */
final class Constants {

	/**
	 * Plugin version - UPDATE THIS FOR NEW RELEASES
	 *
	 * @since 2.0.0
	 */
	public const VERSION = '2.0.1';

	/**
	 * Minimum WordPress version required
	 *
	 * @since 2.0.0
	 */
	public const MIN_WP_VERSION = '6.0';

	/**
	 * Minimum PHP version required
	 *
	 * @since 2.0.0
	 */
	public const MIN_PHP_VERSION = '8.3';

	/**
	 * Plugin text domain for internationalization
	 *
	 * @since 2.0.0
	 */
	public const TEXT_DOMAIN = 'pe-category-filter';

	/**
	 * Plugin option name prefix
	 *
	 * @since 2.0.0
	 */
	public const OPTION_PREFIX = 'pe_category_filter_';

	/**
	 * Settings option name
	 *
	 * @since 2.0.0
	 */
	public const SETTINGS_OPTION = self::OPTION_PREFIX . 'settings';

	/**
	 * Cache group for WordPress object cache
	 *
	 * @since 2.0.0
	 */
	public const CACHE_GROUP = 'pe_category_filter';

	/**
	 * Filter scope: only the blog posts index (main query on is_home()).
	 *
	 * Default value; preserves the plugin's original behavior.
	 *
	 * @since 2.1.0
	 */
	public const SCOPE_BLOG_INDEX = 'blog_index';

	/**
	 * Filter scope: blog index plus a static front page (main query on
	 * is_front_page()).
	 *
	 * @since 2.1.0
	 */
	public const SCOPE_FRONT_PAGE = 'front_page';

	/**
	 * Filter scope: also secondary front-end post queries, such as theme
	 * homepage sections and recent-posts blocks.
	 *
	 * @since 2.1.0
	 */
	public const SCOPE_SECONDARY = 'secondary';

	/**
	 * Default filter scope.
	 *
	 * @since 2.1.0
	 */
	public const DEFAULT_FILTER_SCOPE = self::SCOPE_BLOG_INDEX;

	/**
	 * All valid filter scope values.
	 *
	 * @since 2.1.0
	 * @var array<int, string>
	 */
	public const VALID_FILTER_SCOPES = array(
		self::SCOPE_BLOG_INDEX,
		self::SCOPE_FRONT_PAGE,
		self::SCOPE_SECONDARY,
	);

	/**
	 * Prevent instantiation of this utility class
	 *
	 * @since 2.0.0
	 */
	private function __construct() {
		// This class should never be instantiated.
	}
}
