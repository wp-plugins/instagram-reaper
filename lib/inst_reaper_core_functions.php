<?php

  function inst_reaper_get_photos($array, $parse_data=true) {

    $client_id = get_option('inst_client_id');
    $args = inst_reaper_fill_in_missing_args($array);

    if(isset($array['query']) && $array['query'] == 'hashtag') {
      $url = "https://api.instagram.com/v1/tags/{$args['hashtag']}/media/recent/?client_id=$client_id&count={$args['count']}";
    } else {
      $url = "https://api.instagram.com/v1/users/{$args['user_id']}/media/recent/?client_id=$client_id&count={$args['count']}";
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);

    $return_data_array = array();


    $inst_data = json_decode($result);
    if ( $parse_data == false) {
      return $inst_data;
    } else {
      foreach($inst_data->data as $datum) {
        $image = array(
          'url' => $datum->link,
          'src' => $datum->images->standard_resolution->url,
          'src_low_res' => $datum->imgaes->low_resolution->url,
          'src_thumb' => $datum->images->thumbnail->url,
          'likes' => $datum->likes->count,
          'comments_count' => $datum->comments->count,
          'date' => $datum->created_time
        );
        $return_data_array[] = $image;
      }
      return $return_data_array;
    }
  }



  function inst_reaper_query_args_from_options() {
    $inst_options = get_option('inst_reaper_options');
    $args = array (
      'query' => $inst_options['query'],
      'user_id' => $inst_options['user_id'],
      'hashtag' => $inst_options['hashtag'],
      'username' => $inst_options['username'],
      'count' => $inst_options['count']
    );
    return $args;
  }

  function inst_reaper_fill_in_missing_args($args) {
    $inst_options = get_option('inst_reaper_options');
    $new_args = array();
    if(!isset($args['query'])) {
      return false;
    }

    $new_args['query'] = $args['query'];
    $new_args['username'] = isset($args['username']) ? $args['username'] : $inst_options['username'];

    if (isset($new_args['username']) && $new_args['username'] == $inst_options['username']) {
      $new_args['user_id'] = $inst_options['user_id'];
    } else {
      $new_args['user_id'] = isset($args['user_id']) ? $args['user_id'] : inst_reaper_get_user_id_by_name($new_args['username']);
    }
    $new_args['hashtag'] = isset($args['hashtag']) ? $args['hashtag'] : $inst_options['hashtag'];
    $new_args['count'] = isset($args['count']) ? $args['count'] : $inst_options['count'];
    return $new_args;
  }

  function inst_reaper_save_photos() {
    $args = inst_reaper_query_args_from_options();
    $inst_data = inst_reaper_get_photos($args, false);
    foreach($inst_data->data as $datum) {
      $url =$datum->link;
      $src = $datum->images->standard_resolution->url;
      $src_low_res = $datum->images->low_resolution->url;
      $src_thumb = $datum->images->thumbnail->url;
      $id = $datum->id;
      $likes_count = $datum->likes->count;
      $comment_count = $datum->comments->count;
      $date = $datum->created_time;
      inst_reaper_save_image($id, $url, $src, $src_low_res, $src_thumb, $likes_count, $comment_count, $date);
    }
  }


  function inst_reaper_has_inst($id) {
    global $wpdb;
    $rows = $wpdb->get_results($wpdb->prepare(
      "SELECT `inst_id`
       FROM " . $wpdb->prefix . 'reaper_instagram' . " 
       WHERE `inst_id` LIKE %s LIMIT 1",
       "%$id%"
    ), ARRAY_A);
    if (count($rows) == 0) {
      return false;
    } else {
      return true;
    }
  }


  function inst_reaper_save_image($id, $url, $src, $low, $thumb, $likes, $comments, $date) {
    global $wpdb;
    if (inst_reaper_has_inst($id)) {
      $sql = "UPDATE " . $wpdb->prefix . 'reaper_instagram' . " SET `likes_count`=%s, `comments_count`=%s WHERE `inst_id` = %s";
      $sql = $wpdb->prepare($sql, $likes, $comments, $id);
      $wpdb->query($sql);
      return;
    }
    $sql = "INSERT INTO " . $wpdb->prefix . 'reaper_instagram' . "  (`inst_id`, `url`, `src`, `src_low_res`, `src_thumb`, `likes_count`, `comments_count`, `date_created`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)";
    $sql = $wpdb->prepare($sql, $id, $url, $src, $low, $thumb, $likes, $comments, $date);
    $wpdb->query($sql);
  }

  function inst_reaper_get_harvest() {
    global $wpdb;
    $results = $wpdb->get_results(
      "SELECT * FROM " . $wpdb->prefix . 'reaper_instagram' . " ORDER BY `date_created` DESC", ARRAY_A
    );
    return $results;
  }

  function inst_reaper_get_harvest_in_range($start, $stop) {
    global $wpdb;
    $results = $wpdb->get_results(
      "SELECT * FROM " . $wpdb->prefix . 'reaper_instagram' . " ORDER BY `date_created` DESC LIMIT $start, $stop", ARRAY_A
    );
    return $results;
  }

  function inst_reaper_get_user_id_by_name($username) {
    $client_id = get_option('inst_client_id');
    $url = "https://api.instagram.com/v1/users/search?q=$username&client_id=$client_id";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);

    $inst_user_data = json_decode($result);
    return $inst_user_data->data[0]->id;
  }

  function inst_chron_running() {
    return true;
  }

  function inst_reaper_delete_image($id) {
    global $wpdb;
    $results = $wpdb->delete($wpdb->prefix . 'reaper_instagram', array('id' => $id));
    return true;
  }

  function stop_inst_reaper_chron() {
    wp_clear_scheduled_hook('inst_reaper_event');
    $inst_options = get_option('inst_reaper_options');
    $inst_options['chron'] = false;
    update_option('inst_reaper_options', $inst_options);
  }

