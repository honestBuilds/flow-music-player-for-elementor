<?php
$settings = $this->get_settings_for_display();

$track_data = $settings['track_source'] === 'track_cpt'
    ? $this->get_track_cpt_data($settings['track_cpt'])
    : $this->get_manual_track_data($settings);

if (!$track_data) {
    return;
}

$track_title = $track_data['track_title'];
$track_artist = $track_data['track_artist'];
$track_url = $track_data['track_url'];
$track_duration = $track_data['track_duration_formatted'];

$featured_image = '';
if ($settings['track_source'] === 'track_cpt') {
    $featured_image = $track_data['featured_image_url'];
} else {
    // For manual input, use the track_image control
    $featured_image = $settings['track_image']['url'] ?? '';
}

$use_blurred_background = $settings['use_blurred_background'] === 'yes';
$track_image_url = $track_data['featured_image_url'] ?? '';

// Prepare the artist metadata
$artist_metadata = !empty($track_artist)
    ? "First Love Music ft. {$track_artist}"
    : "First Love Music";

$track_metadata = [
    'track_title' => $track_title,
    'track_artist' => $artist_metadata,
    'album_title' => $track_data['album_title'] ?? '',
    'featured_image_url' => $featured_image,
    // ... any other relevant metadata ...
];

// Encode the track metadata as JSON
$track_metadata_json = json_encode($track_metadata);

// Add this at the beginning of your frontend view
// if ($use_blurred_background && $track_image_url) {
//     echo '<div class="flow-audio-track-player-background fixed inset-0 bg-cover bg-center bg-no-repeat blur-3xl opacity-30" style="background-image: url(\'' . esc_url($track_image_url) . '\');"></div>';
// }
// if ($use_blurred_background && $featured_image) {
//     echo '<div class="flow-audio-track-player fixed inset-0 bg-cover bg-center bg-no-repeat blur-3xl opacity-30" style="background-image: url(\'' . esc_url($featured_image) . '\');" data-track-metadata=\'' . $track_metadata_json . '\'>';
// } else {
//     echo '<div class="flow-audio-track-player" data-track-metadata="' . $track_metadata_json . '">';
// }
?>


<div class="flow-audio-track-player" data-track-metadata='<?php echo $track_metadata_json; ?>'>
    <?php if ($featured_image) : ?>
        <div class="flow-audio-track-player-background bg-cover bg-center bg-no-repeat" style="background-image: url('<?php echo esc_url($featured_image); ?>');" data-track-metadata='<?php echo $track_metadata_json; ?>'>
        <div class="track-image z-index-1">
            <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($track_title); ?>">
        </div>
    <?php endif; ?>
    <div class="track-content z-index-1">
        <div class="track-info">
            <div class="track-details">
                <div class="track-title"><?php echo esc_html($track_title); ?></div>
                <div class="track-artist"><?php echo esc_html($track_artist); ?></div>
            </div>
            <button class="play-pause-btn"></button>
        </div>
        <div class="progress-duration-container">
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
            </div>
            <div class="duration"><?php echo esc_html($track_duration); ?></div>
        </div>
        <audio src="<?php echo esc_url($track_url); ?>"></audio>
    </div>
</div>