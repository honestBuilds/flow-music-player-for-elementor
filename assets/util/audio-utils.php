<?php

require_once(__DIR__ . '/../../vendor/autoload.php');

/**
 * Format the given second duration in the form 'hh:mm:ss'
 * 
 * @param int $seconds
 * @return String
 */
function format_audio_duration($seconds)
{
    // Calculate hours, minutes, and seconds
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;

    // If the audio duration is more than an hour, return hh:mm:ss
    if ($hours > 0) {
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    } else {
        // Return mm:ss for durations less than an hour
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}

/**
 * Get the length of the given track in seconds
 * 
 * @param $source
 * @return int
 */
function get_audio_length($source)
{
    if (is_numeric($source)) {
        $file_path = get_attached_file($source);
    } else {
        // Convert URL to server path
        $site_url = rtrim(site_url(), '/');
        $site_path = rtrim(ABSPATH, '/');
        $file_path = urldecode(str_replace($site_url, $site_path, $source));
    }

    // Check if file exists
    if (!file_exists($file_path)) {
        error_log('Audio file not found: ' . $file_path);
        return 0;
    }

    // Analyze file with getID3
    $getID3 = new getID3();
    try {
        $file_info = $getID3->analyze($file_path);
        return isset($file_info['playtime_seconds']) ? (int)$file_info['playtime_seconds'] : 0;
    } catch (Exception $e) {
        error_log('Error analyzing audio file: ' . $e->getMessage());
        return 0;
    }
}

function get_formatted_audio_length($source)
{
    return format_audio_duration(get_audio_length($source));
}

/**
 * Get the title of the given track from metadata
 * 
 * @param Array $track
 * @return String
 */
function get_audio_title($track)
{

    // Instantiate getID3 object
    $getID3 = new getID3();

    // return if no file is selected
    if (!$track['media_library']) {
        return '';
    } else {

        $attachment_id = $track['media_library']['id'];
        // Get the audio file info
        $file_info = $getID3->analyze(get_attached_file($attachment_id));

        // Check if the file has metadata and extract the track title
        if (isset($file_info['tags'])) {
            // Try to retrieve the title from various possible locations
            if (isset($file_info['tags']['id3v2']['title'][0])) {
                $track_title = $file_info['tags']['id3v2']['title'][0]; // ID3v2 tag
            } elseif (isset($file_info['tags']['id3v1']['title'][0])) {
                $track_title = $file_info['tags']['id3v1']['title'][0]; // ID3v1 tag
            } elseif (isset($file_info['tags']['vorbiscomment']['title'][0])) {
                $track_title = $file_info['tags']['vorbiscomment']['title'][0]; // OGG or similar
            } else {
                $track_title = 'Unknown Title'; // Default if no title found
            }
        } else {
            $track_title = 'No metadata found';
        }
    }

    return $track_title;
}
