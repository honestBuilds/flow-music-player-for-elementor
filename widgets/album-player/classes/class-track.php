<?php

namespace Flow_Music_Player_For_Elementor\Widgets\Classes;

require_once(__DIR__ . '/../../../assets/util/audio-utils.php');

class Track
{
    public string $title;
    public string $metadata_title;
    public string $duration;
    public string $url;
    public string $attachment_id;
    public string $track_number;

    public function __construct($title, $duration, $url, $track_number, $metadata_title, $attachment_id)
    {
        $this->title = $title;
        $this->duration = $duration;
        $this->url = $url;
        $this->track_number = $track_number;
        $this->$metadata_title = $metadata_title;
        $this->attachment_id = $attachment_id;
    }
}
