<?php
// Register Custom Post Type and Meta Fields

use function PHPSTORM_META\type;

add_action('init', 'register_track_post_type_and_meta');

function register_track_post_type_and_meta()
{
    // Register Custom Post Type
    $labels = array(
        'name'               => _x('Tracks', 'post type general name', 'flow-elementor-widgets'),
        'singular_name'      => _x('Track', 'post type singular name', 'flow-elementor-widgets'),
        'menu_name'          => _x('Tracks', 'admin menu', 'flow-elementor-widgets'),
        'name_admin_bar'     => _x('Track', 'add new on admin bar', 'flow-elementor-widgets'),
        'add_new'            => _x('Add New', 'track', 'flow-elementor-widgets'),
        'add_new_item'       => __('Add New Track', 'flow-elementor-widgets'),
        'new_item'           => __('New Track', 'flow-elementor-widgets'),
        'edit_item'          => __('Edit Track', 'flow-elementor-widgets'),
        'view_item'          => __('View Track', 'flow-elementor-widgets'),
        'all_items'          => __('All Tracks', 'flow-elementor-widgets'),
        'search_items'       => __('Search Tracks', 'flow-elementor-widgets'),
        'parent_item_colon'  => __('Parent Tracks:', 'flow-elementor-widgets'),
        'not_found'          => __('No tracks found.', 'flow-elementor-widgets'),
        'not_found_in_trash' => __('No tracks found in Trash.', 'flow-elementor-widgets')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => array('slug' => 'track'),
        'supports'           => array('title', 'thumbnail', 'category'),
        'show_in_menu'       => true,
        'show_ui'            => true,
        'show_in_rest'       => true,
        'menu_position'      => 31,
        'menu_icon'          => 'dashicons-format-audio',
        'publicly_queryable' => true,
        'query_var'          => true,
        'hierarchical'       => false,
        'capability_type'    => 'post',
        'exclude_from_search' => false,
        'show_in_nav_menus' => true,
        'hierarchical' => false,
        'show_in_admin_bar' => true,
    );

    register_post_type('track', $args);

    // Register Meta Fields
    $meta_fields = array(
        'track_url' => array('type' => 'string'),
        'track_download_link' => array('type' => 'string'),
        'track_external_url' => array('type' => 'string'),
        'track_album' => array('type' => 'string'),
    );

    foreach ($meta_fields as $key => $args) {
        register_post_meta('track', $key, array_merge(array(
            'single'       => true,
            'show_in_rest' => true,
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            },
        ), $args));
    }
}

// Add Meta Boxes
add_action('add_meta_boxes', 'add_track_meta_boxes');

function add_track_meta_boxes()
{
    // Album Details Meta Box
    add_meta_box(
        'track_details',
        __('Track Details', 'flow-elementor-widgets'),
        'track_details_meta_box_callback',
        'track',
        'normal',
        'high'
    );

    add_meta_box(
        'track_album',
        __('Album', 'flow-elementor-widgets'),
        'track_album_meta_box_callback',
        'track',
        'normal',
        'high'
    );
}

function track_details_meta_box_callback($post)
{
    // Add nonce field
    wp_nonce_field('track_details_meta_box', 'track_details_meta_box_nonce');

    // Retrieve existing values
    $download_link = get_post_meta($post->ID, 'track_download_link', true);
    $url = get_post_meta($post->ID, 'track_url', true);
    $track_url = get_post_meta($post->ID, 'track_external_url', true); // New field

    echo '<div class="track-details-wrapper">';

    echo '<p><label for="track_download_link">' . __('Download Link', 'flow-elementor-widgets') . '</label></p>';
    echo '<input type="url" id="track_download_link" name="track_download_link" value="' . esc_attr($download_link) . '" style="width: 100%;" />';

    echo '<p><label for="track_url">' . __('Audio File', 'flow-elementor-widgets') . '</label></p>';
    echo '<input type="url" id="track_url" name="track_url" value="' . esc_attr($url) . '" style="width: 100%;" />';
    echo '<button type="button" class="select-track-file button">' . __('Select File', 'flow-elementor-widgets') . '</button>';

    // New URL field
    echo '<p><label for="track_external_url">' . __('Track URL', 'flow-elementor-widgets') . '</label></p>';
    echo '<input type="url" id="track_external_url" name="track_external_url" value="' . esc_attr($track_url) . '" style="width: 100%;" />';

    echo '</div>';
}

function track_album_meta_box_callback($post)
{
    wp_nonce_field('track_album_meta_box', 'track_album_meta_box_nonce');

    $track_album_id = get_post_meta($post->ID, 'track_album', true);

    echo '<div class="track-album-wrapper">';

    echo '<div id="album_container">';
    if ($track_album_id) {
        $album = get_post($track_album_id);
        if ($album) {
            echo '<div class="album-item" data-album-id="' . esc_attr($album->ID) . '">';
            echo '<span>' . esc_html($album->post_title) . '</span>';
            echo '<input type="hidden" name="track_album" value="' . esc_attr($album->ID) . '">';
            echo '<button type="button" class="remove-album button">Remove</button>';
            echo '</div>';
        }
    }
    echo '</div>';

    echo '<input type="text" id="album_search" placeholder="Search for albums...">';
    echo '<div id="album_search_results"></div>';

    echo '</div>';
}
// Enqueue scripts and styles
function track_admin_scripts($hook)
{
    global $post_type;

    if (('post.php' == $hook || 'post-new.php' == $hook) && 'track' === $post_type) {
        wp_enqueue_media();

        $script_path = plugin_dir_path(__FILE__) . 'src/track-admin.js';
        $style_path = plugin_dir_path(__FILE__) . 'src/track-admin.css';

        wp_enqueue_script(
            'track-admin-script',
            plugin_dir_url(__FILE__) . 'src/track-admin.js',
            array('jquery'),
            filemtime($script_path),
            true
        );

        wp_localize_script(
            'track-admin-script',
            'track_admin_vars',
            array(
                'nonce' => wp_create_nonce('track_album_nonce')
            )
        );

        wp_enqueue_style(
            'track-admin-style',
            plugin_dir_url(__FILE__) . 'src/track-admin.css',
            array(),
            filemtime($style_path)
        );
    }
}
add_action('admin_enqueue_scripts', 'track_admin_scripts');

function save_track_meta_box_data($post_id)
{
    // Check if our nonce is set.
    if (!isset($_POST['track_details_meta_box_nonce']) || !isset($_POST['track_album_meta_box_nonce'])) {
        return;
    }

    // Verify that the nonce is valid.
    if (
        !wp_verify_nonce($_POST['track_details_meta_box_nonce'], 'track_details_meta_box') ||
        !wp_verify_nonce($_POST['track_album_meta_box_nonce'], 'track_album_meta_box')
    ) {
        return;
    }

    // Autosave check
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Permissions check
    if (isset($_POST['post_type']) && 'track' == $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    } else {
        return;
    }

    // Sanitize and save the data.

    // Save Download Link
    if (isset($_POST['track_download_link'])) {
        $download_link = esc_url_raw($_POST['track_download_link']);
        update_post_meta($post_id, 'track_download_link', $download_link);
    }

    // Save Audio File
    if (isset($_POST['track_url'])) {
        $url = esc_url_raw($_POST['track_url']);
        $filetype = wp_check_filetype($url);
        if (strpos($filetype['type'], 'audio/') === 0) {
            update_post_meta($post_id, 'track_url', $url);
        } else {
            // Handle error: not an audio file
            error_log("Invalid audio file type for track_url: " . $filetype['type']);
        }
    } else {
        delete_post_meta($post_id, 'track_url');
    }

    // Save Track URL
    if (isset($_POST['track_external_url'])) {
        $track_url = esc_url_raw($_POST['track_external_url']);
        update_post_meta($post_id, 'track_external_url', $track_url);
    } else {
        delete_post_meta($post_id, 'track_external_url');
    }

    // Save Track Album
    if (isset($_POST['track_album'])) {
        $album_id = intval($_POST['track_album']);

        // Get the old album ID if it exists
        $old_album_id = get_post_meta($post_id, 'track_album', true);

        // Remove track from old album if it exists
        if ($old_album_id) {
            $old_tracks = get_post_meta($old_album_id, 'album_tracks', true);
            if (is_array($old_tracks)) {
                $old_tracks = array_diff($old_tracks, array($post_id));
                update_post_meta($old_album_id, 'album_tracks', $old_tracks);
            }
        }

        // Add track to new album
        if ($album_id > 0) {
            update_post_meta($post_id, 'track_album', $album_id);

            // Update album's tracks
            $tracks = get_post_meta($album_id, 'album_tracks', true);
            if (!is_array($tracks)) {
                $tracks = array();
            }
            if (!in_array($post_id, $tracks)) {
                $tracks[] = $post_id;
                update_post_meta($album_id, 'album_tracks', $tracks);
            }
        }
    } else {
        delete_post_meta($post_id, 'track_album');
    }
}

add_action('save_post_track', 'save_track_meta_box_data');

// AJAX handler for album search
function search_albums_callback()
{
    check_ajax_referer('track_album_nonce', 'nonce');

    $query = sanitize_text_field($_GET['query']);
    $args = array(
        'post_type' => 'album',
        'post_status' => 'publish',
        's' => $query,
        'posts_per_page' => 10
    );

    $albums = get_posts($args);

    if ($albums) {
        foreach ($albums as $album) {
            echo '<div class="album-search-result">';
            echo '<span>' . esc_html($album->post_title) . '</span>';
            echo '<button type="button" class="add-album button" data-album-id="' . esc_attr($album->ID) . '" data-album-title="' . esc_attr($album->post_title) . '">Add</button>';
            echo '</div>';
        }
    } else {
        echo '<div class="album-search-result">No albums found.</div>';
    }

    wp_die();
}
add_action('wp_ajax_search_albums', 'search_albums_callback');


// Artist Taxonomy
require_once('artist-taxonomy.php');
