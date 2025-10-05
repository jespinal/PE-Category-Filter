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
     * @param SettingsRepositoryInterface $settingsRepository Settings repository
     */
    public function __construct(SettingsRepositoryInterface $settingsRepository) {
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * Add admin menu
     *
     * @return void
     */
    public function addAdminMenu(): void {
        add_options_page(
            __('PE Category Filter Settings', 'pe-category-filter'),
            __('PECF Plugin', 'pe-category-filter'),
            'manage_options',
            'pecf-settings',
            [$this, 'renderSettingsPage']
        );
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
            [
                'sanitize_callback' => [$this, 'sanitizeCategories'],
                'default' => []
            ]
        );

        add_settings_section(
            'pecf_main_section',
            __('Category Filter Settings', 'pe-category-filter'),
            [$this, 'renderSectionDescription'],
            'pecf_settings'
        );

        // Note: Categories field is rendered directly in the template
        // to avoid duplication with do_settings_sections()
    }

    /**
     * Render settings page
     *
     * @return void
     */
    public function renderSettingsPage(): void {
        $categories = get_categories(['hide_empty' => false]);
        $excludedCategories = $this->settingsRepository->getExcludedCategories();

        include PE_CATEGORY_FILTER_PLUGIN_DIR . 'src/Admin/views/settings-page.php';
    }

    /**
     * Render section description
     *
     * @return void
     */
    public function renderSectionDescription(): void {
        echo '<p>' . esc_html__('Configure which categories should be excluded from the home page.', 'pe-category-filter') . '</p>';
    }


    /**
     * Sanitize categories input
     *
     * @param mixed $value Input value
     * @return array<int> Sanitized categories array
     */
    public function sanitizeCategories($value): array {
        if (!is_array($value)) {
            return [];
        }

        // WordPress settings_fields() already handles nonce verification
        // No additional nonce check needed here

        // Limit to 100 categories to prevent abuse
        if (count($value) > 100) {
            $value = array_slice($value, 0, 100);
        }

        // Sanitize and validate
        $sanitized = array_map('absint', $value);
        return array_values(array_unique(array_filter($sanitized, fn($id) => $id > 0 && $id < 999999)));
    }
}