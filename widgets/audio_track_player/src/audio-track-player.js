class AudioTrackPlayer {
    constructor(element) {
        this.element = element;
        this.audioSrc = this.element.dataset.audioSrc;
        this.audio = null;
        this.playPauseBtn = this.element.querySelector('.play-pause-btn');
        this.progressBar = this.element.querySelector('.progress-bar');
        this.progressFill = this.element.querySelector('.progress-fill');
        this.durationElement = this.element.querySelector('.duration');

        // Parse the track metadata
        this.trackMetadata = JSON.parse(this.element.dataset.trackMetadata);

        this.bindEvents();
    }

    bindEvents() {
        this.playPauseBtn.addEventListener('click', () => this.togglePlayPause());
        this.progressBar.addEventListener('click', (e) => this.seek(e));
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
}

document.addEventListener('DOMContentLoaded', () => {
    const players = document.querySelectorAll('.flow-audio-track-player');
    players.forEach(player => new AudioTrackPlayer(player));
});

class GlobalAudioManager {
    constructor() {
        this.currentPlayer = null;
        this.setupMediaSessionHandlers();
    }

    setCurrentPlayer(player) {
        if (this.currentPlayer && this.currentPlayer !== player) {
            this.currentPlayer.pause();
        }
        this.currentPlayer = player;
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
