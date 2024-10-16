<?php

// Ajax call to get album data
add_action('wp_ajax_get_album_cpt_data', 'flow_get_album_cpt_data');

function flow_get_album_cpt_data()
{
    if (!isset($_POST['album_id'])) {
        wp_send_json_error('No album ID provided');
    }

    $album_id = intval($_POST['album_id']);
    $widget = new Flow_Music_Player_For_Elementor\Widgets\FMP_Album_Player_Widget();
    $album_data = $widget->get_album_cpt_data($album_id);

    if ($album_data) {
        wp_send_json_success($album_data);
    } else {
        wp_send_json_error('Failed to fetch album data');
    }
}

// Add cpts to elementor
function add_cpts_to_elementor($post_types)
{
    $post_types['album'] = __('Album', 'flow-elementor-widgets');
    $post_types['track'] = __('Track', 'flow-elementor-widgets');
    return $post_types;
}
add_filter('elementor/utils/get_public_post_types', 'add_cpts_to_elementor');

// Add thumbnail column to admin table
function flow_audio_manage_cpt_columns($columns)
{
    $new_columns = array(
        'cb' => $columns['cb'],
        'thumbnail' => __('Thumbnail', 'flow-audio'),
    );
    unset($columns['cb']);
    return array_merge($new_columns, $columns);
}

function flow_audio_custom_column_content($column, $post_id)
{
    switch ($column) {
        case 'thumbnail':
            $thumbnail_size = array(80, 80); // Reduced size
            if (has_post_thumbnail($post_id)) {
                echo get_the_post_thumbnail($post_id, $thumbnail_size);
            } else {
                echo '<img src="' . plugins_url('assets/images/default-thumbnail.png', __FILE__) . '" width="80" height="80" />';
            }
            break;
    }
}

// For tracks
add_filter('manage_track_posts_columns', 'flow_audio_manage_cpt_columns');
add_action('manage_track_posts_custom_column', 'flow_audio_custom_column_content', 10, 2);

// For albums
add_filter('manage_album_posts_columns', 'flow_audio_manage_cpt_columns');
add_action('manage_album_posts_custom_column', 'flow_audio_custom_column_content', 10, 2);

function flow_audio_admin_styles()
{
    echo '<style>
        .column-thumbnail { 
            width: 120px; 
            text-align: center; 
            padding: 8px 0;
        }
        .column-thumbnail img { 
            max-width: 80px; 
            height: auto; 
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .column-thumbnail img:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        }
        .wp-list-table .column-title { 
            padding-left: 8px; 
        }
    </style>';
}
add_action('admin_head', 'flow_audio_admin_styles');

function flow_audio_sortable_columns($columns)
{
    $columns['thumbnail'] = 'thumbnail';
    return $columns;
}
add_filter('manage_edit-track_sortable_columns', 'flow_audio_sortable_columns');
add_filter('manage_edit-album_sortable_columns', 'flow_audio_sortable_columns');
