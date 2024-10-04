<?php

namespace Flow_Widgets_For_Elementor\Widgets\Controls;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

/**
 * Controls for the Style section of Elementor panel
 */

class Track_Player_Style_Controls extends Controls_Manager
{

    public function register_controls($widget)
    {
        $widget->start_controls_section(
            'tracks_style_section',
            [
                'label' => esc_html__('Tracks', 'flow-audio'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $widget->end_controls_section();
    }
}
