<?php

use Elementor\Utils;

?>
<script type="text/template" id="tmpl-flow-audio-playlist">
    <# 
    try {
        var albumSource = settings.album_source;
        var albumData = {};

        if (albumSource === 'manual') {
            // Handle manual input
            albumData = {
                playlist_title: settings.playlist_title,
                playlist_artist: settings.playlist_artist,
                playlist_year: settings.playlist_year,
                cover_art: settings.cover_art ? settings.cover_art.url : '<?php echo Utils::get_placeholder_image_src(); ?>',
                tracks: settings.tracks || [],
                download_link: settings.download_link ? settings.download_link.url : '',
                show_track_numbers: settings.show_track_numbers,
                playlist_type: settings.playlist_type || 'music'
            };
        } else if (albumSource === 'album_cpt' && settings.album_cpt) {
            // For CPT, we'll need to make an AJAX call to get the data
            // For now, we'll just show a placeholder message
            albumData = {
                playlist_title: 'Loading Album...',
                playlist_artist: '',
                playlist_year: '',
                cover_art: '<?php echo Utils::get_placeholder_image_src(); ?>',
                tracks: [],
                download_link: '',
                show_track_numbers: settings.show_track_numbers,
                playlist_type: settings.playlist_type || 'music'
            };
        }

        // Add plugin URLs for play and pause buttons
        albumData.playButtonImage = '<?php echo plugin_dir_url(__FILE__) . '../src/play-btn.svg'; ?>';
        albumData.pauseButtonImage = '<?php echo plugin_dir_url(__FILE__) . '../src/pause-btn.svg'; ?>';

        // Calculate total duration and track count
        var totalDurationSecs = 0;
        var trackCount = albumData.tracks.length;
        _.each(albumData.tracks, function(track) {
            totalDurationSecs += parseInt(track.track_duration_secs || 0);
        });
        albumData.total_duration = formatDuration(totalDurationSecs);
        albumData.track_count = trackCount;

        function formatDuration(seconds) {
            var minutes = Math.floor(seconds / 60);
            var remainingSeconds = seconds % 60;
            return minutes + ':' + (remainingSeconds < 10 ? '0' : '') + remainingSeconds;
        }
    #>

    <div id="flow-audio-playlist-body">
        <!-- Mobile Player -->
        <div class="md:hidden fixed bottom-0 left-0 right-0 bg-gray-900 flex flex-col z-20 shadow-lg text-white font-sans">
            <input id="progress-bar" type="range" min="0" max="100" value="0" style="display: none;">
            <div class="flex items-center justify-between p-3">
                <div class="flex items-center">
                    <img id="coverArt" src="{{ albumData.cover_art }}" alt="Cover Art" class="w-12 h-12 rounded-md mr-3">
                    <div>
                        <p class="font-semibold text-sm current-song-title m-track-title">{{ albumData.tracks[0] ? albumData.tracks[0].track_title : '' }}</p>
                        <p class="text-xs text-gray-400 artist-name">{{ albumData.playlist_artist }}</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <!-- ... (controls) ... -->
                </div>
            </div>
        </div>

        <!-- Background Cover Art -->
        <div class="fixed inset-0 bg-cover bg-center bg-no-repeat blur-3xl opacity-30 cover-art" style="background-image: url('{{ albumData.cover_art }}');"></div>

        <!-- Main Container -->
        <div class="container mx-auto px-4 py-8 relative z-10">
            <div class="flex flex-col md:flex-row">
                <!-- Cover and Info -->
                <div id="cover" class="md:w-2/5 mb-8 md:mb-0 text-center md:sticky md:top-8 md:self-start">
                    <div class="w-[300px] h-[300px] rounded-lg shadow-lg bg-gray-800 mx-auto bg-cover bg-no-repeat bg-center cover-art mb-5"
                        style="background-image: url('{{ albumData.cover_art }}'); box-shadow: 0 0 30px rgba(255, 255, 255, 0.3);"
                        alt="Cover Art"></div>

                    <h1 class="album-title text-2xl font-bold mt-11">{{ albumData.playlist_title }}</h1>
                    <p class="text-gray-400 artist-name no-mbe">{{ albumData.playlist_artist }}</p>
                    <p class="text-gray-400 album-info no-mbe">{{ albumData.playlist_type }} • {{ albumData.playlist_year }}</p>
                    <p class="text-gray-400 album-stats no-mbe">{{ albumData.track_count }} {{ albumData.track_count === 1 ? 'track' : 'tracks' }} • {{ albumData.total_duration }}</p>

                    <!-- Controls -->
                    <div class="flex space-x-4 mt-4 justify-center items-center">
                        <!-- ... (controls) ... -->
                    </div>
                </div>

                <!-- Song List -->
                <div id="songList" class="md:w-3/5 md:pl-8 p-1">
                    <ul>
                    <# _.each(albumData.tracks, function(track, index) { #>
                        <li class="flex justify-between items-center p-2 hover:bg-gray-800 cursor-pointer" data-track-index="{{ index }}">
                            <div class="track-info">
                                <# if (albumData.show_track_numbers === 'yes') { #>
                                    <span class="font-semibold track-number">{{ track.track_number }}</span>
                                <# } #>
                                <span class="ml-4 track-title">{{ track.track_title }}</span>
                            </div>
                            <span class="track-duration">{{ track.track_duration_formatted }}</span>
                        </li>
                    <# }); #>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <# } catch (e) { #>
        <p>An error occurred while rendering the preview: {{ e.message }}</p>
    <# } #>
</script>

<div id="flow-audio-playlist-preview"></div>

<script>
    jQuery(document).ready(function($) {
        var template = wp.template('flow-audio-playlist');
        $('#flow-audio-playlist-preview').html(template(elementor.config.widgets.widget_type_to_settings[elementor.config.widgets.activeWidget]));

        // If album_source is 'album_cpt', make an AJAX call to get the album data
        var settings = elementor.config.widgets.widget_type_to_settings[elementor.config.widgets.activeWidget];
        if (settings.album_source === 'album_cpt' && settings.album_cpt) {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'get_album_cpt_data',
                    album_id: settings.album_cpt
                },
                success: function(response) {
                    if (response.success) {
                        // Update the preview with the fetched data
                        $('#flow-audio-playlist-preview').html(template($.extend({}, settings, {
                            albumData: response.data
                        })));
                    } else {
                        console.error('Failed to fetch album data:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        }
    });
</script>