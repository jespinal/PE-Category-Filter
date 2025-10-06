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
                            <!-- Bulk Actions -->
                            <div class="pecf-bulk-actions">
                                <button type="button" class="button button-secondary" id="select-all-categories">
                                    <?php esc_html_e( 'Select All', 'pe-category-filter' ); ?>
                                </button>
                                <button type="button" class="button button-secondary" id="deselect-all-categories">
                                    <?php esc_html_e( 'Deselect All', 'pe-category-filter' ); ?>
                                </button>
                                <span class="pecf-selection-count">
                                    <?php 
                                    $selectedCount = count( $excludedCategories );
                                    printf( 
                                        esc_html( _n( '%d category selected', '%d categories selected', $selectedCount, 'pe-category-filter' ) ), 
                                        $selectedCount 
                                    ); 
                                    ?>
                                </span>
                            </div>
                            
                            <!-- Search/Filter -->
                            <div class="pecf-search-box">
                                <input type="text" id="category-search" placeholder="<?php esc_attr_e( 'Search categories...', 'pe-category-filter' ); ?>" class="regular-text" />
                            </div>
                            
                            <!-- Categories List -->
                            <div class="pecf-categories-list">
                                <?php foreach ( $categories as $category ) : 
                                    $isExcluded = in_array( $category->term_id, $excludedCategories, true );
                                ?>
                                    <label for="category-<?php echo esc_attr( $category->term_id ); ?>" class="pecf-category-item" data-category-name="<?php echo esc_attr( strtolower( $category->name ) ); ?>">
                                        <input 
                                            type="checkbox" 
                                            id="category-<?php echo esc_attr( $category->term_id ); ?>"
                                            name="pecf_excluded_categories[]" 
                                            value="<?php echo esc_attr( $category->term_id ); ?>"
                                            <?php checked( $isExcluded ); ?>
                                            class="category-checkbox"
                                            aria-describedby="category-<?php echo esc_attr( $category->term_id ); ?>-help"
                                        />
                                        <span class="category-name"><?php echo esc_html( $category->name ); ?></span>
                                        <span class="category-count" aria-label="<?php printf( esc_attr__( '%d posts in this category', 'pe-category-filter' ), $category->count ); ?>">
                                            (<?php echo esc_html( $category->count ); ?> <?php esc_html_e( 'posts', 'pe-category-filter' ); ?>)
                                        </span>
                                        <?php if ( $category->description ) : ?>
                                            <span id="category-<?php echo esc_attr( $category->term_id ); ?>-help" class="category-description">
                                                <?php echo esc_html( $category->description ); ?>
                                            </span>
                                        <?php endif; ?>
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

<script>
jQuery(document).ready(function($) {
    // Bulk actions
    $('#select-all-categories').on('click', function() {
        $('.category-checkbox').prop('checked', true);
        updateSelectionCount();
    });
    
    $('#deselect-all-categories').on('click', function() {
        $('.category-checkbox').prop('checked', false);
        updateSelectionCount();
    });
    
    // Search functionality
    $('#category-search').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.pecf-category-item').each(function() {
            var categoryName = $(this).data('category-name');
            if (categoryName.indexOf(searchTerm) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Update selection count
    $('.category-checkbox').on('change', function() {
        updateSelectionCount();
    });
    
    function updateSelectionCount() {
        var selectedCount = $('.category-checkbox:checked').length;
        var totalCount = $('.category-checkbox').length;
        $('.pecf-selection-count').text(selectedCount + ' of ' + totalCount + ' categories selected');
    }
    
    // Initialize count
    updateSelectionCount();
});
</script>

<style>
.pecf-settings-page {
    max-width: 800px;
}

.pecf-settings-page .notice {
    margin: 20px 0;
}

/* Bulk Actions */
.pecf-bulk-actions {
    margin-bottom: 20px;
    padding: 15px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.pecf-bulk-actions button {
    margin-right: 0;
    padding: 8px 16px;
    border-radius: 4px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.pecf-bulk-actions button:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.pecf-selection-count {
    color: #6c757d;
    font-weight: 500;
    margin-left: 20px;
    font-size: 0.95em;
    align-self: center;
}

/* Search Box */
.pecf-search-box {
    margin-bottom: 20px;
    position: relative;
}

.pecf-search-box input {
    width: 100%;
    max-width: 350px;
    padding: 10px 15px;
    border: 2px solid #e1e5e9;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #fff;
}

.pecf-search-box input:focus {
    border-color: #007cba;
    box-shadow: 0 0 0 3px rgba(0, 124, 186, 0.1);
    outline: none;
}

.pecf-search-box::before {
    content: "🔍";
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    opacity: 0.5;
}

/* Categories List */
.pecf-categories-list {
    max-height: 450px;
    overflow-y: auto;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 20px;
    background: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    position: relative;
}

.pecf-categories-list::-webkit-scrollbar {
    width: 8px;
}

.pecf-categories-list::-webkit-scrollbar-track {
    background: #f1f3f4;
    border-radius: 4px;
}

.pecf-categories-list::-webkit-scrollbar-thumb {
    background: #c1c8cd;
    border-radius: 4px;
}

.pecf-categories-list::-webkit-scrollbar-thumb:hover {
    background: #a8b2ba;
}

.pecf-category-item {
    display: block;
    padding: 12px 15px;
    border-bottom: 1px solid #f0f2f5;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.pecf-category-item:last-child {
    border-bottom: none;
}

.pecf-category-item:hover {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.pecf-category-item input[type="checkbox"] {
    margin-right: 12px;
    transform: scale(1.1);
    accent-color: #007cba;
    vertical-align: middle;
}

.pecf-category-item .category-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.05em;
    line-height: 1.4;
    display: inline-block;
    vertical-align: middle;
    margin-right: 8px;
}

.pecf-category-item .category-count {
    color: #6c757d;
    font-size: 0.9em;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    padding: 3px 8px;
    border-radius: 10px;
    font-weight: 500;
    border: 1px solid #90caf9;
    display: inline-block;
    vertical-align: middle;
    margin-left: 8px;
}

.pecf-category-item .category-description {
    color: #6c757d;
    font-size: 0.85em;
    font-style: italic;
    margin-top: 6px;
    margin-left: 24px;
    line-height: 1.4;
    background: #f8f9fa;
    padding: 6px 10px;
    border-radius: 4px;
    border-left: 3px solid #007cba;
    display: block;
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

/* Responsive Design */
@media (max-width: 768px) {
    .pecf-bulk-actions {
        flex-direction: column;
        gap: 10px;
    }
    
    .pecf-bulk-actions button {
        margin-right: 0;
        margin-bottom: 8px;
    }
    
    .pecf-selection-count {
        margin-left: 0;
        text-align: center;
    }
    
    .pecf-categories-list {
        max-height: 350px;
        padding: 15px;
    }
    
    .pecf-category-item {
        padding: 12px 8px;
    }
    
    .pecf-category-item:hover {
        transform: none;
    }
    
    .pecf-search-box input {
        max-width: 100%;
    }
}

@media (max-width: 480px) {
    .pecf-settings-page {
        margin: 0 10px;
    }
    
    .pecf-category-item {
        padding: 10px 8px;
    }
    
    .pecf-category-item input[type="checkbox"] {
        margin-right: 8px;
    }
    
    .pecf-category-item .category-name {
        font-size: 1em;
        margin-right: 6px;
    }
    
    .pecf-category-item .category-count {
        font-size: 0.8em;
        padding: 2px 6px;
        margin-left: 6px;
    }
    
    .pecf-category-item .category-description {
        margin-left: 20px;
        font-size: 0.8em;
        padding: 4px 8px;
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