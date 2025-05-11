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

// Reset counter at the beginning of page render
function reset_track_index_counter()
{
    if (class_exists('\\Flow_Widgets_For_Elementor\\Dynamic_Tags\\Track_Custom_Field_Tag')) {
        Track_Custom_Field_Tag::reset_counter();
    }
}
add_action('wp_head', __NAMESPACE__ . '\\reset_track_index_counter', 1);
add_action('elementor/editor/before_enqueue_scripts', __NAMESPACE__ . '\\reset_track_index_counter', 1);

// Register album custom fields
function add_album_custom_fields_to_elementor($dynamic_tags_manager)
{
    require_once(__DIR__ . '/album-custom-fields.php');
    $dynamic_tags_manager->register_tag(new Album_Custom_Field_Tag());
}
add_action('elementor/dynamic_tags/register', __NAMESPACE__ . '\\add_album_custom_fields_to_elementor');
