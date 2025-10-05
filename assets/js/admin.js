/**
 * PE Category Filter - Admin JavaScript
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize admin functionality
     */
    function init() {
        bindEvents();
    }

    /**
     * Bind event handlers
     */
    function bindEvents() {
        // Form submission
        $('#pecf-settings-form').on('submit', handleFormSubmit);
        
        // Category selection
        $('.pecf-category-item input[type="checkbox"]').on('change', handleCategoryChange);
    }

    /**
     * Handle form submission
     */
    function handleFormSubmit(e) {
        // Add loading state
        $('.pecf-settings-page').addClass('pecf-loading');
        
        // Show success message after a short delay
        setTimeout(function() {
            $('.pecf-settings-page').removeClass('pecf-loading');
        }, 1000);
    }

    /**
     * Handle category selection change
     */
    function handleCategoryChange(e) {
        var $checkbox = $(e.target);
        var $item = $checkbox.closest('.pecf-category-item');
        
        if ($checkbox.is(':checked')) {
            $item.addClass('selected');
        } else {
            $item.removeClass('selected');
        }
    }

    // Initialize when document is ready
    $(document).ready(init);

})(jQuery);