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
    echo '<div class="wrap"><h1>Flow Music Player</h1><p>Welcome to the Flow Music Player plugin.</p></div>';
}

function fmp_share_dashboard_page()
{
    // Implementation for the share dashboard page
    // Include charts, top shared albums, tracks, etc.
}

function fmp_share_logs_page()
{
    // Implementation for the share logs page
    // Display a table of share logs with filtering options
}
