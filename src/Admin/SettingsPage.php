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

        add_settings_field(
            'pecf_excluded_categories',
            __('Excluded Categories', 'pe-category-filter'),
            [$this, 'renderCategoriesField'],
            'pecf_settings',
            'pecf_main_section'
        );
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
     * Render categories field
     *
     * @return void
     */
    public function renderCategoriesField(): void {
        $categories = get_categories(['hide_empty' => false]);
        $excludedCategories = $this->settingsRepository->getExcludedCategories();

        if (empty($categories)) {
            echo '<p class="description">' . esc_html__('No categories found. Create some categories first.', 'pe-category-filter') . '</p>';
            return;
        }

        echo '<div class="pecf-categories-list">';
        foreach ($categories as $category) {
            $checked = in_array($category->term_id, $excludedCategories, true) ? 'checked' : '';
            printf(
                '<label for="category-%d" class="pecf-category-item">
                    <input type="checkbox" id="category-%d" name="pecf_excluded_categories[]" value="%d" %s />
                    <span class="category-name">%s</span>
                    <span class="category-count">(%d %s)</span>
                </label>',
                $category->term_id,
                $category->term_id,
                $category->term_id,
                $checked,
                esc_html($category->name),
                $category->count,
                esc_html__('posts', 'pe-category-filter')
            );
        }
        echo '</div>';
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

        // Validate nonce for security
        if (!isset($_POST['pecf_nonce']) || !wp_verify_nonce($_POST['pecf_nonce'], 'pecf_save_settings')) {
            wp_die(esc_html__('Security check failed. Please try again.', 'pe-category-filter'));
        }

        // Limit to 100 categories to prevent abuse
        if (count($value) > 100) {
            $value = array_slice($value, 0, 100);
        }

        // Sanitize and validate
        $sanitized = array_map('absint', $value);
        return array_values(array_unique(array_filter($sanitized, fn($id) => $id > 0 && $id < 999999)));
    }
}