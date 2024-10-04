<?php
get_header();
?>

<div class="album-archive">
    <h1>All Albums</h1>
    <div class="add-album-button">
        <a href="<?php echo admin_url('post-new.php?post_type=album'); ?>" class="button">Add New Album</a>
    </div>
    <div class="album-grid">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <div class="album-item">
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="album-cover" style="width: 300px; height: 300px; overflow: hidden;">
                                <?php the_post_thumbnail('medium'); ?>
                            </div>
                        <?php endif; ?>
                        <h2><?php the_title(); ?></h2>
                        <p><?php echo count(get_field('tracks')); ?> Tracks</p>
                        <p><?php the_time('F j, Y'); ?></p>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else : ?>
            <p>No albums found.</p>
        <?php endif; ?>
    </div>
</div>

<?php
get_footer();
?>