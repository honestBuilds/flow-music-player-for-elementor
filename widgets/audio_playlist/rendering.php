<?php
// live cover
$live_cover_art = !wp_get_attachment_url($playlist->cover_art) ? $img_placeholder : wp_get_attachment_url($playlist->cover_art);
?>

<div id="flow-audio-playlist-body">
    <!-- Loading Spinner -->
    <div id="loading-overlay" class="bg-gray-900">
        <div class="spinner"></div>
    </div>

    <!-- Mobile Player -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-gray-900 flex flex-col z-20 shadow-lg text-white font-sans">
        <input id="progress-bar" type="range" min="0" max="100" value="0" style="display: none;">
        <!-- <div class="progress-bar w-full h-1 bg-gradient-to-r from-blue-500 to-purple-500" style="width: 0%;"></div> -->
        <div class="flex items-center justify-between p-3">
            <div class="flex items-center">
                <img id="coverArt" src="<?php echo $live_cover_art; ?>" alt="<?php echo $playlist_title; ?> Cover" class="w-12 h-12 rounded-md mr-3">
                <div>
                    <p class="font-semibold text-sm current-song-title m-track-title"></p>
                    <p class="text-xs text-gray-400 artist-name"></p>
                </div>
            </div>
            <div class="flex items-end">
                <button class="mr-4 prev-button">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button class="play-button bg-white text-black p-[17px] rounded-full overflow-hidden w-12 h-12">
                    <img src="<?php echo plugins_url('src/play-btn.svg', __FILE__); ?>" alt="Play Button" class="object-cover">
                </button>
                <button class="ml-4 next-button">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>

    </div>

    <div class="fixed inset-0 bg-cover bg-center bg-no-repeat blur-3xl opacity-30 cover-art"
        style="background-image: url('<?php echo $live_cover_art; ?>');"></div>
    <div class="container mx-auto px-4 py-8 relative z-10">
        <div class="flex flex-col md:flex-row">
            <div id="cover" class="md:w-2/5 mb-8 md:mb-0 text-center md:sticky md:top-8 md:self-start">
                <div class="w-[300px] h-[300px] rounded-lg shadow-lg bg-gray-800 mx-auto bg-cover bg-no-repeat bg-center cover-art mb-5"
                    style="background-image: url('<?php echo $live_cover_art; ?>'); box-shadow: 0 0 30px rgba(255, 255, 255, 0.3);"
                    alt="<?php echo $playlist_title; ?> Cover"></div>

                <h1 class="album-title text-2xl font-bold mt-11"></h1>
                <p class="text-gray-400 artist-name no-mbe"></p>
                <p class="text-gray-400 album-info no-mbe"></p>
                <p class="text-gray-400 album-stats no-mbe"></p>
                <!-- controls -->
                <div class="flex space-x-4 mt-4 justify-center items-center">
                    <button class="bg-gray-800 p-2 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg></button>
                    <button class="bg-gray-800 p-2 rounded-full prev-button"><svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg></button>
                    <button class="play-button bg-white text-black p-[17px] rounded-full overflow-hidden w-12 h-12">
                        <img src="<?php echo plugins_url('src/play-btn.svg', __FILE__); ?>" alt="Play Button" class="object-cover">
                    </button>
                    <button class="bg-gray-800 p-2 rounded-full next-button"><svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg></button>
                    <button class="bg-gray-800 p-2 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg></button>
                </div>
            </div>
            <div id="songList" class="md:w-3/5 md:pl-8 p-2">
                <!-- Song list will be dynamically populated here -->
            </div>
        </div>
    </div>
</div>
<script>
    let currentTrack;
    let isPlaying = false;
    let audio = new Audio();

    var numberOfUnit = "<?php echo $number_of_label; ?>";
    var coverArtAspectRatio = "<?php echo $cover_art_aspect_ratio; ?>";
    var playlistType = "<?php echo $playlist_type; ?>";
    var playlist = <?php echo json_encode($playlist); ?>;
    var cover = playlist["cover_art"];

    // Example albumData
    const albumData = {
        title: playlist["title"],
        artist: playlist["artist"],
        year: playlist["year"],
        coverArt: cover,
        duration: "",
        tracks: playlist["tracks"],
        loadTracks: async function() {

            let totalDuration = 0;

            for (let track of this.tracks) {
                const audio = new Audio(track["url"]);
                await new Promise(resolve => {
                    audio.addEventListener('loadedmetadata', () => {
                        // const title = track["url"].split('/').pop().replace('.mp3', '');
                        const duration = audio.duration;
                        totalDuration += duration;
                        // this.tracks.push({ title, duration: formatDuration(duration), url });

                        resolve();
                    });
                });
            }

            this.duration = formatDuration(totalDuration);
        }
    };

    function formatDuration(seconds) {
        const hours = Math.floor(seconds / 3600); // Get hours
        const minutes = Math.floor((seconds % 3600) / 60); // Get minutes
        const remainingSeconds = Math.floor(seconds % 60); // Get remaining seconds

        // Format the time based on whether it's 60 minutes or more
        if (hours > 0) {
            return `${hours}:${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
        } else {
            return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
        }
    }

    function renderWidgetTemplate() {
        // update with live data
        albumData.loadTracks().then(() => {
            updateUI(albumData);
            setupEventListeners();
            updateProgressBar();
        });
    }

    // function renderWidgetTemplate() {
    //     $.ajax({
    //         url: '<?php //echo admin_url('admin-ajax.php'); 
                        ?>', // Use admin_url for the AJAX endpoint
    //         type: 'POST',
    //         data: {
    //             action: 'load_music_player' // Action name for your PHP handler
    //         },
    //         success: function(response) {
    //             console.log('AJAX successful');
    //             // inject html template
    //             $('#flow-audio-playlist-body').html(response);
    //             // update with live data
    //             albumData.loadTracks().then(() => {
    //                 updateUI(albumData);
    //                 setupEventListeners();
    //                 updateProgressBar();
    //             });
    //         },
    //         error: function(error) {
    //             console.error('AJAX Error:', error);
    //             $('#loadingOverlay').hide(); // Hide the spinner on error
    //         }
    //     });
    // }

    // Function to check if we're inside the Elementor editor
    function isElementorEditor() {
        return window.elementor !== undefined && window.elementor.isEditMode();
    }

    //  function to hide the loading spinner overlay
    function hideSpinner() {
        console.log('hideSpinner called');
        var loadingOverlay = document.getElementById('loading-overlay');
        loadingOverlay.style.display = "none";
    }

    function coverWidgetOnly() {
        console.log('coverWidgetOnly called');
        var loadingOverlay = document.getElementById('loading-overlay');
        loadingOverlay.style.position = "absolute";
        loadingOverlay.style.width = "100%";
        loadingOverlay.style.height = "100%";
    }

    // Function to show the loading spinner
    function showSpinner() {
        const loadingOverlay = document.getElementById("loading-overlay");
        if (loadingOverlay) {
            loadingOverlay.style.display = "block"; // Show spinner
        }
    }

    jQuery(document).ready(function($) {
        // Check if Elementor is in edit mode
        if (typeof elementorFrontend !== 'undefined' && elementorFrontend.isEditMode()) {
            // make spinner small
            coverWidgetOnly();
            // hideSpinner();
            // Hook into the Elementor's lifecycle events
            $(window).on('elementor/frontend/init', function() {
                elementorFrontend.hooks.addAction('frontend/element_ready/flow_audio_playlist_widget.default', function(scope, $) {
                    renderWidgetTemplate();
                    hideSpinner();
                    // Listen for widget updates in the editor
                    if (window.elementor) {
                        elementor.channels.editor.on('change', function(model) {
                            console.log('Widget setting changed:', model);
                            // Reinitialize player when settings are updated
                            if (model.attributes.name === 'flow_audio_playlist_widget') {
                                renderWidgetTemplate();
                                hideSpinner();
                            }
                        });

                        elementor.channels.editor.on('document:loaded', function() {
                            console.log('Document loaded');
                            // Reinitialize player when settings are updated
                            if (model.attributes.name === 'flow_audio_playlist_widget') {
                                renderWidgetTemplate();
                                hideSpinner();
                            }
                        });
                    }
                });
            });
        } else {
            // Normal frontend loading
            renderWidgetTemplate();
        }
    });

    // document.addEventListener('DOMContentLoaded', async () => {
    //     jQuery(window).on('elementor/frontend/init', function() {
    //         if (isElementorEditor()) {
    //             console.log("Rendering inside Elementor editor...");
    //             elementorFrontend.hooks.addAction('frontend/element_ready/flow_audio_playlist_widget.default', renderWidgetTemplate);
    //         } else {
    //             console.log("Rendering on the frontend...");
    //             renderWidgetTemplate(); // For the case Elementor isn't loaded
    //         }
    //     });

    // jQuery(window).on('elementor/frontend/init', function() {
    //     elementorFrontend.hooks.addAction('frontend/element_ready/flow_audio_playlist_widget.default', function($scope, $) {
    //         // Your script here
    //         albumData.loadTracks().then(() => {
    //             updateUI(albumData);
    //             setupEventListeners();
    //             updateProgressBar();
    //         });
    //     });
    // });

    // jQuery(document).ready(function($) {
    //     $(document).on('elementor:init', function() {
    //         elementor.on('update', function(event) {
    //             if (event.target.classList.contains('elementor-widget-flow_audio_playlist_widget')) {
    //                 // Your code to update the widget here
    //                 albumData.loadTracks().then(() => {
    //                     updateUI(albumData);
    //                     setupEventListeners();
    //                     updateProgressBar();
    //                 });
    //             }
    //         });
    //     });
    // });

    // });

    function updateUI(albumData) {
        console.log("Loading tracks...")
        const body = document.querySelector('body');
        body.classList.add("bg-gray-900", "text-white", "font-sans", "relative");
        const p = document.querySelector('p');
        p.style.marginBlockEnd = "0rem";

        document.title = `${albumData.title} - ${albumData.artist}`;
        document.querySelector('.album-title').textContent = albumData.title;
        document.querySelectorAll('.artist-name').forEach(el => el.textContent = albumData.artist);
        document.querySelector('.album-info').textContent = `${playlistType} • ${albumData.year}`;
        document.querySelector('.album-stats').textContent = `${albumData.tracks.length} ${numberOfUnit} • ${albumData.duration}`;

        const coverArts = document.querySelectorAll('.cover-art');
        coverArts.forEach(coverArt => {
            coverArt.style.backgroundImage = `url('${albumData.coverArt}')`;
        });

        const thumbnailForPlayer = document.getElementById('coverArt');
        thumbnailForPlayer.src = albumData.coverArt;
        thumbnailForPlayer.style.borderRadius = "8px";

        const songList = document.getElementById('songList');
        songList.innerHTML = '';
        albumData.tracks.forEach((track, index) => {
            const li = document.createElement('li');
            li.className = 'flex justify-between items-center p-2 hover:bg-gray-800 cursor-pointer';
            li.innerHTML = `
                    <div class="track-info">
                        <span class="font-semibold track-number">${track["track_number"]}</span>
                        <span class="ml-4 track-title">${track["title"]}</span>
                    </div>
                    <span>${track["duration"]}</span>
                `;
            li.addEventListener('click', () => playSong(index));
            songList.appendChild(li);
        });
        const controlButtons = document.querySelectorAll('button');
        controlButtons.forEach(btn => {
            btn.style.border = "none";
            btn.style.color = "white";
        });
    }

    function setupEventListeners() {
        document.getElementById('coverArt').addEventListener('load', () => {
            hideSpinner();
        });
        document.querySelectorAll('.play-button').forEach(button => {
            button.addEventListener('click', togglePlayPause);
        });
        document.querySelectorAll('.prev-button').forEach(button => {
            button.addEventListener('click', playPrevious);
        });
        document.querySelectorAll('.next-button').forEach(button => {
            button.addEventListener('click', playNext);
        });

        // Media session handlers
        // Add event listener to update media session metadata when the audio is playing
        audio.addEventListener('play', () => {
            if ('mediaSession' in navigator) {
                navigator.mediaSession.metadata = new MediaMetadata({
                    title: albumData.tracks[currentTrack]['title'], // Update with the track title
                    artist: albumData.artist, // Update with the artist name
                    album: albumData.title, // Update with the album name
                    artwork: [{
                        src: albumData.coverArt, // Path to album art image
                        sizes: '512x512', // Size of the image
                        type: 'image/webp'
                    }]
                });
            }
        });

        // Handle playback controls
        if ('mediaSession' in navigator) {
            // Handle play action
            navigator.mediaSession.setActionHandler('play', function() {
                audio.play();
            });

            // Handle pause action
            navigator.mediaSession.setActionHandler('pause', function() {
                audio.pause();
            });

            // Handle seekbackward action
            navigator.mediaSession.setActionHandler('seekbackward', function() {
                audio.currentTime = Math.max(audio.currentTime - 10, 0);
            });

            // Handle seekforward action
            navigator.mediaSession.setActionHandler('seekforward', function() {
                audio.currentTime = Math.min(audio.currentTime + 10, audio.duration);
            });

            // Handle seekto action for user interaction with the progress bar
            navigator.mediaSession.setActionHandler('seekto', function(details) {
                if (details.fastSeek && 'fastSeek' in audio) {
                    // If fastSeek is supported by the browser, use it
                    audio.fastSeek(details.seekTime);
                } else {
                    // Otherwise, set the current time to the seeked position
                    audio.currentTime = details.seekTime;
                }

                // Update the position state to reflect the new position
                updatePositionState();
            });
        }

        const progressBar = document.getElementById('progress-bar');
        progressBar.addEventListener('input', () => {
            const seekTime = (progressBar.value / 100) * audio.duration;
            audio.currentTime = seekTime;
        });

    }

    // Function to update the position state (as shown before)
    function updatePositionState() {
        if ('setPositionState' in navigator.mediaSession) {
            navigator.mediaSession.setPositionState({
                duration: audio.duration,
                playbackRate: audio.playbackRate,
                position: audio.currentTime
            });

            const progressBar = document.getElementById('progress-bar');
            progressBar.value = audio.currentTime;
        }
    }

    function togglePlayPause() {
        if (isPlaying) {
            audio.pause();
        } else {
            if (!audio.src) {
                playSong(0);
            } else {
                audio.play();
            }
        }
        isPlaying = !isPlaying;
        updatePlayButton();
    }

    function playPrevious() {
        if (currentTrack > 0) {
            currentTrack--;
            playSong(currentTrack);
        }
    }

    function playNext() {
        if (currentTrack < albumData.tracks.length - 1) {
            currentTrack++;
            playSong(currentTrack);
        }
    }

    function playSong(index) {
        currentTrack = index;
        audio.src = albumData.tracks[index].url;
        audio.play();
        isPlaying = true;
        updatePlayButton();
        updateCurrentSongInfo(index);
        const progressBar = document.getElementById('progress-bar');
        if (progressBar.style.display = "none") {
            progressBar.style.display = "block";
            progressBar.style.width = "100%";
        }
    }

    function updatePlayButton() {
        const playButtons = document.querySelectorAll('.play-button img');
        playButtons.forEach(button => {
            button.src = isPlaying ? "<?php echo plugins_url('src/pause-btn.svg', __FILE__); ?>" : "<?php echo plugins_url('src/play-btn.svg', __FILE__); ?>";
        });
    }

    function updateProgressBar() {
        const progressBars = document.querySelectorAll('.progress-bar');
        audio.addEventListener('timeupdate', () => {
            const progress = (audio.currentTime / audio.duration) * 100;
            progressBars.forEach(bar => {
                bar.style.width = `${progress}%`;
            });

            const progressBar = document.getElementById('progress-bar');
            progressBar.value = progress;
        });
    }

    function updateCurrentSongInfo(index) {
        const currentSongTitles = document.querySelectorAll('.current-song-title');
        currentSongTitles.forEach(title => {
            title.textContent = albumData.tracks[index].title;
        });
    }

    console.log("Live cover art", <?php echo json_encode($live_cover_art); ?>);
</script>