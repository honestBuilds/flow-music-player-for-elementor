jQuery(document).ready(function ($) {
    function album_artist_media_upload(button_class) {
        var _custom_media = true,
            _orig_send_attachment = wp.media.editor.send.attachment;

        $('body').on('click', button_class, function (e) {
            var button_id = '#' + $(this).attr('id');
            var send_attachment_bkp = wp.media.editor.send.attachment;
            var button = $(button_id);
            _custom_media = true;

            wp.media.editor.send.attachment = function (props, attachment) {
                if (_custom_media) {
                    $('#album_artist_thumbnail').val(attachment.id);
                    $('#album_artist_thumbnail_wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                    $('#album_artist_thumbnail_wrapper .custom_media_image').attr('src', attachment.url).css('display', 'block');
                } else {
                    return _orig_send_attachment.apply(button_id, [props, attachment]);
                }
            }

            wp.media.editor.open(button);

            // Set to only allow one image to be selected
            var selection = wp.media.frame.state().get('selection');
            selection.multiple = false;

            return false;
        });
    }

    album_artist_media_upload('.album_artist_media_button.button');

    $('body').on('click', '.album_artist_media_remove', function () {
        $('#album_artist_thumbnail').val('');
        $('#album_artist_thumbnail_wrapper').html('');
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
                        $('#album_artist_thumbnail_wrapper').html('');
                    }
                }
            }
        }
    });
});
