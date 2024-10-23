<?php

namespace Flow_Music_Player_For_Elementor\Widgets\Controls;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Utils;

/**
 * Controls for the Content section of Elementor panel
 */

class Album_Player_Content_Controls extends Controls_Manager
{

    public function register_controls($widget)
    {
        $widget->start_controls_section(
            'playlist_content_section',
            [
                'label' => esc_html__('Playlist', 'flow-audio'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $widget->add_control(
            'playlist_type',
            [
                'label' => esc_html__('Playlist Type', 'flow-audio'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'music' => esc_html__('Music', 'flow-audio'),
                    'preaching' => esc_html__('Preaching', 'flow-audio'),
                    'audiobook' => esc_html__('Audiobook', 'flow-audio'),
                ],
                'default' => 'music',
            ]
        );

        $widget->add_control(
            'album_source',
            [
                'label' => esc_html__('Album Source', 'flow-audio'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'manual' => esc_html__('Manual Input', 'flow-audio'),
                    'album_cpt' => esc_html__('Album Posts', 'flow-audio'),
                ],
                'default' => 'manual',
            ]
        );

        $widget->add_control(
            'album_cpt',
            [
                'label' => esc_html__('Select Album', 'flow-audio'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__('Enter Album ID or select from list', 'flow-audio'),
                'condition' => [
                    'album_source' => 'album_cpt',
                ],
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        // Add a separate control for the album dropdown
        $widget->add_control(
            'album_cpt_dropdown',
            [
                'label' => esc_html__('Or Select Album', 'flow-audio'),
                'type' => Controls_Manager::SELECT2,
                'options' => $this->get_album_options(),
                'condition' => [
                    'album_source' => 'album_cpt',
                ],
            ]
        );

        // Playlist Title Control
        $widget->add_control(
            'playlist_title',
            [
                'label' => esc_html__('Playlist Title', 'flow-audio'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('My Playlist', 'flow-audio'),
                'placeholder' => esc_html__('Enter playlist title', 'flow-audio'),
                'dynamic' => [
                    'active' => true, // Enable dynamic tags
                ],
                'condition' => [
                    'album_source' => 'manual',
                ],
            ]
        );

        // Playlist Artist Control
        $widget->add_control(
            'playlist_artist',
            [
                'label' => esc_html__('Artist', 'flow-audio'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Dag Heward-Mills', 'flow-audio'), // change in production
                'placeholder' => esc_html__('Enter artist name', 'flow-audio'),
                'dynamic' => [
                    'active' => true, // Enable dynamic tags
                ],
                'condition' => [
                    'album_source' => 'manual',
                ],
            ]
        );

        // Year Control
        $widget->add_control(
            'playlist_year',
            [
                'label' => esc_html__('Year', 'flow-audio'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__('Enter year of production', 'flow-audio'),
                'dynamic' => [
                    'active' => true, // Enable dynamic tags
                ],
                'condition' => [
                    'album_source' => 'manual',
                ],
            ]
        );

        $widget->add_control(
            'playlist_location',
            [
                'label' => esc_html__('Location', 'flow-audio'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__('Enter location', 'flow-audio'),
                'dynamic' => [
                    'active' => true, // Enable dynamic tags
                ],
                'condition' => [
                    'album_source' => 'manual',
                ],
            ]
        );

        // Cover Art Control (Image Field outside repeater)
        $widget->add_control(
            'cover_art',
            [
                'label' => esc_html__('Album Art', 'flow-audio'),
                'type' => Controls_Manager::MEDIA,
                'media_type' => 'image',
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'dynamic' => [
                    'active' => true, // Enable dynamic tags
                ],
                'condition' => [
                    'album_source' => 'manual',
                ],
            ]
        );

        $widget->end_controls_section();

        $widget->start_controls_section(
            'tracks_content_section',
            [
                'label' => esc_html__('Tracks', 'flow-audio'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        // Toggle to show track numbers
        $widget->add_control(
            'show_track_numbers',
            [
                'label' => __('Show Track numbers?', 'flow-audio'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'flow-audio'),
                'label_off' => __('No', 'flow-audio'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        // Toggle to use CPT's tracks
        // $widget->add_control(
        //     'use_cpt_tracks',
        //     [
        //         'label' => __('Use Tracks from Album Post', 'flow-audio'),
        //         'type' => Controls_Manager::SWITCHER,
        //         'label_on' => __('Yes', 'flow-audio'),
        //         'label_off' => __('No', 'flow-audio'),
        //         'return_value' => 'yes',
        //         'default' => 'no',
        //     ]
        // );

        // Repeater Field for Tracks
        $repeater = new Repeater();

        // Track Number Control
        $repeater->add_control(
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

            ]
        );

        // Track Title Control
        $repeater->add_control(
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
            ]
        );

        // $repeater->add_control(
        //     'audio_source',
        //     [
        //         'label' => esc_html__('Audio Source', 'flow-audio'),
        //         'type' => Controls_Manager::SELECT,
        //         'options' => [
        //             'media_library' => esc_html__('Media Library', 'flow-audio'),
        //             'custom_url' => esc_html__('Custom URL', 'flow-audio'),
        //         ],
        //         'default' => 'media_library',
        //     ]
        // );

        $repeater->add_control(
            'media_library',
            [
                'label' => esc_html__('Choose Audio File', 'flow-audio'),
                'type' => Controls_Manager::MEDIA,
                'media_type' => 'audio',
                'dynamic' => [
                    'active' => true, // Dynamic media support
                ],
                // 'condition' => [
                //     'audio_source' => 'media_library',
                // ],
            ]
        );

        // $repeater->add_control(
        //     'custom_url',
        //     [
        //         'label' => esc_html__('Enter Custom URL', 'flow-audio'),
        //         'type' => Controls_Manager::URL,
        //         'dynamic' => [
        //             'active' => true, // Dynamic URL support
        //         ],
        //         'placeholder' => esc_html__('Paste or type URL', 'flow-audio'),
        //         'condition' => [
        //             'audio_source' => 'custom_url',
        //         ],
        //     ]
        // );


        // Repeater: Add the tracks array
        $widget->add_control(
            'tracks',
            [
                'label' => esc_html__('Tracks', 'flow-audio'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [],
                'title_field' => '{{{ track_number }}} {{{ track_title }}}',
                'condition' => [
                    'album_source' => 'manual',
                ],
            ]
        );

        $widget->add_control(
            'adjust_track_count',
            [
                'label' => esc_html__('Adjust track count by:', 'flow-audio'),
                'type' => Controls_Manager::NUMBER,
                'default' => 0,
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );


        $widget->end_controls_section();

        // Links
        $widget->start_controls_section(
            'links_content_section',
            [
                'label' => esc_html__('Links', 'flow-audio'),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'album_source' => 'manual',
                ],
            ]
        );

        // Download Link Control
        $widget->add_control(
            'download_link',
            [
                'label' => esc_html__('Download Link', 'flow-audio'),
                'type' => Controls_Manager::URL,
                'placeholder' => esc_html__('Paste or type URL', 'flow-audio'),
                'options' => ['is_external', 'nofollow'],
                'default' => [
                    'url' => '',
                    'is_external' => true,
                    'nofollow' => true,
                ],
                'dynamic' => [
                    'active' => true, // Enable dynamic tags
                ],
                'condition' => [
                    'album_source' => 'manual',
                ],
            ]
        );

        $widget->end_controls_section();
    }

    private function get_album_options()
    {
        $albums = get_posts([
            'post_type' => 'album',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $options = ['' => esc_html__('Select Album', 'flow-audio')];
        foreach ($albums as $album) {
            $options[$album->ID] = $album->post_title;
        }

        return $options;
    }
}
