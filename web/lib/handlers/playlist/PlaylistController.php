<?php
require_once __DIR__ . '/../../messages.php';

class PlaylistController {

    private $db;
    private $action;
    private $playlistId;
    private $playlistData;
    private $administrator;
    private $songData;
    private $songId;

    public function __construct($dbConnection, $action, $playlistId, $songId, $administrator)
    {
        $this->songData = new SongData($dbConnection);
        $this->playlistData = new PlaylistData($dbConnection);
        $this->action = $action;
        $this->playlistId = $playlistId;
        $this->songId = $songId;
        $this->administrator = $administrator;
    }

    public function processRequest()
    {
        switch ($this->action) {
            case GET_ACTION:

                if ($this->playlistId) {
                    $response = $this->_getPlaylist();
                } else {
                    $response = $this->_getAllPlaylists();
                };
                break;
            case DELETE_ACTION:
                $response = $this->_deletePlaylist();
                break;
            case UPDATE_ACTION:
                $response = $this->_updatePlaylist();
                break;
            case CREATE_ACTION:
                $response = $this->_createPlaylist();
                break;
            case ADD_TO_PLAYLIST_ACTION:
                $response = $this->_addTooPlaylist();
                break;
            case REMOVE_FROM_PLAYLIST_ACTION:
                $response = $this->_removeFromPlaylist();
                break;
            case GET_PLAYLIST_SONGS_ACTION:
                $response = $this->_getPlaylistSongs();
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

    private function _getAllPlaylists()
    {
        $result = $this->playlistData->findAll();
        return $this->_okResponse(array_map(function($val) {
            return $val->expose();
        }, $result));
    }

    private function _getPlaylist()
    {
        $playlist = $this->playlistData->find($this->playlistId);
        if (!$playlist) {
            error_log('playlistId from _getPlaylist: ' . $this->playlistId);
            return $this->_notFoundResponse();
        }
        return $this->_okResponse($playlist->expose());
    }

    private function _getPlaylistSongs() {
        $result = $this->playlistData->getPlaylistSongs();
        return $this->_okResponse(array_map(function($val) {
            return get_object_vars($val);
        }, $result));
    }

    private function _createPlaylist()
    {
        $json = json_decode(file_get_contents('php://input'));
        if (isset($json->data)) {
            $playlist = Playlist::fromJson(json_encode($json->data));
        } else {
            $playlist = Playlist::fromJson(file_get_contents('php://input'));
        }
        $validationIssues = $this->_validationIssues($playlist);
        if ((bool)$validationIssues) {
            return $this->_unprocessableEntityResponse([
                "playlistCreated" => false,
                "errorMessages" => $validationIssues
            ]);
        }
        try {
            $playlistId = $this->playlistData->insert($playlist, $this->administrator);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "playlistCreated" => true,
                "playlistId" => $playlistId
            ]);

        } catch (DuplicateTitleException $e) {
            return $this->_conflictResponse([
                "playlistCreated" => false,
                "errorMessages" => [$e->getCode() => $e->getMessage()]
            ]);
        }
        return $response;
    }

    private function _updatePlaylist()
    {
        $json = json_decode(file_get_contents('php://input'));
        if (isset($json->data)) {
            $playlist = Playlist::fromJson(json_encode($json->data));
        } else {
            $playlist = Playlist::fromJson(file_get_contents('php://input'));
        }
        $validationIssues = $this->_validationIssues($playlist);
        if ((bool)$validationIssues) {
            return $this->_unprocessableEntityResponse([
                "playlistUpdated" => false,
                "errorMessages" => $validationIssues
            ]);
        }

        $existingPlaylist = $this->playlistData->find($playlist->id);
        if (!$existingPlaylist) {
            return $this->_notFoundResponse();
        }

        try {
            $this->playlistData->update($playlist, $this->administrator);

            return $this->_okResponse([
                "playlistUpdated" => true,
                "playlistId" => $playlist->id
            ]);
        } catch (DuplicateTitleException $e) {
            return $this->_conflictResponse([
                "playlistUpdated" => false,
                "playlistId" => $playlist->id,
                "errorMessages" => array($e->getCode() => $e->getMessage())
            ]);
        }

        return $response;
    }

    private function _deletePlaylist()
    {
        $result = $this->playlistData->find($this->playlistId);
        if (!$result) {
            return $this->_notFoundResponse();
        }
        try {
            $this->playlistData->delete($this->playlistId);
        } catch (DeletePlaylistInAlbumException $e) {
            return $this->_conflictResponse([
                "playlistDeleted" => false,
                "playlistId" => $this->playlistId,
                "errorMessages" => array(DELETE_PLAYLIST_IN_ALBUM_CODE => DELETE_PLAYLIST_IN_ALBUM_MESSAGE)
            ]);
        }
        return $this->_okResponse();
    }

    public function _addTooPlaylist()
    {
        $song = $this->songData->find($this->songId);
        if (!$song) {
            return $this->_notFoundResponse();
        }
        $playlist = $this->playlistData->find($this->playlistId);
        if (!$playlist) {
            return $this->_notFoundResponse();
        }
        $this->playlistData->addToPlaylist($this->playlistId, $this->songId, $this->administrator);
        $playlist = $this->playlistData->find($this->playlistId);
        return $this->_okResponse($playlist->expose());
    }

    public function _removeFromPlaylist()
    {
        $song = $this->songData->find($this->songId);
        if (!$song) {
            return $this->_notFoundResponse();
        }
        $playlist = $this->playlistData->find($this->playlistId);
        if (!$playlist) {
            return $this->_notFoundResponse(null,
                [PLAYLIST_NOT_FOUND_CODE => PLAYLIST_NOT_FOUND_MESSAGE]);
        }
        $this->playlistData->removeFromPlaylist($this->playlistId, $this->songId);

        $playlist = $this->playlistData->find($this->playlistId);
        return $this->_okResponse($playlist->expose());
    }

    private function _validationIssues($playlist)
    {
        $errorMessages = [];

        if (!isset($playlist->title) || $playlist->title == '') {
            $errorMessages[TITLE_BLANK_CODE] = TITLE_BLANK_MESSAGE;
        } else {
            if (strlen($playlist->title) > 64) {
                $errorMessages[TITLE_LONG_CODE] = TITLE_LONG_MESSAGE;
            }
            if ($this->_isInputStrValid($playlist->title)) {
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
                    PLAYLIST_NOT_FOUND_CODE => PLAYLIST_NOT_FOUND_MESSAGE
                ]
            ];
        }
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode($json);
        return $response;
    }

    private function _isInputStrValid($str) {
        // invalid chars are ' \ ` | ; @ " < > \
        return preg_match('/[\'\/`\|;@"\<\>\\\]/', $str);
    }
}
