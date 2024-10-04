<?php

?>
<div class="flow-audio-playlist-widget">
    <div class="cover-art-container" style="width: 40%; float: left;">
        <?php if ($cover_art) : ?>
            <img src="<?php echo wp_get_attachment_url($cover_art); ?>" alt="<?php echo esc_attr($playlist_title); ?> cover art" class="dynamic_image <?php echo esc_attr($aspect_ratio_class); ?>" style="width: 100%; border-radius: <?php echo esc_attr($settings['cover_art_border_radius']['top']); ?>px;" />
        <?php else : ?>
            <img src="<?php echo $img_placeholder; ?>" alt="<?php echo esc_attr($playlist_title); ?> cover art" class="cover-art" />
        <?php endif; ?>

        <div class="playback-controls" style="margin-top: 20px;">
            <button class="play-pause-button">Play</button>
            <button class="previous-button">Previous</button>
            <button class="next-button">Next</button>
            <button class="shuffle-button">Shuffle</button>
            <button class="repeat-button">Repeat</button>
            <div class="volume-slider">
                <input type="range" min="0" max="100" value="50">
            </div>
        </div>
    </div>

    <div class="tracks-container" style="width: 60%; float: left;">
        <?php if (! empty($playlist_title)) : ?>
            <h3 class="playlist-title" style="<?php echo esc_attr($title_typography); ?>"><?php echo esc_html($playlist_title); ?></h3>
        <?php endif; ?>

        <ul class="track-list">
            <?php if (! empty($tracks)) : ?>
                <?php foreach ($tracks as $track) :
                    $track_duration = get_audio_length($track);
                    $track_title = get_audio_title($track);
                ?>
                    <li class="track-item">
                        <span class="track-number"><?php echo esc_html($track['track_number']); ?></span>
                        <span class="track-title"><?php echo empty($track['track_title']) ? $track_title : esc_html($track['track_title']); ?></span>
                        <span class="track-duration"><?php echo esc_html($track_duration); ?></span>
                    </li>
                <?php endforeach; ?>
            <?php else : ?>
                <li>No tracks available.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div style="clear: both;"></div>

</div>