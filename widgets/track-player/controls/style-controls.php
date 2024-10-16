<?php

namespace Flow_Music_Player_For_Elementor\Widgets\Controls;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;

/**
 * Controls for the Style section of Elementor panel
 */

class Track_Player_Style_Controls extends Controls_Manager
{

    public function register_controls($widget)
    {
        $widget->start_controls_section(
            'background_style_section',
            [
                'label' => esc_html__('Background', 'flow-audio'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        $widget->add_control(
            'background_color',
            [
                'label' => esc_html__('Color', 'flow-audio'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .flow-audio-track-player' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $widget->add_control(
            'background_border_radius',
            [
                'label' => esc_html__('Border Radius', 'flow-audio'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .flow-audio-track-player' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $widget->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'player_box_shadow',
                'label' => esc_html__('Box Shadow', 'flow-music-player'),
                'selector' => '{{WRAPPER}} .flow-audio-track-player',
            ]
        );

        $widget->add_control(
            'use_blurred_background',
            [
                'label' => esc_html__('Use Blurred Background', 'flow-audio'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'flow-audio'),
                'label_off' => esc_html__('No', 'flow-audio'),
                'return_value' => 'yes',
                'default' => 'no',
                'render_type' => 'template',
            ]
        );

        $widget->add_control(
            'blur_intensity',
            [
                'label' => esc_html__('Blur Intensity', 'flow-audio'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 36,
                ],
                'selectors' => [
                    '{{WRAPPER}} .flow-audio-track-player-background' => 'filter: blur({{SIZE}}{{UNIT}});',
                ],
                'condition' => [
                    'use_blurred_background' => 'yes',
                ],
            ]
        );

        $widget->end_controls_section();

        $widget->start_controls_section(
            'title_style_section',
            [
                'label' => esc_html__('Title', 'flow-audio'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $widget->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .track-title',
            ]
        );

        $widget->add_control(
            'title_color',
            [
                'label' => esc_html__('Color', 'flow-audio'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .track-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $widget->end_controls_section();

        $widget->start_controls_section(
            'artist_style_section',
            [
                'label' => esc_html__('Artist', 'flow-audio'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $widget->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'artist_typography',
                'selector' => '{{WRAPPER}} .track-artist',
            ]
        );

        $widget->add_control(
            'artist_color',
            [
                'label' => esc_html__('Color', 'flow-audio'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .track-artist' => 'color: {{VALUE}};',
                ],
            ]
        );

        $widget->end_controls_section();

        $widget->start_controls_section(
            'duration_style_section',
            [
                'label' => esc_html__('Duration', 'flow-audio'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $widget->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'duration_typography',
                'selector' => '{{WRAPPER}} .duration',
            ]
        );

        $widget->add_control(
            'duration_color',
            [
                'label' => esc_html__('Color', 'flow-audio'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .duration' => 'color: {{VALUE}};',
                ],
            ]
        );

        $widget->end_controls_section();

        $widget->start_controls_section(
            'cover_style_section',
            [
                'label' => esc_html__('Cover Art', 'flow-audio'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $widget->add_control(
            'cover_border_radius',
            [
                'label' => esc_html__('Border Radius', 'flow-audio'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .flow-audio-track-player .track-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $widget->end_controls_section();

        $widget->start_controls_section(
            'download_share_style_section',
            [
                'label' => esc_html__('Download & Share', 'flow-audio'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $widget->add_responsive_control(
            'download_share_spacing',
            [
                'label' => esc_html__('Spacing', 'flow-audio'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .share-download-container' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Download Link Controls
        $widget->add_control(
            'download_link_heading',
            [
                'label' => esc_html__('Download Link', 'flow-audio'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $widget->start_controls_tabs('download_link_style_tabs');

        $widget->start_controls_tab(
            'download_link_normal_tab',
            [
                'label' => esc_html__('Normal', 'flow-audio'),
            ]
        );

        $widget->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'download_link_typography',
                'label' => esc_html__('Typography', 'flow-audio'),
                'selector' => '{{WRAPPER}} .download-link',
            ]
        );

        $widget->add_control(
            'download_link_color',
            [
                'label' => esc_html__('Color', 'flow-audio'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .download-link' => 'color: {{VALUE}};',
                ],
            ]
        );

        $widget->end_controls_tab();

        $widget->start_controls_tab(
            'download_link_hover_tab',
            [
                'label' => esc_html__('Hover', 'flow-audio'),
            ]
        );

        $widget->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'download_link_hover_typography',
                'label' => esc_html__('Typography', 'flow-audio'),
                'selector' => '{{WRAPPER}} .download-link:hover',
            ]
        );

        $widget->add_control(
            'download_link_hover_color',
            [
                'label' => esc_html__('Color', 'flow-audio'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .download-link:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $widget->end_controls_tab();

        $widget->end_controls_tabs();

        // Share Link Controls
        $widget->add_control(
            'share_link_heading',
            [
                'label' => esc_html__('Share Link', 'flow-audio'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $widget->start_controls_tabs('share_link_style_tabs');

        $widget->start_controls_tab(
            'share_link_normal_tab',
            [
                'label' => esc_html__('Normal', 'flow-audio'),
            ]
        );

        $widget->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'share_link_typography',
                'label' => esc_html__('Typography', 'flow-audio'),
                'selector' => '{{WRAPPER}} .share-link',
            ]
        );

        $widget->add_control(
            'share_link_color',
            [
                'label' => esc_html__('Color', 'flow-audio'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .share-link' => 'color: {{VALUE}};',
                ],
            ]
        );

        $widget->end_controls_tab();

        $widget->start_controls_tab(
            'share_link_hover_tab',
            [
                'label' => esc_html__('Hover', 'flow-audio'),
            ]
        );

        $widget->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'share_link_hover_typography',
                'label' => esc_html__('Typography', 'flow-audio'),
                'selector' => '{{WRAPPER}} .share-link:hover',
            ]
        );

        $widget->add_control(
            'share_link_hover_color',
            [
                'label' => esc_html__('Color', 'flow-audio'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .share-link:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $widget->end_controls_tab();

        $widget->end_controls_tabs();

        $widget->end_controls_section();
    }
}
