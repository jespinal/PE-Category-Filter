<?php

namespace PavelEspinal\WpPlugins\PECategoryFilter\Tests\Integration\Core;

use PavelEspinal\WpPlugins\PECategoryFilter\Core\Container;
use PavelEspinal\WpPlugins\PECategoryFilter\Core\Plugin;
use PHPUnit\Framework\TestCase;

/**
 * Plugin Integration Test
 *
 * @package PE Category Filter
 * @since 2.0.0
 */
class PluginTest extends TestCase
{
    /**
     * Plugin instance
     */
    private Plugin $plugin;

    /**
     * Set up test
     */
    protected function setUp(): void
    {
        $this->plugin = new Plugin();
        
        // Mock WordPress functions
        $this->mockWordPressFunctions();
    }

    /**
     * Test plugin construction
     */
    public function testPluginConstruction(): void
    {
        $this->assertInstanceOf(Plugin::class, $this->plugin);
    }

    /**
     * Test plugin run method
     */
    public function testPluginRun(): void
    {
        // This should not throw any exceptions
        $this->plugin->run();
        
        $this->assertTrue(true);
    }

    /**
     * Test plugin get container
     */
    public function testGetContainer(): void
    {
        $container = $this->plugin->getContainer();
        
        $this->assertInstanceOf(Container::class, $container);
    }

    /**
     * Test plugin get version
     */
    public function testGetVersion(): void
    {
        $version = $this->plugin->getVersion();
        
        $this->assertEquals('2.0.0', $version);
    }

    /**
     * Test plugin get name
     */
    public function testGetName(): void
    {
        $name = $this->plugin->getName();
        
        $this->assertEquals('pe-category-filter', $name);
    }

    /**
     * Test plugin service registration
     */
    public function testServiceRegistration(): void
    {
        $container = $this->plugin->getContainer();
        
        // Test that services are registered
        $this->assertTrue($container->has('PavelEspinal\\WpPlugins\\PECategoryFilter\\Interfaces\\SettingsRepositoryInterface'));
        $this->assertTrue($container->has('PavelEspinal\\WpPlugins\\PECategoryFilter\\Filters\\CategoryFilter'));
        $this->assertTrue($container->has('PavelEspinal\\WpPlugins\\PECategoryFilter\\Admin\\SettingsPage'));
        $this->assertTrue($container->has('PavelEspinal\\WpPlugins\\PECategoryFilter\\WordPress\\WordPressIntegration'));
    }

    /**
     * Test plugin service resolution
     */
    public function testServiceResolution(): void
    {
        $container = $this->plugin->getContainer();
        
        // Test that services can be resolved
        $settingsRepository = $container->make('PavelEspinal\\WpPlugins\\PECategoryFilter\\Interfaces\\SettingsRepositoryInterface');
        $this->assertNotNull($settingsRepository);
        
        $categoryFilter = $container->make('PavelEspinal\\WpPlugins\\PECategoryFilter\\Filters\\CategoryFilter');
        $this->assertNotNull($categoryFilter);
        
        $settingsPage = $container->make('PavelEspinal\\WpPlugins\\PECategoryFilter\\Admin\\SettingsPage');
        $this->assertNotNull($settingsPage);
        
        $wordPressIntegration = $container->make('PavelEspinal\\WpPlugins\\PECategoryFilter\\WordPress\\WordPressIntegration');
        $this->assertNotNull($wordPressIntegration);
    }

    /**
     * Test plugin service dependencies
     */
    public function testServiceDependencies(): void
    {
        $container = $this->plugin->getContainer();
        
        // Test that CategoryFilter has SettingsRepositoryInterface dependency
        $categoryFilter = $container->make('PavelEspinal\\WpPlugins\\PECategoryFilter\\Filters\\CategoryFilter');
        $this->assertInstanceOf('PavelEspinal\\WpPlugins\\PECategoryFilter\\Filters\\CategoryFilter', $categoryFilter);
        
        // Test that SettingsPage has SettingsRepositoryInterface dependency
        $settingsPage = $container->make('PavelEspinal\\WpPlugins\\PECategoryFilter\\Admin\\SettingsPage');
        $this->assertInstanceOf('PavelEspinal\\WpPlugins\\PECategoryFilter\\Admin\\SettingsPage', $settingsPage);
        
        // Test that WordPressIntegration has Container dependency
        $wordPressIntegration = $container->make('PavelEspinal\\WpPlugins\\PECategoryFilter\\WordPress\\WordPressIntegration');
        $this->assertInstanceOf('PavelEspinal\\WpPlugins\\PECategoryFilter\\WordPress\\WordPressIntegration', $wordPressIntegration);
    }

    /**
     * Test plugin service singletons
     */
    public function testServiceSingletons(): void
    {
        $container = $this->plugin->getContainer();
        
        // Test that SettingsRepositoryInterface is a singleton
        $instance1 = $container->make('PavelEspinal\\WpPlugins\\PECategoryFilter\\Interfaces\\SettingsRepositoryInterface');
        $instance2 = $container->make('PavelEspinal\\WpPlugins\\PECategoryFilter\\Interfaces\\SettingsRepositoryInterface');
        
        $this->assertSame($instance1, $instance2);
    }

    /**
     * Test plugin service transients
     */
    public function testServiceTransients(): void
    {
        $container = $this->plugin->getContainer();
        
        // Test that CategoryFilter is not a singleton (transient)
        $instance1 = $container->make('PavelEspinal\\WpPlugins\\PECategoryFilter\\Filters\\CategoryFilter');
        $instance2 = $container->make('PavelEspinal\\WpPlugins\\PECategoryFilter\\Filters\\CategoryFilter');
        
        $this->assertNotSame($instance1, $instance2);
    }

    /**
     * Mock WordPress functions
     */
    private function mockWordPressFunctions(): void
    {
        // Mock common WordPress functions
        $functions = [
            'add_action', 'add_filter', 'register_activation_hook', 'register_deactivation_hook',
            'load_plugin_textdomain', 'plugin_basename', 'admin_url', 'get_option',
            'update_option', 'register_setting', 'add_options_page', 'current_user_can',
            'wp_die', 'get_categories', 'admin_url', 'plugin_basename'
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
        if (!function_exists($functionName)) {
            eval("function {$functionName}() { return call_user_func_array('{$callback}', func_get_args()); }");
        }
    }
}
