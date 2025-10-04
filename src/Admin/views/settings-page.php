<?php
/**
 * Admin Settings Page Template
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php esc_html_e('PE Category Filter Settings', 'pe-category-filter'); ?></h1>
    
    <div class="notice notice-info">
        <p>
            <?php esc_html_e('Select the categories which you want to exclude from the home page.', 'pe-category-filter'); ?>
        </p>
    </div>
    
    <form method="post" action="options.php">
        <?php
        settings_fields('pecf_settings');
        do_settings_sections('pecf_settings');
        ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="pecf-excluded-categories">
                        <?php esc_html_e('Excluded Categories', 'pe-category-filter'); ?>
                    </label>
                </th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text">
                            <?php esc_html_e('Select categories to exclude from home page', 'pe-category-filter'); ?>
                        </legend>
                        
                        <?php if (empty($categories)): ?>
                            <p class="description">
                                <?php esc_html_e('No categories found. Create some categories first.', 'pe-category-filter'); ?>
                            </p>
                        <?php else: ?>
                            <div class="pecf-categories-list">
                                <?php foreach ($categories as $category): ?>
                                    <label for="category-<?php echo esc_attr($category->term_id); ?>" class="pecf-category-item">
                                        <input 
                                            type="checkbox" 
                                            id="category-<?php echo esc_attr($category->term_id); ?>"
                                            name="pecf_excluded_categories[]" 
                                            value="<?php echo esc_attr($category->term_id); ?>"
                                            <?php checked(in_array($category->term_id, $excludedCategories, true)); ?>
                                        />
                                        <span class="category-name"><?php echo esc_html($category->name); ?></span>
                                        <span class="category-count">(<?php echo esc_html($category->count); ?> <?php esc_html_e('posts', 'pe-category-filter'); ?>)</span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </fieldset>
                    
                    <p class="description">
                        <?php esc_html_e('Select categories to exclude from the home page. These posts will still be accessible through category pages, search, and direct URLs.', 'pe-category-filter'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(__('Save Changes', 'pe-category-filter')); ?>
    </form>
    
    <div class="pecf-info">
        <h3><?php esc_html_e('How it works', 'pe-category-filter'); ?></h3>
        <ul>
            <li><?php esc_html_e('Posts from selected categories will not appear on the home page', 'pe-category-filter'); ?></li>
            <li><?php esc_html_e('Posts will still be accessible through category archives, search, and direct URLs', 'pe-category-filter'); ?></li>
            <li><?php esc_html_e('The filter only affects the home page, not other parts of your site', 'pe-category-filter'); ?></li>
        </ul>
    </div>
</div>

<style>
.pecf-categories-list {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 10px;
    background: #fff;
}

.pecf-category-item {
    display: block;
    padding: 5px 0;
    border-bottom: 1px solid #f0f0f0;
}

.pecf-category-item:last-child {
    border-bottom: none;
}

.pecf-category-item input[type="checkbox"] {
    margin-right: 8px;
}

.category-name {
    font-weight: 500;
}

.category-count {
    color: #666;
    font-size: 0.9em;
    margin-left: 5px;
}

.pecf-info {
    margin-top: 30px;
    padding: 20px;
    background: #f9f9f9;
    border-left: 4px solid #0073aa;
}

.pecf-info h3 {
    margin-top: 0;
}
</style>
