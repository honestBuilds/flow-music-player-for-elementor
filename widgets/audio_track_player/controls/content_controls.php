<?php

namespace Flow_Widgets_For_Elementor\Widgets\Controls;

use Elementor\Controls_Manager;
use Elementor\Modules\DynamicTags\Module as TagsModule;
use Elementor\Utils;

/**
 * Controls for the Content section of Elementor panel
 */

class Track_Player_Content_Controls extends Controls_Manager
{

    public function register_controls($widget)
    {
        $widget->start_controls_section(
            'track_content_section',
            [
                'label' => esc_html__('Track', 'flow-audio'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $widget->add_control(
            'track_source',
            [
                'label' => esc_html__('Track Source', 'flow-audio'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'manual' => esc_html__('Manual Input', 'flow-audio'),
                    'track_cpt' => esc_html__('Track Posts', 'flow-audio'),
                ],
                'default' => 'manual',
            ]
        );

        $widget->add_control(
            'track_cpt',
            [
                'label' => esc_html__('Select Track', 'flow-audio'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__('Enter Track ID or select from list', 'flow-audio'),
                'condition' => [
                    'track_source' => 'track_cpt',
                ],
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        // Add a separate control for the album dropdown
        $widget->add_control(
            'track_cpt_dropdown',
            [
                'label' => esc_html__('Or Select Track', 'flow-audio'),
                'type' => Controls_Manager::SELECT2,
                'options' => $this->get_track_options(),
                'condition' => [
                    'track_source' => 'track_cpt',
                ],
            ]
        );

        $widget->add_control(
            'track_image',
            [
                'label' => __('Track Image', 'flow-audio'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'track_source' => 'manual',
                ],
                'dynamic' => [
                    'active' => true,
                    'categories' => [
                        TagsModule::IMAGE_CATEGORY,
                    ],
                ],
            ]
        );

        // Track Number Control
        $widget->add_control(
            'track_number',
            [
                'label' => esc_html__('Track Number', 'flow-audio'),
                'type' => Controls_Manager::TEXT,
                // 'default' => '1',
                'placeholder' => esc_html__('Track number', 'flow-audio'),
                'label_block' => true,
                'dynamic' => [
                    'active' => true, // Enable dynamic tags
                ],
                'condition' => [
                    'track_source' => 'manual',
                ],

            ]
        );

        // Track Title Control
        $widget->add_control(
            'track_title',
            [
                'label' => esc_html__('Track Title', 'flow-audio'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Track Title', 'flow-audio'),
                'placeholder' => esc_html__('Track title', 'flow-audio'),
                'label_block' => true,
                'dynamic' => [
                    'active' => true, // Enable dynamic tags
                ],
                'condition' => [
                    'track_source' => 'manual',
                ],
            ]
        );

        // Track Artist Control
        $widget->add_control(
            'track_artist',
            [
                'label' => esc_html__('Track Artist(s)', 'flow-audio'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Artist Name', 'flow-audio'),
                'placeholder' => esc_html__('Artist 1, Artist 2, ...', 'flow-audio'),
                'label_block' => true,
                'dynamic' => [
                    'active' => true, // Enable dynamic tags
                ],
                'condition' => [
                    'track_source' => 'manual',
                ],

            ]
        );

        $widget->add_control(
            'media_library',
            [
                'label' => esc_html__('Choose Audio File', 'flow-audio'),
                'type' => Controls_Manager::MEDIA,
                'media_type' => 'audio',
                'dynamic' => [
                    'active' => true, // Dynamic media support
                ],
                'condition' => [
                    'track_source' => 'manual',
                ],
            ]
        );

        $widget->end_controls_section();
    }

    private function get_track_options()
    {
        $tracks = get_posts([
            'post_type' => 'track',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $options = ['' => esc_html__('Select Track', 'flow-audio')];
        foreach ($tracks as $track) {
            $options[$track->ID] = $track->post_title;
        }

        return $options;
    }
}
