<?php

/*
Plugin Name: Speech
Description: Speech Plugin.
Version: 1.0
Author: Nutthaon
*/

// Enqueue script
function speech_enqueue_script() {
  wp_enqueue_style( 'speech', plugin_dir_url(__FILE__) . '/speech.css' );

  wp_enqueue_script('speech-ajax', plugin_dir_url(__FILE__) . 'speech-ajax.js', array('jquery'), '1.0', true);
  wp_localize_script('speech-ajax', 'speech_ajax_object', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'interval' => 5000 // Update interval in milliseconds (5 seconds)
  ));
}

add_action('admin_enqueue_scripts', 'speech_enqueue_script');


// Register menu page
function speech_menu_page() {
  add_menu_page(
    'Speech',       // Page title
    'Speech',       // Menu title
    'manage_options', // Capability required to access the menu page
    'speech',       // Menu slug
    'speech_display_page', // Callback function to display the menu page
    'dashicons-microphone', // Menu icon
    6               // Menu position
  );
}
add_action('admin_menu', 'speech_menu_page');

// Callback function to display the menu page
function speech_display_page() {
  echo '<div class="wrap">';
  echo '<h1>Speech</h1>';

  // Category filter
  $categories = get_categories();

  echo '<div style="display: flex; margin-bottom: 1rem;">';
  echo '<div class="speech-filter">';
  echo '<label for="speech-category">เลือกหมวดหมู่</label>';
  echo '<select id="speech-category" style="display:block;">';
  echo '<option value="">All Categories</option>';
  foreach ($categories as $category) {
    echo '<option value="' . $category->slug . '">' . $category->name . '</option>';
  }
  echo '</select>';
  echo '</div>';
  
  // Time range filter
  echo '<div class="speech-filter">';
  echo '<label for="speech-time-range">เลือกช่วงเวลา</label>';
  echo '<select id="speech-time-range" style="display:block;">';
  echo '<option value="">All Time</option>';
  echo '<option value="1">Last 1 Day</option>';
  echo '<option value="3">Last 3 Days</option>';
  echo '<option value="7">Last 7 Days</option>';
  echo '<option value="30">Last 30 Days</option>';
  echo '<option value="365">Last Year</option>';

  echo '</select>';
  echo '</div>';
  echo '</div>';
  // Display table
  echo '<table id="speech-table" class="wp-list-table widefat fixed">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>Order</th>';
  echo '<th>Image</th>';
  echo '<th>Title</th>';
  echo '<th>Category</th>';
  echo '<th>Author</th>';
  echo '<th>Date</th>';
  echo '<th>Status</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';

  echo '</tbody>';
  echo '</table>';
  echo '</div>';
}

// AJAX callback function to retrieve updated table content
// function speech_update_table_callback() {
//   $args = array(
//     'post_type' => 'post',
//     'posts_per_page' => -1
//   );
//   $articles = new WP_Query($args);
function speech_update_table_callback() {
  $category = isset($_POST['category']) ? $_POST['category'] : '';
  $timeRange = isset($_POST['time_range']) ? $_POST['time_range'] : '';

  $args = array(
    'post_type' => 'post',
    'posts_per_page' => -1,
    'category_name' => $category,
    'date_query' => array(
      array(
        'after' => '-' . $timeRange . ' days',
        'inclusive' => true,
      ),
    ),
  );

  $articles = new WP_Query($args);


  ob_start();

  // Loop through articles
  if ($articles->have_posts()) {
    $order = 1;
    while ($articles->have_posts()) {
      $articles->the_post();
      $status = get_post_status(get_the_ID());
      $image = get_the_post_thumbnail(get_the_ID(), array(150, 150));
      $categories = get_the_category();
      $category_list = '';
      foreach ($categories as $category) {
        $category_list .= $category->name . ', ';
      }
      $category_list = rtrim($category_list, ', ');
      $author = get_the_author();
      $date = get_the_date();
      $time = get_the_time();
      echo '<tr>';
      echo '<td>' . $order . '</td>';
      echo '<td>' . $image . '</td>';
      echo '<td>' . get_the_title() . '</td>';
      echo '<td>' . $category_list . '</td>';
      echo '<td>' . $author . '</td>';
      echo '<td>' . $date , ' ' , $time . '</td>';
      echo '<td>' . $status . '</td>';
      echo '</tr>';
      $order++;
    }
    wp_reset_postdata();
  } else {
    echo '<tr>';
    echo '<td colspan="7">No articles found.</td>';
    echo '</tr>';
  }

  $table_content = ob_get_clean();
  wp_send_json_success($table_content);
}
add_action('wp_ajax_speech_update_table', 'speech_update_table_callback');
add_action('wp_ajax_nopriv_speech_update_table', 'speech_update_table_callback');



