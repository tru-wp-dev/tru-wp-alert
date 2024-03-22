jQuery(document).ready(function($){

    jQuery('.alt-generate-token').on('click',function(){
       
        jQuery('#alt-popup').show();
    });
    jQuery('.alt-confirm-action').on('click', function() {
       
        $('.alt-loader').show();
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
                $('#alt-popup').hide();
            },
            error: function (error) {
            },
            complete: function(){
                $('.alt-loader').hide();
            }
        });
    });
    jQuery('.alt-cancelled-action').on('click', function() {
        $('#alt-popup').hide();
    });
});