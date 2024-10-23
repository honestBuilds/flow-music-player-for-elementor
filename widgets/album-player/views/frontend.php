<div id="fmp-album-player" data-post-id="<?php echo esc_attr(get_the_ID()); ?>" data-post-type="<?php echo esc_attr(get_post_type()); ?>" data-post-url="<?php echo esc_url(get_permalink()); ?>">

    <!-- Floating Player -->
    <div id="floating-player" class="fixed bottom-0 left-0 right-0 flex flex-col z-20 shadow-lg text-white font-sans">
        <div id="progress-bar-container" class="w-full bg-gray-700">
            <div id="progress-bar-fill" class="h-full bg-red-500 relative">
                <div id="progress-bar-head" class="absolute right-0 top-1/2 transform translate-x-1/2 -translate-y-1/2 w-3 h-3 bg-red-500 rounded-full"></div>
            </div>
        </div>
        <div class="flex items-center justify-between p-3 floating-player-content">
            <div class="flex items-center">
                <img id="coverArt" src="<?php echo esc_url($cover_art_url); ?>" alt="Cover Art" class="w-12 h-12 rounded-md mr-3">
                <div>
                    <p class="font-semibold text-sm current-song-title m-track-title"><?php echo esc_html($tracks_arr[0]['track_title'] ?? ''); ?></p>
                    <p class="text-xs artist-name"><?php echo esc_html($playlist_artist); ?></p>
                </div>
            </div>
            <div class="flex items-center">
                <button class="mr-4 prev-button">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button class="play-button bg-white text-black p-[17px] rounded-full overflow-hidden w-12 h-12 relative">
                    <div class="button-content relative w-full h-full">
                        <img src="<?php echo plugin_dir_url(__FILE__) . '../src/play-btn.svg'; ?>" alt="Play Button" class="play-icon absolute inset-0 m-auto">
                        <div class="spinner-border hidden absolute inset-0 m-auto border-3 border-[#2f2f2f] border-t-transparent rounded-full"></div>
                    </div>
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
    <div class="fixed inset-0 bg-cover bg-center bg-no-repeat blur-3xl opacity-30 cover-art z-0"
        style="background-image: url('<?php echo esc_url($cover_art_url); ?>');"></div>

    <!-- Main Container -->
    <div class="container mx-auto py-8 relative z-10">
        <div class="flex flex-col md:flex-row">
            <!-- Cover and Info -->
            <div id="cover" class="md:w-2/5 mb-8 md:mb-0 text-center md:sticky md:top-8 md:self-start">
                <div class="w-[300px] h-[300px] rounded-lg shadow-lg bg-gray-800 mx-auto bg-cover bg-no-repeat bg-center cover-art mb-5"
                    style="background-image: url('<?php echo esc_url($cover_art_url); ?>'); box-shadow: 0 0 30px rgba(255, 255, 255, 0.3);"
                    alt="Cover Art"></div>

                <h1 class="album-title text-2xl font-bold mt-11"><?php echo esc_html($playlist_title); ?></h1>
                <?php if (!empty($playlist_location)): ?>
                    <p class="album-location no-mbe font-bold"><?php echo esc_html($playlist_location); ?></p>
                <?php endif; ?>
                <p class="artist-name no-mbe"><?php echo esc_html($playlist_artist); ?></p>
                <p class="album-info no-mbe"><?php echo esc_html(ucfirst($playlist_type)) .  (empty($playlist_year) ? '' : ' • ' . esc_html($playlist_year)); ?></p>
                <?php
                $track_count = is_array($tracks_arr) ? count($tracks_arr) : 0;
                $track_count = $track_count - $adjust_track_count; // adjust track count
                $count_unit = $track_count === 1 ? substr($count_unit, 0, -1) : $count_unit; // handle plural or singular
                $track_info = $track_count > 0 ? $track_count . ' ' . esc_html($count_unit) . ' • ' . esc_html($total_duration) : 'No tracks';
                ?>
                <p class="album-stats no-mbe"><?php echo $track_info; ?></p>

                <!-- Controls -->
                <div class="flex space-x-4 mt-4 justify-center items-center">
                    <!-- Download Button -->
                    <a href="<?php echo $download_link ? esc_url($download_link) : '#'; ?>" class="download-link bg-gray-800 p-2 rounded-full text-white" target="_self" rel="noopener noreferrer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor"
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
                    <button class="play-button bg-white text-black p-[17px] rounded-full overflow-hidden w-12 h-12 relative">
                        <div class="button-content relative w-full h-full">
                            <img src="<?php echo plugin_dir_url(__FILE__) . '../src/play-btn.svg'; ?>" alt="Play Button" class="play-icon absolute inset-0 m-auto">
                            <div class="spinner-border hidden absolute inset-0 m-auto border-3 border-[#2f2f2f] border-t-transparent rounded-full"></div>
                        </div>
                    </button>
                    <!-- Next Button -->
                    <button class="bg-gray-800 p-2 rounded-full next-button text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    <!-- Share Button -->
                    <button class="bg-gray-800 p-1 rounded-full text-white share-button">
                        <svg fill="currentColor" width="32" height="32" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path d="M30.3 13.7L25 8.4l-5.3 5.3-1.4-1.4L25 5.6l6.7 6.7z"></path>
                                <path d="M24 7h2v21h-2z"></path>
                                <path d="M35 40H15c-1.7 0-3-1.3-3-3V19c0-1.7 1.3-3 3-3h7v2h-7c-.6 0-1 .4-1 1v18c0 .6.4 1 1 1h20c.6 0 1-.4 1-1V19c0-.6-.4-1-1-1h-7v-2h7c1.7 0 3 1.3 3 3v18c0 1.7-1.3 3-3 3z"></path>
                            </g>
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
                                    <span class="album-track-title"><?php echo esc_html($track['track_title'] ?? ''); ?></span>
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