<?php
require_once __DIR__ . '/../../messages.php';

class SongController {

    private $db;
    private $action;
    private $songId;
    private $songData;
    private $administrator;

    public function __construct($dbConnection, $action, $songId, $administrator)
    {
        $this->action = $action;
        $this->songId = $songId;
        $this->administrator = $administrator;
        $this->songData = new SongData($dbConnection);
    }

    public function processRequest()
    {

        switch ($this->action) {
            case GET_ACTION:
                if ($this->songId) {
                    $response = $this->_getSong();
                } else {
                    $response = $this->_getAllSongs();
                };
                break;
            case DELETE_ACTION:
                $response = $this->_deleteSong();
                break;
            case UPDATE_ACTION:
                $response = $this->_updateSong();
                break;
            case CREATE_ACTION:
                $response = $this->_createSong();
                break;
            default:
                $response = $this->_notFoundResponse();
                break;
        }

        if (array_key_exists('problem_header', $response)) {
            header('Content-Type: application/problem+json; charset=UTF-8');
        }

        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function _getAllSongs()
    {
        $result = $this->songData->findAll();
        return $this->_okResponse(array_map(function($val) { 
            return $val->expose(); 
        }, $result));
        return $response;
    }

    private function _getSong()
    {
        $song = $this->songData->find($this->songId);
        if (!$song) {
            return $this->_notFoundResponse();
        }
        return $this->_okResponse($song->expose());
    }

    private function _createSong()
    {
        $song = Song::fromJson(file_get_contents('php://input'));
        $validationIssues = $this->_validationIssues($song);
        
        if ((bool)$validationIssues) {
            return $this->_unprocessableEntityResponse([
                "songCreated" => false,
                "errorMessages" => $validationIssues
            ]);
        }
        try {
            $songId = $this->songData->insert($song, $this->administrator);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "songCreated" => true, 
                "songId" => $songId
            ]);
        } catch (DuplicateTitleException $e) {
            return $this->_conflictResponse([
                "songCreated" => false,
                "errorMessages" => [$e->getCode() => $e->getMessage()]
            ]);
        }
        return $response;
    }

    private function _updateSong()
    {
        $song = Song::fromJson(file_get_contents('php://input'));
        $validationIssues = $this->_validationIssues($song);
        if ((bool)$validationIssues) {
            return $this->_unprocessableEntityResponse([
                "songUpdated" => false,
                "errorMessages" => $validationIssues
            ]);
        }
        
        $existingSong = $this->songData->find($song->id);
        if (!$existingSong) {
            return $this->_notFoundResponse();
        }

        try {
            $this->songData->update($song, $this->administrator);

            return $this->_okResponse([
                "songUpdated" => true, 
                "songId" => $song->id
            ]);
        } catch (DuplicateTitleException $e) {
            return $this->_conflictResponse([
                "songUpdated" => false, 
                "songId" => $song->id,
                "errorMessages" => array($e->getCode() => $e->getMessage())
            ]);
        }

        return $response;
    }

    private function _deleteSong()
    {
        $song = $this->songData->find($this->songId);
        if (!$song) {
            return $this->_notFoundResponse();
        }
        $deleted = $this->songData->delete($this->songId);
        
        return $this->_okResponse();
    }

    private function _validationIssues($song)
    {
        $errorMessages = [];
        
        if (!isset($song->title) || $song->title == '') {
            $errorMessages[TITLE_BLANK_CODE] = TITLE_BLANK_MESSAGE;
        } else {
            if (strlen($song->title) > 64) {
                $errorMessages[TITLE_LONG_CODE] = TITLE_LONG_MESSAGE;
            }
            if ($this->_isInputStrValid($song->title)) {
                $errorMessages[TITLE_INVALID_CODE] = TITLE_INVALID_MESSAGE;
            }
        }
        return $errorMessages;
    }

    private function _okResponse($json = '{}')
    {
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($json);
        return $response;
    }

    private function _conflictResponse($json = '{}')
    {
        $response['problem_header'] = true;
        $response['status_code_header'] = 'HTTP/1.1 409 Conflict';
        $response['body'] = json_encode($json);
        return $response;
    }

    private function _unprocessableEntityResponse($json = '{}')
    {
        $response['problem_header'] = true;
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode($json);
        return $response;
    }

    private function _notFoundResponse($json = null)
    {
        $response['problem_header'] = true;
        if ($json == null) {
            $json = [
                "errorMessages" => [
                    SONG_NOT_FOUND_CODE => SONG_NOT_FOUND_MESSAGE
                ]
            ];
        }
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode($json);
        return $response;
    }

    private function _isInputStrValid($str) {
        // invalid chars are ' \ ` | ; @ " < > \
        return preg_match('/[\/`\|;@"\<\>\\\]/', $str);
        // return preg_match('/[\'\/`\|;@"\<\>\\\]/', $str);
    }
}