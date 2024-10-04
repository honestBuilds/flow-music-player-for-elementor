<?php

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
