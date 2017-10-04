<?php


/*
 Plugin Name: EZ Multisite Alerts
 Plugin URI:  http://wordpress.org/extend/plugins/health-check/
 Description: Schedule alerts that will be displayed across all sites/networks on a single multisite installation.
 Version:     1.0
 Author:      Robert Thaggard
 Author URI:  http://wordpress.org/extend/plugins/health-check/
 Text Domain: ez-ms-alerts
 */

define('EZ_MS_ALERTS_PLUGIN_PATH', plugin_dir_path(__FILE__));

register_activation_hook(__FILE__, function() {
    include_once(EZ_MS_ALERTS_PLUGIN_PATH . 'inc/ez-ms-alerts.inc.php');
    ez_ms_alerts\create_table();
});

add_shortcode('ez-ms-alerts', 'ez_ms_alerts_display');
function ez_ms_alerts_display(){
  include(EZ_MS_ALERTS_PLUGIN_PATH . 'inc/ez-ms-alerts.inc.php');

  ez_ms_alerts\process_cache_queue();

  $output = '';
  $active_alerts = ez_ms_alerts\get_active_alerts();
  if(count($active_alerts) > 0){
    $output  = '<div class="ez-alert-bar">';
    $output .= '<h1 class="a-hide">Urgent Alert Notice</h1>';

    $settings_title       = get_site_option('ez_ms_alerts_title', true);
    $settings_start_time  = get_site_option('ez_ms_alerts_start_time', true);
    $settings_end_time    = get_site_option('ez_ms_alerts_end_time', true);
    $settings_message     = get_site_option('ez_ms_alerts_message', true);

    foreach($active_alerts as $alert){
      $css      = $alert->css;
      $title    = $alert->title;
      $message  = $alert->message;

      $output .= "<div class=\"ez-alert {$css}\">";

        if($settings_title){
          $output .= "<h2>{$title}</h2>";
        }

        if($settings_message){
          $output .= "<p>{$message}</p>";
        }

      $output .= '</div>';
    }
    $output .= '</div><!--/ez-alert-bar-->';
  }
  return $output;
}

add_action('network_admin_menu', function() {

    add_menu_page('EZ MS Alerts', 'EZ MS Alerts', 'manage_options', 'ez_ms_alerts', function() {
        $deleted = 0;
        include_once(EZ_MS_ALERTS_PLUGIN_PATH . 'inc/ez-ms-alerts.inc.php');
        if(isset($_POST['ez_ms_alerts_delete']) && check_admin_referer('ez_ms_alerts_delete_alerts')){
            if(isset($_POST['ez_ms_delete']) && is_array($_POST['ez_ms_delete'])){
                foreach($_POST['ez_ms_delete'] as $alert_id){
                    if(ez_ms_alerts\delete_alert($alert_id)){
                        $deleted++;
                    }
                }
            }
            $alerts = ez_ms_alerts\get_alerts();
            include_once(EZ_MS_ALERTS_PLUGIN_PATH . 'parts/dashboard.php');
        } else {
            $alerts = ez_ms_alerts\get_alerts();
            include_once(EZ_MS_ALERTS_PLUGIN_PATH . 'parts/dashboard.php');
        }
    }
   , 'dashicons-megaphone');

    add_submenu_page('ez_ms_alerts', 'All Alerts', 'All Alerts', 'manage_options', 'ez_ms_alerts');

    add_submenu_page('ez_ms_alerts', 'Add Alert', 'Add Alert', 'manage_options', 'ez_ms_alerts_create', function() {
      include_once(EZ_MS_ALERTS_PLUGIN_PATH . 'inc/ez-ms-alerts.inc.php');
      if(isset($_POST['ez_ms_alerts_create']) && check_admin_referer('ez_ms_alerts_create_alert')){

          $start    = isset($_POST['alert_start']) ? $_POST['alert_start'] : '';
          $end      = isset($_POST['alert_end']) ? $_POST['alert_end'] : '';
          $title    = isset($_POST['alert_title']) ? $_POST['alert_title'] : '';
          $message  = isset($_POST['alert_message']) ? $_POST['alert_message'] : '';
          $css      = isset($_POST['alert_css'])  ? $_POST['alert_css'] : '';

          $alert = array(
              'start_time'  => $start,
              'end_time'    => $end,
              'title'       => $title,
              'message'     => $message,
              'css'         => $css
          );

          $validation_errors = false;
          if($title == '' || $message == ''){
            $validation_errors = true;
            include_once(EZ_MS_ALERTS_PLUGIN_PATH . 'parts/create_alert.php');
          } else {
            $result = ez_ms_alerts\save_alert($alert);
            include_once(EZ_MS_ALERTS_PLUGIN_PATH . 'parts/create_alert.php');
          }
      } else {
        include_once(EZ_MS_ALERTS_PLUGIN_PATH . 'parts/create_alert.php');
      }
    });

    add_submenu_page('ez_ms_alerts', 'Settings', 'Settings', 'manage_options', 'ez_ms_alerts_settings', function() {
        include_once(EZ_MS_ALERTS_PLUGIN_PATH . 'inc/ez-ms-alerts.inc.php');
        if(isset($_POST['ez_ms_alerts_save_settings']) && check_admin_referer('ez_ms_alerts_modify_settings')){

          $settings_title       = isset($_POST['ez_ms_alerts_title']) ? true : false;
          $settings_start_time  = isset($_POST['ez_ms_alerts_start_time']) ? true : false;
          $settings_end_time    = isset($_POST['ez_ms_alerts_end_time']) ? true : false;
          $settings_message     = isset($_POST['ez_ms_alerts_message']) ? true : false;

          update_site_option('ez_ms_alerts_title', $settings_title);
          update_site_option('ez_ms_alerts_start_time', $settings_start_time);
          update_site_option('ez_ms_alerts_end_time', $settings_end_time);
          update_site_option('ez_ms_alerts_message', $settings_message);

          $saved = true;
          include_once(EZ_MS_ALERTS_PLUGIN_PATH . 'parts/ez-ms-alerts-settings.php');
        } else {

          $settings_title       = get_site_option('ez_ms_alerts_title', false);
          $settings_start_time  = get_site_option('ez_ms_alerts_start_time', false);
          $settings_end_time    = get_site_option('ez_ms_alerts_end_time', false);
          $settings_message     = get_site_option('ez_ms_alerts_message', false);

          include_once(EZ_MS_ALERTS_PLUGIN_PATH . 'parts/ez-ms-alerts-settings.php');
        }
    });

    add_submenu_page('', 'Edit Alert', 'Edit Alert', 'manage_options', 'ez_ms_alerts_edit', function() {
        include_once(EZ_MS_ALERTS_PLUGIN_PATH . 'inc/ez-ms-alerts.inc.php');


        if(isset($_POST['ez_ms_alerts_update']) && check_admin_referer('ez_ms_alerts_update_alert')){


            $start    = isset($_POST['alert_start']) ? $_POST['alert_start'] : '';
            $end      = isset($_POST['alert_end']) ? $_POST['alert_end'] : '';
            $title    = isset($_POST['alert_title']) ? $_POST['alert_title'] : '';
            $message  = isset($_POST['alert_message']) ? $_POST['alert_message'] : '';
            $css      = isset($_POST['alert_css'])  ? $_POST['alert_css'] : '';
            $id       = isset($_POST['ezmsid']) ? $_POST['ezmsid'] : null;

            $alert = array(
                'start_time'  => $start,
                'end_time'    => $end,
                'title'       => $title,
                'message'     => $message,
                'css'         => $css
            );

            $validation_errors = false;
            if($title == '' || $message == ''){
              $validation_errors = true;
            } else {
              $result = ez_ms_alerts\update_alert($id, $alert);
            }

            include_once(EZ_MS_ALERTS_PLUGIN_PATH . 'parts/edit_alert.php');
        } else {
          $found_alert = false;
          if(isset($_GET['ezmsid'])){
            if(($alert = ez_ms_alerts\get_alert($_GET['ezmsid'])) != false){
              $found_alert = true;
            }
          }
          include_once(EZ_MS_ALERTS_PLUGIN_PATH . 'parts/edit_alert.php');
        }
    });
});

add_action('admin_enqueue_scripts', function(){
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-slider', '', array('jquery-ui'));
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-timepicker', plugins_url('js/jquery-ui-timepicker-addon.js', __FILE__), array( 'jquery' ), '', true);
    wp_enqueue_style('jquery-ui-theme', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');
    wp_enqueue_style('jquery-ui-timepicker-style', plugins_url('css/jquery-ui-timepicker-addon.css', __FILE__));
    wp_enqueue_style('ez-ms-alerts', plugins_url('css/ez-ms-alerts.css', __FILE__));
});

add_action('wp_enqueue_scripts', function(){
  wp_enqueue_style('ez-ms-alerts', plugins_url('css/ez-ms-alerts.css', __FILE__));
});

?>
