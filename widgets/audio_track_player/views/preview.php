<#
    var trackData;
    if (settings.track_source==='track_cpt' ) {
    trackData={
    track_title: '{{ settings.track_cpt }}' ,
    track_artist: 'Artist Name' ,
    track_duration: '0:00' ,
    featured_image: '{{ settings.track_cpt_image.url }}'
    };
    } else {
    trackData={
    track_title: settings.track_title,
    track_artist: settings.track_artist,
    track_duration: '0:00' ,
    featured_image: settings.track_image.url
    };
    }
    #>
    <div class="flow-audio-track-player">
        <# if (trackData.featured_image) { #>
            <div class="track-image">
                <img src="{{ trackData.featured_image }}" alt="{{ trackData.track_title }}">
            </div>
            <# } #>
                <div class="track-content">
                    <div class="track-info">
                        <div class="track-details">
                            <div class="track-title">{{{ trackData.track_title }}}</div>
                            <div class="track-artist">{{{ trackData.track_artist }}}</div>
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