<?php
function fmp_create_database_tables()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'fmp_share_log';

    error_log("Attempting to create or update table: $table_name");

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL,
        post_type varchar(20) NOT NULL,
        ip_address varchar(45) NOT NULL,
        country varchar(2) NOT NULL,
        shared_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(wp_normalize_path(admin_url('includes/upgrade.php')));
    $result = dbDelta($sql);

    error_log("dbDelta result: " . print_r($result, true));

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        error_log("Failed to create table: $table_name");
        error_log("Last SQL error: " . $wpdb->last_error);
    } else {
        error_log("Table $table_name created successfully or already exists");
    }
}
