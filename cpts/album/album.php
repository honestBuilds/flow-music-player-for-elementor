<?php
// Register Custom Post Type and Meta Fields

use function PHPSTORM_META\type;

add_action('init', 'register_album_post_type_and_meta');

function register_album_post_type_and_meta()
{
    // Register Custom Post Type
    $labels = array(
        'name'               => _x('Albums', 'post type general name', 'flow-elementor-widgets'),
        'singular_name'      => _x('Album', 'post type singular name', 'flow-elementor-widgets'),
        'menu_name'          => _x('Albums', 'admin menu', 'flow-elementor-widgets'),
        'name_admin_bar'     => _x('Album', 'add new on admin bar', 'flow-elementor-widgets'),
        'add_new'            => _x('Add New', 'album', 'flow-elementor-widgets'),
        'add_new_item'       => __('Add New Album', 'flow-elementor-widgets'),
        'new_item'           => __('New Album', 'flow-elementor-widgets'),
        'edit_item'          => __('Edit Album', 'flow-elementor-widgets'),
        'view_item'          => __('View Album', 'flow-elementor-widgets'),
        'all_items'          => __('All Albums', 'flow-elementor-widgets'),
        'search_items'       => __('Search Albums', 'flow-elementor-widgets'),
        'parent_item_colon'  => __('Parent Albums:', 'flow-elementor-widgets'),
        'not_found'          => __('No albums found.', 'flow-elementor-widgets'),
        'not_found_in_trash' => __('No albums found in Trash.', 'flow-elementor-widgets')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => array('slug' => 'album'),
        'supports'           => array('title', 'thumbnail'),
        'show_in_menu'       => true,
        'show_ui'            => true,
        'show_in_rest'       => true,
        'menu_position'      => 32,
        'menu_icon'          => 'dashicons-album',
        'publicly_queryable' => true,
        'query_var'          => true,
        'hierarchical'       => false,
        'capability_type'    => 'post',
        'exclude_from_search' => false,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'hierarchical' => false,
    );

    register_post_type('album', $args);

    // Register Meta Fields
    $meta_fields = array(
        'album_year' => array(
            'type' => 'string',
            'description' => 'Album Release Year',
            'single' => true,
            'show_in_rest' => true,
        ),
        'album_location' => array(
            'type' => 'string',
            'description' => 'Album Location',
            'single' => true,
            'show_in_rest' => true,
        ),
        'album_download_link' => array(
            'type' => 'string',
            'description' => 'Album Download Link',
            'single' => true,
            'show_in_rest' => true,
        ),
        'album_tracks' => array(
            'type' => 'string', // Change to string for simplicity
            'description' => 'Album Tracks',
            'single' => true,
            'show_in_rest' => true,
        ),
    );

    foreach ($meta_fields as $key => $args) {
        register_post_meta('album', $key, array_merge($args, array(
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            },
        )));
    }
}

// Sanitize callback for tracks
function sanitize_album_tracks($tracks)
{
    album_error_log('Sanitizing tracks: ' . print_r($tracks, true));

    if (!is_array($tracks)) {
        album_error_log('Tracks is not an array. Converting to array.');
        $tracks = array($tracks);
    }

    $sanitized_tracks = array();
    foreach ($tracks as $track_id) {
        $track_id = intval($track_id);
        if ($track_id > 0) {
            $sanitized_tracks[] = $track_id;
        }
    }

    album_error_log('Sanitized tracks: ' . print_r($sanitized_tracks, true));
    return $sanitized_tracks;
}

// Add Meta Boxes
add_action('add_meta_boxes', 'add_album_meta_boxes');

function add_album_meta_boxes()
{
    // Album Details Meta Box
    add_meta_box(
        'album_details',
        __('Album Details', 'flow-elementor-widgets'),
        'album_details_meta_box_callback',
        'album',
        'normal',
        'high'
    );

    // Tracks Meta Box
    add_meta_box(
        'album_tracks',
        __('Tracks', 'flow-elementor-widgets'),
        'album_tracks_meta_box_callback',
        'album',
        'normal',
        'high'
    );
}

// Enqueue scripts and styles
function album_admin_scripts($hook)
{
    global $post_type;

    if (('post.php' == $hook || 'post-new.php' == $hook) && 'album' === $post_type) {
        wp_enqueue_media();

        $script_path = plugin_dir_path(__FILE__) . 'src/album-admin.js';
        $style_path = plugin_dir_path(__FILE__) . 'src/album-admin.css';

        wp_enqueue_script(
            'album-admin-script',
            plugin_dir_url(__FILE__) . 'src/album-admin.js',
            array('jquery'),
            filemtime($script_path),
            true
        );
        wp_localize_script('album-admin-script', 'album_admin_vars', array(
            'nonce' => wp_create_nonce('album_tracks_nonce'),
            'new_track_url' => admin_url('post-new.php?post_type=track')
        ));
        wp_enqueue_style(
            'album-admin-style',
            plugin_dir_url(__FILE__) . 'src/album-admin.css',
            array(),
            filemtime($style_path)
        );
    }
}
add_action('admin_enqueue_scripts', 'album_admin_scripts');

// Save post data
add_action('save_post_album', 'save_album_meta_box_data');

function save_album_meta_box_data($post_id)
{
    // Check if our nonce is set.
    if (!isset($_POST['album_details_meta_box_nonce']) || !isset($_POST['album_tracks_meta_box_nonce'])) {
        album_error_log('Nonce not set for post ID: ' . $post_id);
        return;
    }

    // Verify that the nonce is valid.
    if (
        !wp_verify_nonce($_POST['album_details_meta_box_nonce'], 'album_details_meta_box') ||
        !wp_verify_nonce($_POST['album_tracks_meta_box_nonce'], 'album_tracks_meta_box')
    ) {
        album_error_log('Nonce verification failed for post ID: ' . $post_id);
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permissions.
    if (isset($_POST['post_type']) && 'album' == $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            album_error_log('User does not have permission to edit post ID: ' . $post_id);
            return;
        }
    } else {
        return;
    }

    // OK, it's safe for us to save the data now.

    // Save Year
    if (isset($_POST['album_year'])) {
        $year = sanitize_text_field($_POST['album_year']);
        update_post_meta($post_id, 'album_year', $year);
    }

    // Save Location
    if (isset($_POST['album_location'])) {
        $location = sanitize_text_field($_POST['album_location']);
        update_post_meta($post_id, 'album_location', $location);
    }

    // Save Download Link
    if (isset($_POST['album_download_link'])) {
        $download_link = esc_url_raw($_POST['album_download_link']);
        update_post_meta($post_id, 'album_download_link', $download_link);
    }

    // Save Tracks
    album_error_log('Saving tracks for post ID: ' . $post_id);
    album_error_log('POST data: ' . print_r($_POST, true));

    if (isset($_POST['tracks'])) {
        $tracks = sanitize_album_tracks($_POST['tracks']);
        $update_result = update_post_meta($post_id, 'album_tracks', $tracks);
        album_error_log('Update result: ' . ($update_result ? 'success' : 'failure'));
    } else {
        album_error_log('No tracks data found. Deleting meta.');
        delete_post_meta($post_id, 'album_tracks');
    }

    album_error_log('Finished saving album meta data');
}

function album_details_meta_box_callback($post)
{
    // Add nonce field
    wp_nonce_field('album_details_meta_box', 'album_details_meta_box_nonce');

    // Retrieve existing values
    $year = get_post_meta($post->ID, 'album_year', true);
    $location = get_post_meta($post->ID, 'album_location', true);
    $download_link = get_post_meta($post->ID, 'album_download_link', true);

    echo '<p><label for="album_year">' . __('Year', 'flow-elementor-widgets') . '</label></p>';
    echo '<input type="text" id="album_year" name="album_year" value="' . esc_attr($year) . '" size="25" />';

    echo '<p><label for="album_location">' . __('Location', 'flow-elementor-widgets') . '</label></p>';
    echo '<input type="text" id="album_location" name="album_location" value="' . esc_attr($location) . '" size="25" />';

    // Made the Download Link field longer by adding a style
    echo '<p><label for="album_download_link">' . __('Download Link', 'flow-elementor-widgets') . '</label></p>';
    echo '<input type="url" id="album_download_link" name="album_download_link" value="' . esc_attr($download_link) . '" style="width: 100%;" />';
}

function album_tracks_meta_box_callback($post)
{
    wp_nonce_field('album_tracks_meta_box', 'album_tracks_meta_box_nonce');

    $tracks = get_post_meta($post->ID, 'album_tracks', true);
    album_error_log('Retrieved tracks for post ID ' . $post->ID . ': ' . print_r($tracks, true));

    if (!is_array($tracks)) {
        album_error_log('Tracks is not an array. Setting to empty array.');
        $tracks = array();
    }

    echo '<div class="album-tracks-wrapper">';

    echo '<div id="tracks_container">';
    foreach ($tracks as $track_id) {
        $track = get_post($track_id);
        if ($track) {
            echo '<div class="track-item" data-track-id="' . esc_attr($track_id) . '">';
            echo '<span>' . esc_html($track->post_title) . '</span>';
            echo '<input type="hidden" name="tracks[]" value="' . esc_attr($track_id) . '">';
            echo '<button type="button" class="remove-track button">Remove</button>';
            echo '</div>';
        }
    }
    echo '</div>';

    echo '<input type="text" id="track_search" placeholder="Search for tracks...">';
    echo '<div id="track_search_results"></div>';
    echo '<button type="button" id="add_new_track" class="button">Add New Track</button>';

    echo '</div>';
}

function search_tracks_callback()
{
    check_ajax_referer('album_tracks_nonce', 'nonce');

    $query = sanitize_text_field($_GET['query']);
    $args = array(
        'post_type' => 'track',
        'post_status' => 'publish',
        's' => $query,
        'posts_per_page' => 10
    );

    $tracks = get_posts($args);

    if ($tracks) {
        foreach ($tracks as $track) {
            echo '<div class="track-search-result">';
            echo '<span>' . esc_html($track->post_title) . '</span>';
            echo '<button type="button" class="add-track-to-album button" data-track-id="' . esc_attr($track->ID) . '" data-track-title="' . esc_attr($track->post_title) . '">Add</button>';
            echo '</div>';
        }
    } else {
        echo '<div class="track-search-result">No tracks found.</div>';
    }

    wp_die();
}
add_action('wp_ajax_search_tracks', 'search_tracks_callback');

function album_error_log($message)
{
    if (defined('WP_DEBUG') && WP_DEBUG === true) {
        error_log('Album CPT Error: ' . $message);
    }
}

// Album Artist Taxonomy
require_once('album-artist-taxonomy.php');
