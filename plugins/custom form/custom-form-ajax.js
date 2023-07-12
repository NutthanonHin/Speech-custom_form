jQuery(document).ready(function($) {
    setInterval(function() {
        $.ajax({
            url: customFormAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'update_custom_form_data'
            },
            success: function(response) {
                $('#custom-form-data-container').html(response);
            }
        });
    }, 5000);
});
