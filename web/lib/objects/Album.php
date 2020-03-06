<?php
declare(strict_types=1);
require_once __dir__ . '/JsonConvertible.php';

class Album extends JsonConvertible {

    public $id;
    public $title;
    public $description;
    public $playlistId;
    public $playlist;
    public $artworkFilename;
    public $publishDate;
    public $fileInput;

    function __construct() {
    }
}
