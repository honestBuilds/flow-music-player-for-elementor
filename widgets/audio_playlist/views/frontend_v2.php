<?php

use Elementor\Plugin;

?>
<div id="flow-audio-playlist-body">

    <!-- Mobile Player -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-gray-900 flex flex-col z-20 shadow-lg text-white font-sans">
        <input id="progress-bar" type="range" min="0" max="100" value="0" style="display: none;">
        <div class="flex items-center justify-between p-3">
            <div class="flex items-center">
                <img id="coverArt" src="<?php echo esc_url($cover_art_url); ?>" alt="Cover Art" class="w-12 h-12 rounded-md mr-3">
                <div>
                    <p class="font-semibold text-sm current-song-title m-track-title"><?php echo esc_html($tracks_arr[0]['track_title'] ?? ''); ?></p>
                    <p class="text-xs text-gray-400 artist-name"><?php echo esc_html($playlist_artist); ?></p>
                </div>
            </div>
            <div class="flex items-center">
                <button class="mr-4 prev-button">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button class="play-button bg-white text-black p-[17px] rounded-full overflow-hidden w-12 h-12">
                    <img src="<?php echo plugin_dir_url(__FILE__) . '../src/play-btn.svg'; ?>" alt="Play Button" class="object-cover">
                </button>
                <button class="ml-4 next-button">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Background Cover Art -->
    <div class="fixed inset-0 bg-cover bg-center bg-no-repeat blur-3xl opacity-30 cover-art"
        style="background-image: url('<?php echo esc_url($cover_art_url); ?>');"></div>

    <!-- Main Container -->
    <div class="container mx-auto px-4 py-8 relative z-10">
        <div class="flex flex-col md:flex-row">
            <!-- Cover and Info -->
            <div id="cover" class="md:w-2/5 mb-8 md:mb-0 text-center md:sticky md:top-8 md:self-start">
                <div class="w-[300px] h-[300px] rounded-lg shadow-lg bg-gray-800 mx-auto bg-cover bg-no-repeat bg-center cover-art mb-5"
                    style="background-image: url('<?php echo esc_url($cover_art_url); ?>'); box-shadow: 0 0 30px rgba(255, 255, 255, 0.3);"
                    alt="Cover Art"></div>

                <h1 class="album-title text-2xl font-bold mt-11"><?php echo esc_html($playlist_title); ?></h1>
                <p class="text-gray-400 artist-name no-mbe"><?php echo esc_html($playlist_artist); ?></p>
                <p class="text-gray-400 album-info no-mbe"><?php echo esc_html(ucfirst($playlist_type)) .  (empty($playlist_year) ? '' : ' • ' . esc_html($playlist_year)); ?></p>
                <?php
                $track_count = is_array($tracks_arr) ? count($tracks_arr) : 0;
                $track_info = $track_count > 0 ? $track_count . ' ' . esc_html($count_unit) . ' • ' . esc_html($total_duration) : 'No tracks';
                ?>
                <p class="text-gray-400 album-stats no-mbe"><?php echo $track_info; ?></p>

                <!-- Controls -->
                <div class="flex space-x-4 mt-4 justify-center items-center">
                    <!-- Download Button -->
                    <a href="<?php echo $download_link ? esc_url($download_link) : '#'; ?>" class="bg-gray-800 p-2 rounded-full text-white" target="<?php echo $download_link ? '_blank' : '_self'; ?>" rel="noopener noreferrer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                    </a>
                    <!-- Previous Button -->
                    <button class="bg-gray-800 p-2 rounded-full prev-button text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <!-- Play Button -->
                    <button class="play-button bg-white text-black p-[17px] rounded-full overflow-hidden w-12 h-12">
                        <img src="<?php echo plugin_dir_url(__FILE__) . '../src/play-btn.svg'; ?>" alt="Play Button" class="object-cover">
                    </button>
                    <!-- Next Button -->
                    <button class="bg-gray-800 p-2 rounded-full next-button text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    <!-- Refresh Button -->
                    <button class="bg-gray-800 p-2 rounded-full text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Song List -->
            <div id="songList" class="md:w-3/5 md:pl-8 p-1">
                <ul>
                    <?php if (!empty($tracks_arr) && is_array($tracks_arr)): ?>
                        <?php foreach ($tracks_arr as $index => $track): ?>
                            <li class="flex justify-between items-center p-2 hover:bg-gray-800 cursor-pointer"
                                data-track-index="<?php echo esc_attr($index); ?>"
                                data-track-url="<?php echo esc_url($track['track_url'] ?? ''); ?>"
                                data-track-title="<?php echo esc_attr($track['track_title'] ?? ''); ?>"
                                data-track-number="<?php echo esc_attr($track['track_number'] ?? ''); ?>">
                                <div class="album-track-info">
                                    <?php if ($show_track_numbers === 'yes'): ?>
                                        <span class="font-semibold track-number"><?php echo esc_html($track['track_number'] ?? ''); ?></span>
                                    <?php endif; ?>
                                    <span class="ml-4 album-track-title"><?php echo esc_html($track['track_title'] ?? ''); ?></span>
                                </div>
                                <span class="track-duration"><?php echo esc_html($track['track_duration_formatted'] ?? ''); ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No tracks found.</p>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>