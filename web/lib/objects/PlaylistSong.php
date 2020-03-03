<?php
declare(strict_types=1);
require_once __dir__ . '/JsonConvertible.php';

class PlaylistSong extends Song {

    public $orderIndex;

    function __construct() {
    }
}