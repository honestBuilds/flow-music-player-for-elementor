<?php
/*
Plugin Name: Flow Elementor Widgets
Description: Custom Elementor widgets for Flow Church.
Version: 1.0
Author: Joseph Mills
*/

// namespace Flow_Widgets_For_Elementor;

use Flow_Widgets_For_Elementor\Widgets\Flow_Audio_Playlist_Widget;
use Flow_Widgets_For_Elementor\Widgets\Flow_Audio_Track_Player_Widget;
use Flow_Widgets_For_Elementor\Dynamic_Tags\Taxonomy_Image_Dynamic_Tag;

if (! defined('ABSPATH')) exit; // Exit if accessed directly

// require 'assets/util/audio_utils.php';

// add_action('init_get_audio_length', 'get_audio_length');
// add_action('init', 'get_audio_length');

function flow_elementor_widgets_init()
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
    add_action('wp_ajax_get_album_cpt_data', 'flow_get_album_cpt_data');

    function flow_get_album_cpt_data()
    {
        if (!isset($_POST['album_id'])) {
            wp_send_json_error('No album ID provided');
        }

        $album_id = intval($_POST['album_id']);
        $widget = new Flow_Widgets_For_Elementor\Widgets\Flow_Audio_Playlist_Widget();
        $album_data = $widget->get_album_cpt_data($album_id);

        if ($album_data) {
            wp_send_json_success($album_data);
        } else {
            wp_send_json_error('Failed to fetch album data');
        }
    }

    // Register the dynamic tag
    add_action('elementor/dynamic_tags/register', __NAMESPACE__ . '\\register_flow_audio_dynamic_tags');

    // Add this function to register the dynamic tag
    function register_flow_audio_dynamic_tags($dynamic_tags_manager)
    {
        require_once(__DIR__ . '/dynamic_tags/taxonomy-image-dynamic-tag.php');
        $dynamic_tags_manager->register(new Taxonomy_Image_Dynamic_Tag());
    }

    // Add this function to register the dynamic tag group
    function add_flow_audio_dynamic_tags_group($dynamic_tags)
    {
        \Elementor\Plugin::$instance->dynamic_tags->register_group(
            'flow-audio-dynamic-tags',
            [
                'title' => esc_html__('Flow Audio', 'flow-elementor-widgets'),
                'conditions' => [], // Optional: Define conditions if needed
            ]
        );
    }
    add_action('elementor/dynamic_tags/register_groups', __NAMESPACE__ . '\\add_flow_audio_dynamic_tags_group');

    // Add this function to debug dynamic tags
    function debug_dynamic_tags_groups($dynamic_tags)
    {
        // error_log('Dynamic Tags Groups: ' . print_r($dynamic_tags->get_groups(), true));
    }
    add_action('elementor/dynamic_tags/register_groups', 'debug_dynamic_tags_groups', 999);

    // // Add this function to debug dynamic tags
    // function debug_dynamic_tags($dynamic_tags_manager)
    // {
    //     error_log('Dynamic Tags Manager: ' . print_r($dynamic_tags_manager, true));
    //     // error_log('Registered Dynamic Tags: ' . print_r($dynamic_tags_manager->get_tags(), true));
    // }
    // add_action('elementor/dynamic_tags/register', 'debug_dynamic_tags', 999);

    // Add filter to log builder content data
    add_filter('elementor/frontend/builder_content_data', function ($data, $post_id) {
        // error_log("Builder content data for post $post_id: " . print_r($data, true));

        // Check if we're in a loop
        if (isset($data[0]['elements'][0]['elements'])) {
            foreach ($data[0]['elements'][0]['elements'] as &$element) {
                if (isset($element['settings']['__dynamic__'])) {
                    foreach ($element['settings']['__dynamic__'] as &$dynamic_setting) {
                        if (strpos($dynamic_setting, 'taxonomy-image') !== false) {
                            // Add loop item data to the dynamic tag
                            $dynamic_setting = str_replace(']', ', "loop_item": {"id": "{{ID}}"}]', $dynamic_setting);
                        }
                    }
                }
            }
        }

        return $data;
    }, 10, 2);

    // Add filter to log loop grid query args
    add_filter('elementor/query/query_args', function ($query_args, $widget) {
        if ('loop-grid' === $widget->get_name()) {
            // error_log("Loop Grid Query Args: " . print_r($query_args, true));
        }
        return $query_args;
    }, 10, 2);

    // Add this new filter
    add_filter('elementor/frontend/loop/dynamic_tag_data', function ($data, $tag) {
        // error_log("Dynamic tag data: " . print_r($data, true));
        return $data;
    }, 10, 2);

    // Add this new filter to log loop grid settings
    add_action('elementor/frontend/before_render', function ($element) {
        if ('loop-grid' === $element->get_name()) {
            $settings = $element->get_settings();
            error_log("Loop Grid Settings: " . print_r($settings, true));
        }
    });

    // Add filter to log builder content data
    add_filter('elementor/frontend/builder_content_data', function ($data, $post_id) {
        // error_log("Builder content data for post $post_id: " . print_r($data, true));
        return $data;
    }, 10, 2);
}
add_action('plugins_loaded', 'flow_elementor_widgets_init', 20);
