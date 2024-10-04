jQuery(document).ready(function ($) {

    // Access albumData passed from PHP
    if (typeof albumData !== 'undefined') {
        const { title, artist, year, coverArt, tracks, playButtonImage, pauseButtonImage, totalDuration, downloadLink } = albumData;

        console.log('Album Data:', albumData);

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

        function togglePlayPause() {
            if (isPlaying && audio) {
                audio.pause();
            } else {
                if (!audio.src) {
                    playSong(0);
                } else {
                    audio.play();
                }
            }
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

        function playSong(index) {
            if (trackList[index]) {
                if (audio.src !== trackList[index].url) {
                    audio.src = trackList[index].url;
                    currentTrack = index;
                    audio.load();
                }
                audio.play().then(() => {
                    // Playback started successfully
                }).catch(error => {
                    console.error("Error playing audio:", error);
                });

                updateCurrentSongInfo(index);
            } else {
                console.error("Track index out of range: ", index);
                return;
            }
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



        // Initialize the player
        initTracksFromLocalisedData();
        setupEventListeners();


    } else {
        console.error('Album data is not available.');
    }

});