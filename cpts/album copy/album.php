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
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-album',
        'publicly_queryable' => true,
        'query_var'          => true,
        'hierarchical'       => false,
        'capability_type'    => 'post',
        'exclude_from_search' => false,
    );

    register_post_type('album', $args);

    // Register Meta Fields
    $meta_fields = array(
        'album_year' => array('type' => 'string'),
        'album_artist' => array('type' => 'string'),
        'album_download_link' => array('type' => 'string'),
        'album_tracks' => array(
            'type' => 'array',
            'sanitize_callback' => 'sanitize_album_tracks',
            'show_in_rest' => array(
                'schema' => array(
                    'type'  => 'array',
                    'items' => array(
                        'type'       => 'object',
                        'properties' => array(
                            'title'  => array('type' => 'string'),
                            'number' => array('type' => 'string'),
                            'url'   => array('type' => 'string'),
                        ),
                    ),
                ),
            ),
        ),
    );

    foreach ($meta_fields as $key => $args) {
        register_post_meta('album', $key, array_merge(array(
            'single'       => true,
            'show_in_rest' => true,
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            },
        ), $args));
    }
}

// Sanitize callback for tracks
function sanitize_album_tracks($tracks)
{
    if (!is_array($tracks)) {
        return array();
    }
    foreach ($tracks as &$track) {
        $track['title'] = isset($track['title']) ? sanitize_text_field($track['title']) : '';
        $track['number'] = isset($track['number']) ? sanitize_text_field($track['number']) : '';
        $track['url'] = isset($track['url']) ? esc_url_raw($track['url']) : '';
    }
    return $tracks;
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
add_action('save_post', 'save_album_meta_box_data');

function save_album_meta_box_data($post_id)
{
    // Check if our nonce is set.
    if (!isset($_POST['album_details_meta_box_nonce']) || !isset($_POST['album_tracks_meta_box_nonce'])) {
        return;
    }

    // Verify that the nonce is valid.
    if (!wp_verify_nonce($_POST['album_details_meta_box_nonce'], 'album_details_meta_box')) {
        return;
    }
    if (!wp_verify_nonce($_POST['album_tracks_meta_box_nonce'], 'album_tracks_meta_box')) {
        return;
    }

    // Autosave check
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Permissions check
    if (isset($_POST['post_type']) && 'album' == $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    } else {
        return;
    }

    // Sanitize and save the data.

    // Save Year
    if (isset($_POST['album_year'])) {
        $year = sanitize_text_field($_POST['album_year']);
        update_post_meta($post_id, 'album_year', $year);
    }

    // Save Artist
    if (isset($_POST['album_artist'])) {
        $artist = sanitize_text_field($_POST['album_artist']);
        update_post_meta($post_id, 'album_artist', $artist);
    }

    // Save Download Link
    if (isset($_POST['album_download_link'])) {
        $download_link = esc_url_raw($_POST['album_download_link']);
        update_post_meta($post_id, 'album_download_link', $download_link);
    }

    // Save Tracks
    if (isset($_POST['tracks']) && is_array($_POST['tracks'])) {
        $tracks = array();
        foreach ($_POST['tracks'] as $index => $track) {
            // Skip if all fields are empty
            $track_title = isset($track['title']) ? sanitize_text_field($track['title']) : '';
            $track_number = isset($track['number']) ? sanitize_text_field($track['number']) : '';
            $track_url = isset($track['url']) ? esc_url_raw($track['url']) : '';

            if (empty($track_title) && empty($track_number) && empty($track_url)) {
                continue; // Skip empty tracks
            }

            $tracks[] = array(
                'title' => $track_title,
                'number' => $track_number,
                'url' => $track_url,
            );
        }

        if (!empty($tracks)) {
            update_post_meta($post_id, 'album_tracks', $tracks);
        } else {
            delete_post_meta($post_id, 'album_tracks');
        }
    } else {
        delete_post_meta($post_id, 'album_tracks');
    }
}

function album_details_meta_box_callback($post)
{
    // Add nonce field
    wp_nonce_field('album_details_meta_box', 'album_details_meta_box_nonce');

    // Retrieve existing values
    $year = get_post_meta($post->ID, 'album_year', true);
    $artist = get_post_meta($post->ID, 'album_artist', true);
    $download_link = get_post_meta($post->ID, 'album_download_link', true);

    echo '<p><label for="album_year">' . __('Year', 'flow-elementor-widgets') . '</label></p>';
    echo '<input type="text" id="album_year" name="album_year" value="' . esc_attr($year) . '" size="25" />';

    echo '<p><label for="album_artist">' . __('Artist', 'flow-elementor-widgets') . '</label></p>';
    echo '<input type="text" id="album_artist" name="album_artist" value="' . esc_attr($artist) . '" size="25" />';

    // Made the Download Link field longer by adding a style
    echo '<p><label for="album_download_link">' . __('Download Link', 'flow-elementor-widgets') . '</label></p>';
    echo '<input type="url" id="album_download_link" name="album_download_link" value="' . esc_attr($download_link) . '" style="width: 100%;" />';
}

function album_tracks_meta_box_callback($post)
{
    // Add nonce field
    wp_nonce_field('album_tracks_meta_box', 'album_tracks_meta_box_nonce');

    // Retrieve existing tracks
    $tracks = get_post_meta($post->ID, 'album_tracks', true);
    $tracks = is_array($tracks) ? $tracks : array();

    echo '<div id="tracks_container">';

    foreach ($tracks as $index => $track) {
        $track_number = $index + 1;
        $track_title = !empty($track['title']) ? esc_html($track['title']) : '(No Title)';

        echo '<div class="track-item" data-track-index="' . $index . '">';
        // Track header with collapsible functionality
        echo '<div class="track-header">';
        echo '<span class="track-title">' . 'Track ' . $track_number . ': ' . $track_title . '</span>';
        echo '<span class="toggle-icon dashicons dashicons-arrow-right"></span>';
        echo '</div>'; // .track-header

        // Collapsible content
        echo '<div class="track-content" style="display: none;">';
        // Track Number
        echo '<p><label>' . __('Track Number', 'flow-elementor-widgets') . '</label><br>';
        echo '<input type="text" class="track-number" name="tracks[' . $index . '][number]" value="' . esc_attr($track['number']) . '" /></p>';
        // Track Title
        echo '<p><label>' . __('Track Title', 'flow-elementor-widgets') . '</label><br>';
        echo '<input type="text" class="track-title-input" name="tracks[' . $index . '][title]" value="' . esc_attr($track['title']) . '" /></p>';
        // Track File URL
        echo '<p><label>' . __('Track File URL', 'flow-elementor-widgets') . '</label><br>';
        echo '<input type="url" class="track_file" name="tracks[' . $index . '][url]" value="' . esc_attr($track['url']) . '" /></p>';
        // Buttons
        echo '<div class="track-buttons">';
        echo '<button type="button" class="select-track-file button">' . __('Select File', 'flow-elementor-widgets') . '</button> ';
        echo '<button type="button" class="remove-track button">' . __('Remove Track', 'flow-elementor-widgets') . '</button>';
        echo '</div>';
        echo '</div>'; // .track-content
        echo '</div>'; // .track-item
    }

    echo '</div>'; // #tracks_container
    echo '<button type="button" id="add-track" class="button">' . __('Add Track', 'flow-elementor-widgets') . '</button>';

    // Hidden template for new tracks
    echo '<div id="track_template" style="display: none;">';
    echo '<div class="track-item" data-track-index="__index__">';
    echo '<div class="track-header">';
    echo '<span class="track-title">Track __track_number__: (No Title)</span>';
    echo '<span class="toggle-icon dashicons dashicons-arrow-right"></span>';
    echo '</div>'; // .track-header

    echo '<div class="track-content" style="display: none;">';
    // Track Number
    echo '<p><label>' . __('Track Number', 'flow-elementor-widgets') . '</label><br>';
    echo '<input type="text" class="track-number" data-name="tracks[__index__][number]" value="" /></p>';
    // Track Title
    echo '<p><label>' . __('Track Title', 'flow-elementor-widgets') . '</label><br>';
    echo '<input type="text" class="track-title-input" data-name="tracks[__index__][title]" value="" /></p>';
    // Track File URL
    echo '<p><label>' . __('Track File URL', 'flow-elementor-widgets') . '</label><br>';
    echo '<input type="url" class="track_file" data-name="tracks[__index__][url]" value="" /></p>';
    // Buttons
    echo '<div class="track-buttons">';
    echo '<button type="button" class="select-track-file button">' . __('Select File', 'flow-elementor-widgets') . '</button> ';
    echo '<button type="button" class="remove-track button">' . __('Remove Track', 'flow-elementor-widgets') . '</button>';
    echo '</div>';
    echo '</div>'; // .track-content
    echo '</div>'; // .track-item
    echo '</div>'; // #track_template
}
