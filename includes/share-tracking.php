<?php

use GeoIp2\Database\Reader;

function fmp_log_share()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'fmp_share_log';

    $post_id = intval($_POST['post_id']);
    $post_type = sanitize_text_field($_POST['post_type']);
    $ip_address = fmp_get_client_ip();
    $country = fmp_get_country_from_ip($ip_address);

    $result = $wpdb->insert(
        $table_name,
        array(
            'post_id' => $post_id,
            'post_type' => $post_type,
            'ip_address' => $ip_address,
            'country' => $country,
        ),
        array('%d', '%s', '%s', '%s')
    );

    wp_send_json_success($result);
}

function fmp_get_client_ip()
{
    $ip_address = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }
    return $ip_address;
}

function fmp_get_country_from_ip($ip_address)
{
    try {
        // Path to the GeoLite2 database file
        $database_path = plugin_dir_path(__FILE__) . '../assets/geoip/GeoLite2-Country.mmdb';

        // Create a new Reader instance
        $reader = new Reader($database_path);

        // Look up the IP address
        $record = $reader->country($ip_address);

        // Return the country code
        return $record->country->isoCode;
    } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
        // IP address not found in the database
        error_log("IP address not found: " . $e->getMessage());
        return 'Unknown';
    } catch (\Exception $e) {
        // Other errors (e.g., invalid database or IP address)
        error_log("Error in GeoIP lookup: " . $e->getMessage());
        return 'Error';
    }
}

// Add share count column to CPT list
function fmp_add_share_count_column($columns)
{
    $columns['share_count'] = 'Shares';
    return $columns;
}
add_filter('manage_track_posts_columns', 'fmp_add_share_count_column');
add_filter('manage_album_posts_columns', 'fmp_add_share_count_column');

function fmp_display_share_count_column($column, $post_id)
{
    if ($column === 'share_count') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fmp_share_log';

        // Check if the table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            echo 'N/A';
            return;
        }

        $share_count = fmp_get_share_count($post_id);
        echo $share_count;
    }
}
add_action('manage_track_posts_custom_column', 'fmp_display_share_count_column', 10, 2);
add_action('manage_album_posts_custom_column', 'fmp_display_share_count_column', 10, 2);

function fmp_get_share_count($post_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'fmp_share_log';

    // Check if the table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        error_log("FMP: Share log table does not exist. Attempting to create it.");
        fmp_create_database_tables();
        return 0; // Return 0 if the table didn't exist
    }

    return $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE post_id = %d",
        $post_id
    ));
}

// Add share count meta box to CPT edit page
function fmp_add_share_count_meta_box()
{
    add_meta_box(
        'fmp_share_count_meta_box',
        'Share Count',
        'fmp_render_share_count_meta_box',
        array('track', 'album'),
        'side',
        'low'
    );
}
add_action('add_meta_boxes', 'fmp_add_share_count_meta_box');

function fmp_render_share_count_meta_box($post)
{
    $share_count = fmp_get_share_count($post->ID);
    echo "<p>This post has been shared $share_count times.</p>";
}
