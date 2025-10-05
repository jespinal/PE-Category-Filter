<?php
/**
 * Admin Settings Page Template
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap pecf-settings-page">
    <h1><?php esc_html_e( 'PE Category Filter Settings', 'pe-category-filter' ); ?></h1>
    
    <?php
    // Show success message if settings were saved
    if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] === 'true' ) :
    ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                <strong><?php esc_html_e( 'Settings saved successfully!', 'pe-category-filter' ); ?></strong>
                <?php esc_html_e( 'Your category filter settings have been updated.', 'pe-category-filter' ); ?>
            </p>
        </div>
    <?php endif; ?>

    <form method="post" action="options.php" id="pecf-settings-form">
        <?php
        settings_fields( 'pecf_settings' );
        do_settings_sections( 'pecf_settings' );
        ?>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="pecf-excluded-categories">
                            <?php esc_html_e( 'Excluded Categories', 'pe-category-filter' ); ?>
                        </label>
                    </th>
                    <td>
                    <fieldset>
                        <legend class="screen-reader-text">
                            <?php esc_html_e( 'Select categories to exclude from home page', 'pe-category-filter' ); ?>
                        </legend>
                        
                        <?php if ( empty( $categories ) ) : ?>
                            <div class="notice notice-warning">
                                <p>
                                    <strong><?php esc_html_e( 'No categories found.', 'pe-category-filter' ); ?></strong>
                                    <?php esc_html_e( 'Create some categories first, then return to configure the filter.', 'pe-category-filter' ); ?>
                                </p>
                                <p>
                                    <a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=category' ) ); ?>" class="button button-secondary">
                                        <?php esc_html_e( 'Create Categories', 'pe-category-filter' ); ?>
                                    </a>
                                </p>
                            </div>
                        <?php else : ?>
                            <div class="pecf-categories-list">
                                <?php foreach ( $categories as $category ) : ?>
                                    <label for="category-<?php echo esc_attr( $category->term_id ); ?>" class="pecf-category-item">
                                        <input 
                                            type="checkbox" 
                                            id="category-<?php echo esc_attr( $category->term_id ); ?>"
                                            name="pecf_excluded_categories[]" 
                                            value="<?php echo esc_attr( $category->term_id ); ?>"
                                            <?php checked( in_array( $category->term_id, $excludedCategories, true ) ); ?>
                                            aria-describedby="category-<?php echo esc_attr( $category->term_id ); ?>-help"
                                        />
                                        <span class="category-name"><?php echo esc_html( $category->name ); ?></span>
                                        <span class="category-count" aria-label="<?php printf( esc_attr__( '%d posts in this category', 'pe-category-filter' ), $category->count ); ?>">
                                            (<?php echo esc_html( $category->count ); ?> <?php esc_html_e( 'posts', 'pe-category-filter' ); ?>)
                                        </span>
                                        <?php if ( $category->description ) : ?>
                                            <span class="category-description"><?php echo esc_html( $category->description ); ?></span>
                                        <?php endif; ?>
                                        <span id="category-<?php echo esc_attr( $category->term_id ); ?>-help" class="description">
                                            <?php esc_html_e( 'This category will be excluded from the home page', 'pe-category-filter' ); ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </fieldset>
                    
                    </td>
                </tr>
            </tbody>
        </table>
        
        <?php submit_button( __( 'Save Changes', 'pe-category-filter' ) ); ?>
    </form>
    
</div>

<style>
.pecf-settings-page {
    max-width: 800px;
}

.pecf-settings-page .notice {
    margin: 20px 0;
}

.pecf-categories-list {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.pecf-category-item {
    display: flex;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s ease;
}

.pecf-category-item:last-child {
    border-bottom: none;
}

.pecf-category-item:hover {
    background-color: #f9f9f9;
}

.pecf-category-item input[type="checkbox"] {
    margin-right: 12px;
    transform: scale(1.1);
}

.pecf-category-item .category-name {
    font-weight: 500;
    color: #333;
    flex: 1;
}

.pecf-category-item .category-count {
    color: #666;
    font-size: 0.9em;
    margin-left: 8px;
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 3px;
}

.pecf-category-item .category-description {
    display: block;
    color: #666;
    font-size: 0.85em;
    margin-top: 2px;
    font-style: italic;
}

.pecf-info {
    margin-top: 30px;
    padding: 20px;
    background: #f9f9f9;
    border-left: 4px solid #0073aa;
    border-radius: 0 4px 4px 0;
}

.pecf-info h3 {
    margin-top: 0;
    color: #0073aa;
}

.pecf-info ul {
    margin: 10px 0;
    padding-left: 20px;
}

.pecf-info li {
    margin: 5px 0;
    color: #555;
}

.pecf-settings-page .form-table th {
    width: 200px;
    padding: 20px 10px 20px 0;
    vertical-align: top;
}

.pecf-settings-page .form-table td {
    padding: 20px 10px;
}

.pecf-settings-page .form-table fieldset {
    border: none;
    margin: 0;
    padding: 0;
}

.pecf-settings-page .form-table legend {
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
}

.pecf-settings-page .description {
    font-style: italic;
    color: #666;
    margin-top: 8px;
}

.pecf-settings-page .submit {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

@media (max-width: 768px) {
    .pecf-categories-list {
        max-height: 300px;
    }
    .pecf-category-item {
        flex-direction: column;
        align-items: flex-start;
        padding: 10px 0;
    }
    .pecf-category-item input[type="checkbox"] {
        margin-bottom: 5px;
    }
    .pecf-category-item .category-count {
        margin-left: 0;
        margin-top: 5px;
    }
}

/* Loading and Message Styles */
.pecf-loading {
    opacity: 0.6;
    pointer-events: none;
}

.pecf-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #0073aa;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: pecf-spin 1s linear infinite;
}

@keyframes pecf-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.pecf-message {
    padding: 12px 15px;
    margin: 15px 0;
    border-radius: 4px;
    border-left: 4px solid;
}

.pecf-message.success {
    background: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.pecf-message.error {
    background: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
}

.pecf-message.warning {
    background: #fff3cd;
    border-color: #ffc107;
    color: #856404;
}

.pecf-message.info {
    background: #d1ecf1;
    border-color: #17a2b8;
    color: #0c5460;
}
</style>