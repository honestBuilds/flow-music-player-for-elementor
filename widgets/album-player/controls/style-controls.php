<?php

namespace Flow_Music_Player_For_Elementor\Widgets\Controls;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

/**
 * Controls for the Style section of Elementor panel
 */

class Album_Player_Style_Controls extends Controls_Manager
{

    public function register_controls($widget)
    {


        // Title
        $widget->start_controls_section(
            'title_style_section',
            [
                'label' => esc_html__('Title', 'flow-audio'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        // Title alignment
        $widget->add_responsive_control(
            'title_alignment',
            [
                'label' => esc_html__('Alignment', 'flow-audio'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'flow-audio'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'flow-audio'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'flow-audio'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center', // Center alignment by default
                'selectors' => [
                    '{{WRAPPER}} .widget-title' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        // Typography for Title
        $widget->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => esc_html__('Typography', 'flow-audio'),
                'selector' => '{{WRAPPER}} .widget-title',
            ]
        );

        $widget->end_controls_section();

        // Background & Layout
        $widget->start_controls_section(
            'background_layout_style_section',
            [
                'label' => esc_html__('Background & Layout', 'flow-audio'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        // Background Color
        $widget->add_control(
            'background_color',
            [
                'label' => esc_html__('Background Color', 'flow-audio'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
            ]
        );

        $widget->end_controls_section();

        // Tracks
        $widget->start_controls_section(
            'tracks_style_section',
            [
                'label' => esc_html__('Tracks', 'flow-audio'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $widget->end_controls_section();

        // Cover Art
        $widget->start_controls_section(
            'cover_art_style_section',
            [
                'label' => esc_html__('Cover Art', 'flow-audio'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $widget->add_control(
            'cover_art_aspect_ratio',
            [
                'label' => esc_html__('Aspect Ratio', 'flow-audio'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'square' => esc_html__('1:1 (Square)', 'flow-audio'),
                    'widescreen' => esc_html__('16:9 (Widescreen)', 'flow-audio'),
                    'portrait' => esc_html__('9:16 (Portrait)', 'flow-audio'),
                    'book' => esc_html__('5:7 (Book)', 'flow-audio'),
                ],
                'default' => 'square',
            ]
        );


        // Responsive Border Radius Control for Cover Art
        $widget->add_control(
            'cover_art_border_radius',
            [
                'label' => esc_html__('Border Radius', 'flow-audio'),
                'type' => Controls_Manager::DIMENSIONS, // Using the DIMENSIONS control for responsive radius
                'size_units' => ['px', '%', 'em'], // Allows units in px, %, and em
                'selectors' => [
                    '{{WRAPPER}} .cover-art' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'responsive' => true,
                'default' => [
                    'unit' => 'px',
                    'top' => '',
                    'right' => '',
                    'bottom' => '',
                    'left' => '',
                    'isLinked' => true,
                ],
            ]
        );

        $widget->end_controls_section();
    }
}
