<?php

namespace Flow_Widgets_For_Elementor\Dynamic_Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Track_Custom_Field_Tag extends Tag
{
    // Static counter for tracking position
    private static $instance_counter = 0;

    // Store already rendered track indices to avoid double counting
    private static $rendered_tracks = [];

    public function get_name()
    {
        return 'track-custom-field';
    }

    public function get_title()
    {
        return esc_html__('Track Custom Field', 'flow-elementor-widgets');
    }

    public function get_group()
    {
        return 'fmp-dynamic-tags-group';
    }

    public function get_categories()
    {
        return [
            TagsModule::TEXT_CATEGORY,
            TagsModule::URL_CATEGORY,
        ];
    }

    protected function register_controls()
    {
        $this->add_control(
            'key',
            [
                'label' => esc_html__('Key', 'flow-elementor-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'track_url' => esc_html__('Track Audio File URL', 'flow-elementor-widgets'),
                    'track_download_link' => esc_html__('Track Download Link', 'flow-elementor-widgets'),
                    'track_external_url' => esc_html__('Track External URL', 'flow-elementor-widgets'),
                    'track_index' => esc_html__('Track Index (for numbering)', 'flow-elementor-widgets'),
                ],
            ]
        );

        $this->add_control(
            'starting_index',
            [
                'label' => esc_html__('Starting Index', 'flow-elementor-widgets'),
                'type' => Controls_Manager::NUMBER,
                'default' => 1,
                'min' => 1,
                'condition' => [
                    'key' => 'track_index',
                ],
                'description' => esc_html__('Start numbering from this value', 'flow-elementor-widgets'),
            ]
        );
    }

    // Reset the counter - can be called before a new query/page load
    public static function reset_counter()
    {
        self::$instance_counter = 0;
        self::$rendered_tracks = []; // Reset the rendered tracks array too
    }

    public function render()
    {
        $key = $this->get_settings('key');
        $post_id = get_the_ID();

        if ($post_id && $key) {
            if ($key === 'track_index') {
                // Get the starting index from settings - ensure it's an integer
                $starting_index = (int)($this->get_settings('starting_index') ?: 1);

                // Get a unique identifier for this track instance
                $instance_id = $post_id . '_' . $this->get_id();

                // Check if we've already processed this track
                if (isset(self::$rendered_tracks[$instance_id])) {
                    // Return the previously assigned number
                    echo esc_html(self::$rendered_tracks[$instance_id]);
                    return;
                }

                // For debugging
                // error_log('Processing track: ' . $instance_id . ', Counter before: ' . self::$instance_counter);

                // For the very first track, use starting_index exactly
                // For subsequent tracks, increment from there
                if (self::$instance_counter === 0) {
                    $current_index = $starting_index;
                } else {
                    $current_index = $starting_index + self::$instance_counter;
                }

                // Store the track's assigned number for future reference
                self::$rendered_tracks[$instance_id] = $current_index;

                // Increment the counter for the next track
                self::$instance_counter++;

                // Output the track number
                echo esc_html($current_index);

                // For debugging
                // error_log('Assigned index: ' . $current_index . ', Counter after: ' . self::$instance_counter);
            } else {
                // For regular post meta fields
                $value = get_post_meta($post_id, $key, true);
                if (in_array($key, ['track_url', 'track_download_link', 'track_external_url'])) {
                    echo esc_url($value);
                } else {
                    echo wp_kses_post($value);
                }
            }
        }
    }
}
