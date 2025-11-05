class AudioTrackPlayer {
    constructor(element) {
        this.element = element;
        this.audioSrc = this.element.dataset.audioSrc;
        this.audio = null;
        this.playPauseBtn = this.element.querySelector('.play-pause-btn');
        this.progressBar = this.element.querySelector('.progress-bar');
        this.progressFill = this.element.querySelector('.progress-fill');
        this.durationElement = this.element.querySelector('.duration');
        this.shareLink = this.element.querySelector('.share-link');
        this.downloadLink = this.element.querySelector('.download-link');
        this.siteName = this.element.dataset.siteName;

        // Parse the track metadata with error handling
        try {
            this.trackMetadata = JSON.parse(this.element.dataset.trackMetadata);
        } catch (error) {
            console.error('Error parsing track metadata:', error);
            console.log('Raw metadata:', this.element.dataset.trackMetadata);
            this.trackMetadata = {}; // Set a default empty object
        }

        this.bindEvents();
        globalAudioManager.addPlayer(this);
    }

    bindEvents() {
        this.playPauseBtn.addEventListener('click', (e) => {
            e.preventDefault();
            this.togglePlayPause();
        });
        this.progressBar.addEventListener('click', (e) => this.seek(e));
        this.shareLink.addEventListener('click', (e) => this.shareTrack(e));
    }

    initAudio() {
        if (!this.audio) {
            this.audio = new Audio(this.audioSrc);
            this.audio.addEventListener('timeupdate', () => this.updateProgress());
            this.audio.addEventListener('ended', () => this.onEnded());
            this.audio.addEventListener('loadedmetadata', () => this.updateDuration());
            this.audio.addEventListener('play', () => {
                globalAudioManager.setCurrentPlayer(this);
                this.updatePlayPauseButton(true);
                this.updateMediaSessionMetadata();
            });
            this.audio.addEventListener('pause', () => {
                this.updatePlayPauseButton(false);
            });
        }
    }

    play() {
        this.initAudio();
        this.audio.play().then(() => {
            globalAudioManager.setCurrentPlayer(this);
        }).catch(error => {
            console.error('Error playing track:', error);
        });
    }

    togglePlayPause() {
        this.initAudio();
        if (this.audio.paused) {
            this.play();
        } else {
            this.pause();
        }
    }

    pause() {
        if (this.audio) {
            this.audio.pause();
        }
    }

    updatePlayPauseButton(isPlaying) {
        if (isPlaying) {
            this.playPauseBtn.classList.add('playing');
        } else {
            this.playPauseBtn.classList.remove('playing');
        }
    }

    updateProgress() {
        const percent = (this.audio.currentTime / this.audio.duration) * 100;
        this.progressFill.style.width = `${percent}%`;
    }

    seek(e) {
        if (this.audio) {
            const percent = e.offsetX / this.progressBar.offsetWidth;
            this.audio.currentTime = percent * this.audio.duration;
        }
    }

    onEnded() {
        this.playPauseBtn.classList.remove('playing');
        this.progressFill.style.width = '0%';
        globalAudioManager.playNext();
    }

    updateDuration() {
        const duration = this.formatTime(this.audio.duration);
        this.durationElement.textContent = duration;
    }

    formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        return `${minutes}:${remainingSeconds < 10 ? '0' : ''}${remainingSeconds}`;
    }

    updateMediaSessionMetadata() {
        if ('mediaSession' in navigator) {
            navigator.mediaSession.metadata = new MediaMetadata({
                title: this.trackMetadata.track_title,
                artist: this.trackMetadata.track_artist,
                album: this.trackMetadata.album_title,
                artwork: [
                    { src: this.trackMetadata.featured_image_url, sizes: '512x512', type: 'image/webp' }
                ]
            });
        }
    }

    shareTrack(e) {
        e.preventDefault(); // Prevent the default link behavior

        const title = this.trackMetadata.track_title;
        const artist = this.trackMetadata.track_artist;
        const url = this.shareLink.href;

        // Determine the artist to display. If track_artist is empty, use the site name.
        const displayArtist = artist || this.siteName;

        const shareText = `Listen to "${title}" by ${displayArtist} on ${this.siteName}.`;
        const shareTitle = `${title} by ${displayArtist}`;

        if (navigator.share) {
            navigator.share({
                title: shareTitle,
                text: shareText,
                url: url
            }).then(() => {
                this.logShare();
            }).catch((error) => {
                console.error('Error sharing:', error);
                this.fallbackShare(title, artist, url);
            });
        } else {
            this.fallbackShare(title, artist, url);
        }
    }

    fallbackShare(title, artist, url) {
        // Determine the artist to display. If track_artist is empty, use the site name.
        const displayArtist = artist || this.siteName;
        const shareText = `Listen to "${title}" by ${displayArtist}: ${url}`;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(shareText)
                .then(() => {
                    this.logShare();

                    alert('Share link copied to clipboard!');
                })
                .catch(err => {
                    console.error('Failed to copy: ', err);
                    this.manualCopyFallback(shareText);
                });
        } else {
            this.manualCopyFallback(shareText);
        }
    }

    manualCopyFallback(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';  // Avoid scrolling to bottom
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            const successful = document.execCommand('copy');
            const msg = successful ? 'successful' : 'unsuccessful';
            this.logShare();
            alert('Share link copied to clipboard!');
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
            alert('Unable to copy to clipboard. Please copy the link manually.');
        }

        document.body.removeChild(textArea);
    }

    logShare() {
        const postId = this.element.dataset.postId;
        const postType = this.element.dataset.postType;

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
}

// Use a MutationObserver to handle dynamically added players
const observeDOM = (function () {
    const MutationObserver = window.MutationObserver || window.WebKitMutationObserver;

    return function (obj, callback) {
        if (!obj || obj.nodeType !== 1) return;

        if (MutationObserver) {
            const obs = new MutationObserver(function (mutations, observer) {
                callback(mutations);
            });
            obs.observe(obj, { childList: true, subtree: true });
        } else if (window.addEventListener) {
            obj.addEventListener('DOMNodeInserted', callback, false);
            obj.addEventListener('DOMNodeRemoved', callback, false);
        }
    }
})();

// Initialize players and observe DOM for changes
document.addEventListener('DOMContentLoaded', () => {
    initializePlayers();

    // Observe the body for changes
    observeDOM(document.body, function (mutations) {
        mutations.forEach(function (mutation) {
            const addedNodes = mutation.addedNodes;
            for (let i = 0; i < addedNodes.length; i++) {
                if (addedNodes[i].nodeType === 1 && addedNodes[i].classList.contains('fmp-track-player')) {
                    new AudioTrackPlayer(addedNodes[i]);
                }
            }
        });
    });
});

function initializePlayers() {
    const players = document.querySelectorAll('.fmp-track-player');
    players.forEach(player => new AudioTrackPlayer(player));
}

class GlobalAudioManager {
    constructor() {
        this.currentPlayer = null;
        this.players = [];
        this.setupMediaSessionHandlers();
    }

    addPlayer(player) {
        this.players.push(player);
    }

    setCurrentPlayer(player) {
        if (this.currentPlayer && this.currentPlayer !== player) {
            this.currentPlayer.pause();
        }
        this.currentPlayer = player;
        this.updateMediaSessionMetadata();
    }

    getNextPlayer() {
        const currentIndex = this.players.indexOf(this.currentPlayer);
        if (currentIndex === -1 || currentIndex === this.players.length - 1) {
            return this.players[0]; // Loop back to the first player
        }
        return this.players[currentIndex + 1];
    }

    playNext() {
        const nextPlayer = this.getNextPlayer();
        if (nextPlayer) {
            nextPlayer.play();
            this.setCurrentPlayer(nextPlayer);
        } else {
            console.error('No next player found');
        }
    }

    getPreviousPlayer() {
        const currentIndex = this.players.indexOf(this.currentPlayer);
        if (currentIndex === -1 || currentIndex === 0) {
            return this.players[this.players.length - 1]; // Loop back to the last player
        }
        return this.players[currentIndex - 1];
    }

    playPrevious() {
        const previousPlayer = this.getPreviousPlayer();
        if (previousPlayer) {
            previousPlayer.play();
            this.setCurrentPlayer(previousPlayer);
        } else {
            console.error('No previous player found');
        }
    }

    updateMediaSessionMetadata() {
        if ('mediaSession' in navigator && this.currentPlayer) {
            const metadata = this.currentPlayer.trackMetadata;
            navigator.mediaSession.metadata = new MediaMetadata({
                title: metadata.track_title,
                artist: metadata.track_artist,
                album: metadata.album_title,
                artwork: [
                    { src: metadata.featured_image_url, sizes: '512x512', type: 'image/jpeg' }
                ]
            });
        } else {
            console.error('MediaSession not available or no current player');
        }
    }

    setupMediaSessionHandlers() {
        if ('mediaSession' in navigator) {
            navigator.mediaSession.setActionHandler('play', () => {
                if (this.currentPlayer) this.currentPlayer.play();
            });
            navigator.mediaSession.setActionHandler('pause', () => {
                if (this.currentPlayer) this.currentPlayer.pause();
            });
            navigator.mediaSession.setActionHandler('previoustrack', () => {
                this.playPrevious();

            });
            navigator.mediaSession.setActionHandler('nexttrack', () => {
                this.playNext();
            });
        } else {
            console.error('MediaSession not available');
        }
    }
}

const globalAudioManager = new GlobalAudioManager();
