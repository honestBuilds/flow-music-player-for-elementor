<?php

namespace Flow_Widgets_For_Elementor\Dynamic_Tags;

use Elementor\Modules\DynamicTags\Module as TagsModule;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Album_Custom_Field_Tag extends Tag
{
    public function get_name()
    {
        return 'album-custom-field';
    }

    public function get_title()
    {
        return esc_html__('Album Custom Field', 'flow-elementor-widgets');
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
                    'album_year' => esc_html__('Album Release Year', 'flow-elementor-widgets'),
                    'album_download_link' => esc_html__('Album Download Link', 'flow-elementor-widgets'),
                    'album_tracks' => esc_html__('Album Tracks', 'flow-elementor-widgets'),
                ],
            ]
        );
    }

    public function render()
    {
        $key = $this->get_settings('key');
        $post_id = get_the_ID();
        if ($post_id && $key) {
            $value = get_post_meta($post_id, $key, true);
            if ($key === 'album_download_link') {
                echo esc_url($value);
            } else {
                echo wp_kses_post($value);
            }
        }
    }
}
