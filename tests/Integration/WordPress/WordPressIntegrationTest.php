<?php

namespace PavelEspinal\WpPlugins\PECategoryFilter\Tests\Integration\WordPress;

use PavelEspinal\WpPlugins\PECategoryFilter\Admin\SettingsPage;
use PavelEspinal\WpPlugins\PECategoryFilter\Core\Container;
use PavelEspinal\WpPlugins\PECategoryFilter\Filters\CategoryFilter;
use PavelEspinal\WpPlugins\PECategoryFilter\Interfaces\SettingsRepositoryInterface;
use PavelEspinal\WpPlugins\PECategoryFilter\Repositories\SettingsRepository;
use PavelEspinal\WpPlugins\PECategoryFilter\WordPress\WordPressIntegration;
use PHPUnit\Framework\TestCase;

/**
 * WordPress Integration Test
 *
 * @package PE Category Filter
 * @since 2.0.0
 */
class WordPressIntegrationTest extends TestCase
{
    /**
     * Container instance
     */
    private Container $container;

    /**
     * WordPress integration instance
     */
    private WordPressIntegration $wordPressIntegration;

    /**
     * Set up test
     */
    protected function setUp(): void
    {
        $this->container = new Container();
        
        // Register services in container (like the real Plugin class does)
        $this->registerTestServices();
        
        $this->wordPressIntegration = new WordPressIntegration($this->container);
        
        // Mock WordPress functions
        $this->mockWordPressFunctions();
    }

    /**
     * Register test services in container
     */
    private function registerTestServices(): void
    {
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
     * Test WordPress integration initialization
     */
    public function testInitialize(): void
    {
        // This should not throw any exceptions
        $this->wordPressIntegration->initialize();
        
        $this->assertTrue(true);
    }

    /**
     * Test plugin activation
     */
    public function testOnActivation(): void
    {
        // Mock WordPress functions
        $this->mockWordPressFunction('set_transient', function ($key, $value, $expiration) {
            $this->assertEquals('pecf_activated', $key);
            $this->assertTrue($value);
            $this->assertEquals(30, $expiration);
            return true;
        });
        
        $this->mockWordPressFunction('flush_rewrite_rules', function () {
            return true;
        });

        // This should not throw any exceptions
        $this->wordPressIntegration->onActivation();
        
        $this->assertTrue(true);
    }

    /**
     * Test plugin deactivation
     */
    public function testOnDeactivation(): void
    {
        // Mock WordPress functions
        $this->mockWordPressFunction('flush_rewrite_rules', function () {
            return true;
        });
        
        $this->mockWordPressFunction('delete_transient', function ($key) {
            $this->assertEquals('pecf_activated', $key);
            return true;
        });

        // This should not throw any exceptions
        $this->wordPressIntegration->onDeactivation();
        
        $this->assertTrue(true);
    }

    /**
     * Test WordPress init
     */
    public function testOnInit(): void
    {
        // Mock WordPress functions
        $this->mockWordPressFunction('load_plugin_textdomain', function ($domain, $deprecated, $path) {
            $this->assertEquals('pe-category-filter', $domain);
            $this->assertStringContainsString('languages', $path);
            return true;
        });

        // This should not throw any exceptions
        $this->wordPressIntegration->onInit();
        
        $this->assertTrue(true);
    }

    /**
     * Test admin init
     */
    public function testOnAdminInit(): void
    {
        // Mock WordPress functions
        $this->mockWordPressFunction('register_setting', function ($option_group, $option_name, $args) {
            $this->assertEquals('pecf_settings', $option_group);
            $this->assertEquals('pecf_excluded_categories', $option_name);
            $this->assertIsArray($args);
            return true;
        });

        // This should not throw any exceptions
        $this->wordPressIntegration->onAdminInit();
        
        $this->assertTrue(true);
    }

    /**
     * Test filter main query
     */
    public function testFilterMainQuery(): void
    {
        // Create mock WP_Query
        $query = $this->createMock(\WP_Query::class);
        
        $query->method('is_main_query')
            ->willReturn(true);
            
        $query->method('is_home')
            ->willReturn(true);
            
        $query->method('set')
            ->with('category__not_in', [1, 2, 3]);

        // Mock WordPress functions
        $this->mockWordPressFunction('get_option', function ($option) {
            if ($option === 'pecf_excluded_categories') {
                return [1, 2, 3];
            }
            return null;
        });

        // This should not throw any exceptions
        $this->wordPressIntegration->filterMainQuery($query);
        
        $this->assertTrue(true);
    }

    /**
     * Test filter main query with non-main query
     */
    public function testFilterMainQueryWithNonMainQuery(): void
    {
        // Create mock WP_Query
        $query = $this->createMock(\WP_Query::class);
        
        $query->method('is_main_query')
            ->willReturn(false);
            
        $query->method('is_home')
            ->willReturn(true);

        // Expect set method NOT to be called
        $query->expects($this->never())
            ->method('set');

        // This should not throw any exceptions
        $this->wordPressIntegration->filterMainQuery($query);
        
        $this->assertTrue(true);
    }

    /**
     * Test filter main query with non-home query
     */
    public function testFilterMainQueryWithNonHomeQuery(): void
    {
        // Create mock WP_Query
        $query = $this->createMock(\WP_Query::class);
        
        $query->method('is_main_query')
            ->willReturn(true);
            
        $query->method('is_home')
            ->willReturn(false);

        // Expect set method NOT to be called
        $query->expects($this->never())
            ->method('set');

        // This should not throw any exceptions
        $this->wordPressIntegration->filterMainQuery($query);
        
        $this->assertTrue(true);
    }

    /**
     * Test add action links
     */
    public function testAddActionLinks(): void
    {
        $existingLinks = ['existing' => 'link'];
        
        // Mock WordPress functions
        $this->mockWordPressFunction('admin_url', function ($path) {
            $this->assertEquals('options-general.php?page=pecf-settings', $path);
            return 'http://example.com/wp-admin/options-general.php?page=pecf-settings';
        });

        $result = $this->wordPressIntegration->addActionLinks($existingLinks);
        
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('existing', $result);
        $this->assertStringContainsString('Settings', $result[0]);
    }

    /**
     * Test add plugin row meta
     */
    public function testAddPluginRowMeta(): void
    {
        $existingLinks = ['existing' => 'link'];
        $pluginFile = plugin_basename(PE_CATEGORY_FILTER_PLUGIN_FILE);
        
        $result = $this->wordPressIntegration->addPluginRowMeta($existingLinks, $pluginFile);
        
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertArrayHasKey('existing', $result);
        
        // Check that both GitHub and Author links are present
        $resultString = implode(' ', $result);
        $this->assertStringContainsString('GitHub', $resultString);
        $this->assertStringContainsString('Author', $resultString);
    }

    /**
     * Test sanitize excluded categories
     */
    public function testSanitizeExcludedCategories(): void
    {
        $input = [1, -2, 0, 3, 'invalid'];
        $expected = [1, 2, 3]; // -2 becomes 2, 0 filtered out, 'invalid' becomes 0 and filtered out
        
        $result = $this->wordPressIntegration->sanitizeExcludedCategories($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test sanitize excluded categories with non-array
     */
    public function testSanitizeExcludedCategoriesWithNonArray(): void
    {
        $input = 'invalid';
        $expected = [];
        
        $result = $this->wordPressIntegration->sanitizeExcludedCategories($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Mock WordPress functions
     */
    private function mockWordPressFunctions(): void
    {
        // Mock common WordPress functions
        $functions = [
            'set_transient', 'delete_transient', 'flush_rewrite_rules',
            'load_plugin_textdomain', 'register_setting', 'get_option',
            'admin_url', 'plugin_basename'
        ];
        
        foreach ($functions as $function) {
            $this->mockWordPressFunction($function, function () {
                return true;
            });
        }
    }

    /**
     * Mock WordPress function
     *
     * @param string $functionName Function name to mock
     * @param callable $callback Callback function
     * @return void
     */
    private function mockWordPressFunction(string $functionName, callable $callback): void
    {
        // Use the central mock registry when available to register the
        // callable. The registry will create a forwarding function that
        // looks up the callback at runtime, so we never stringify the
        // Closure and avoid conversion errors.
        if (function_exists('pecf_register_wp_function_mock')) {
            pecf_register_wp_function_mock($functionName, $callback);
            return;
        }

        // Fallback: define a global function that forwards to the
        // provided callable. This path attempts to avoid embedding the
        // closure as a string; it is less robust than the registry.
        if (!function_exists($functionName)) {
            $cb = $callback;
            eval(sprintf('function %1$s() { $args = func_get_args(); return call_user_func_array($GLOBALS["__pecf_wp_function_mocks"]["%1$s"] ?? %2$s, $args); }', $functionName, var_export(null, true)));
        }
    }
}
