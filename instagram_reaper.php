<?php
/*
Plugin Name: Instagram Reaper
Plugin URI: 
Description: An Instagram plugin for Wordpress Developers.  Set wp_chron jobs, get images by hashtag or username
Version: 0
Author: Kyle Shike
Author URI: 
License: GPL
Copyright: Kyle Shike
*/
  include('lib/inst_reaper_core_functions.php');

  function inst_reaper_database_tables_setup() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'reaper_instagram';
    $charset_collate = '';

    if ( ! empty( $wpdb->charset ) ) {
      $charset_collate = "DEFAULT CHARSET={$wpdb->charset}";
    }

    if ( ! empty( $wpdb->collate ) ) {
      $charset_collate .= " COLLATE {$wpdb->collate}";
    }

    $sql = "CREATE TABLE $table_name (
      id int(11) NOT NULL primary key AUTO_INCREMENT,
      inst_id varchar(255) NOT NULL,
      url varchar(255) NOT NULL,
      src varchar(255) NOT NULL,
      src_low_res varchar(255) NOT NULL,
      src_thumb varchar(255) NOT NULL,
      likes_count varchar(255) NOT NULL,
      comments_count varchar(255) NOT NULL,
      date_created bigint(30) NOT NULL
    ) $charset_collate;";

    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);
  }

  function inst_reaper_activate() {
    inst_reaper_database_tables_setup();
    $inst_reaper_options = array(
      'chron' => false,
      'query' => 'hashtag',
      'hashtag' => '',
      'username' => '',
      'user_id' => '',
      'count' => 50,
      'recurrence' => ''
    );
    if (!get_option('inst_reaper_options')) {
      update_option('inst_reaper_options', $inst_reaper_options);
    }
  }
 
  function inst_reaper_cron_add_schedules( $schedules ) {
   // Adds once weekly to the existing schedules.
    $schedules['minutely'] = array(
      'interval' => 60,
      'display' => __( 'Once a Minute' )
    );
    $schedules['weekly'] = array(
      'interval' => 604800,
      'display' => __( 'Weekly' )
    );
    $schedules['half_hour'] = array(
      'interval' => 1800,
      'display' => __( 'Every Half Hour')
    );
    return $schedules;
  }
  add_filter( 'cron_schedules', 'inst_reaper_cron_add_schedules' );

  add_action('inst_reaper_event', 'inst_reaper_save_photos');


  register_activation_hook(__FILE__, 'inst_reaper_activate');

  add_action( 'admin_menu', 'inst_reaper_menu_pages_init' );

  function inst_reaper_menu_pages_init(){
    add_menu_page(
      __('Instagram Reaper'),
      __('Instagram Reaper'), 
      'manage_options',
      'instagram_reaper/lib/inst_reaper_results.php',
      '',
      plugins_url('instagram_reaper/assets/sickle.png')
    ); 
    add_submenu_page( 
      'instagram_reaper/lib/inst_reaper_results.php', 
      'Reaper Options', 
      'Reaper Options', 
      'manage_options', 
      'instagram_reaper/lib/inst_reaper_options.php', 
      ''
    );
  }



  register_deactivation_hook( __FILE__, 'inst_reaper_prefix_deactivation' );
  /**
   * On deactivation, remove all functions from the scheduled action hook.
   */
  function inst_reaper_prefix_deactivation() {
    wp_clear_scheduled_hook( 'inst_reaper_event' );
  }

  function styles() {
    wp_enqueue_style( 'styles', plugins_url('/assets/inst_reaper_styles.css', __FILE__));
  }

  add_action('admin_print_styles', 'styles');

?>
