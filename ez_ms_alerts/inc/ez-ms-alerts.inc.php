<?php

namespace ez_ms_alerts {
    define('ez_ms_alerts\TABLE_NAME', 'ezmsalerts');

    function create_table() {
        global $wpdb;
        $table_name = $wpdb->base_prefix . TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
          id smallint NOT NULL AUTO_INCREMENT,
          start_time datetime DEFAULT '0000-00-00 00:00:00',
          end_time datetime DEFAULT '0000-00-00 00:00:00',
          title varchar(500) DEFAULT '' NOT NULL,
          message text DEFAULT '' NOT NULL,
          css text DEFAULT '' NOT NULL,
          UNIQUE KEY id (id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    function save_alert(array $alert) {
        global $wpdb;
        $table_name = $wpdb->base_prefix . TABLE_NAME;

        $start_time = isset($alert['start_time']) && $alert['start_time'] != '' ? create_utc_time($alert['start_time']) : '';
        $end_time   = isset($alert['end_time']) && $alert['end_time'] != '' ? create_utc_time($alert['end_time']) : '';

        if($start_time != ''){
            enqueue_cache_flush(strtotime($start_time));
        }

        if($end_time != ''){
            enqueue_cache_flush(strtotime($end_time));
        }

        $result = $wpdb->insert($table_name,
            array(
                'start_time'    => $start_time,
                'end_time'      => $end_time,
                'title'         => stripslashes($alert['title']),
                'message'       => stripslashes($alert['message']),
                'css'           => stripslashes($alert['css'])
            ),
            array('%s','%s','%s','%s','%s')
        );
         if($result){
            return $wpdb->insert_id;
        } else {
            return false;
        }
    }

    function create_utc_time($time){
    
      $date_time_format_string = get_option('date_format') . " " . get_option('time_format');

      if($time == '0000-00-00 00:00:00'){
        return '';
      }
     
      if(($new_time = \DateTime::createFromFormat($date_time_format_string, $time, new \DateTimeZone("America/Toronto"))) != false){
        $new_time->setTimeZone(new \DateTimeZone("UTC"));
        return $new_time->format('Y-m-d H:i:s');
      } else {
        return '';
      }

    }

    function create_local_time($time){
      
      $date_time_format_string = get_option('date_format') . " " . get_option('time_format');
      
      if($time == '0000-00-00 00:00:00'){
        return '';
      }

      if(($new_time = \DateTime::createFromFormat('Y-m-d H:i:s', $time, new \DateTimeZone("UTC"))) != false){
        $new_time->setTimeZone(new \DateTimeZone("America/Toronto"));
        return $new_time->format($date_time_format_string);
      } else if(($new_time = \DateTime::createFromFormat('F j, Y h:i a', $time)) != false){
        return $new_time->format($date_time_format_string);
      } else {
        return '';
      }

    }

    function update_alert($id, array $alert){
        global $wpdb;
        $table_name = $wpdb->base_prefix . TABLE_NAME;

        $start_time = isset($alert['start_time']) && $alert['start_time'] != '' ? create_utc_time($alert['start_time']) : '';
        $end_time   = isset($alert['end_time']) && $alert['end_time'] != '' ? create_utc_time($alert['end_time']) : '';

        if($start_time != ''){
            enqueue_cache_flush(strtotime($start_time));
        }

        if($end_time != ''){
            enqueue_cache_flush(strtotime($end_time));
        }

        return $result = $wpdb->update($table_name,
            array(
                'start_time'    => $start_time,
                'end_time'      => $end_time,
                'title'         => stripslashes($alert['title']),
                'message'       => stripslashes($alert['message']),
                'css'           => stripslashes($alert['css'])
            ),
            array( 'id' => $id ),
            array('%s','%s','%s','%s','%s'),
            array('%d')
        );
    }

    function delete_alert($id){
        global $wpdb;
        $table_name = $wpdb->base_prefix . TABLE_NAME;
        return $wpdb->delete($table_name, array('id' => intval($id)), array('%d'));
    }

    function get_alerts() {
        global $wpdb;
        $table_name = $wpdb->base_prefix . TABLE_NAME;
        return $wpdb->get_results("SELECT * FROM $table_name");
    }

    function get_alert($id) {
        global $wpdb;
        $table_name = $wpdb->base_prefix . TABLE_NAME;
        return $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id", ARRAY_A);
    }

    function get_active_alerts() {
        global $wpdb;
        $table_name = $wpdb->base_prefix . TABLE_NAME;
        $current_time = (new \DateTime())->format('Y-m-d H:i:s');
        return $wpdb->get_results("SELECT* FROM $table_name WHERE (start_time <= '$current_time' AND start_time <> '0000-00-00 00:00:00') AND (end_time > '$current_time' OR end_time = '0000-00-00 00:00:00')");
    }

    function alert_status($start_time, $end_time){
        $start  = new \DateTime($start_time);
        $end    = new \DateTime($end_time);
        $now    = new \DateTime();
        if ($now > $start && ($now < $end || $start > $end)) {
            return 'Active';
        } else if ($now < $start) {
            return 'Pending';
        } else if ($now > $end && $end > $start) {
            return 'Expired';
        } else {
            return 'Disabled';
        }
    }

    function display_time($time){
      if(!$time){
        return '';
      }
      $display_time = new \DateTime($time);
      return $display_time->format('m/d/Y H:i');
    }

    function populate_field($field_name, array &$source, $wp_editor = false) {
        if(isset($source[$field_name])){
            if($wp_editor){
                return stripslashes($source[$field_name]);
            } else {
                return stripslashes(sanitize_text_field($source[$field_name]));
            }
        } else {
            return '';
        }
    }

    function enqueue_cache_flush($timestamp){
        $queue = get_site_option('ez_ms_alerts_cache_flush_queue', array());
        $queue[] = $timestamp;
        sort($queue);
        update_site_option('ez_ms_alerts_cache_flush_queue', $queue);
    }

    function process_cache_queue(){
        $queue = get_site_option('ez_ms_alerts_cache_flush_queue', array());
        $current_timestamp = time();
        foreach($queue as $key => $entry){
            if($entry < $current_timestamp){
                unset($queue[$key]);
                \WpeCommon::purge_varnish_cache();
            }   
        }
        update_site_option('ez_ms_alerts_cache_flush_queue', $queue);
    }

}
// namespace ez_ms_alerts

?>
