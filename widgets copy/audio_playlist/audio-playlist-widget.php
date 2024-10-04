<?php

namespace Flow_Widgets_For_Elementor\Widgets;

// Ensure all imports are from the right namespace
use Elementor\Widget_Base;
use Elementor\Utils;
use Flow_Widgets_For_Elementor\Widgets\Controls\Style_Controls;
use Flow_Widgets_For_Elementor\Widgets\Controls\Content_Controls;
use Flow_Widgets_For_Elementor\Widgets\Classes\Track;
use Flow_Widgets_For_Elementor\Widgets\Classes\Playlist;

// Security Note: Blocks direct access to the plugin PHP files.
if (!defined('ABSPATH')) exit;

// Widget controls imports
require_once(__DIR__ . '/controls/content_controls.php');
require_once(__DIR__ . '/controls/style_controls.php');

// Util imports
require_once(__DIR__ . '/../../assets/util/audio_utils.php');
require_once(__DIR__ . '/classes/track.php');
require_once(__DIR__ . '/classes/playlist.php');


class Flow_Audio_Playlist_Widget extends Widget_Base
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
        return 'eicon-play';
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
            'tailwind.css' => 'tailwind_css_for_audio_playlist',
            'style.css'    => 'other_css_for_audio_playlist',
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
            'audio-player.js' => 'audio-player-js',
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
        $content_controls = new Content_Controls();
        $content_controls->register_controls($this);

        // Style
        $style_controls = new Style_Controls();
        $style_controls->register_controls($this);
    }

    public function render()
    {
        // Get widget settings
        $settings = $this->get_settings_for_display();

        // Playlist Details
        $playlist_title = '';
        $playlist_artist = '';
        $playlist_year = '';
        // Download Link
        $download_link = '';
        $total_duration = '';

        $show_track_numbers = $settings['show_track_numbers'];

        // Get Tracks Data
        if ('yes' === $settings['use_cpt_tracks']) {
            $post_id = get_the_ID();

            // Get details from CPT's 'album_details' custom field
            $playlist_y = strval(get_post_meta($post_id, 'album_year', true));
            if (!empty($playlist_y)) {
                $playlist_year = $playlist_y;
            }

            $playlist_d = strval(get_post_meta($post_id, 'album_download_link', true));
            if (!empty($playlist_d)) {
                $download_link = $playlist_d;
            }

            $playlist_a = strval(get_post_meta($post_id, 'album_artist', true));
            if (!empty($playlist_a)) {
                $playlist_artist = $playlist_a;
            }

            // Get tracks from CPT's 'album_tracks' custom field
            $album_tracks = get_post_meta($post_id, 'album_tracks', true);
            $tracks_arr = [];
            $total_dur_secs = 0;

            if (!empty($album_tracks) && is_array($album_tracks)) {
                foreach ($album_tracks as $track) {
                    $track_attachment_id = attachment_url_to_postid($track['url']) ?? 0;
                    $audio_duration = get_audio_length($track_attachment_id);
                    $total_dur_secs += $audio_duration;

                    $tracks_arr[] = [
                        'track_number' => $track['number'],
                        'track_title' => $track['title'],
                        'track_url' => $track['url'],
                        'track_attachment_id' => $track_attachment_id,
                        'track_duration_secs' => $audio_duration,
                        'track_duration_formatted' => format_audio_duration($audio_duration),
                    ];
                }
            }

            $total_duration = format_audio_duration($total_dur_secs);

            $cover_art_url = wp_get_attachment_url(get_post_thumbnail_id($post_id));
            $playlist_title = get_the_title($post_id);

            $playlist_type = 'preaching';
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
        } else {
            $playlist_title = $settings['playlist_title'];
            $playlist_artist = $settings['playlist_artist'];
            $playlist_year = $settings['playlist_year'];

            $download_link = $settings['download_link']['url'] ?? '';
            // Get tracks from the widget's repeater field
            $tracks = $settings['tracks']; // Repeater field for tracks
            $tracks_arr = [];
            $total_dur_secs = 0;

            if (!empty($tracks)) {
                foreach ($tracks as $track) {
                    $track_file_url = '';
                    $track_attachment_id = '';

                    if (!empty($track['media_library']['url'])) {
                        $track_file_url = $track['media_library']['url'];
                        $track_attachment_id = $track['media_library']['id'];
                        $audio_duration = get_audio_length($track_attachment_id);
                        $total_dur_secs += $audio_duration;
                    }

                    $tracks_arr[] = [
                        'track_number' => $track['track_number'],
                        'track_title' => $track['track_title'],
                        'track_url' => $track_file_url,
                        'track_attachment_id' => $track_attachment_id,
                        'track_duration_secs' => $audio_duration,
                        'track_duration_formatted' => format_audio_duration($audio_duration),
                    ];
                }
            }

            $total_duration = format_audio_duration($total_dur_secs);
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

            // Cover Art
            $cover_art = $settings['cover_art'];
            $cover_art_url = !empty($cover_art['id']) ? wp_get_attachment_url($cover_art['id']) : Utils::get_placeholder_image_src();
        }

        // Localize script with data
        wp_localize_script(
            'audio-player-js',
            'albumData', // JavaScript object name
            [
                'title' => $playlist_title,
                'artist' => $playlist_artist,
                'year' => $playlist_year,
                'coverArt' => $cover_art_url,
                'tracks' => $tracks_arr,
                'playButtonImage' => plugin_dir_url(__FILE__) . 'src/play-btn.svg',
                'pauseButtonImage' => plugin_dir_url(__FILE__) . 'src/pause-btn.svg',
                'downloadLink' => $download_link,
                'totalDuration' => $total_duration,
                'show_track_numbers' => $show_track_numbers,
            ]
        );

        // wp_enqueue_script('your-plugin-audio-player-js');

        // Start output buffer
        ob_start();

        // Include the frontend template
        include(__DIR__ . '/views/frontend_v2.php');

        // End output buffer and echo
        echo ob_get_clean();
    }




    // protected function render()
    // {
    //     // Get widget settings or other PHP data
    //     $widget_settings = $this->get_settings_for_display();

    //     // Get all tracks as objects
    //     $tracks = $widget_settings['tracks']; // Repeater field for tracks
    //     $tracks_arr = convert_tracks($tracks);
    //     $artist = $widget_settings['playlist_artist'];
    //     $year = $widget_settings['playlist_year'];

    //     $cover_art = $widget_settings['cover_art'];
    //     $img_placeholder = Utils::get_placeholder_image_src();

    //     $playlist_title = $widget_settings['playlist_title'] ?? ''; // Playlist title
    //     $title_typography = $widget_settings['title_typography']; // Typography settings for title
    //     $download_link = $widget_settings['download_link'] ?? ''; // Download link

    //     $playlist = new Playlist($playlist_title, !wp_get_attachment_url($cover_art['id']) ? $img_placeholder : wp_get_attachment_url($cover_art['id']), $tracks_arr, $artist, $year);

    //     $cover_art_aspect_ratio = $widget_settings['cover_art_aspect_ratio'];
    //     // Map aspect ratio to CSS class
    //     switch ($cover_art_aspect_ratio) {
    //         case 'widescreen':
    //             $aspect_ratio_class = 'widescreen_cover';
    //             break;
    //         case 'portrait':
    //             $aspect_ratio_class = 'portrait_cover';
    //             break;
    //         case 'book':
    //             $aspect_ratio_class = 'book_cover';
    //             break;
    //         default: // square
    //             $aspect_ratio_class = 'square_cover';
    //             break;
    //     }

    //     // Map playlist type to labels
    //     switch ($widget_settings['playlist_type']) {
    //         case 'Preaching':
    //             $count_unit = 'tracks';
    //             break;
    //         case 'Audiobooks':
    //             $count_unit = 'chapters';
    //             break;
    //         default:
    //             $count_unit = 'songs';
    //             break;
    //     }


    //     // Pass PHP variables to the JS file using wp_localize_script
    //     wp_localize_script(
    //         'music-player-js', // Handle of the script being localized
    //         'widgetData', // Object name accessible in JS
    //         array(
    //             'settings' => $widget_settings,
    //             'playlist' => $playlist,
    //             'playlistType' => $widget_settings['playlist_type'],
    //             'coverArtAspectRatio' => $aspect_ratio_class,
    //             'srcUrl' => plugins_url('src/', __FILE__),
    //             'titleTypography' => $title_typography,
    //             'downloadLink' => $download_link,
    //             'countUnit' => $count_unit
    //         )
    //     );

    //     // Start output buffer
    //     ob_start();

    //     require_once(__DIR__ . '/views/frontend.php');

    //     // End output buffer and echo
    //     echo ob_get_clean();
    // }

    protected function content_template()
    {

        ob_start();

        require_once(__DIR__ . '/views/preview.php');

        // End output buffer and echo
        echo ob_get_clean();
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
