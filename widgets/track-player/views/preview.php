<#
    var trackData;
    if (settings.track_source==='track_cpt' ) {
    trackData={
    track_title: 'Track Title' ,
    track_artist: 'Artist Name' ,
    track_duration: '0:00' ,
    featured_image: '/wp-content/plugins/flow-music-player-for-elementor/assets/img/placeholder.webp'
    };
    } else {
    trackData={
    track_title: settings.track_title,
    track_artist: settings.track_artist,
    track_duration: '0:00' ,
    featured_image: settings.track_image.url
    };
    }

    var useBlurredBackground=settings.use_blurred_background==='yes' ;
    #>
    <div class="fmp-track-player">
        <# if (trackData.featured_image && useBlurredBackground) { #>
            <div class="fmp-track-player-background bg-cover bg-center bg-no-repeat"
                style="background-image: url('{{ trackData.featured_image }}');"
                data-track-metadata='{{ JSON.stringify(trackData) }}'>
            </div>
            <# } #>
                <# if (trackData.featured_image) { #>
                    <div class="track-image">
                        <img src="{{ trackData.featured_image }}" alt="{{ trackData.track_title }}">
                    </div>
                    <# } #>
                        <div class="track-content">
                            <div class="track-info">
                                <div class="track-details">
                                    <div class="track-title">{{{ trackData.track_title }}}</div>
                                    <div class="track-artist-line">
                                        <span class="track-artist">{{{ trackData.track_artist }}}</span>
                                        <# if (settings.show_track_number==='yes' && settings.track_list_number_input) { #>
                                            <span class="fmp-track-list-number">({{{ settings.track_list_number_input }}})</span>
                                            <# } #>
                                    </div>
                                </div>
                                <button class="play-pause-btn"></button>
                            </div>
                            <div class="progress-container">
                                <div class="progress-bar">
                                    <div class="progress-fill"></div>
                                </div>
                            </div>
                            <div class="duration">{{{ trackData.track_duration }}}</div>
                        </div>
    </div>