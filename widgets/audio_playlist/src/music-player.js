let currentTrack;
let isPlaying = false;
let audio = new Audio();

var countUnit = widgetData.countUnit;
var coverArtAspectRatio = widgetData.coverArtAspectRatio;
var playlistType = widgetData.playlistType;
var playlist = widgetData.playlist;
var cover = widgetData.playlist["cover_art"];

console.log("srcUrl", widgetData.srcUrl);

// Example albumData
const albumData = {
    title: playlist["title"],
    artist: playlist["artist"],
    year: playlist["year"],
    coverArt: cover,
    duration: "",
    tracks: playlist["tracks"],
    loadTracks: async function () {

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

jQuery(document).ready(function ($) {
    // Check if Elementor is in edit mode
    if (typeof elementorFrontend !== 'undefined' && elementorFrontend.isEditMode()) {
        // make spinner small
        coverWidgetOnly();
        hideSpinner();
        // Hook into the Elementor's lifecycle events
        $(window).on('elementor/frontend/init', function () {
            elementorFrontend.hooks.addAction('frontend/element_ready/flow_audio_playlist_widget.default', function (scope, $) {
                renderWidgetTemplate();
                hideSpinner();
                // Listen for widget updates in the editor
                if (window.elementor) {
                    elementor.channels.editor.on('change', function (model) {
                        console.log('Widget setting changed:', model);
                        // Reinitialize player when settings are updated
                        if (model.attributes.name === 'flow_audio_playlist_widget') {
                            renderWidgetTemplate();
                            hideSpinner();
                        }
                    });

                    elementor.channels.editor.on('document:loaded', function () {
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
    document.querySelector('.album-stats').textContent = `${albumData.tracks.length} ${countUnit} • ${albumData.duration}`;

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
        button.src = `${widgetData.srcUrl}/${isPlaying ? "pause-btn.svg" : "play-btn.svg"}`;
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