<?php
declare(strict_types=1);
require_once __dir__ . '/JsonConvertible.php';

class Playlist extends JsonConvertible {

    public $id;
    public $title;
    public $description;

    function __construct() {
    }
}