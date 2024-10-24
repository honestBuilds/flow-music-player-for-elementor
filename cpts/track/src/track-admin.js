jQuery(document).ready(function ($) {

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

    let searchTimeout;

    // Album search functionality
    $('#album_search').on('keyup', function () {
        clearTimeout(searchTimeout);
        const query = $(this).val();

        if (query.length < 2) {
            $('#album_search_results').empty();
            return;
        }

        searchTimeout = setTimeout(function () {
            $.ajax({
                url: ajaxurl,
                type: 'GET',
                data: {
                    action: 'search_albums',
                    nonce: track_admin_vars.nonce,
                    query: query
                },
                success: function (response) {
                    $('#album_search_results').html(response);
                }
            });
        }, 300);
    });

    // Add album
    $(document).on('click', '.add-album', function () {
        const albumId = $(this).data('album-id');
        const albumTitle = $(this).data('album-title');

        // Clear existing album
        $('#album_container').empty();

        // Add new album
        const albumHtml = `
            <div class="album-item" data-album-id="${albumId}">
                <span>${albumTitle}</span>
                <input type="hidden" name="track_album" value="${albumId}">
                <button type="button" class="remove-album button">Remove</button>
            </div>
        `;

        $('#album_container').html(albumHtml);
        $('#album_search').val('');
        $('#album_search_results').empty();
    });

    // Remove album
    $(document).on('click', '.remove-album', function () {
        $(this).closest('.album-item').remove();
    });
});