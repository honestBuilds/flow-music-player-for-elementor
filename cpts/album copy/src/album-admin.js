jQuery(document).ready(function ($) {
    console.log('album-admin.js loaded');

    var trackIndex = $('#tracks_container .track-item').length;
    console.log('Initial trackIndex:', trackIndex);

    // Function to add a new track
    function addNewTrack() {
        var newTrack = $('#track_template .track-item').clone();

        // Replace placeholders
        var indexPlaceholder = '__index__';
        var numberPlaceholder = '__track_number__';
        var trackNumber = trackIndex + 1;
        var newTrackHtml = newTrack.html()
            .replace(new RegExp(indexPlaceholder, 'g'), trackIndex)
            .replace(new RegExp(numberPlaceholder, 'g'), trackNumber);
        newTrack.html(newTrackHtml);

        // Update data attributes
        newTrack.attr('data-track-index', trackIndex);

        // Replace 'data-name' with 'name'
        newTrack.find('[data-name]').each(function () {
            var name = $(this).attr('data-name');
            $(this).attr('name', name);
            $(this).removeAttr('data-name');
        });

        // Append the new track
        $('#tracks_container').append(newTrack);

        // Attach event listeners
        attachTrackEventListeners(newTrack);

        // Increment the track index
        trackIndex++;
    }

    // Function to attach event listeners
    function attachTrackEventListeners(trackItem) {
        // Toggle track content visibility
        trackItem.find('.track-header').on('click', function () {
            var content = $(this).next('.track-content');
            content.toggle();
            // Update toggle icon
            var icon = $(this).find('.toggle-icon');
            if (content.is(':visible')) {
                icon.removeClass('dashicons-arrow-right').addClass('dashicons-arrow-down');
            } else {
                icon.removeClass('dashicons-arrow-down').addClass('dashicons-arrow-right');
            }
        });

        // Update track title in header when input changes
        trackItem.find('.track-title-input').on('input', function () {
            var title = $(this).val() || '(No Title)';
            var index = $(this).closest('.track-item').attr('data-track-index');
            var trackNumber = parseInt(index) + 1;
            var headerTitle = 'Track ' + trackNumber + ': ' + title;
            $(this).closest('.track-item').find('.track-title').text(headerTitle);
        });

        // Remove track
        trackItem.find('.remove-track').on('click', function (e) {
            e.preventDefault();
            $(this).closest('.track-item').remove();
            updateAllTracks();
        });

        // Select track file
        trackItem.find('.select-track-file').on('click', function (e) {
            e.preventDefault();
            var fileInput = $(this).closest('.track-content').find('.track_file');

            var file_frame = wp.media({
                title: 'Select or Upload an Audio File',
                library: { type: 'audio' },
                button: { text: 'Use this audio' },
                multiple: false
            });

            file_frame.on('select', function () {
                var attachment = file_frame.state().get('selection').first().toJSON();
                fileInput.val(attachment.url);
            });

            file_frame.open();
        });
    }

    // Function to update tracks after removal
    function updateAllTracks() {
        var newTrackIndex = 0;
        $('#tracks_container .track-item').each(function () {
            var trackItem = $(this);

            // Update data-track-index
            trackItem.attr('data-track-index', newTrackIndex);

            // Update names of inputs
            trackItem.find('[name]').each(function () {
                var name = $(this).attr('name');
                name = name.replace(/tracks\[\d+\]/, 'tracks[' + newTrackIndex + ']');
                $(this).attr('name', name);
            });

            // Update track number in header
            var titleInput = trackItem.find('.track-title-input');
            var title = titleInput.val() || '(No Title)';
            var trackNumber = newTrackIndex + 1;
            var headerTitle = 'Track ' + trackNumber + ': ' + title;
            trackItem.find('.track-title').text(headerTitle);

            // Update toggle icon based on content visibility
            var content = trackItem.find('.track-content');
            var icon = trackItem.find('.toggle-icon');
            if (content.is(':visible')) {
                icon.removeClass('dashicons-arrow-right').addClass('dashicons-arrow-down');
            } else {
                icon.removeClass('dashicons-arrow-down').addClass('dashicons-arrow-right');
            }

            // Re-attach event listeners
            trackItem.find('.track-header').off('click');
            trackItem.find('.track-title-input').off('input');
            trackItem.find('.remove-track').off('click');
            trackItem.find('.select-track-file').off('click');
            attachTrackEventListeners(trackItem);

            newTrackIndex++;
        });

        // Update global trackIndex
        trackIndex = newTrackIndex;
    }

    // Event listener for the "Add Track" button
    $('#add-track').on('click', function (e) {
        e.preventDefault();
        addNewTrack();
    });

    // Initialize existing track items
    $('#tracks_container .track-item').each(function () {
        var trackItem = $(this);
        attachTrackEventListeners(trackItem);
    });
});
