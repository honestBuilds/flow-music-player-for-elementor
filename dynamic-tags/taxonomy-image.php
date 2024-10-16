<?php

namespace Flow_Widgets_For_Elementor\Dynamic_Tags;

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Taxonomy_Image_Dynamic_Tag extends Data_Tag
{
    public function get_name()
    {
        return 'taxonomy-image';
    }

    public function get_title()
    {
        return __('Taxonomy Image', 'flow-elementor-widgets');
    }

    public function get_group()
    {
        return 'fmp-dynamic-tags-group';
    }

    public function get_categories()
    {
        return [TagsModule::IMAGE_CATEGORY];
    }

    protected function register_controls()
    {
        $this->add_control(
            'taxonomy',
            [
                'label' => __('Taxonomy', 'flow-elementor-widgets'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'artist' => __('Artist', 'flow-elementor-widgets'),
                    'album_artist' => __('Album Artist', 'flow-elementor-widgets'),
                ],
                'default' => 'artist',
            ]
        );
    }

    public function get_value(array $options = [])
    {
        $taxonomy = $this->get_settings('taxonomy');
        error_log("Taxonomy Image Dynamic Tag - Taxonomy: $taxonomy");

        $term_id = $this->get_current_term_id($taxonomy);

        if (!$term_id) {
            error_log("Taxonomy Image Dynamic Tag - No valid term ID found");
            return $this->get_default_value();
        }

        $meta_key = ($taxonomy === 'artist') ? 'artist_thumbnail_id' : 'album_artist_thumbnail_id';
        $image_id = get_term_meta($term_id, $meta_key, true);

        error_log("Taxonomy Image Dynamic Tag - Term ID: {$term_id}, Image ID: $image_id");

        if (!$image_id) {
            error_log("Taxonomy Image Dynamic Tag - No image found for term");
            return $this->get_default_value();
        }

        $image_url = wp_get_attachment_image_src($image_id, 'full');

        if (!$image_url) {
            error_log("Taxonomy Image Dynamic Tag - Failed to get image URL");
            return $this->get_default_value();
        }

        error_log("Taxonomy Image Dynamic Tag - Image URL: " . $image_url[0]);

        return [
            'id' => $image_id,
            'url' => $image_url[0],
        ];
    }

    private function get_current_term_id($taxonomy)
    {
        // Check if we're in an Elementor loop
        if (class_exists('\Elementor\Plugin')) {
            $document = \Elementor\Plugin::$instance->documents->get_current();
            if ($document) {
                $loop_settings = $document->get_settings('loop_settings');
                error_log("Loop settings: " . print_r($loop_settings, true));

                if ($loop_settings && isset($loop_settings['loop_template_id'])) {
                    $template_id = $loop_settings['loop_template_id'];
                    $current_data = \Elementor\Plugin::$instance->documents->get_current()->get_elements_data();
                    error_log("Current data: " . print_r($current_data, true));

                    foreach ($current_data as $element) {
                        if (isset($element['elType']) && $element['elType'] === 'widget' && isset($element['widgetType']) && $element['widgetType'] === 'loop-grid') {
                            $widget_settings = $element['settings'];
                            error_log("Widget settings: " . print_r($widget_settings, true));

                            if (isset($widget_settings['query_id'])) {
                                $query_id = $widget_settings['query_id'];
                                $current_item = \Elementor\Plugin::$instance->db->get_query_results($query_id);
                                error_log("Current item: " . print_r($current_item, true));

                                if ($current_item && isset($current_item['term_id'])) {
                                    return $current_item['term_id'];
                                }
                            }
                        }
                    }
                }
            }
        }

        // If we're not in a loop, try to get the current queried object
        $queried_object = get_queried_object();
        if ($queried_object instanceof WP_Term && $queried_object->taxonomy === $taxonomy) {
            return $queried_object->term_id;
        }

        error_log("No term ID found for taxonomy: $taxonomy");
        return null;
    }

    private function get_default_value()
    {
        return [
            'id' => null,
            'url' => '',
        ];
    }
}
