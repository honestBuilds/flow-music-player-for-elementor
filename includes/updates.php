<?php

require_once(__DIR__ . '/../vendor/plugin-update-checker/plugin-update-checker.php');

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;


function initialize_flow_music_player_update_checker()
{
    $myUpdateChecker = PucFactory::buildUpdateChecker(
        'https://github.com/honestBuilds/flow-music-player-for-elementor/',
        FLOW_MUSIC_PLAYER_FILE,
        'flow-music-player-for-elementor',
        24
    );
}
