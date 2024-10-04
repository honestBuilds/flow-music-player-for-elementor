<?php
function register_artist_taxonomy()
{
    $labels = array(
        'name'              => _x('Artists', 'taxonomy general name', 'flow-elementor-widgets'),
        'singular_name'     => _x('Artist', 'taxonomy singular name', 'flow-elementor-widgets'),
        'search_items'      => __('Search Artists', 'flow-elementor-widgets'),
        'all_items'         => __('All Artists', 'flow-elementor-widgets'),
        'parent_item'       => __('Parent Artist', 'flow-elementor-widgets'),
        'parent_item_colon' => __('Parent Artist:', 'flow-elementor-widgets'),
        'edit_item'         => __('Edit Artist', 'flow-elementor-widgets'),
        'update_item'       => __('Update Artist', 'flow-elementor-widgets'),
        'add_new_item'      => __('Add New Artist', 'flow-elementor-widgets'),
        'new_item_name'     => __('New Artist Name', 'flow-elementor-widgets'),
        'menu_name'         => __('Artists', 'flow-elementor-widgets'),
    );

    $args = array(
        'hierarchical'      => false,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'artist'),
        'show_in_rest'      => true,
        'public'            => true,
        'show_in_nav_menus' => true,
    );

    register_taxonomy('artist', array('track'), $args);
}
add_action('init', 'register_artist_taxonomy', 0);

function add_artist_taxonomy_image_support()
{
    register_taxonomy_for_object_type('artist', 'attachment');
}
add_action('init', 'add_artist_taxonomy_image_support');

function artist_taxonomy_add_image_field($taxonomy)
{
?>
    <div class="form-field term-group">
        <label for="artist_thumbnail"><?php _e('Artist Thumbnail', 'flow-elementor-widgets'); ?></label>
        <input type="hidden" id="artist_thumbnail" name="artist_thumbnail" class="custom_media_url" value="">
        <div id="artist_thumbnail_wrapper"></div>
        <p>
            <input type="button" class="button button-secondary artist_media_button" id="artist_media_button" name="artist_media_button" value="<?php _e('Add Thumbnail', 'flow-elementor-widgets'); ?>" />
            <input type="button" class="button button-secondary artist_media_remove" id="artist_media_remove" name="artist_media_remove" value="<?php _e('Remove Thumbnail', 'flow-elementor-widgets'); ?>" />
        </p>
    </div>
<?php
}
add_action('artist_add_form_fields', 'artist_taxonomy_add_image_field', 10, 2);

function artist_taxonomy_edit_image_field($term, $taxonomy)
{
    $image_id = get_term_meta($term->term_id, 'artist_thumbnail_id', true);
?>
    <tr class="form-field term-group-wrap">
        <th scope="row">
            <label for="artist_thumbnail"><?php _e('Artist Thumbnail', 'flow-elementor-widgets'); ?></label>
        </th>
        <td>
            <input type="hidden" id="artist_thumbnail" name="artist_thumbnail" value="<?php echo esc_attr($image_id); ?>">
            <div id="artist_thumbnail_wrapper">
                <?php if ($image_id) {
                    echo wp_get_attachment_image($image_id, 'thumbnail');
                } ?>
            </div>
            <p>
                <input type="button" class="button button-secondary artist_media_button" id="artist_media_button" name="artist_media_button" value="<?php _e('Add Thumbnail', 'flow-elementor-widgets'); ?>" />
                <input type="button" class="button button-secondary artist_media_remove" id="artist_media_remove" name="artist_media_remove" value="<?php _e('Remove Thumbnail', 'flow-elementor-widgets'); ?>" />
            </p>
        </td>
    </tr>
<?php
}
add_action('artist_edit_form_fields', 'artist_taxonomy_edit_image_field', 10, 2);

function save_artist_taxonomy_image($term_id, $tt_id)
{
    if (isset($_POST['artist_thumbnail']) && '' !== $_POST['artist_thumbnail']) {
        $image_id = absint($_POST['artist_thumbnail']);
        update_term_meta($term_id, 'artist_thumbnail_id', $image_id);
    } else {
        delete_term_meta($term_id, 'artist_thumbnail_id');
    }
}
add_action('created_artist', 'save_artist_taxonomy_image', 10, 2);
add_action('edited_artist', 'save_artist_taxonomy_image', 10, 2);

function artist_taxonomy_admin_scripts($hook)
{
    global $taxonomy;

    if (!in_array($hook, array('edit-tags.php', 'term.php')) || $taxonomy !== 'artist') {
        return;
    }

    wp_enqueue_media();

    $js_file = plugin_dir_path(__FILE__) . 'src/artist-taxonomy-admin.js';
    $js_file_url = plugin_dir_url(__FILE__) . 'src/artist-taxonomy-admin.js';

    if (file_exists($js_file)) {
        wp_enqueue_script('artist-taxonomy-admin', $js_file_url, array('jquery'), filemtime($js_file), true);
    } else {
        error_log('Artist taxonomy admin JS file not found: ' . $js_file);
    }
}
add_action('admin_enqueue_scripts', 'artist_taxonomy_admin_scripts');

function get_artist_thumbnail_url($term_id, $size = 'thumbnail')
{
    $image_id = get_term_meta($term_id, 'artist_thumbnail_id', true);
    if ($image_id) {
        $image_url = wp_get_attachment_image_url($image_id, $size);
        return $image_url ? $image_url : '';
    }
    return '';
}
