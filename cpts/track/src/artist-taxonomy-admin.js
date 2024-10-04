jQuery(document).ready(function ($) {
    var file_frame;

    // Handle the artist thumbnail upload button click
    $(document).on('click', '.artist_media_button', function (e) {
        e.preventDefault();

        // If the media frame already exists, reopen it.
        if (file_frame) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Select or Upload Artist Thumbnail',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        // When an image is selected, run a callback.
        file_frame.on('select', function () {
            var attachment = file_frame.state().get('selection').first().toJSON();
            $('#artist_thumbnail').val(attachment.id);
            $('#artist_thumbnail_wrapper').html('<img src="' + attachment.url + '" style="max-width:100%;"/>');
            $('.artist_media_remove').show();
        });

        // Finally, open the modal
        file_frame.open();
    });

    // Handle the remove thumbnail button click
    $(document).on('click', '.artist_media_remove', function (e) {
        e.preventDefault();
        $('#artist_thumbnail').val('');
        $('#artist_thumbnail_wrapper').html('');
        $(this).hide();
    });

    // Handle AJAX response
    $(document).ajaxComplete(function (event, xhr, settings) {
        if (settings.data && typeof settings.data === 'string') {
            var queryStringArr = settings.data.split('&');
            if ($.inArray('action=add-tag', queryStringArr) !== -1) {
                var xml = xhr.responseXML;
                if (xml && $(xml).find('term_id').length) {
                    var response = $(xml).find('term_id').text();
                    if (response != "") {
                        // Clear the thumb image
                        $('#artist_thumbnail_wrapper').html('');
                    }
                }
            }
        }
    });
});