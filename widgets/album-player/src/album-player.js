jQuery(document).ready(function ($) {
    // Access albumData passed from PHP
    if (typeof albumData !== 'undefined') {
        console.log('Album Data:', albumData);

        const { title, artist, year, coverArt, tracks, playButtonImage, pauseButtonImage, totalDuration, downloadLink, siteName } = albumData;

        let currentTrack = 0; // Initialize currentTrack to 0
        let isPlaying = false;
        let audio = new Audio();

        const trackList = []; // This will be populated from the localized data

        const albumPlayer = document.getElementById('fmp-album-player');
        const postId = albumPlayer.dataset.postId;
        const postType = albumPlayer.dataset.postType;
        const postUrl = albumPlayer.dataset.postUrl;

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

        function initPlayer() {
            console.log("Initializing player");
            if (trackList.length > 0) {
                audio.src = trackList[0].url;
                audio.load();
                updateCurrentSongInfo(0);
                console.log("First track loaded:", trackList[0].url);
            } else {
                console.error("Track list is empty");
            }
        }

        let isDragging = false;
        let progressBarContainer, progressBarFill, progressBarHead;
        let lastTouchX;

        function setupEventListeners() {
            console.log("Setting up event listeners");

            $('#songList li').off('click').on('click', function () {
                showLoadingSpinner(); // Show spinner before playing
                const trackIndex = $(this).data('track-index');
                playSong(trackIndex);
            });

            document.querySelectorAll('.play-button').forEach(button => {
                button.addEventListener('click', () => {
                    if (!isPlaying && !isCurrentTrackInitialised()) {
                        playSong(0);
                    } else {
                        togglePlayPause();
                    }
                });
            });

            document.querySelectorAll('.prev-button').forEach(button => {
                button.addEventListener('click', playPrevious);
            });

            document.querySelectorAll('.next-button').forEach(button => {
                button.addEventListener('click', playNext);
            });

            document.querySelectorAll('.share-button').forEach(button => {
                button.addEventListener('click', shareAlbum);
            });

            // Media session handlers
            // Add event listener to update media session metadata when the audio is playing
            // Event listeners to update 'isPlaying' and UI based on audio state
            audio.addEventListener('play', () => {
                console.log("Audio 'play' event triggered");
                isPlaying = true;
                updatePlayButton();
            });

            audio.addEventListener('pause', () => {
                console.log("Audio 'pause' event triggered");
                isPlaying = false;
                updatePlayButton();
            });

            audio.addEventListener('ended', () => {
                console.log("Audio 'ended' event triggered");
                isLoading = true;
                showLoadingSpinner();
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

            progressBarContainer = document.getElementById('progress-bar-container');
            progressBarFill = document.getElementById('progress-bar-fill');
            progressBarHead = document.getElementById('progress-bar-head');

            if (progressBarContainer && progressBarFill && progressBarHead) {
                progressBarContainer.addEventListener('mousedown', startDragging);
                progressBarContainer.addEventListener('touchstart', startDragging);
                document.addEventListener('mousemove', drag);
                document.addEventListener('touchmove', drag, { passive: false });
                document.addEventListener('mouseup', stopDragging);
                document.addEventListener('touchend', stopDragging);
                document.addEventListener('mouseleave', stopDragging);
                document.addEventListener('touchcancel', stopDragging);
            }

            progressBarContainer.addEventListener('click', (e) => {
                console.log("Progress bar clicked");
                const rect = progressBarContainer.getBoundingClientRect();
                const clickPosition = e.clientX - rect.left;
                const clickPercentage = clickPosition / rect.width;
                const seekTime = clickPercentage * audio.duration;
                audio.currentTime = seekTime;
            });

            audio.addEventListener('timeupdate', updateProgressBar);
            audio.addEventListener('loadedmetadata', () => {
                console.log("Audio metadata loaded");
                updateProgressBar();
            });

            if (progressBarFill) {
                progressBarFill.addEventListener('transitionend', updateProgressBar);
            }

            // Add event listener for keyboard controls
            document.addEventListener('keydown', handleKeyPress);
        }

        function handleKeyPress(e) {
            // Check if the pressed key is the space bar and the target is not an input or textarea
            if (e.code === 'Space' && !/input|textarea/i.test(e.target.tagName)) {
                e.preventDefault(); // Prevent default space bar behavior (usually page scroll)
                togglePlayPause();
            }
        }

        function startDragging(e) {
            e.preventDefault();
            isDragging = true;
            lastTouchX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
            updateProgressFromEvent(e);
            progressBarHead.style.opacity = '1';
        }

        function drag(e) {
            if (isDragging) {
                e.preventDefault();
                requestAnimationFrame(() => updateProgressFromEvent(e));
            }
        }

        function stopDragging() {
            if (isDragging) {
                isDragging = false;
                audio.currentTime = calculateSeekTime(parseFloat(progressBarFill.style.width) / 100);
                progressBarHead.style.opacity = '';
            }
        }

        function updateProgressFromEvent(e) {
            const rect = progressBarContainer.getBoundingClientRect();
            const containerWidth = rect.width;
            const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;

            // For touch events, calculate the delta movement to reduce jitter
            if (e.type.includes('touch')) {
                const delta = clientX - lastTouchX;
                lastTouchX = clientX;
                const currentWidth = parseFloat(progressBarFill.style.width) || 0;
                const newPercentage = Math.max(0, Math.min(100, currentWidth + (delta / containerWidth * 100)));
                updateProgressBarPosition(newPercentage / 100);
            } else {
                const clickPosition = clientX - rect.left;
                const percentage = Math.max(0, Math.min(1, clickPosition / containerWidth));
                updateProgressBarPosition(percentage);
            }
        }

        function updateProgressBarPosition(percentage) {
            const fillWidth = percentage * 100;
            const containerWidth = progressBarContainer.offsetWidth;
            const headWidth = progressBarHead.offsetWidth;
            const headPosition = (percentage * containerWidth) - (headWidth / 2);

            progressBarFill.style.width = `${fillWidth}%`;
            progressBarHead.style.transform = `translate(${headPosition}px, -50%)`;
        }

        function calculateSeekTime(percentage) {
            return percentage * audio.duration;
        }

        function updateProgressBar() {
            if (!isNaN(audio.duration) && !isDragging) {
                const progress = audio.currentTime / audio.duration;
                updateProgressBarPosition(progress);
            }
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

        let isLoading = false;

        function togglePlayPause() {
            if (isPlaying) {
                pauseAudio();
            } else if (!isLoading) {
                if (!isCurrentTrackInitialised()) {
                    playSong(0); // Play the first track if no track is currently initialized
                } else {
                    showLoadingSpinner();
                    playAudio();
                }
            }
        }

        function playAudio() {
            console.log("Attempting to play audio");
            isLoading = true;
            audio.play().then(() => {
                console.log("Audio playing successfully");
                isLoading = false;
                isPlaying = true;
                updatePlayButton();
                updatePlayingState(currentTrack);
                hideLoadingSpinner();
            }).catch(error => {
                console.error("Error playing audio:", error);
                isLoading = false;
                isPlaying = false;
                updatePlayButton();
                hideLoadingSpinner();
            });
        }

        function pauseAudio() {
            console.log("Pausing audio");
            audio.pause();
            isPlaying = false;
            updatePlayButton();
            updatePlayingState(null);
        }

        function playPrevious() {
            showLoadingSpinner(); // Show spinner before playing
            if (currentTrack > 0) {
                currentTrack--;
                console.log(`Playing previous track. New index: ${currentTrack}`);
            } else {
                console.log("Already at the first track. Cannot go to previous.");
                currentTrack = trackList.length - 1;
            }
            playSong(currentTrack);
        }

        function playNext() {
            showLoadingSpinner(); // Show spinner before playing
            console.log("playNext called. Current track:", currentTrack);
            if (currentTrack < trackList.length - 1) {
                currentTrack++;
                console.log(`Playing next track. New index: ${currentTrack}`);
            } else {
                console.log("Already at the last track. Looping to first track.");
                currentTrack = 0;
            }
            playSong(currentTrack);
        }

        // Modify the existing playSong function
        function playSong(index) {
            console.log(`playSong called with index: ${index}`);
            if (trackList[index]) {
                isLoading = true;
                showLoadingSpinner();

                console.log("Track found. Stopping current audio.");
                audio.pause();
                audio.currentTime = 0;  // Reset the current time

                console.log("Loading new track.");
                audio.src = trackList[index].url;
                audio.load();
                currentTrack = index;

                // Remove previous event listeners
                audio.removeEventListener('canplay', onCanPlay);

                // Add new event listener for this playback
                audio.addEventListener('canplay', onCanPlay);

                console.log("Waiting for audio to be ready...");
            } else {
                console.error("Track index out of range: ", index);
            }
        }

        function onCanPlay() {
            console.log("Audio is ready to play");
            if (isLoading) {
                isLoading = false;
                hideLoadingSpinner();
                audio.play().then(() => {
                    console.log("Audio playing successfully");
                    isPlaying = true;
                    updatePlayButton();
                    updatePlayingState(currentTrack);
                    updateCurrentSongInfo(currentTrack);
                    updateProgressBar();
                    if ('mediaSession' in navigator) {
                        console.log("Updating media session metadata.");
                        updateMediaSessionMetadata(currentTrack);
                    }
                }).catch(error => {
                    console.error("Error playing audio:", error);
                    isPlaying = false;
                    updatePlayButton();
                });
            }
        }

        function updateMediaSessionMetadata(index) {
            navigator.mediaSession.metadata = new MediaMetadata({
                title: trackList[index].title,
                artist: artist,
                album: title,
                artwork: [{
                    src: coverArt,
                    sizes: '512x512',
                    type: 'image/webp'
                }]
            });
        }

        function updatePlayingState(index) {
            console.log(`Updating playing state for index: ${index}`);
            $('#songList li').removeClass('playing');
            if (index !== null && index !== undefined) {
                $(`#songList li[data-track-index="${index}"]`).addClass('playing');
            }
        }

        function updatePlayButton() {
            console.log("Updating play button. isPlaying:", isPlaying);
            const playIcons = document.querySelectorAll('.play-button .play-icon');
            playIcons.forEach(icon => {
                if (isPlaying) {
                    icon.src = pauseButtonImage;
                    icon.alt = "Pause";
                } else {
                    icon.src = playButtonImage;
                    icon.alt = "Play";
                }
                if (!isLoading) {
                    icon.style.display = 'block';
                    icon.nextElementSibling.classList.add('hidden');
                    icon.nextElementSibling.style.display = 'none';
                }
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

                var newTitle = titleParts.join(' - ') + ' | ' + siteName;
                document.title = newTitle;
            }
        }

        function showLoadingSpinner() {
            console.log("Showing loading spinner");
            document.querySelectorAll('.play-button .button-content').forEach(content => {
                content.querySelector('.play-icon').style.display = 'none';
                let spinner = content.querySelector('.spinner-border');
                spinner.classList.remove('hidden');
                spinner.style.display = 'block';
            });
        }

        function hideLoadingSpinner() {
            console.log("Hiding loading spinner");
            document.querySelectorAll('.play-button .button-content').forEach(content => {
                content.querySelector('.play-icon').style.display = 'block';
                let spinner = content.querySelector('.spinner-border');
                spinner.classList.add('hidden');
                spinner.style.display = 'none';
            });
        }

        // Add this function to initialize the button state
        function initializeButtonState() {
            document.querySelectorAll('.play-button .button-content').forEach(content => {
                content.querySelector('.play-icon').style.display = 'block';
                let spinner = content.querySelector('.spinner-border');
                spinner.classList.add('hidden');
                spinner.style.display = 'none';
            });
        }

        // Call this function when the page loads
        document.addEventListener('DOMContentLoaded', initializeButtonState);

        function shareAlbum() {
            // e.preventDefault(); // Prevent the default link behavior

            const shareText = artist ? `Listen to "${title}" by ${artist} on the ${siteName} website.` : `Listen to "${title}" on the ${siteName} website.`;
            const shareTitle = artist ? `${title} by ${artist} - ${siteName}` : `${title} - ${siteName}`;

            if (navigator.share) {
                navigator.share({
                    title: shareTitle,
                    text: shareText,
                    url: postUrl
                }).then(() => {
                    logShare();
                }).catch((error) => {
                    console.error('Error sharing:', error);
                    fallbackShare(title, artist, postUrl, siteName);
                });
            } else {
                fallbackShare(title, artist, postUrl, siteName);
            }
        }

        function fallbackShare(title, artist, url, siteName) {
            const shareText = artist ? `Listen to "${title}" by ${artist} on the ${siteName} website: ${url}` : `Listen to "${title}" on the ${siteName} website: ${url}`;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(shareText)
                    .then(() => {
                        alert('Share link copied to clipboard!');
                    })
                    .catch(err => {
                        console.error('Failed to copy: ', err);
                        manualCopyFallback(shareText);
                    });
            } else {
                manualCopyFallback(shareText);
            }
        }

        function manualCopyFallback(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';  // Avoid scrolling to bottom
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand('copy');
                const msg = successful ? 'successful' : 'unsuccessful';
                alert('Share link copied to clipboard!');
            } catch (err) {
                console.error('Fallback: Oops, unable to copy', err);
                alert('Unable to copy to clipboard. Please copy the link manually.');
            }

            document.body.removeChild(textArea);
        }

        function logShare() {
            if (!postId || !postType) {
                console.error('Missing post ID or post type for share logging');
                return;
            }

            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'fmp_log_share',
                    post_id: postId,
                    post_type: postType,
                }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Share logged successfully');
                    } else {
                        console.error('Failed to log share:', data.data);
                    }
                })
                .catch(error => {
                    console.error('Error logging share:', error);
                });
        }

        // Change page title
        updatePageTitle();
        // Initialize the player
        initTracksFromLocalisedData();
        initPlayer();
        setupEventListeners();


    } else {
        console.error('Album data is not available.');
    }

    console.log("Script initialization complete");
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