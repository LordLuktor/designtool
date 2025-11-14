/**
 * Custom Product Designer Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Toggle options based on enable designer checkbox
        $('#_enable_designer').on('change', function() {
            toggleDesignerOptions();
        }).trigger('change');

        // File input styling
        $('input[type="file"]').on('change', function() {
            const fileName = $(this).val().split('\\').pop();
            if (fileName) {
                $(this).next('.file-name').remove();
                $(this).after('<span class="file-name" style="margin-left: 10px;">' + fileName + '</span>');
            }
        });
    });

    function toggleDesignerOptions() {
        const isEnabled = $('#_enable_designer').is(':checked');
        const $options = $('#custom_designer_options').find('.form-field').not(':first');

        if (isEnabled) {
            $options.show();
        } else {
            $options.hide();
        }
    }

})(jQuery);
