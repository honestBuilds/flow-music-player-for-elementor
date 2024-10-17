<?php
/*
Plugin Name: Flow Music Player for Elementor
Description: Music Player for Elementor: MP3 Audio Player & Podcast Player
Version: 0.0.1
Author: Joseph Mills
*/

namespace Flow_Music_Player_For_Elementor;

if (!defined('ABSPATH')) exit;

class FMP_For_Elementor
{
    private $version;

    public function __construct()
    {
        add_action('plugins_loaded', array($this, 'init'), 20);
    }

    public function init()
    {
        $this->check_elementor_dependency();
        $this->set_version();
        $this->load_required_files();
        $this->init_plugin_activation();
        $this->register_widgets();
        $this->enqueue_scripts();
        $this->add_custom_widget_categories();
        $this->init_functionality();
    }

    private function set_version()
    {
        $plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
        $this->version = $plugin_data['Version'] . '.' . filemtime(__FILE__);
    }

    public function check_version()
    {
        if (get_option('fmp_version') != $this->version) {
            $this->create_database_tables();
            update_option('fmp_version', $this->version);
        }
    }

    public function activate_plugin()
    {
        $this->create_database_tables();
        update_option('fmp_version', $this->version);
    }

    // Activation hook
    function init_plugin_activation()
    {
        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
        // Run database creation on plugin load as well (in case activation hook didn't run)
        add_action('plugins_loaded', array($this, 'check_version'));

        // add_action('plugins_loaded', array($this, 'create_database_tables'));
    }

    function create_database_tables()
    {
        require_once(__DIR__ . '/includes/database.php');
        fmp_create_database_tables();
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
        add_action('wp_enqueue_scripts', array($this, 'fmp_enqueue_scripts'));
    }

    public function fmp_enqueue_scripts()
    {
        $script_path = plugin_dir_path(__FILE__) . 'assets/js/script.js';
        wp_enqueue_script(
            'general-script',
            plugin_dir_url(__FILE__) . 'assets/js/script.js',
            array('jquery'),
            filemtime($script_path),
            true
        );

        // AJAX
        $ajax_script_path = plugin_dir_path(__FILE__) . 'assets/js/script.js';
        wp_enqueue_script('ajax-js', plugin_dir_url(__FILE__) . 'js/flow-music-player.js', array('jquery'), filemtime($ajax_script_path), true,);
        wp_localize_script('fmp-ajax-localised', 'fmpAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
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

    private function init_functionality()
    {
        // Ajax handler for logging shares
        add_action('wp_ajax_fmp_log_share', 'fmp_log_share');
        add_action('wp_ajax_nopriv_fmp_log_share', 'fmp_log_share');
    }

    private function load_required_files()
    {
        // Include Composer autoloader
        require_once(__DIR__ . '/vendor/autoload.php');
        require_once(__DIR__ . '/cpts/album/album.php');
        require_once(__DIR__ . '/cpts/track/track.php');
        require_once(__DIR__ . '/cpts/cpt-util.php');
        require_once(__DIR__ . '/dynamic-tags/dynamic-tags.php');
        // require_once(__DIR__ . '/debug.php');
        require_once(__DIR__ . '/includes/admin-menu.php');
        require_once(__DIR__ . '/includes/share-tracking.php');
    }
}

// Initialize the plugin
new \Flow_Music_Player_For_Elementor\FMP_For_Elementor();