<?php
protected function _register_controls()
    {
        // Playlist Settings Section
        $this->start_controls_section(
            'playlist_content_section',
            [
                'label' => esc_html__('Playlist Settings', 'flow-audio'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Playlist Title Control
        $this->add_control(
            'playlist_title',
            [
                'label' => esc_html__('Playlist Title', 'flow-audio'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('My Playlist', 'flow-audio'),
                'placeholder' => esc_html__('Enter playlist title', 'flow-audio'),
            ]
        );

        // Album Art Control (Image Field outside repeater)
        $this->add_control(
            'album_art',
            [
                'label' => esc_html__('Album Art', 'flow-audio'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'media_type' => 'image',
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        // Download Link Control
        $this->add_control(
            'download_link',
            [
                'label' => esc_html__('Download Link', 'flow-audio'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-download-link.com', 'flow-audio'),
                'options' => ['is_external', 'nofollow'],
                'default' => [
                    'url' => '',
                    'is_external' => true,
                    'nofollow' => true,
                ],
            ]
        );

        // Playlist Background Color Control
        $this->add_control(
            'background_color',
            [
                'label' => esc_html__('Background Color', 'flow-audio'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
            ]
        );

        // Repeater Field for Tracks
        $repeater = new \Elementor\Repeater();

        // Track Number Control
        $repeater->add_control(
            'track_number',
            [
                'label' => esc_html__('Track Number', 'flow-audio'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '1',
                'label_block' => true,
            ]
        );

        // Track Title Control
        $repeater->add_control(
            'track_title',
            [
                'label' => esc_html__('Track Title', 'flow-audio'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Track Title', 'flow-audio'),
                'label_block' => true,
            ]
        );

        // Track Audio File Control
        $repeater->add_control(
            'track_audio',
            [
                'label' => esc_html__('Upload Audio File', 'flow-audio'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'media_type' => 'audio',
            ]
        );

        // Repeater: Add the tracks array
        $this->add_control(
            'tracks',
            [
                'label' => esc_html__('Tracks', 'flow-audio'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [],
                'title_field' => '{{{ track_number }}}. {{{ track_title }}}',
            ]
        );

        $this->end_controls_section();

        // Styling Tab
        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__('Style', 'flow-audio'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Typography for Playlist Title
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'playlist_title_typography',
                'label' => esc_html__('Title Typography', 'flow-audio'),
                'selector' => '{{WRAPPER}} .playlist-title',
            ]
        );

        // Typography for Track Number
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'track_number_typography',
                'label' => esc_html__('Track Number Typography', 'flow-audio'),
                'selector' => '{{WRAPPER}} .track-number',
            ]
        );

        // Typography for Track Title
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'track_title_typography',
                'label' => esc_html__('Track Title Typography', 'flow-audio'),
                'selector' => '{{WRAPPER}} .track-title',
            ]
        );

        // Typography for Track Duration
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'track_duration_typography',
                'label' => esc_html__('Track Duration Typography', 'flow-audio'),
                'selector' => '{{WRAPPER}} .track-duration',
            ]
        );

        // Cover Art Style
        $this->add_control(
            'cover_art_style',
            [
                'label' => esc_html__('Cover Art', 'flow-audio'),
                'type' => \Elementor\Controls_Manager::IMAGE_DIMENSIONS,
                'selector' => '{{WRAPPER}} .album-art',
            ]
        );

        // Style Tab: Cover Art Styling Settings
        $this->start_controls_section(
            'style_cover_art_section',
            [
                'label' => esc_html__('Cover Art', 'flow-audio'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Cover Art Resolution (Size)
        $this->add_control(
            'cover_art_width',
            [
                'label' => esc_html__('Cover Art Width', 'flow-audio'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 1000,
                        'step' => 1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .playlist-cover-art' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Cover Art Object Fit
        $this->add_control(
            'cover_art_object_fit',
            [
                'label' => esc_html__('Object Fit', 'flow-audio'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'cover',
                'options' => [
                    'fill' => esc_html__('Fill', 'flow-audio'),
                    'cover' => esc_html__('Cover', 'flow-audio'),
                    'contain' => esc_html__('Contain', 'flow-audio'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .playlist-cover-art img' => 'object-fit: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
?>

        <div class="flow-audio-playlist-widget" style="background-color: <?php echo esc_attr($settings['background_color']); ?>;">
            <h2><?php echo esc_html($settings['playlist_title']); ?></h2>

            <!-- Display Album Art if Available -->
            <?php if (!empty($settings['album_art']['url'])) : ?>
                <img src="<?php echo esc_url($settings['album_art']['url']); ?>" alt="<?php echo esc_attr($settings['playlist_title']); ?>" class="album-art">
            <?php endif; ?>

            <audio id="audio-player" controls></audio>

            <ul id="track-list">
                <?php foreach ($settings['tracks'] as $index => $track) : ?>
                    <li data-audio="<?php echo esc_url($track['track_audio']['url']); ?>">
                        <span class="track-number"><?php echo esc_html($track['track_number']); ?></span>
                        <strong class="track-title"><?php echo esc_html($track['track_title']); ?></strong>
                        <span class="track-duration" id="duration-<?php echo $index; ?>"></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="controls">
                <button id="prev">Previous</button>
                <button id="play-pause">Play/Pause</button>
                <button id="next">Next</button>
                <button id="repeat-toggle">Repeat</button>
                <button id="shuffle-toggle">Shuffle</button>
                <div class="volume-control">
                    <i class="volume-icon" onclick="toggleVolumeSlider()"></i>
                    <input type="range" min="0" max="1" step="0.01" id="volume-slider" onchange="setVolume(this.value)" style="display: none;">
                </div>
            </div>
        </div>

        <!-- Sticky Footer Controls -->
        <div id="sticky-controls" class="sticky-controls" style="display: none;">
            <div class="sticky-cover-art">
                <img src="<?php echo esc_url($settings['album_art']['url']); ?>" alt="Album Cover" class="sticky-album-art">
            </div>
            <div class="sticky-info">
                <span id="sticky-track-title">Track Title</span><br>
                <span id="sticky-album-title"><?php echo esc_html($settings['playlist_title']); ?></span>
            </div>
            <div class="sticky-progress-bar">
                <input type="range" id="progress-bar" min="0" max="100" value="0">
            </div>
            <div class="sticky-controls-buttons">
                <button id="sticky-prev">Previous</button>
                <button id="sticky-play-pause">Play/Pause</button>
                <button id="sticky-next">Next</button>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tracks = Array.from(document.querySelectorAll('#track-list li'));
                let currentTrack = 0;
                const audioPlayer = document.getElementById('audio-player');
                const stickyControls = document.getElementById('sticky-controls');
                const progressBar = document.getElementById('progress-bar');
                const stickyPlayPauseBtn = document.getElementById('sticky-play-pause');
                const stickyTrackTitle = document.getElementById('sticky-track-title');
                const playPauseBtn = document.getElementById('play-pause');

                function loadTrack(index) {
                    const track = tracks[index];
                    audioPlayer.src = track.getAttribute('data-audio');
                    stickyTrackTitle.textContent = track.querySelector('.track-title').textContent;
                    audioPlayer.play();
                    showStickyControls();
                }

                function showStickyControls() {
                    stickyControls.style.display = 'block';
                }

                function hideStickyControls() {
                    stickyControls.style.display = 'none';
                }

                audioPlayer.addEventListener('timeupdate', function() {
                    const progressPercent = (audioPlayer.currentTime / audioPlayer.duration) * 100;
                    progressBar.value = progressPercent;
                });

                progressBar.addEventListener('input', function() {
                    const seekTime = (progressBar.value / 100) * audioPlayer.duration;
                    audioPlayer.currentTime = seekTime;
                });

                // Play/Pause functionality
                playPauseBtn.addEventListener('click', () => {
                    if (audioPlayer.paused) {
                        audioPlayer.play();
                    } else {
                        audioPlayer.pause();
                    }
                });

                stickyPlayPauseBtn.addEventListener('click', () => {
                    if (audioPlayer.paused) {
                        audioPlayer.play();
                        stickyPlayPauseBtn.textContent = 'Pause';
                    } else {
                        audioPlayer.pause();
                        stickyPlayPauseBtn.textContent = 'Play';
                    }
                });

                // Load the first track on page load
                loadTrack(currentTrack);
            });
        </script>

        <style>
            /* Sticky Footer Controls */
            .sticky-controls {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background-color: #f8f9fa;
                border-top: 1px solid #ccc;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 10px;
                box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
                z-index: 9999;
            }

            .sticky-cover-art img {
                width: 50px;
                height: 50px;
                object-fit: cover;
            }

            .sticky-info {
                flex-grow: 1;
                margin-left: 10px;
            }

            .sticky-progress-bar input {
                width: 100%;
                margin: 5px 0;
            }

            .sticky-controls-buttons button {
                background: none;
                border: none;
                font-size: 16px;
                cursor: pointer;
                margin: 0 5px;
            }

            /* Additional styling */
            .flow-audio-playlist-widget {
                /* Playlist styling */
            }
        </style>

<?php
    }