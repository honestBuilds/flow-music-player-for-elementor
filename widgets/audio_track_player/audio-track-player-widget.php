<?php

namespace Flow_Widgets_For_Elementor\Widgets;

// Ensure all imports are from the right namespace
use Elementor\Widget_Base;
use Elementor\Utils;
use Flow_Widgets_For_Elementor\Widgets\Controls\Track_Player_Style_Controls;
use Flow_Widgets_For_Elementor\Widgets\Controls\Track_Player_Content_Controls;
use Flow_Widgets_For_Elementor\Widgets\Classes\Track;
use Flow_Widgets_For_Elementor\Widgets\Classes\Playlist;

// Security Note: Blocks direct access to the plugin PHP files.
if (!defined('ABSPATH')) exit;

// Widget controls imports
require_once(__DIR__ . '/controls/content_controls.php');
require_once(__DIR__ . '/controls/style_controls.php');

// Util imports
require_once(__DIR__ . '/../../assets/util/audio_utils.php');


class Flow_Audio_Track_Player_Widget extends Widget_Base
{
    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);

        $this->enqueue_custom_css();
        $this->enqueue_custom_js();

        // only enqueue styles and scripts when widget is present on the page
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_custom_css']);
        add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_custom_js']);
        add_action('elementor/editor/after_enqueue_styles', [$this, 'enqueue_custom_css']);
    }

    public function get_name()
    {
        return 'flow_audio_track_player_widget';
    }

    public function get_title()
    {
        return esc_html__('Audio Track Player', 'flow-audio');
    }

    public function get_icon()
    {
        return 'eicon-play-o';
    }

    public function get_categories()
    {
        return ['flow', 'basic'];
    }

    public function get_keywords()
    {
        return ['track', 'audio', 'flow', 'player'];
    }

    public function get_script_depends()
    {
        return ['audio_track-player-js'];
    }

    public function get_style_depends()
    {
        return ['audio-track-player-css',];
    }

    public function enqueue_custom_css()
    {
        // Files relative to the current file's directory
        $css_files = [
            'audio-track-player.css'    => 'audio-track-player-css',
        ];

        foreach ($css_files as $filename => $handle) {
            // Filesystem path for filemtime
            $css_path = plugin_dir_path(__FILE__) . 'src/' . $filename;

            // URL to the CSS file
            $css_url = plugin_dir_url(__FILE__) . 'src/' . $filename;

            // Check if the file exists to prevent errors
            if (file_exists($css_path)) {
                wp_enqueue_style(
                    $handle,        // Handle
                    $css_url,       // Source
                    [],             // Dependencies
                    filemtime($css_path) // Version based on last modified time
                );
            } else {
                // Optionally, handle the error if the file doesn't exist
                error_log("CSS file not found: " . $css_path);
            }
        }
    }


    public function enqueue_custom_js()
    {
        // Files relative to the current file's directory
        $js_files = [
            'audio-track-player.js' => 'audio-track-player-js',
        ];

        foreach ($js_files as $filename => $handle) {
            // Filesystem path for filemtime
            $js_path = plugin_dir_path(__FILE__) . 'src/' . $filename;

            // URL to the JS file
            $js_url = plugin_dir_url(__FILE__) . 'src/' . $filename;

            // Check if the file exists to prevent errors
            if (file_exists($js_path)) {
                wp_enqueue_script(
                    $handle,        // Handle
                    $js_url,        // Source
                    ['jquery'],     // Dependencies
                    filemtime($js_path), // Version based on last modified time
                    true            // Load in footer
                );
            } else {
                // Optionally, handle the error if the file doesn't exist
                error_log("JS file not found: " . $js_path);
            }
        }
    }

    protected function _register_controls()
    {
        // Content
        $content_controls = new Track_Player_Content_Controls();
        $content_controls->register_controls($this);

        // Style
        $style_controls = new Track_Player_Style_Controls();
        $style_controls->register_controls($this);
    }

    public function render()
    {
        // Start output buffer
        ob_start();

        // Include the frontend template
        include(__DIR__ . '/views/frontend.php');

        // End output buffer and echo
        echo ob_get_clean();
    }

    protected function content_template()
    {
        // Include the preview template
        include(__DIR__ . '/views/preview.php');
    }

    private function get_track_cpt_data($track_id)
    {
        error_log("Getting track data for ID: " . $track_id);
        $track = get_post($track_id);
        if (!$track) {
            error_log("Track not found for ID: " . $track_id);
            return null;
        }

        $track_artists = wp_get_post_terms($track_id, 'artist');
        $track_artist = !empty($track_artists) ? implode(', ', wp_list_pluck($track_artists, 'name')) : '';

        $track_title = $track->post_title;
        $track_url = get_post_meta($track_id, 'track_url', true);
        $track_attachment_id = attachment_url_to_postid($track_url) ?? 0;
        $track_duration_secs = get_audio_length($track_attachment_id);
        $track_duration_formatted = format_audio_duration($track_duration_secs);

        // Get the featured image URL
        $featured_image_url = get_the_post_thumbnail_url($track_id, 'thumbnail');
        if (!$featured_image_url) {
            $featured_image_url = plugins_url('assets/img/placeholder.webp', __FILE__); // Set a default image URL or leave empty
        }

        $track_data = [
            'track_title' => $track_title,
            'track_url' => $track_url,
            'track_attachment_id' => $track_attachment_id,
            'track_duration_secs' => $track_duration_secs,
            'track_duration_formatted' => $track_duration_formatted,
            'track_artist' => $track_artist,
            'featured_image_url' => $featured_image_url, // Add the featured image URL
        ];

        return $track_data;
    }

    private function get_manual_track_data($settings)
    {
        $track_artist = $settings['track_artist'];
        $track_title = $settings['track_title'];
        $track_url = $settings['media_library']['url'];
        $track_attachment_id = attachment_url_to_postid($track_url) ?? 0;
        $track_duration_secs = get_audio_length($track_attachment_id);
        $track_duration_formatted = format_audio_duration($track_duration_secs);

        // Handle multiple artists
        $artists = explode(',', $track_artist);
        $track_artist = implode(', ', array_map('trim', $artists));

        $track_data = [
            'track_title' => $track_title,
            'track_url' => $track_url,
            'track_attachment_id' => $track_attachment_id,
            'track_duration_secs' => $track_duration_secs,
            'track_duration_formatted' => $track_duration_formatted,
            'track_artist' => $track_artist,
            'featured_image_url' => $settings['track_image']['url'] ?? plugins_url('assets/img/placeholder.webp', __FILE__), // Add this line
        ];

        return $track_data;
    }
}
