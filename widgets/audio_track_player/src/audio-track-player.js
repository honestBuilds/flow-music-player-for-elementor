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
        // Parse the track metadata
        this.trackMetadata = JSON.parse(this.element.dataset.trackMetadata);

        this.bindEvents();
        globalAudioManager.addPlayer(this);
    }

    bindEvents() {
        this.playPauseBtn.addEventListener('click', () => this.togglePlayPause());
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
        this.audio.play();
    }

    togglePlayPause() {
        this.initAudio();
        if (this.audio.paused) {
            globalAudioManager.setCurrentPlayer(this);
            this.audio.play();
        } else {
            this.audio.pause();
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
                    { src: this.trackMetadata.featured_image_url, sizes: '512x512', type: 'image/jpeg' }
                ]
            });
        }
    }

    shareTrack(e) {
        e.preventDefault(); // Prevent the default link behavior

        const title = this.shareLink.dataset.trackTitle;
        const artist = this.shareLink.dataset.trackArtist;
        const url = this.shareLink.href;
        const shareText = artist ? `Listen to "${title}" by First Love Music ft. ${artist}` : `Listen to "${title}" by First Love Music`;
        const shareTitle = artist ? `${title} by First Love Music ft. ${artist}` : `${title} by First Love Music`;

        if (navigator.share) {
            navigator.share({
                title: shareTitle,
                text: shareText,
                url: url
            }).then(() => {
                console.log('Successfully shared');
            }).catch((error) => {
                console.error('Error sharing:', error);
                this.fallbackShare(title, artist, url);
            });
        } else {
            this.fallbackShare(title, artist, url);
        }
    }

    fallbackShare(title, artist, url) {
        const shareText = `Check out "${title}" by ${artist}: ${url}`;
        const textArea = document.createElement('textarea');
        textArea.value = shareText;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            alert('Share link copied to clipboard!');
        } catch (err) {
            console.error('Failed to copy: ', err);
        }
        document.body.removeChild(textArea);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const players = document.querySelectorAll('.flow-audio-track-player');
    players.forEach(player => new AudioTrackPlayer(player));
});

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
        }
    }

    setupMediaSessionHandlers() {
        if ('mediaSession' in navigator) {
            navigator.mediaSession.setActionHandler('play', () => {
                if (this.currentPlayer) this.currentPlayer.audio.play();
            });
            navigator.mediaSession.setActionHandler('pause', () => {
                if (this.currentPlayer) this.currentPlayer.audio.pause();
            });
            navigator.mediaSession.setActionHandler('previoustrack', () => {
                // Implement previous track logic if needed
            });
            navigator.mediaSession.setActionHandler('nexttrack', () => {
                // Implement next track logic if needed
            });
        }
    }
}

const globalAudioManager = new GlobalAudioManager();
