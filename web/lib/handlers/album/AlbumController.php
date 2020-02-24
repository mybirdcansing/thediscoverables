<?php
require_once __DIR__ . '/../../messages.php';

class AlbumController {

    private $db;
    private $action;
    private $albumId;
    private $albumData;
    private $administrator;

    public function __construct($dbConnection, $action, $albumId, $administrator)
    {
        $this->albumData = new AlbumData($dbConnection);
        $this->action = $action;
        $this->albumId = $albumId;
        $this->administrator = $administrator;
    }

    public function processRequest()
    {

        switch ($this->action) {
            case GET_ACTION:
                if ($this->albumId) {
                    $response = $this->_getAlbum();
                } else {
                    $response = $this->_getAllAlbums();
                };
                break;
            case DELETE_ACTION:
                $response = $this->_deleteAlbum();
                break;
            case UPDATE_ACTION:
                $response = $this->_updateAlbum();
                break;
            case CREATE_ACTION:
                $response = $this->_createAlbum();
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

    private function _getAllAlbums()
    {
        $result = $this->albumData->findAll();
        return $this->_okResponse(array_map(function($val) {
            return $val->expose();
        }, $result));
    }

    private function _getAlbum()
    {
        $album = $this->albumData->find($this->albumId);
        if (!$album) {
            return $this->_notFoundResponse();
        }
        return $this->_okResponse($album->expose());
    }

    private function _createAlbum()
    {
        $album = Album::fromJson(file_get_contents('php://input'));
        $validationIssues = $this->_validationIssues($album);
        if ((bool)$validationIssues) {
            return $this->_unprocessableEntityResponse([
                "albumCreated" => false,
                "errorMessages" => $validationIssues
            ]);
        }
        try {
            $albumId = $this->albumData->insert($album, $this->administrator);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "albumCreated" => true,
                "albumId" => $albumId
            ]);
        } catch (DuplicateTitleException $e) {
            return $this->_conflictResponse([
                "albumCreated" => false,
                "errorMessages" => [$e->getCode() => $e->getMessage()]
            ]);
        }
        return $response;
    }

    private function _updateAlbum()
    {
        $album = Album::fromJson(file_get_contents('php://input'));
        $validationIssues = $this->_validationIssues($album);
        if ((bool)$validationIssues) {
            return $this->_unprocessableEntityResponse([
                "albumUpdated" => false,
                "errorMessages" => $validationIssues
            ]);
        }

        $existingAlbum = $this->albumData->find($album->id);
        if (!$existingAlbum) {
            return $this->_notFoundResponse();
        }

        try {
            $this->albumData->update($album, $this->administrator);
            return $this->_okResponse([
                "albumUpdated" => true,
                "albumId" => $album->id
            ]);
        } catch (DuplicateTitleException $e) {
            return $this->_conflictResponse([
                "albumUpdated" => false,
                "albumId" => $album->id,
                "errorMessages" => array($e->getCode() => $e->getMessage())
            ]);
        }

        return $response;
    }

    private function _deleteAlbum()
    {
        $result = $this->albumData->find($this->albumId);
        if (!$result) {
            return $this->_notFoundResponse();
        }
        $this->albumData->delete($this->albumId);
        return $this->_okResponse();
    }

    private function _validationIssues($album)
    {
        $errorMessages = [];
        if (!isset($album->title) || $album->title == '') {
            $errorMessages[TITLE_BLANK_CODE] = TITLE_BLANK_MESSAGE;
        } else {
            if (strlen($album->title) > 64) {
                $errorMessages[TITLE_LONG_CODE] = TITLE_LONG_MESSAGE;
            }
            if ($this->_isInputStrValid($album->title)) {
                $errorMessages[TITLE_INVALID_CODE] = TITLE_INVALID_MESSAGE;
            }
        }
        if (!isset($album->playlist)) {
            $errorMessages[PLAYLIST_BLANK_CODE] = PLAYLIST_BLANK_MESSAGE;
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
