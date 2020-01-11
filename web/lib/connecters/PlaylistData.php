<?php
require_once __dir__ . '/../objects/Playlist.php';
require_once __dir__ . '/../objects/Song.php';
require_once __dir__ . '/../objects/User.php';
require_once __dir__ . '/../objects/DuplicateTitleException.php';

class PlaylistData
{ 
	private $dbConnection = null;

    function __construct($dbConnection) 
    {
        $this->dbConnection = $dbConnection;
    }

	public function findAll()
    {
        $sql = "
            SELECT 
				playlist_id,
				title,
				description
		  	FROM 
		  		playlist;";

        try {
			$stmt = $this->dbConnection->query($sql);
            $playlists = [];
			while ($row = $stmt->fetch_assoc()) {
			    $playlists[] = $this->_rowToPlaylist($row);
			}
            return $playlists;
        } catch (\mysqli_sql_exception $e) {
            exit($e->getMessage());
        }
    }

    public function find($id)
    {
        $sql = "
            SELECT 
                playlist_id,
                title,
                description
		  	FROM 
		  		playlist
		  	WHERE playlist_id = ?;
        ";

		$stmt = $this->dbConnection->prepare($sql);
		$stmt->bind_param("s", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows == 1) {
		    $row = $result->fetch_assoc();
		    $playlist = $this->_rowToPlaylist($row);
		    return $playlist;
		} else {
			return 0;
		}   
    }

    public function insert(Playlist $playlist, User $administrator)
    {
        $sql = "
			INSERT INTO 
				playlist (
                    playlist_id,
                    title,
                    description,
                    modified_date,
                    modified_by_id,
                    created_date,
                    created_by_id
                )
			VALUES (?, ?, ?, now(), ?, now(), ?);
        ";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $playlistId = GUID();
            $stmt->bind_param("sssss",
                $playlistId,
				$playlist->title,
				$playlist->description,
                $administrator->id,
                $administrator->id
            );
            $stmt->execute();
            $stmt->store_result();
            return $playlistId;
        } catch (mysqli_sql_exception $e) {
           $mysqliErrorMessage = $e->getMessage();
           if (strpos($mysqliErrorMessage, 'title') !== false) {
                throw new DuplicateTitleException(
                    sprintf(TITLE_TAKEN_MESSAGE, $playlist->title), TITLE_TAKEN_CODE);
            } else {
                error_log($e->getMessage());
                throw $e;
            }
        }
    }

    public function update(Playlist $playlist, User $administrator)
    {
        $sql = "
            UPDATE playlist
                SET
                    title = ?,
                    description = ?, 
                    modified_date = now(),
                    modified_by_id = ?
                WHERE playlist_id = ?;
        ";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("ssss",
                $playlist->title,
                $playlist->description,
                $administrator->id,
                $playlist->id
            );
            $stmt->execute();
            $stmt->store_result();
            return $playlist->id;
        } catch (mysqli_sql_exception $e) {
           $mysqliErrorMessage = $e->getMessage();
           if (strpos($mysqliErrorMessage, 'title') !== false) {
                throw new DuplicateTitleException(
                    sprintf(TITLE_TAKEN_MESSAGE, $playlist->title), TITLE_TAKEN_CODE);
            } else {
                error_log($e->getMessage());
                throw $e;
            }
        }
    }

    public function delete($id)
    {
        // if playlist is in an album, throw an exception

        $sql = "DELETE FROM playlist WHERE playlist_id = ?;";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $stmt->store_result();
            $rowsEffected = $stmt->num_rows;
            return $rowsEffected;
        } catch (\mysqli_sql_exception $e) {
            exit($e->getMessage());
        }    
    }

    public function leavePlaylists($id)
    {
        $sql = "DELETE FROM playlist_song WHERE playlist_id = ?";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $stmt->store_result();
            $rowsEffected = $stmt->num_rows;
            return $rowsEffected;
        } catch (\mysqli_sql_exception $e) {
            exit($e->getMessage());
        }    
    }

	private function _rowToPlaylist($row)
    {
	    $playlist = new Playlist();
	    $playlist->id = $row["playlist_id"];
	    $playlist->title = $row["title"];
	    $playlist->description = $row["description"];
	    return $playlist;
	}
}