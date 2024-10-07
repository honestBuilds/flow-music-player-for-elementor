<?php

namespace Flow_Widgets_For_Elementor\Dynamic_Tags;

use Flow_Widgets_For_Elementor\Dynamic_Tags\Taxonomy_Image_Dynamic_Tag;

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

// Add this function to register the dynamic tag group
add_action('elementor/dynamic_tags/register',  __NAMESPACE__ . '\\add_fmp_dynamic_tags_group');

function add_fmp_dynamic_tags_group($dynamic_tags_manager)
{
    $dynamic_tags_manager->register_group(
        'fmp-dynamic-tags-group',
        [
            'title' => esc_html__('Flow Music Player', 'flow-elementor-widgets'),
        ]
    );
}

// Register the dynamic tag
add_action('elementor/dynamic_tags/register', __NAMESPACE__ . '\\register_flow_audio_dynamic_tags');



// Register taxonomy image
function register_flow_audio_dynamic_tags($dynamic_tags_manager)
{
    require_once(__DIR__ . '/taxonomy-image.php');
    $dynamic_tags_manager->register(new Taxonomy_Image_Dynamic_Tag());
}

// Register track custom fields
function add_track_custom_fields_to_elementor($dynamic_tags_manager)
{
    require_once(__DIR__ . '/track-custom-fields.php');
    $dynamic_tags_manager->register_tag(new Track_Custom_Field_Tag());
}
add_action('elementor/dynamic_tags/register', __NAMESPACE__ . '\\add_track_custom_fields_to_elementor');

// Register album custom fields
function add_album_custom_fields_to_elementor($dynamic_tags_manager)
{
    require_once(__DIR__ . '/album-custom-fields.php');
    $dynamic_tags_manager->register_tag(new Album_Custom_Field_Tag());
}
add_action('elementor/dynamic_tags/register', __NAMESPACE__ . '\\add_album_custom_fields_to_elementor');
