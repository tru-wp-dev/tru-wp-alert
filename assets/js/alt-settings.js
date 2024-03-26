jQuery(document).ready(function($){

    jQuery('.alt-generate-token').on('click',function(){
        jQuery('body').addClass('alt-open-popup');
        jQuery('#alt-popup').show();
    });
    jQuery('.alt-confirm-action').on('click', function() {

        jQuery('.alt-actions').hide();
        jQuery('.alt-loader').show();
        jQuery('.alt-pop-msg').hide();
        jQuery('.alt-pop-msg').after('<div class="alt-generating-msg">Generating Please Wait...........</div>');
        var request_data = {
            'action':'alt_regenerate_token',
            '_ajax_nonce':alt_object.alt_nonce_submission,
        };
        jQuery.ajax({
            url: alt_object.alt_ajax_url,
            data: request_data,
            type: 'POST',
            success: function(response) {
                jQuery('.alt-pop-msg').hide();
                jQuery('input[name="alt_secret_token"]').val(response.token);
                jQuery('.alt-loader').hide();
                jQuery('.alt-generating-msg').hide();
                jQuery('.alt-actions').hide();
                
            },
            error: function (error) {
            },
            complete: function(){

                jQuery('h3.alt-pop-msg').after('<div class="alt-success-msg">The WP secret Token has been successfully updated.</div>');
                setTimeout(function() {
                    jQuery('.alt-popup').hide();
                    jQuery('.alt-actions').show();
                    jQuery('.alt-pop-msg').show();
                    jQuery('.alt-success-msg').remove();
                    jQuery('.alt-generating-msg').remove();
                }, 1000);
            }
        });
    });

    jQuery('.alt-cancelled-action').on('click', function() {
        jQuery('#alt-popup').hide();
    });
    
    $("input.alt-wp-secret").hover(function(){

        if (!$(this).data('icon-added')) {
            $(this).after('<div class="alt-copy-token">📋</div>'); 
       
            $(this).data('icon-added', true);
          }
       
    });
    
    jQuery(document).on('click', '.alt-copy-token', function() {
        
        var copyText = jQuery(".alt-wp-secret");
        var tempTextarea = $('<textarea>');
        jQuery('body').append(tempTextarea);
        tempTextarea.val(copyText.val()).select();
        document.execCommand('copy');
        tempTextarea.remove();
        copyText.select();
        $(this).html('✓');
        var $this = $(this);
        setTimeout(function() {
            $this.remove();
            $('input.alt-wp-secret').data('icon-added', false);
        }, 1000);

    });
});