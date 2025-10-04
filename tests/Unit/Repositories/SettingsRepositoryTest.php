<?php

namespace PavelEspinal\WpPlugins\PECategoryFilter\Tests\Unit\Repositories;

use PavelEspinal\WpPlugins\PECategoryFilter\Repositories\SettingsRepository;
use PHPUnit\Framework\TestCase;

/**
 * Settings Repository Test
 *
 * @package PE Category Filter
 * @since 2.0.0
 */
class SettingsRepositoryTest extends TestCase
{
    /**
     * Settings repository instance
     */
    private SettingsRepository $repository;

    /**
     * Set up test
     */
    protected function setUp(): void
    {
        $this->repository = new SettingsRepository();
    }

    /**
     * Test get excluded categories with empty result
     */
    public function testGetExcludedCategoriesEmpty(): void
    {
        // Mock WordPress get_option to return false
        $this->mockWordPressFunction('get_option', function ($option) {
            if ($option === 'pecf_excluded_categories') {
                return false;
            }
            return null;
        });

        $categories = $this->repository->getExcludedCategories();
        
        $this->assertIsArray($categories);
        $this->assertEmpty($categories);
    }

    /**
     * Test get excluded categories with valid data
     */
    public function testGetExcludedCategoriesValid(): void
    {
        $expectedCategories = [1, 2, 3];
        
        // Set the option directly in WordPress
        update_option('pecf_excluded_categories', $expectedCategories);

        $categories = $this->repository->getExcludedCategories();
        
        $this->assertEquals($expectedCategories, $categories);
        
        // Clean up
        delete_option('pecf_excluded_categories');
    }

    /**
     * Test get excluded categories with invalid data
     */
    public function testGetExcludedCategoriesInvalid(): void
    {
        // Mock WordPress get_option to return non-array
        $this->mockWordPressFunction('get_option', function ($option) {
            if ($option === 'pecf_excluded_categories') {
                return 'invalid';
            }
            return null;
        });

        $categories = $this->repository->getExcludedCategories();
        
        $this->assertIsArray($categories);
        $this->assertEmpty($categories);
    }

    /**
     * Test set excluded categories
     */
    public function testSetExcludedCategories(): void
    {
        $categories = [1, 2, 3];
        
        // Mock WordPress update_option
        $this->mockWordPressFunction('update_option', function ($option, $value) use ($categories) {
            if ($option === 'pecf_excluded_categories') {
                $this->assertEquals($categories, $value);
                return true;
            }
            return false;
        });

        $result = $this->repository->setExcludedCategories($categories);
        
        $this->assertTrue($result);
    }

    /**
     * Test set excluded categories with sanitization
     */
    public function testSetExcludedCategoriesSanitization(): void
    {
        $inputCategories = [1, -2, 0, 3, 'invalid'];
        $expectedCategories = [1, 2, 3]; // -2 becomes 2, 0 filtered out, 'invalid' becomes 0 and filtered out
        
        // Mock WordPress update_option
        $this->mockWordPressFunction('update_option', function ($option, $value) use ($expectedCategories) {
            if ($option === 'pecf_excluded_categories') {
                $this->assertEquals($expectedCategories, $value);
                return true;
            }
            return false;
        });

        $result = $this->repository->setExcludedCategories($inputCategories);
        
        $this->assertTrue($result);
    }

    /**
     * Test get default settings
     */
    public function testGetDefaultSettings(): void
    {
        $settings = $this->repository->getDefaultSettings();
        
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('excluded_categories', $settings);
        $this->assertArrayHasKey('version', $settings);
        $this->assertArrayHasKey('last_updated', $settings);
        $this->assertEmpty($settings['excluded_categories']);
    }

    /**
     * Test get all settings
     */
    public function testGetAllSettings(): void
    {
        $expectedSettings = [
            'excluded_categories' => [1, 2, 3],
            'version' => '2.0.0',
            'last_updated' => '2025-01-04 12:00:00'
        ];
        
        // Set the option directly in WordPress
        update_option('pecf_settings', $expectedSettings);

        $settings = $this->repository->getAllSettings();
        
        // Test that the settings are properly returned
        $this->assertEquals($expectedSettings['excluded_categories'], $settings['excluded_categories']);
        $this->assertEquals($expectedSettings['version'], $settings['version']);
        $this->assertArrayHasKey('last_updated', $settings);
        $this->assertIsString($settings['last_updated']);
        
        // Clean up
        delete_option('pecf_settings');
    }

    /**
     * Test get all settings with defaults
     */
    public function testGetAllSettingsWithDefaults(): void
    {
        // Mock WordPress get_option to return false
        $this->mockWordPressFunction('get_option', function ($option) {
            if ($option === 'pecf_settings') {
                return false;
            }
            return null;
        });

        $settings = $this->repository->getAllSettings();
        
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('excluded_categories', $settings);
        $this->assertArrayHasKey('version', $settings);
        $this->assertArrayHasKey('last_updated', $settings);
    }

    /**
     * Test update setting
     */
    public function testUpdateSetting(): void
    {
        $key = 'test_key';
        $value = 'test_value';
        
        // Mock WordPress get_option and update_option
        $this->mockWordPressFunction('get_option', function ($option) {
            if ($option === 'pecf_settings') {
                return ['existing' => 'value'];
            }
            return null;
        });
        
        $this->mockWordPressFunction('update_option', function ($option, $value) use ($key) {
            if ($option === 'pecf_settings') {
                $this->assertArrayHasKey($key, $value);
                return true;
            }
            return false;
        });

        $result = $this->repository->updateSetting($key, $value);
        
        $this->assertTrue($result);
    }

    /**
     * Test get setting
     */
    public function testGetSetting(): void
    {
        $key = 'test_key';
        $expectedValue = 'test_value';
        
        // Set the option directly in WordPress
        update_option('pecf_settings', [$key => $expectedValue]);

        $value = $this->repository->getSetting($key);
        
        $this->assertEquals($expectedValue, $value);
        
        // Clean up
        delete_option('pecf_settings');
    }

    /**
     * Test get setting with default
     */
    public function testGetSettingWithDefault(): void
    {
        $key = 'non_existent_key';
        $defaultValue = 'default_value';
        
        // Mock WordPress get_option to return empty array
        $this->mockWordPressFunction('get_option', function ($option) {
            if ($option === 'pecf_settings') {
                return [];
            }
            return null;
        });

        $value = $this->repository->getSetting($key, $defaultValue);
        
        $this->assertEquals($defaultValue, $value);
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
            eval("function {$functionName}(\$option, \$default = false) { return call_user_func_array('{$callback}', func_get_args()); }");
        }
    }
}
