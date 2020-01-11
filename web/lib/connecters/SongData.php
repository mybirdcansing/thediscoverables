<?php
require_once __dir__ . '/../objects/Song.php';
require_once __dir__ . '/../objects/User.php';
require_once __dir__ . '/../objects/DuplicateTitleException.php';

class SongData
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
				song_id,
				title,
				filename,
				description
		  	FROM 
		  		song;";

        try {
			$stmt = $this->dbConnection->query($sql);
            $songs = [];
			while ($row = $stmt->fetch_assoc()) {
			    $songs[] = $this->_rowToSong($row);
			}
            return $songs;
        } catch (\mysqli_sql_exception $e) {
            exit($e->getMessage());
        }
    }

    public function find($id)
    {
        $sql = "
            SELECT 
                song_id,
                title,
                filename,
                description
		  	FROM 
		  		song
		  	WHERE song_id = ?;
        ";

		$stmt = $this->dbConnection->prepare($sql);
		$stmt->bind_param("s", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows == 1) {
		    $row = $result->fetch_assoc();
		    $song = $this->_rowToSong($row);
		    return $song;
		} else {
			return 0;
		}   
    }

    public function insert(Song $song, User $administrator)
    {
        $sql = "
			INSERT INTO 
				song (
                    song_id,
                    title,
                    description, 
                    filename,
                    modified_date,
                    modified_by_id,
                    created_date,
                    created_by_id
                )
			VALUES (?, ?, ?, ?, now(), ?, now(), ?);
        ";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $songId = GUID();
            $stmt->bind_param("ssssss",
                $songId,
				$song->title,
				$song->description,
				$song->filename,
                $administrator->id,
                $administrator->id
            );
            $stmt->execute();
            $stmt->store_result();
            return $songId;
        } catch (mysqli_sql_exception $e) {
           $mysqliErrorMessage = $e->getMessage();
           if (strpos($mysqliErrorMessage, 'title') !== false) {
                throw new DuplicateTitleException(
                    sprintf(TITLE_TAKEN_MESSAGE, $song->title), TITLE_TAKEN_CODE);
            } else {
                error_log($e->getMessage());
                throw $e;
            }
        }
    }

    public function update(Song $song, User $administrator)
    {
        $sql = "
            UPDATE song
                SET
                    title = ?,
                    description = ?, 
                    filename = ?,
                    modified_date = now(),
                    modified_by_id = ?
                WHERE song_id = ?;
        ";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("sssss",
                $song->title,
                $song->description,
                $song->filename,
                $administrator->id,
                $song->id
            );
            $stmt->execute();
            $stmt->store_result();
            return $song->id;
        } catch (mysqli_sql_exception $e) {
           $mysqliErrorMessage = $e->getMessage();
           if (strpos($mysqliErrorMessage, 'title') !== false) {
                throw new DuplicateTitleException(
                    sprintf(TITLE_TAKEN_MESSAGE, $song->title), TITLE_TAKEN_CODE);
            } else {
                error_log($e->getMessage());
                throw $e;
            }
        }
    }

    public function delete($id)
    {
        $this->leavePlaylists($id);

        $sql = "DELETE FROM song WHERE song_id = ?;";

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
        $sql = "DELETE FROM playlist_song WHERE song_id = ?";

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

	private function _rowToSong($row)
    {
	    $song = new Song();
	    $song->id = $row["song_id"];
	    $song->title = $row["title"];
	    $song->description = $row["description"];
	    $song->filename = $row["filename"];
	    return $song;
	}
}