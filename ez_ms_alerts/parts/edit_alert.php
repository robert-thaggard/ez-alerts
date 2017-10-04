<div class="wrap">
    <script type="text/javascript">
        function checkForm(){
            
            // Clear previous errors first.
            jQuery('p.ez_ms_alerts_error_msg').remove();
            
            var error = false;
            
            var title = jQuery('#alert_title');
            var startDate = jQuery('#alert_start');
			var endDate = jQuery('#alert_end');
            var message = jQuery('#alert_message');
            
            // Check our title.
            if(title.val().length === 0){
                error = true;
                title.after('<p class="ez_ms_alerts_error_msg">Title is a required field and cannot be empty.</p>');
                title.addClass('ez_ms_alerts_error');    
            } else {
                title.removeClass('ez_ms_alerts_error');
            }
            
            // Check the start/end dates to avoid incompatible settings.
            var start = 0;
            if(startDate.datetimepicker('getDate')){
                start = startDate.datetimepicker('getDate').valueOf();  
            }
            
            var end = 0;
            if(endDate.datetimepicker('getDate')){
                end = endDate.datetimepicker('getDate').valueOf();
            }
            
            if((start > end) && end != ''){
                error = true;
                startDate.addClass('ez_ms_alerts_error');
                endDate.addClass('ez_ms_alerts_error');
                endDate.after('<p class="ez_ms_alerts_error_msg">The starting date and time cannot occur after the ending date and time.</p>');
            } else if (start == '' && end != '') {
                error = true;
                startDate.addClass('ez_ms_alerts_error');
                endDate.addClass('ez_ms_alerts_error');
                endDate.after('<p class="ez_ms_alerts_error_msg">You cannot set an ending date and time without a valid starting date and time.</p>');
            } else {
                startDate.removeClass('ez_ms_alerts_error');
                endDate.removeClass('ez_ms_alerts_error');
            }
            
            // Check message.
            if(tinymce.activeEditor.getContent() == ""){
                error = true;
                message.addClass('ez_ms_alerts_error');
                message.after('<p class="ez_ms_alerts_error_msg" style="margin-left: 5px;">Message is a required field and cannot be empty.</p>');
            } else {
                message.removeClass('ez_ms_alerts_error');
            }
            
            if(error){
                return false;
            } else {
                return true;
            }       
        }
        
        jQuery(document).ready(function(){
            jQuery('#alert_start').datetimepicker({
                timeFormat: "hh:mm tt",
            });
            jQuery('#alert_end').datetimepicker({
                timeFormat: "hh:mm tt",
            });
            jQuery('form').on('submit', function(){
                return checkForm();   
            });
        });
    </script>

    <h2>Edit Alert</h2>

    <!-- Validation Errors -->
    <?php if((isset($validation_errors) && $validation_errors === true)){ ?>
      <div id="message" class="error notice notice-error is-dismissible below-h2">
          <p>One or more required fields are invalid. A valid message and title are required. Please correct them and try again.</p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
      </div>
    <?php } ?>

    <!-- Unable to Save to Database -->
    <?php if((isset($result) && $result === false)){ ?>
      <div id="message" class="error notice notice-error is-dismissible below-h2">
          <p>There was a problem saving the Alert to the database. Please try again or contact your server administrator.</p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
      </div>
    <?php } ?>

    <!-- Alert Updated -->
    <?php if((isset($result) && (int)$result !== false)){ ?>
      <div id="message" class="updated notice notice-error is-dismissible below-h2">
          <p>Alert updated successfully.</p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
      </div>
    <?php } ?>

    <form method="post">
        <?php if(isset($_GET['ezmsid'])) { ?>
            <input type="hidden" name="ezmsid" value="<?php echo esc_attr($_GET['ezmsid']); ?>">
        <?php } ?>
        <?php wp_nonce_field('ez_ms_alerts_update_alert'); ?>
        <table class="form-table" style="margin-bottom: 15px;">
            <tbody>
                <!-- Alert Title -->
                <tr>
                    <th scope="row">
                        <label for="alert_title">Title</label>
                    </th>
                    <td>
                        <input class="<?php echo isset($validation_errors) && $validation_errors === true && $_POST['alert_title'] == '' ? 'ez_ms_alerts_error' : ''?>" type="text" name="alert_title" id="alert_title" size=60 value="<?php echo ez_ms_alerts\populate_field('title', $alert); ?>">
                    </td>
                </tr>

                <!-- Alert CSS -->
                <tr>
                    <th scope="row">
                        <label for="alert_css">Extra CSS classes</label>
                    </th>
                    <td>
                        <input type="text" name="alert_css" id="alert_css" size=60 value="<?php echo ez_ms_alerts\populate_field('css', $alert); ?>">
                    </td>
                </tr>


                <!-- Alert Start Time/Date -->
                <tr>
                    <th scope="row">
                        <label for="alert_start">Start</label>
                    </th>
                    <td>
                        <input type="text" name="alert_start" id="alert_start" value="<?php echo ez_ms_alerts\create_local_time(ez_ms_alerts\populate_field('start_time', $alert)); ?>">
                    </td>
                </tr>

                <!-- Alert End Time/Date -->
                <tr>
                    <th scope="row">
                        <label for="alert_end">End</label>
                    </th>
                    <td>
                        <input type="text" name="alert_end" id="alert_end" value="<?php echo ez_ms_alerts\create_local_time(ez_ms_alerts\populate_field('end_time', $alert)); ?>">
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
          $error_class = isset($validation_errors) && $validation_errors === true && $_POST['alert_message'] == '' ? 'ez_ms_alerts_error' : '';
          if(isset($_POST['message'])){
            wp_editor(\ez_ms_alerts\populate_field('message', $_POST, true), 'alert_message', array('editor_class' => $error_class));
          } else {
            wp_editor(\ez_ms_alerts\populate_field('message', $alert, true), 'alert_message', array('editor_class' => $error_class));
          }
        ?>
        <p class="submit">
            <input name="ez_ms_alerts_update" id="ez_ms_alerts_update" class="button button-primary" value="Update Alert" type="submit">
        </p>
    </form>
</div>
