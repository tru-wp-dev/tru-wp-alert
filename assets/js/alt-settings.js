jQuery(document).ready(function($){

    jQuery('.tra-generate-token').on('click',function(){
       
        jQuery('#tra-popup').show();
    });
    jQuery('.tra-confirm-action').on('click', function() {
       
        $('.tra-loader').show();
        var request_data = {
            'action':'alt_regenerate_token',
            '_ajax_nonce':alt_object.alt_nonce_submission,
        };
        jQuery.ajax({
            url: alt_object.alt_ajax_url,
            data: request_data,
            type: 'POST',
            success: function(response) {
               jQuery('input[name="alt_secret_token"]').val(response.token);
                $('#tra-popup').hide();
            },
            error: function (error) {
            },
            complete: function(){
                $('.tra-loader').hide();
            }
        });
    });
    jQuery('.tra-cancelled-action').on('click', function() {
        $('#tra-popup').hide();
    });
});