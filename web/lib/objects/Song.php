<?php
declare(strict_types=1);
require_once __dir__ . '/JsonConvertible.php';

class Song extends JsonConvertible {

    public $id;
    public $title;
    public $filename;
    public $description;
    public $fileInput;
    public $duration;

    function __construct() {
    }
}