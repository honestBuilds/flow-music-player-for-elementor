<?php
get_header();
?>

<div class="album-single">
    <?php if (has_post_thumbnail()) : ?>
        <div class="album-cover" style="width: 300px; height: 300px;">
            <?php the_post_thumbnail('medium'); ?>
        </div>
    <?php endif; ?>

    <h1><?php the_title(); ?></h1>
    <p>Year: <?php echo get_field('year'); ?></p>
    <p>Type: <?php echo get_field('type'); ?></p>

    <?php if (have_rows('tracks')) : ?>
        <h3>Tracks</h3>
        <ul>
            <?php while (have_rows('tracks')) : the_row(); ?>
                <li>
                    Track <?php echo get_sub_field('track_number'); ?>: <?php echo get_sub_field('track_title'); ?>
                    <audio controls src="<?php echo get_sub_field('track_file'); ?>"></audio>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php endif; ?>
</div>

<?php
get_footer();
?>