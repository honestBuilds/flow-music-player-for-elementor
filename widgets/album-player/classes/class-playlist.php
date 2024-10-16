<?php

namespace Flow_Music_Player_For_Elementor\Widgets\Classes;

use JsonSerializable;

class Playlist
{
    public string $title;
    public array $tracks;
    public string $cover_art;
    public string $artist;
    public string $year;
    public string $duration;

    public function __construct($title, $cover_art, $tracks, $artist, $year)
    {
        $this->title = $title;
        $this->cover_art = $cover_art;
        $this->tracks = $tracks;
        $this->artist = $artist;
        $this->year = $year;
    }

    public function get_num_tracks()
    {
        return count($this->tracks);
    }

    public function calculate_duration()
    {
        // let audio = new Audio();

    }
}
