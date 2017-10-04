<div class="wrap">
	<h2 style="margin-bottom: 10px;">Dashboard<a style="margin-left: 8px;" href="<?php echo network_admin_url('admin.php?page=ez_ms_alerts_create'); ?>" class="add-new-h2">Add New</a></h2>
    <?php if(isset($deleted) && $deleted > 0) { ?>
        <div id="message" class="updated notice notice-success is-dismissible below-h2">
            <p><?php echo $deleted; ?> alerts have been deleted.</p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>
    <?php } ?>
    <?php if(isset($_GET['ezms_result']) && $_GET['ezms_result'] === 'success') { ?>
        <div id="message" class="updated notice notice-success is-dismissible below-h2">
            <p>Alert created.</p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>
    <?php } ?>
    <?php if(isset($_GET['ezms_result']) && $_GET['ezms_result'] === 'updated') { ?>
        <div id="message" class="updated notice notice-success is-dismissible below-h2">
            <p>Alert updated.</p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>
    <?php } ?>
    <form name="delete_alerts" method="post">
        <?php wp_nonce_field('ez_ms_alerts_delete_alerts'); ?>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <td class="manage-column column-cb check-column"><input id="ez_select_all" type="checkbox"></th>
                    <th scope="col">Title</th>
                    <th scope="col">Message</th>
                    <th scope="col">Start</th>
                    <th scope="col">End</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($alerts as $alert){
                        $title      = '<a href="' . network_admin_url('admin.php?page=ez_ms_alerts_edit&ezmsid=' . $alert->id) . '">' . $alert->title . '</a>';
                        $message    = ($alert->message != '') ? strip_tags($alert->message) : '';
                        $start_time = ($alert->start_time != '') ? ez_ms_alerts\create_local_time($alert->start_time) : '';
                        $end_time   = ($alert->end_time != '') ? ez_ms_alerts\create_local_time($alert->end_time) : '';
                        $status     = ez_ms_alerts\alert_status($alert->start_time, $alert->end_time);
                        echo "
                            <tr id='ez_ms_alert_{$alert->id}'>
                                <th scope='row' class='check-column'><input value='{$alert->id}' name='ez_ms_delete[]' type='checkbox'></th>
                                <td>{$title}</td>
                                <td>{$message}</td>
                                <td>{$start_time}</td>
                                <td>{$end_time}</td>
                                <td>{$status}</td>
                                <td></td>
                            </tr>
                        ";
                    }
                ?>
            </tbody>
        </table>
        <div class="tablenav bottom">
            <div class="alignleft action bulkactions">
                <input id="ez_ms_alerts_delete" name="ez_ms_alerts_delete" class="button action" type="submit" value="Delete">
            </div>
        </div>
	</form>
</div>
