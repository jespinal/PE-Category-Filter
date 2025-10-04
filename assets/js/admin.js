/**
 * PE Category Filter - Admin JavaScript
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

(function($) {
    'use strict';

    /**
     * PE Category Filter Admin
     */
    const PECFAdmin = {
        
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.bindEvents();
            this.initializeUI();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Form submission
            $('#pecf-settings-form').on('submit', this.handleFormSubmit);
            
            // Category selection
            $('.pecf-category-item input[type="checkbox"]').on('change', this.handleCategoryChange);
            
            // Select all/none functionality
            $(document).on('click', '.pecf-select-all', this.selectAllCategories);
            $(document).on('click', '.pecf-select-none', this.selectNoCategories);
            
            // Search functionality
            $('#pecf-category-search').on('input', this.filterCategories);
        },

        /**
         * Initialize UI elements
         */
        initializeUI: function() {
            this.addSearchBox();
            this.addBulkActions();
            this.updateCategoryCounts();
        },

        /**
         * Add search box for categories
         */
        addSearchBox: function() {
            const searchBox = $('<input>', {
                type: 'text',
                id: 'pecf-category-search',
                placeholder: 'Search categories...',
                class: 'regular-text'
            });

            $('.pecf-categories-list').before(
                $('<div>', {
                    class: 'pecf-search-container',
                    style: 'margin-bottom: 15px;'
                }).append(searchBox)
            );
        },

        /**
         * Add bulk action buttons
         */
        addBulkActions: function() {
            const bulkActions = $('<div>', {
                class: 'pecf-bulk-actions',
                style: 'margin-bottom: 15px;'
            }).html(`
                <button type="button" class="button pecf-select-all">Select All</button>
                <button type="button" class="button pecf-select-none">Select None</button>
            `);

            $('.pecf-categories-list').before(bulkActions);
        },

        /**
         * Handle form submission
         */
        handleFormSubmit: function(e) {
            const $form = $(this);
            const $submitBtn = $form.find('input[type="submit"]');
            
            // Show loading state
            $submitBtn.prop('disabled', true).val('Saving...');
            $form.addClass('pecf-loading');
            
            // Simulate processing time (remove in production)
            setTimeout(function() {
                $submitBtn.prop('disabled', false).val('Save Changes');
                $form.removeClass('pecf-loading');
                
                // Show success message
                PECFAdmin.showMessage('Settings saved successfully!', 'success');
            }, 1000);
        },

        /**
         * Handle category checkbox change
         */
        handleCategoryChange: function() {
            PECFAdmin.updateCategoryCounts();
            PECFAdmin.updateBulkActionButtons();
        },

        /**
         * Select all categories
         */
        selectAllCategories: function() {
            $('.pecf-category-item input[type="checkbox"]').prop('checked', true);
            PECFAdmin.updateCategoryCounts();
            PECFAdmin.updateBulkActionButtons();
        },

        /**
         * Select no categories
         */
        selectNoCategories: function() {
            $('.pecf-category-item input[type="checkbox"]').prop('checked', false);
            PECFAdmin.updateCategoryCounts();
            PECFAdmin.updateBulkActionButtons();
        },

        /**
         * Filter categories based on search
         */
        filterCategories: function() {
            const searchTerm = $(this).val().toLowerCase();
            
            $('.pecf-category-item').each(function() {
                const $item = $(this);
                const categoryName = $item.find('.category-name').text().toLowerCase();
                
                if (categoryName.includes(searchTerm)) {
                    $item.show();
                } else {
                    $item.hide();
                }
            });
        },

        /**
         * Update category counts display
         */
        updateCategoryCounts: function() {
            const totalCategories = $('.pecf-category-item').length;
            const selectedCategories = $('.pecf-category-item input[type="checkbox"]:checked').length;
            
            // Update count display if it exists
            if ($('.pecf-category-count-display').length === 0) {
                $('.pecf-categories-list').after(
                    $('<div>', {
                        class: 'pecf-category-count-display',
                        style: 'margin-top: 10px; font-style: italic; color: #666;'
                    })
                );
            }
            
            $('.pecf-category-count-display').text(
                `${selectedCategories} of ${totalCategories} categories selected`
            );
        },

        /**
         * Update bulk action button states
         */
        updateBulkActionButtons: function() {
            const totalCategories = $('.pecf-category-item').length;
            const selectedCategories = $('.pecf-category-item input[type="checkbox"]:checked').length;
            
            $('.pecf-select-all').prop('disabled', selectedCategories === totalCategories);
            $('.pecf-select-none').prop('disabled', selectedCategories === 0);
        },

        /**
         * Show message to user
         */
        showMessage: function(message, type) {
            const $message = $('<div>', {
                class: `pecf-message ${type}`,
                text: message
            });

            // Remove existing messages
            $('.pecf-message').remove();
            
            // Add new message
            $('.pecf-settings-page h1').after($message);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $message.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        PECFAdmin.init();
    });

})(jQuery);
