<?php

// function debug_registered_post_types()
// {
//     $post_types = get_post_types(['public' => true], 'names');
//     error_log('Registered public post types: ' . print_r($post_types, true));
// }
// add_action('init', 'debug_registered_post_types', 999);

// function test_direct_cpt_query()
// {
//     $args = array(
//         'post_type' => 'track',
//         'post_status' => 'publish',
//         'posts_per_page' => -1,
//     );
//     $query = new WP_Query($args);
//     error_log('Direct CPT Query Post Count: ' . $query->post_count);
//     error_log('Direct CPT Query SQL: ' . $query->request);
// }
// add_action('wp_footer', 'test_direct_cpt_query');

// function debug_elementor_query($query)
// {
//     if (defined('ELEMENTOR_VERSION')) {
//         error_log('Elementor Query: ' . print_r($query->query_vars, true));
//         error_log('Elementor SQL: ' . $query->request);
//     }
// }
// add_action('pre_get_posts', 'debug_elementor_query');

/*
 * Debugging...
 */
// function debug_elementor_query($query) {
//     if (defined('WP_DEBUG') && WP_DEBUG === true) {
//         error_log('Elementor Query: ' . print_r($query->query_vars, true));
//     }
// }
// add_action('elementor/query/query_results', 'debug_elementor_query');

// function check_published_posts() {
//     $post_types = ['album', 'track'];
//     foreach ($post_types as $post_type) {
//         $counts = wp_count_posts($post_type);
//         $publish_count = isset($counts->publish) ? $counts->publish : 0;
//         error_log("Published {$post_type}s: {$publish_count}");
//     }
// }
// add_action('init', 'check_published_posts');