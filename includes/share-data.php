<?php

function fmp_get_total_shares()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'fmp_share_log';
    return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
}

function fmp_get_shares_by_cpt()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'fmp_share_log';
    $results = $wpdb->get_results("SELECT post_type, COUNT(*) as count FROM $table_name GROUP BY post_type");
    $shares_by_cpt = array();
    foreach ($results as $result) {
        $shares_by_cpt[$result->post_type] = $result->count;
    }
    return $shares_by_cpt;
}

function fmp_get_top_shared_posts($limit = 10)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'fmp_share_log';
    return $wpdb->get_results($wpdb->prepare(
        "SELECT post_id, post_type, COUNT(*) as share_count 
        FROM $table_name 
        GROUP BY post_id 
        ORDER BY share_count DESC 
        LIMIT %d",
        $limit
    ));
}

function fmp_get_share_data_for_chart($time_range = 'month')
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'fmp_share_log';

    switch ($time_range) {
        case 'today':
            $group_by = "DATE_FORMAT(shared_at, '%H:00')";
            $where = "shared_at >= CURDATE()";
            break;
        case 'week':
            $group_by = "DATE(shared_at)";
            $where = "shared_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $group_by = "DATE(shared_at)";
            $where = "shared_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
            break;
        case 'year':
            $group_by = "DATE_FORMAT(shared_at, '%Y-%m')";
            $where = "shared_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
            break;
        default:
            $group_by = "DATE_FORMAT(shared_at, '%Y-%m')";
            $where = "1=1";
    }

    $results = $wpdb->get_results("
        SELECT $group_by as label, COUNT(*) as count 
        FROM $table_name 
        WHERE $where 
        GROUP BY label 
        ORDER BY shared_at
    ");

    $labels = array();
    $data = array();
    foreach ($results as $result) {
        $labels[] = $result->label;
        $data[] = $result->count;
    }

    return array('labels' => $labels, 'data' => $data);
}

function fmp_get_share_logs($offset, $per_page, $filters)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'fmp_share_log';

    $where = array('1=1');
    if (!empty($filters['post_type'])) {
        $where[] = $wpdb->prepare("post_type = %s", $filters['post_type']);
    }
    if (!empty($filters['date_from'])) {
        $where[] = $wpdb->prepare("shared_at >= %s", $filters['date_from']);
    }
    if (!empty($filters['date_to'])) {
        $where[] = $wpdb->prepare("shared_at <= %s", $filters['date_to']);
    }

    $where_clause = implode(' AND ', $where);

    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name 
        WHERE $where_clause 
        ORDER BY shared_at DESC 
        LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ));
}

function fmp_get_total_share_logs($filters)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'fmp_share_log';

    $where = array('1=1');
    if (!empty($filters['post_type'])) {
        $where[] = $wpdb->prepare("post_type = %s", $filters['post_type']);
    }
    if (!empty($filters['date_from'])) {
        $where[] = $wpdb->prepare("shared_at >= %s", $filters['date_from']);
    }
    if (!empty($filters['date_to'])) {
        $where[] = $wpdb->prepare("shared_at <= %s", $filters['date_to']);
    }

    $where_clause = implode(' AND ', $where);

    return $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where_clause");
}
