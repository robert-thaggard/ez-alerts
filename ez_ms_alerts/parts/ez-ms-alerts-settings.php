<div class="wrap">
    <h2>Settings</h2>

    <!-- Settings Saved -->
    <?php if((isset($saved) && $saved !== false)){ ?>
      <div id="message" class="updated notice notice-error is-dismissible below-h2">
          <p>Settings updated successfully.</p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
      </div>
    <?php } ?>

    <form method="post">
        <?php wp_nonce_field('ez_ms_alerts_modify_settings'); ?>
        <table class="form-table" style="margin-bottom: 15px;">
            <tbody>
                <!-- Alert Title -->
                <tr>
                    <th scope="row">
                      <label for="template">Display</label>
                    </th>
                    <td>
                      <fieldset><legend class="screen-reader-text"><span>Alert Title</span></legend>
                        <label for="ez_ms_alerts_title">
                          <input name="ez_ms_alerts_title" type="checkbox" id="ez_ms_alerts_title" value="1" <?php if($settings_title){ echo 'checked="checked"'; } ?>>
                          Title
                        </label>
                        <br>
                        <label for="ez_ms_alerts_start_time">
                          <input name="ez_ms_alerts_start_time" type="checkbox" id="ez_ms_alerts_start_time" value="1" <?php if($settings_start_time){ echo 'checked="checked"'; } ?>>
                          Start Time
                        </label>
                        <br>
                        <label for="ez_ms_alerts_end_time">
                          <input name="ez_ms_alerts_end_time" type="checkbox" id="ez_ms_alerts_end_time" value="1" <?php if($settings_end_time){ echo 'checked="checked"'; } ?>>
                          End Time
                        </label>
                        <br>
                        <label for="ez_ms_alerts_message">
                          <input name="ez_ms_alerts_message" type="checkbox" id="ez_ms_alerts_message" value="1" <?php if($settings_message){ echo 'checked="checked"'; } ?>>
                          Message
                        </label>
                      </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input name="ez_ms_alerts_save_settings" id="ez_ms_alerts_save_settings" class="button button-primary" value="Save Settings" type="submit">
        </p>
    </form>
</div>
