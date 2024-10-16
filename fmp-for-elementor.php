<?php
/*
Plugin Name: Flow Music Player for Elementor
Description: Music Player for Elementor: MP3 Audio Player & Podcast Player
Version: 1.0
Author: Joseph Mills
*/

namespace Flow_Music_Player_For_Elementor;

if (!defined('ABSPATH')) exit;

class FMP_For_Elementor
{
    public function __construct()
    {
        add_action('plugins_loaded', array($this, 'init'), 20);
    }

    public function init()
    {
        $this->check_elementor_dependency();
        $this->register_widgets();
        $this->enqueue_scripts();
        $this->add_custom_widget_categories();
        $this->load_required_files();
    }

    private function check_elementor_dependency()
    {
        if (!defined('ELEMENTOR_VERSION')) {
            add_action('admin_notices', array($this, 'elementor_not_active_notice'));
            return;
        }
    }

    public function elementor_not_active_notice()
    {
        echo '<div class="error"><p>Flow Music Player for Elementor requires Elementor to be installed and activated.</p></div>';
    }

    private function register_widgets()
    {
        add_action('elementor/widgets/widgets_registered', array($this, 'register_flow_audio_widgets'));
    }

    public function register_flow_audio_widgets($widgets_manager)
    {
        require_once(__DIR__ . '/widgets/album-player/widget-fmp-album-player.php');
        require_once(__DIR__ . '/widgets/track-player/widget-fmp-track-player.php');

        try {
            $widgets_manager->register_widget_type(new \Flow_Music_Player_For_Elementor\Widgets\FMP_Album_Player_Widget());
            $widgets_manager->register_widget_type(new \Flow_Music_Player_For_Elementor\Widgets\FMP_Track_Player_Widget());
        } catch (\Exception $e) {
            error_log('Error registering Flow Audio widgets: ' . $e->getMessage());
        }
    }

    private function enqueue_scripts()
    {
        add_action('wp_enqueue_scripts', array($this, 'flow_audio_playlist_enqueue_scripts'));
    }

    public function flow_audio_playlist_enqueue_scripts()
    {
        $script_path = plugin_dir_path(__FILE__) . 'assets/js/script.js';
        wp_enqueue_script(
            'general-script',
            plugin_dir_url(__FILE__) . 'assets/js/script.js',
            array('jquery'),
            filemtime($script_path),
            true
        );
    }

    private function add_custom_widget_categories()
    {
        add_action('elementor/elements/categories_registered', array($this, 'add_custom_widget_categories_callback'));
    }

    public function add_custom_widget_categories_callback($elements_manager)
    {
        $elements_manager->add_category(
            'flow',
            [
                'title' => esc_html__('Flow', 'flow'),
                'icon' => 'fa-cross',
            ]
        );
    }

    private function load_required_files()
    {
        require_once(__DIR__ . '/cpts/album/album.php');
        require_once(__DIR__ . '/cpts/track/track.php');
        require_once(__DIR__ . '/cpts/cpt-util.php');
        require_once(__DIR__ . '/dynamic-tags/dynamic-tags.php');
        // require_once(__DIR__ . '/debug.php');
    }
}

// Initialize the plugin
new \Flow_Music_Player_For_Elementor\FMP_For_Elementor();
