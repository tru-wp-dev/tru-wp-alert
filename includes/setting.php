<!-- Token Settings-->
<div class="wrap">
    <h1><?php _e( 'Settings', 'tru-wp-alert' ); ?></h1>
    <div class="row">
        <form class="row" method="post">
            <table class="form-table" role="presentation">
            <tbody>
               <tr class="">
                    <th><label><?php _e( 'Token', 'tru-wp-alert' ); ?></label></th>
                    <td>
                    <?php
                        $token = get_option('updates_bearer_token'); 
                        $pos = 6;
                        $token_string = substr($token,0, $pos);
                        $x = str_repeat("X", strlen($token)-$pos);
                        $token_string .= $x;
                    ?>
                    <input  type="text" name="updates_bearer_token" value="<?php echo $token_string; ?>" disabled="" style="width: 26%;">
                    <button type="submit" name="submit" class="button" value="regenerate_token"><?php _e( 'Regenerate Token', 'tru-wp-alert' ); ?></button>&nbsp;
                    <button type="button" class="button" aria-expanded="true" id="copy-token"><?php _e( 'Copy', 'tru-wp-alert' ); ?></button>
                    <div class="copied" style="display:none;color: green;float: right;width: 57%;margin: auto;">
                        <p><?php _e( 'Copied!', 'tru-wp-alert' ); ?></p>
                    </div>
                  </td>
               </tr>
            </tbody>
            </table>
        </form>
    </div>
</div>
