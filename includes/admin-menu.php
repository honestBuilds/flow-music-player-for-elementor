<?php
function fmp_add_admin_menu()
{
    add_menu_page(
        'Flow Music Player',
        'Flow Music Player',
        'manage_options',
        'flow-music-player',
        'fmp_main_page',
        'dashicons-playlist-audio',
        30
    );

    add_submenu_page(
        'flow-music-player',
        'Share Dashboard',
        'Share Dashboard',
        'manage_options',
        'fmp-share-dashboard',
        'fmp_share_dashboard_page'
    );

    add_submenu_page(
        'flow-music-player',
        'Share Logs',
        'Share Logs',
        'manage_options',
        'fmp-share-logs',
        'fmp_share_logs_page'
    );
}
add_action('admin_menu', 'fmp_add_admin_menu');

// Ensure CPT menus are positioned correctly
function fmp_reorder_cpt_menus()
{
    global $menu;

    // Find the position of the Flow Music Player menu
    $fmp_position = null;
    foreach ($menu as $position => $item) {
        if ($item[2] === 'flow-music-player') {
            $fmp_position = $position;
            break;
        }
    }

    if ($fmp_position !== null) {
        // Move Track and Album CPT menus
        $cpts = ['track', 'album'];
        $offset = 1;

        foreach ($cpts as $cpt) {
            foreach ($menu as $position => $item) {
                if (isset($item[2]) && $item[2] === "edit.php?post_type=$cpt") {
                    // Remove the menu item from its current position
                    $cpt_menu = $menu[$position];
                    unset($menu[$position]);

                    // Insert it at the new position
                    $new_position = $fmp_position + $offset;
                    $menu[$new_position] = $cpt_menu;
                    $offset++;
                    break;
                }
            }
        }

        // Re-sort the menu
        ksort($menu);
    }
}
// add_action('admin_menu', 'fmp_reorder_cpt_menus', 99);

function fmp_main_page()
{
    // Get all albums
    $args = array(
        'post_type' => 'album',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    );
    $albums = get_posts($args);

    // Handle CSV download
    if (isset($_POST['download_csv'])) {
        // Clear any previous output
        ob_clean();

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="albums.csv"');

        // Create output stream
        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel compatibility
        fputs($output, "\xEF\xBB\xBF");

        // Write headers
        fputcsv($output, array('#', 'Album Title', 'Number of Tracks'));

        // Write album data
        foreach ($albums as $index => $album) {
            $tracks = get_posts(array(
                'post_type' => 'track',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'album',
                        'value' => $album->ID
                    )
                )
            ));
            fputcsv($output, array($index + 1, $album->post_title, count($tracks)));
        }

        fclose($output);
        exit();
    }

    // Regular page display
?>
    <div class="wrap">
        <h1>Flow Music Player</h1>
        <p>Welcome to the Flow Music Player plugin.</p>

        <form method="post">
            <button type="submit" name="download_csv" class="button button-primary">Download Albums CSV</button>
        </form>
    </div>
<?php
}

function fmp_share_dashboard_page()
{
    // Enqueue Chart.js and custom CSS
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), null, true);

    $css_path = plugin_dir_path(__FILE__) . '../assets/css/admin-style.css';
    wp_enqueue_style(
        'fmp-admin-style',
        plugins_url('assets/css/admin-style.css', dirname(__FILE__)),
        [],             // Dependencies
        filemtime($css_path)
    );

    // Fetch data
    $total_shares = fmp_get_total_shares();
    $shares_by_cpt = fmp_get_shares_by_cpt();
    $top_shared_posts = fmp_get_top_shared_posts(10);
    $share_data_json = json_encode(fmp_get_share_data_for_chart());

    // Display the dashboard
?>
    <div class="wrap fmp-dashboard">
        <h1>Share Dashboard</h1>

        <div class="fmp-dashboard-cards">
            <div class="fmp-card">
                <h2>Total Shares</h2>
                <p class="fmp-big-number"><?php echo number_format($total_shares); ?></p>
            </div>
            <?php foreach ($shares_by_cpt as $cpt => $count): ?>
                <div class="fmp-card">
                    <h2><?php echo ucfirst($cpt); ?> Shares</h2>
                    <p class="fmp-big-number"><?php echo number_format($count); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="fmp-dashboard-chart">
            <h3>Shares Over Time</h3>
            <select id="timeRangeSelect">
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="year">This Year</option>
                <option value="all">All Time</option>
            </select>
            <div class="chart-container">
                <canvas id="sharesChart"></canvas>
            </div>
        </div>

        <div class="fmp-dashboard-top-posts">
            <h3>Top 10 Shared Posts</h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Post Title</th>
                        <th>Post Type</th>
                        <th>Shares</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_shared_posts as $post): ?>
                        <tr>
                            <td><?php echo get_the_title($post->post_id); ?></td>
                            <td><?php echo $post->post_type; ?></td>
                            <td><?php echo $post->share_count; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('sharesChart').getContext('2d');
            var shareData = <?php echo $share_data_json; ?>;
            var chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: shareData.labels,
                    datasets: [{
                        label: 'Shares',
                        data: shareData.data,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            document.getElementById('timeRangeSelect').addEventListener('change', function() {
                var timeRange = this.value;
                // AJAX call to get new data based on time range
                // Update chart with new data
            });
        });
    </script>
<?php
}

function fmp_share_logs_page()
{
    // Handle filtering
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 20;
    $offset = ($page - 1) * $per_page;

    $filters = array(
        'post_type' => isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '',
        'date_from' => isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '',
        'date_to' => isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '',
    );

    // Fetch logs
    $logs = fmp_get_share_logs($offset, $per_page, $filters);
    $total_logs = fmp_get_total_share_logs($filters);

    // Display the logs page
?>
    <div class="wrap">
        <h1>Share Logs</h1>

        <form method="get" action="<?php echo admin_url('admin.php'); ?>">
            <input type="hidden" name="page" value="fmp-share-logs">
            <select name="post_type">
                <option value="">All Post Types</option>
                <option value="track" <?php selected($filters['post_type'], 'track'); ?>>Track</option>
                <option value="album" <?php selected($filters['post_type'], 'album'); ?>>Album</option>
            </select>
            <input type="date" name="date_from" value="<?php echo esc_attr($filters['date_from']); ?>" placeholder="From Date">
            <input type="date" name="date_to" value="<?php echo esc_attr($filters['date_to']); ?>" placeholder="To Date">
            <input type="submit" value="Filter" class="button">
        </form>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Post Title</th>
                    <th>Post Type</th>
                    <th>IP Address</th>
                    <th>Country</th>
                    <th>Shared At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo get_the_title($log->post_id); ?></td>
                        <td><?php echo $log->post_type; ?></td>
                        <td><?php echo $log->ip_address; ?></td>
                        <td><?php echo $log->country; ?></td>
                        <td><?php echo $log->shared_at; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php
        $current_url = add_query_arg($filters, admin_url('admin.php?page=fmp-share-logs'));
        echo paginate_links(array(
            'base' => $current_url . '&paged=%#%',
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => ceil($total_logs / $per_page),
            'current' => $page
        ));
        ?>
    </div>
<?php
}
