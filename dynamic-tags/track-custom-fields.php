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

    public function render()
    {
        $key = $this->get_settings('key');
        $post_id = get_the_ID();

        if ($post_id && $key) {
            if ($key === 'track_index') {
                // Get the starting index from settings
                $starting_index = $this->get_settings('starting_index') ?: 1;

                // For track_index, we need to get the current post index in the loop
                global $wp_query;
                if (isset($wp_query->current_post)) {
                    $current_index = $wp_query->current_post + $starting_index;
                    echo esc_html($current_index);
                } else {
                    // Fallback if not in a main query loop
                    echo esc_html($starting_index);
                }
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
