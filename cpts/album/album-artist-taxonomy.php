<?php

function register_album_artist_taxonomy()
{
    $labels = array(
        'name'              => _x('Album Artists', 'taxonomy general name', 'flow-elementor-widgets'),
        'singular_name'     => _x('Album Artist', 'taxonomy singular name', 'flow-elementor-widgets'),
        'search_items'      => __('Search Album Artists', 'flow-elementor-widgets'),
        'all_items'         => __('All Album Artists', 'flow-elementor-widgets'),
        'parent_item'       => __('Parent Album Artist', 'flow-elementor-widgets'),
        'parent_item_colon' => __('Parent Album Artist:', 'flow-elementor-widgets'),
        'edit_item'         => __('Edit Album Artist', 'flow-elementor-widgets'),
        'update_item'       => __('Update Album Artist', 'flow-elementor-widgets'),
        'add_new_item'      => __('Add New Album Artist', 'flow-elementor-widgets'),
        'new_item_name'     => __('New Album Artist Name', 'flow-elementor-widgets'),
        'menu_name'         => __('Album Artists', 'flow-elementor-widgets'),
    );

    $args = array(
        'hierarchical'      => false,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'album-artist'),
        'show_in_rest'      => true,
        'public'            => true,
        'show_in_nav_menus' => true,
    );

    register_taxonomy('album_artist', array('album'), $args);
}
add_action('init', 'register_album_artist_taxonomy', 0);

function album_artist_taxonomy_add_image_field($taxonomy)
{
?>
    <div class="form-field term-group">
        <label for="album_artist_thumbnail"><?php _e('Album Artist Thumbnail', 'flow-elementor-widgets'); ?></label>
        <input type="hidden" id="album_artist_thumbnail" name="album_artist_thumbnail" class="custom_media_url" value="">
        <div id="album_artist_thumbnail_wrapper"></div>
        <p>
            <input type="button" class="button button-secondary album_artist_media_button" id="album_artist_media_button" name="album_artist_media_button" value="<?php _e('Add Thumbnail', 'flow-elementor-widgets'); ?>" />
            <input type="button" class="button button-secondary album_artist_media_remove" id="album_artist_media_remove" name="album_artist_media_remove" value="<?php _e('Remove Thumbnail', 'flow-elementor-widgets'); ?>" />
        </p>
    </div>
<?php
}
add_action('album_artist_add_form_fields', 'album_artist_taxonomy_add_image_field', 10, 2);

function album_artist_taxonomy_edit_image_field($term, $taxonomy)
{
    $image_id = get_term_meta($term->term_id, 'album_artist_thumbnail_id', true);
?>
    <tr class="form-field term-group-wrap">
        <th scope="row">
            <label for="album_artist_thumbnail"><?php _e('Album Artist Thumbnail', 'flow-elementor-widgets'); ?></label>
        </th>
        <td>
            <input type="hidden" id="album_artist_thumbnail" name="album_artist_thumbnail" value="<?php echo $image_id; ?>">
            <div id="album_artist_thumbnail_wrapper">
                <?php if ($image_id) {
                    echo wp_get_attachment_image($image_id, 'thumbnail');
                } ?>
            </div>
            <p>
                <input type="button" class="button button-secondary album_artist_media_button" id="album_artist_media_button" name="album_artist_media_button" value="<?php _e('Add Thumbnail', 'flow-elementor-widgets'); ?>" />
                <input type="button" class="button button-secondary album_artist_media_remove" id="album_artist_media_remove" name="album_artist_media_remove" value="<?php _e('Remove Thumbnail', 'flow-elementor-widgets'); ?>" />
            </p>
        </td>
    </tr>
<?php
}
add_action('album_artist_edit_form_fields', 'album_artist_taxonomy_edit_image_field', 10, 2);

function save_album_artist_taxonomy_image($term_id, $tt_id)
{
    if (isset($_POST['album_artist_thumbnail']) && '' !== $_POST['album_artist_thumbnail']) {
        $image = absint($_POST['album_artist_thumbnail']);
        update_term_meta($term_id, 'album_artist_thumbnail_id', $image);
    } else {
        delete_term_meta($term_id, 'album_artist_thumbnail_id');
    }
}
add_action('created_album_artist', 'save_album_artist_taxonomy_image', 10, 2);
add_action('edited_album_artist', 'save_album_artist_taxonomy_image', 10, 2);

function album_artist_taxonomy_admin_scripts($hook)
{
    global $taxonomy;

    if (!in_array($hook, array('edit-tags.php', 'term.php')) || $taxonomy !== 'album_artist') {
        return;
    }

    wp_enqueue_media();

    $js_file = plugin_dir_path(__FILE__) . 'src/album-artist-taxonomy-admin.js';
    $js_file_url = plugin_dir_url(__FILE__) . 'src/album-artist-taxonomy-admin.js';

    if (file_exists($js_file)) {
        wp_enqueue_script('album-artist-taxonomy-admin', $js_file_url, array('jquery'), filemtime($js_file), true);
    } else {
        error_log('Album artist taxonomy admin JS file not found: ' . $js_file);
    }
}
add_action('admin_enqueue_scripts', 'album_artist_taxonomy_admin_scripts');

function get_album_artist_thumbnail_url($term_id, $size = 'thumbnail')
{
    $image_id = get_term_meta($term_id, 'album_artist_thumbnail_id', true);
    if ($image_id) {
        $image_url = wp_get_attachment_image_url($image_id, $size);
        return $image_url ? $image_url : '';
    }
    return '';
}
