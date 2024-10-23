<?php

namespace Flow_Music_Player_For_Elementor\Widgets;

// Ensure all imports are from the right namespace
use Elementor\Widget_Base;
use Elementor\Utils;
use Flow_Music_Player_For_Elementor\Widgets\Controls\Album_Player_Style_Controls;
use Flow_Music_Player_For_Elementor\Widgets\Controls\Album_Player_Content_Controls;
use Flow_Music_Player_For_Elementor\Widgets\Classes\Track;

// Security Note: Blocks direct access to the plugin PHP files.
if (!defined('ABSPATH')) exit;

// Widget controls imports
require_once(__DIR__ . '/controls/content-controls.php');
require_once(__DIR__ . '/controls/style-controls.php');

// Util imports
require_once(__DIR__ . '/../../assets/util/audio-utils.php');
require_once(__DIR__ . '/classes/class-track.php');


class FMP_Album_Player_Widget extends Widget_Base
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
        return 'flow_audio_playlist_widget';
    }

    public function get_title()
    {
        return esc_html__('Flow Audio Playlist', 'flow-audio');
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
        return ['playlist', 'audio', 'flow'];
    }

    public function get_script_depends()
    {
        return ['music-player-js'];
    }

    public function get_style_depends()
    {
        return ['other_css_for_audio_playlist', 'tailwind_css_for_audio_playlist'];
    }

    public function check_widget_and_enqueue($widget)
    {
        if ($widget->get_name() === $this->get_name()) {
            $this->enqueue_custom_css();
            $this->enqueue_custom_js();
        }
    }

    public function enqueue_custom_css()
    {
        // Files relative to the current file's directory
        $css_files = [
            'tailwind.css' => 'tailwind-css-for-album-player',
            'album-player.css'    => 'album-player-css',
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
            'album-player.js' => 'audio-player-js',
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
        $content_controls = new Album_Player_Content_Controls();
        $content_controls->register_controls($this);

        // Style
        $style_controls = new Album_Player_Style_Controls();
        $style_controls->register_controls($this);
    }

    public function render()
    {
        $settings = $this->get_settings_for_display();

        if ($settings['album_source'] === 'album_cpt' && !empty($settings['album_cpt'])) {
            $post_id = $settings['album_cpt'];
            $album_data = $this->get_album_cpt_data($post_id);
            // Playlist Details
            $playlist_title = '';
            $playlist_artist = '';
            $playlist_year = '';
            $playlist_location = '';
            // Download Link
            $download_link = '';
            $total_duration = '';

            $show_track_numbers = $settings['show_track_numbers'];
            // Playlist Type and Count Unit
            $playlist_type = $settings['playlist_type'] ?? 'music';
            switch ($playlist_type) {
                case 'preaching':
                    $count_unit = 'tracks';
                    break;
                case 'audiobook':
                    $count_unit = 'chapters';
                    break;
                default:
                    $count_unit = 'songs';
                    break;
            }

            // Fetch the value from the adjust_track_count control
            $adjust_track_count = isset($settings['adjust_track_count']) ? intval($settings['adjust_track_count']) : 0;

            // Get Tracks Data
            if ($settings['album_source'] === 'album_cpt' && !empty($settings['album_cpt'])) {
                $post_id = $settings['album_cpt'];
                $album_data = $this->get_album_cpt_data($post_id);

                $playlist_title = $album_data['playlist_title'];
                $playlist_artist = $album_data['playlist_artist'];
                $playlist_year = $album_data['playlist_year'];
                $playlist_location = $album_data['playlist_location'];
                $cover_art_url = $album_data['cover_art'];
                $tracks_arr = $album_data['tracks'];
                $total_duration = $album_data['total_duration'];
                $download_link = $album_data['download_link'];
            } else {
                $playlist_data = $this->get_manual_album_data($post_id);
                $playlist_location = $playlist_data['playlist_location'];
                $playlist_title = $playlist_data['playlist_title'];
                $playlist_artist = $playlist_data['playlist_artist'];
                $playlist_year = $playlist_data['playlist_year'];
                $cover_art_url = $playlist_data['cover_art'];
                $tracks_arr = $playlist_data['tracks'];
                $total_duration = $playlist_data['total_duration'];
                $download_link = $playlist_data['download_link'];
            }

            // Localize script with data
            wp_localize_script(
                'audio-player-js',
                'albumData',
                [
                    'title' => $playlist_title,
                    'artist' => $playlist_artist,
                    'year' => $playlist_year,
                    'coverArt' => $cover_art_url,
                    'tracks' => $tracks_arr,
                    'location' => $playlist_location,
                    'playButtonImage' => plugin_dir_url(__FILE__) . 'src/play-btn.svg',
                    'pauseButtonImage' => plugin_dir_url(__FILE__) . 'src/pause-btn.svg',
                    'downloadLink' => $download_link,
                    'totalDuration' => $total_duration,
                    'show_track_numbers' => $show_track_numbers,
                    'siteName' => get_bloginfo('name'),
                ]
            );

            // Start output buffer
            ob_start();

            // Include the frontend template
            include(__DIR__ . '/views/frontend.php');

            // End output buffer and echo
            echo ob_get_clean();
        }
    }

    public function get_settings_for_display($setting_key = null)
    {
        $settings = parent::get_settings_for_display($setting_key);
        if (null === $settings) {
            return []; // Return an empty array as fallback
        }
        return $settings;
    }

    protected function content_template()
    {
        // Include the preview template
        include(__DIR__ . '/views/preview.php');
    }

    private function get_album_cpt_data($album_id)
    {

        $album = get_post($album_id);
        if (!$album) {
            return null;
        }

        $album_artists = wp_get_post_terms($album_id, 'album_artist');
        $album_artist = !empty($album_artists) ? $album_artists[0]->name : '';

        $tracks = get_post_meta($album_id, 'album_tracks', true);

        $formatted_tracks = [];
        $total_dur_secs = 0;

        if (!empty($tracks) && is_array($tracks)) {
            foreach ($tracks as $index => $track_id) {
                $track_post = get_post($track_id);
                if ($track_post) {
                    $track_file_url = get_post_meta($track_id, 'track_url', true);
                    $track_attachment_id = attachment_url_to_postid($track_file_url) ?? 0;
                    $audio_duration = get_audio_length($track_attachment_id);
                    $total_dur_secs += $audio_duration;

                    $formatted_tracks[] = [
                        'track_number' => $index + 1,
                        'track_title' => $track_post->post_title,
                        'track_url' => $track_file_url,
                        'track_attachment_id' => $track_attachment_id,
                        'track_duration_secs' => $audio_duration,
                        'track_duration_formatted' => format_audio_duration($audio_duration),
                    ];
                } else {
                    error_log("Track not found for ID: " . $track_id);
                }
            }
        } else {
            error_log("No tracks found or tracks data is not an array");
        }

        $cover_art_url = get_the_post_thumbnail_url($album_id, 'full');

        $album_data = [
            'playlist_title' => $album->post_title,
            'playlist_artist' => $album_artist,
            'playlist_year' => strval(get_post_meta($album_id, 'album_year', true)),
            'playlist_location' => strval(get_post_meta($album_id, 'album_location', true)),
            'cover_art' => $cover_art_url ? $cover_art_url : Utils::get_placeholder_image_src(),
            'tracks' => $formatted_tracks,
            'total_duration' => format_audio_duration($total_dur_secs),
            'download_link' => get_post_meta($album_id, 'album_download_link', true),
        ];

        return $album_data;
    }

    private function get_manual_album_data($settings)
    {
        $tracks = [];
        $total_dur_secs = 0;

        foreach ($settings['tracks'] as $track) {
            $track_file_url = $track['media_library']['url'] ?? '';
            $track_attachment_id = $track['media_library']['id'] ?? '';
            $audio_duration = get_audio_length($track_attachment_id);
            $total_dur_secs += $audio_duration;

            $tracks[] = [
                'track_number' => $track['track_number'],
                'track_title' => $track['track_title'],
                'track_url' => $track_file_url,
                'track_attachment_id' => $track_attachment_id,
                'track_duration_secs' => $audio_duration,
                'track_duration_formatted' => format_audio_duration($audio_duration),
            ];
        }

        return [
            'playlist_title' => $settings['playlist_title'],
            'playlist_artist' => $settings['playlist_artist'],
            'playlist_year' => $settings['playlist_year'],
            'playlist_location' => $settings['playlist_location'],
            'cover_art' => $settings['cover_art']['url'] ?? Utils::get_placeholder_image_src(),
            'tracks' => $tracks,
            'total_duration' => format_audio_duration($total_dur_secs),
            'download_link' => $settings['download_link']['url'] ?? '',
        ];
    }
}

function convert_tracks($tracks)
{
    $tracks_arr = [];
    if (!empty($tracks && is_array(($tracks)))) :
        foreach ($tracks as $track) :
            $track_duration = get_formatted_audio_length($track);
            $metadata_title = get_audio_title($track);
            $attachment_id = $track['media_library']['id'];

            $tracks_arr[] = new Track($track['track_title'], $track_duration, wp_get_attachment_url($attachment_id), $track['track_number'],  $metadata_title, $attachment_id);
        endforeach;
    endif;

    return $tracks_arr;
}
