jQuery(document).ready(function ($) {
    console.log('track-admin.js loaded');

    if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
        console.error('WordPress media library not available');
        return;
    }

    // Event listener for the "Select File" button
    $('.select-track-file').on('click', function (e) {
        console.log('Select File button clicked');
        e.preventDefault();

        // Create a media frame
        var frame = wp.media({
            title: 'Select Audio File',
            button: {
                text: 'Use this audio'
            },
            multiple: false,
            library: {
                type: 'audio'
            }
        });

        // When an audio is selected, run a callback
        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            if (attachment && attachment.url) {
                $('#track_url').val(attachment.url);
                $('.select-track-file').text('Change Audio File'); // Update button text
            } else {
                console.error('No valid audio file selected');
                // Optionally, show an error message to the user
            }
        });

        // Open the media frame
        frame.open();
    });
});