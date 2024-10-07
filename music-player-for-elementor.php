<?php
/*
Plugin Name: Flow Music Player for Elementor
Description: Music Player for Elementor: MP3 Audio Player & Podcast Player
Version: 1.0
Author: Joseph Mills
*/

// namespace Flow_Widgets_For_Elementor;

use Flow_Widgets_For_Elementor\Widgets\Flow_Audio_Playlist_Widget;
use Flow_Widgets_For_Elementor\Widgets\Flow_Audio_Track_Player_Widget;

if (! defined('ABSPATH')) exit; // Exit if accessed directly

// require 'assets/util/audio_utils.php';

// add_action('init_get_audio_length', 'get_audio_length');
// add_action('init', 'get_audio_length');

function music_player_for_elementor_init()
{
    // Check if Elementor is activated
    function flow_audio_playlist_is_elementor_active()
    {
        return defined('ELEMENTOR_VERSION');
    }

    // Register the widget with Elementor
    function register_flow_audio_widgets($widgets_manager)
    {
        require_once(__DIR__ . '/widgets/audio_playlist/audio-playlist-widget.php');
        require_once(__DIR__ . '/widgets/audio_track_player/audio-track-player-widget.php');

        try {
            $widgets_manager->register_widget_type(new Flow_Audio_Playlist_Widget());
            $widgets_manager->register_widget_type(new Flow_Audio_Track_Player_Widget());
        } catch (Exception $e) {
            error_log('Error registering Flow Audio widgets: ' . $e->getMessage());
        }
    }

    // Hook into Elementor widget registration
    add_action('elementor/widgets/widgets_registered', 'register_flow_audio_widgets');

    // Enqueue scripts
    function flow_audio_playlist_enqueue_scripts()
    {
        $script_path = plugin_dir_path(__FILE__) . 'assets/js/script.js';
        // Enqueue JavaScript file
        wp_enqueue_script(
            'general-script', // Handle
            plugin_dir_url(__FILE__) . 'assets/js/script.js', // Path to JS file
            array('jquery'), // Dependencies
            filemtime($script_path), // Version number
            true // Load in footer
        );
    }
    add_action('wp_enqueue_scripts', 'flow_audio_playlist_enqueue_scripts');

    // function flow_audio_playlist_enqueue_styles()
    // {
    // $style_path = plugins_url('assets/css/style.css', __FILE__);
    // // Enqueue stylesheets
    // wp_enqueue_style(
    //     'general-style', // Handle
    //     $style_path, // Path to CSS file
    //     array(), // Dependencies (none in this case)
    //     filemtime($style_path), // Version number
    //     'all' // Media type
    // );

    // wp_enqueue_style(
    //     'tailwind_css_for_audio_playlist',
    //     plugins_url('widgets/audio_playlist/src/tailwind.css', __FILE__),
    //     [],
    //     '1.0.0',
    // );
    // wp_enqueue_style(
    //     'loading-overlay-style',
    //     plugins_url('widgets/audio_playlist/src/style.css', __FILE__),
    //     [],
    //     '1.0.0'
    // );
    // }
    // add_action('wp_enqueue_styles', 'flow_audio_playlist_enqueue_styles');


    // Add Custom Category
    function add_custom_widget_categories($elements_manager)
    {

        $elements_manager->add_category(
            'flow',
            [
                'title' => esc_html__('Flow', 'flow'),
                'icon' => 'fa-cross',
            ]
        );
    }
    add_action('elementor/elements/categories_registered', 'add_custom_widget_categories');

    // add_action( 'admin_menu', 'settings_page' );

    // function settings_page() {
    //     add_menu_page(
    //         'Flow Elementor Widgets',     // Page title
    //         'Flow',              // Menu title
    //         'manage_options',         // Capability
    //         'flow-elementor-widgets',     // Menu slug
    //         'my_plugin_render_settings_page' // Function to display the page content
    //     );
    // }

    require_once(__DIR__ . '/cpts/album/album.php');
    require_once(__DIR__ . '/cpts/track/track.php');
    require_once(__DIR__ . '/cpts/cpt-util.php');
    require_once(__DIR__ . '/dynamic_tags/dynamic-tags.php');
    require_once(__DIR__ . '/debug.php');
}
add_action('plugins_loaded', 'music_player_for_elementor_init', 20);
