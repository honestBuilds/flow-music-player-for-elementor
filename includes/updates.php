<?php

require_once(__DIR__ . '/vendor/plugin-update-checker/plugin-update-checker.php');

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;


// Define environment constant
if (!defined('FLOW_MUSIC_PLAYER_ENV')) {
    define('FLOW_MUSIC_PLAYER_ENV', (defined('WP_DEBUG') && WP_DEBUG) ? 'development' : 'production');
}

function initialize_flow_music_player_update_checker()
{
    $myUpdateChecker = PucFactory::buildUpdateChecker(
        'https://api.github.com/repos/honestBuilds/flow-music-player-for-elementor/releases',
        __FILE__,
        'flow-music-player-for-elementor'
    );

    if (FLOW_MUSIC_PLAYER_ENV === 'production') {
        $myUpdateChecker->setCheckPeriod(24); // Check every 24 hours in production
    } else {
        $myUpdateChecker->setCheckPeriod(1); // Check every hour in development
    }

    // Store the update checker instance in a global variable
    $GLOBALS['flow_music_player_update_checker'] = $myUpdateChecker;

    // Add filter to modify plugin action links
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'flow_music_player_add_check_update_link');

    // Add action to handle the update check
    add_action('admin_post_flow_music_player_check_update', 'flow_music_player_check_update');
}


function flow_music_player_add_check_update_link($links)
{
    $check_update_url = add_query_arg(
        array(
            'action' => 'flow_music_player_check_update',
            '_wpnonce' => wp_create_nonce('flow_music_player_check_update'),
        ),
        admin_url('admin-post.php')
    );

    $check_update_link = sprintf(
        '<a href="%s">%s</a>',
        esc_url($check_update_url),
        __('Check for Updates', 'flow-music-player')
    );

    array_unshift($links, $check_update_link);
    return $links;
}

function flow_music_player_check_update()
{
    if (!current_user_can('update_plugins')) {
        wp_die(__('You do not have sufficient permissions to update plugins for this site.', 'flow-music-player'));
    }

    check_admin_referer('flow_music_player_check_update');

    $update_checker = $GLOBALS['flow_music_player_update_checker'];
    $update_checker->checkForUpdates();

    if (isset($_GET['redirect'])) {
        wp_safe_redirect($_GET['redirect']);
    } else {
        wp_safe_redirect(admin_url('plugins.php'));
    }
    exit;
}
