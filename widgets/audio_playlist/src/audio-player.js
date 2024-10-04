jQuery(document).ready(function ($) {

    // Access albumData passed from PHP
    if (typeof albumData !== 'undefined') {
        console.log('Album Data:', albumData);

        const { title, artist, year, coverArt, tracks, playButtonImage, pauseButtonImage, totalDuration, downloadLink } = albumData;

        let currentTrack;
        let isPlaying = false;
        let audio = new Audio();

        const trackList = []; // This will be populated from the localized data

        function isCurrentTrackInitialised() {
            return typeof currentTrack !== 'undefined'
        }

        // Function to initialize tracks from localized data
        function initTracksFromLocalisedData() {
            if (Array.isArray(tracks)) {
                tracks.forEach((track, index) => {
                    if (track.track_url) {
                        trackList.push({
                            title: track.track_title,
                            number: track.track_number,
                            url: track.track_url,
                            duration: track.track_duration_formatted,
                        });
                    } else {
                        console.warn('Missing track_url for track at index:', index);
                    }
                });
                console.log('Tracks initialized:', trackList);
            } else {
                console.error('Invalid tracks data.');
            }
        }

        // Example albumData
        // const albumData = {
        //     title: playlist["title"],
        //     artist: playlist["artist"],
        //     year: playlist["year"],
        //     coverArt: cover,
        //     duration: "",
        //     tracks: playlist["tracks"],
        //     loadTracks: async function () {

        //         let totalDuration = 0;

        //         for (let track of this.tracks) {
        //             const audio = new Audio(track["url"]);
        //             await new Promise(resolve => {
        //                 audio.addEventListener('loadedmetadata', () => {
        //                     // const title = track["url"].split('/').pop().replace('.mp3', '');
        //                     const duration = audio.duration;
        //                     totalDuration += duration;
        //                     // this.tracks.push({ title, duration: formatDuration(duration), url });

        //                     resolve();
        //                 });
        //             });
        //         }

        //         this.duration = formatDuration(totalDuration);
        //     }
        // };

        function setupEventListeners() {

            $('#songList li').off('click').on('click', function () {
                const trackIndex = $(this).data('track-index');
                playSong(trackIndex);
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
            // Event listeners to update 'isPlaying' and UI based on audio state
            audio.addEventListener('play', () => {
                isPlaying = true;
                updatePlayButton();

                const progressBar = document.getElementById('progress-bar');
                if (progressBar && progressBar.style.display === "none") {
                    progressBar.style.display = "block";
                    progressBar.style.width = "100%";
                }

                if ('mediaSession' in navigator) {
                    navigator.mediaSession.metadata = new MediaMetadata({
                        title: trackList[isCurrentTrackInitialised ? currentTrack : 0]['title'], // Update with the track title
                        artist: artist, // Update with the artist name
                        album: title, // Update with the album name
                        artwork: [{
                            src: coverArt, // Path to album art image
                            sizes: '512x512', // Size of the image
                            type: 'image/webp'
                        }]
                    });
                }
            });

            audio.addEventListener('pause', function () {
                isPlaying = false;
                updatePlayButton();
            });

            audio.addEventListener('ended', function () {
                isPlaying = false;
                updatePlayButton();
                playNext();
            });

            // Handle playback controls
            if ('mediaSession' in navigator) {
                // Handle play action
                navigator.mediaSession.setActionHandler('play', function () {
                    audio.play();
                });

                // Handle pause action
                navigator.mediaSession.setActionHandler('pause', function () {
                    audio.pause();
                });

                // Handle seekbackward action
                navigator.mediaSession.setActionHandler('seekbackward', function () {
                    audio.currentTime = Math.max(audio.currentTime - 10, 0);
                });

                // Handle seekforward action
                navigator.mediaSession.setActionHandler('seekforward', function () {
                    audio.currentTime = Math.min(audio.currentTime + 10, audio.duration);
                });

                // Handle seekto action for user interaction with the progress bar
                navigator.mediaSession.setActionHandler('seekto', function (details) {
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

            audio.addEventListener('timeupdate', () => {
                const progress = (audio.currentTime / audio.duration) * 100;
                const progressBar = document.getElementById('progress-bar');
                progressBar.value = progress;
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

        function selectTrack(index) {
            console.log(`Selecting track at index: ${index}`);
            if (trackList[index]) {
                if (currentTrack !== index) {
                    // If it's a different track, load it and play
                    audio.pause();
                    audio.src = trackList[index].url;
                    audio.load();
                    currentTrack = index;
                    playAudio();
                } else {
                    // If it's the same track, toggle play/pause
                    togglePlayPause();
                }
                updatePlayingState(index);
                updateCurrentSongInfo(index);
            } else {
                console.error("Track index out of range: ", index);
            }
        }

        function togglePlayPause() {
            if (isPlaying) {
                pauseAudio();
            } else {
                playAudio();
            }
        }

        function playAudio() {
            console.log("Attempting to play audio");
            audio.play().then(() => {
                console.log("Audio playing successfully");
                isPlaying = true;
                updatePlayButton();
                // Keep all existing code here (media session updates, etc.)
            }).catch(error => {
                console.error("Error playing audio:", error);
            });
        }

        function pauseAudio() {
            console.log("Pausing audio");
            audio.pause();
            isPlaying = false;
            updatePlayButton();
        }

        function playPrevious() {
            if (currentTrack > 0) {
                currentTrack--;
                console.log(`Playing previous track. New index: ${currentTrack}`);
                playSong(currentTrack);
            } else {
                console.log("Already at the first track. Cannot go to previous.");
            }
        }

        function playNext() {
            if (currentTrack < trackList.length - 1) {
                currentTrack++;
                console.log(`Playing next track. New index: ${currentTrack}`);
                playSong(currentTrack);
            } else {
                console.log("Already at the last track. Cannot go to next.");
            }
        }

        function playTrack(index) {
            console.log(`Attempting to play track at index: ${index}`);
            if (trackList[index]) {
                // Always pause the current audio before starting a new one
                audio.pause();

                // If it's a different track, load the new one
                if (currentTrack !== index) {
                    audio.src = trackList[index].url;
                    audio.load();
                    currentTrack = index;
                }

                // Play the track
                audio.play().then(() => {
                    console.log(`Now playing track at index: ${index}`);
                    isPlaying = true;
                    updatePlayingState(index);
                    updatePlayButton();
                    updateCurrentSongInfo(index);
                    // Keep all existing code here (media session updates, etc.)
                }).catch(error => {
                    console.error("Error playing audio:", error);
                });
            } else {
                console.error("Track index out of range: ", index);
            }
        }

        // Modify the existing playSong function
        function playSong(index) {
            console.log(`Attempting to play song at index: ${index}`);
            if (trackList[index]) {
                if (currentTrack === index) {
                    // If it's the same track, toggle play/pause
                    if (isPlaying) {
                        audio.pause();
                        isPlaying = false;
                    } else {
                        audio.play().catch(error => console.error("Error playing audio:", error));
                        isPlaying = true;
                    }
                } else {
                    // If it's a different track, stop current and play new
                    audio.pause();
                    audio.src = trackList[index].url;
                    audio.load();
                    currentTrack = index;
                    audio.play().then(() => {
                        console.log(`Now playing track at index: ${index}`);
                        isPlaying = true;
                        // Keep all existing code here (media session updates, etc.)
                    }).catch(error => {
                        console.error("Error playing audio:", error);
                    });
                }

                updatePlayingState(index);
                updatePlayButton();
                updateCurrentSongInfo(index);
            } else {
                console.error("Track index out of range: ", index);
            }
        }

        function updatePlayingState(index) {
            $('#songList li').removeClass('playing');
            $(`#songList li[data-track-index="${index}"]`).addClass('playing');
        }

        function updatePlayButton() {
            const playButtons = document.querySelectorAll('.play-button img');
            playButtons.forEach(button => {
                button.src = isPlaying ? pauseButtonImage : playButtonImage;
            });
        }

        function updateCurrentSongInfo(index) {
            const currentSongTitles = document.querySelectorAll('.current-song-title');
            currentSongTitles.forEach(title => {
                title.textContent = trackList[index].title;
            });
        }

        function updatePageTitle() {
            if (typeof albumData !== 'undefined') {
                var titleParts = [albumData.title];

                if (albumData.artist) {
                    titleParts.push(albumData.artist);
                }

                var newTitle = titleParts.join(' - ') + ' | ' + albumData.siteName;
                document.title = newTitle;
            }
        }

        // Change page title
        updatePageTitle();
        // Initialize the player
        initTracksFromLocalisedData();
        setupEventListeners();


    } else {
        console.error('Album data is not available.');
    }

});

// Elementor editor code
if (window.elementorFrontend && window.elementorFrontend.isEditMode()) {
    elementorFrontend.hooks.addAction('frontend/element_ready/flow_audio_playlist_widget.default', function ($scope) {
        var $albumCpt = $scope.find('[data-setting="album_cpt"]');
        var $albumCptDropdown = $scope.find('[data-setting="album_cpt_dropdown"]');

        if ($albumCpt.length && $albumCptDropdown.length) {
            $albumCptDropdown.off('change').on('change', function () {
                console.log('Dropdown changed to:', $(this).val());
                $albumCpt.val($(this).val()).trigger('input');
            });

            $albumCpt.off('input').on('input', function () {
                console.log('Input changed to:', $albumCpt.val());
                $albumCptDropdown.val($albumCpt.val());
            });
        }
    });
}