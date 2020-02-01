<?php
require_once __dir__ . '/../objects/Playlist.php';
require_once __dir__ . '/../objects/Song.php';
require_once __dir__ . '/../objects/User.php';
require_once __dir__ . '/SongData.php';
require_once __dir__ . '/../objects/DuplicateTitleException.php';
require_once __dir__ . '/../objects/DeletePlaylistInAlbumException.php';

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
            error_log($e->getMessage());
            throw $e;
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
		} else {
			return 0;
		}

        $sql = "
            SELECT 
                s.song_id,
                s.title,
                s.description,
                s.filename
            FROM song s
            JOIN playlist_song pls ON 
                s.song_id = pls.song_id
            WHERE pls.playlist_id = ?;
        ";
        $playlist->songs = [];
        try {
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $songData = new SongData($this->dbConnection);
                while ($row = $result->fetch_assoc()) {
                    $playlist->songs[] = $songData->rowToSong($row);
                }
            }
        } catch (\mysqli_sql_exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
	    return $playlist;
    }

    public function insert(Playlist $playlist, User $administrator)
    {

        $sql1 = "
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
            $stmt1 = $this->dbConnection->prepare($sql1);
            $this->dbConnection->autocommit(FALSE);
            $playlistId = GUID();
            $adminId = $administrator->id;
            $stmt1->bind_param("sssss",
                $playlistId,
				$playlist->title,
				$playlist->description,
                $adminId,
                $adminId
            );

            $stmt1->execute();

            $sql2 = "
                INSERT INTO 
                    playlist_song (
                        playlist_song_id,
                        song_id,
                        playlist_id,
                        created_date,
                        created_by_id
                    )
                VALUES (?, ?, ?, now(), ?);
            ";

            foreach($playlist->songs as $song) {
                $stmt2 = $this->dbConnection->prepare($sql2);
                $playlistSongId = GUID();
                $stmt2->bind_param("ssss",
                    $playlistSongId,
                    $song->id,
                    $playlistId,
                    $administrator->id
                );
                $stmt2->execute();
            }
            $this->dbConnection->commit();
            return $playlistId;
        } catch (mysqli_sql_exception $e) {
            $this->dbConnection->rollback();
            $mysqliErrorMessage = $e->getMessage();
            if (strpos($mysqliErrorMessage, 'title') !== false) {
                throw new DuplicateTitleException(
                    sprintf(TITLE_TAKEN_MESSAGE, $playlist->title), TITLE_TAKEN_CODE);
            } else {
                error_log($e->getMessage());
                throw $e;
            }
        } finally {
            $this->dbConnection->autocommit(TRUE);
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
            $this->dbConnection->autocommit(FALSE);
            $stmt1 = $this->dbConnection->prepare($sql);
            $stmt1->bind_param("ssss",
                $playlist->title,
                $playlist->description,
                $administrator->id,
                $playlist->id
            );
            $stmt1->execute();
            $sql2 = "DELETE FROM playlist_song WHERE playlist_id = ?;";
            $stmt2 = $this->dbConnection->prepare($sql2);
            $stmt2->bind_param("s", $playlist->id);
            $stmt2->execute();
            $sql3 = "
                INSERT INTO 
                    playlist_song (
                        playlist_song_id,
                        song_id,
                        playlist_id,
                        created_date,
                        created_by_id
                    )
                VALUES (?, ?, ?, now(), ?);
            ";
            if (isset($playlist->songs)) {
                foreach($playlist->songs as $song) {
                    $stmt3 = $this->dbConnection->prepare($sql3);
                    $playlistSongId = GUID();
                    $stmt3->bind_param("ssss",
                        $playlistSongId,
                        $song->id,
                        $playlist->id,
                        $administrator->id
                    );
                    $stmt3->execute();
                }
            }
            $this->dbConnection->commit();
            return $playlist->id;
        } catch (mysqli_sql_exception $e) {
           $this->dbConnection->rollback();
           $mysqliErrorMessage = $e->getMessage();
           if (strpos($mysqliErrorMessage, 'title') !== false) {
                throw new DuplicateTitleException(
                    sprintf(TITLE_TAKEN_MESSAGE, $playlist->title), TITLE_TAKEN_CODE);
            } else {
                error_log($e->getMessage());
                throw $e;
            }
        } finally {
            $this->dbConnection->autocommit(TRUE);
        }
    }

    public function delete($id)
    {
        try {
            $this->dbConnection->autocommit(FALSE);

            $sql = "DELETE FROM playlist_song WHERE playlist_id = ?;";
            $stmt1 = $this->dbConnection->prepare($sql);
            $stmt1->bind_param("s", $id);
            $stmt1->execute();

            $sql2 = "DELETE FROM playlist WHERE playlist_id = ?;";
            $stmt2 = $this->dbConnection->prepare($sql2);
            $stmt2->bind_param("s", $id);
            $stmt2->execute();

            $this->dbConnection->commit();

            return true;
        } catch (mysqli_sql_exception $e) {
            $this->dbConnection->rollback();
            if ($e->getCode() == 1451) {
                throw new DeletePlaylistInAlbumException($e->getMessage());
            } else {
                error_log($e->getMessage());
                throw $e;
            }
        } finally {
            $this->dbConnection->autocommit(TRUE);
        }
    }

    public function addToPlaylist($playlistId, $songId, User $administrator)
    {
        $sql = "
            INSERT INTO 
                playlist_song (
                    playlist_song_id,
                    song_id,
                    playlist_id,
                    created_date,
                    created_by_id
                )
            VALUES (?, ?, ?, now(), ?);
        ";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $playlistSongId = GUID();
            $stmt->bind_param("ssss",
                $playlistSongId,
                $songId,
                $playlistId,
                $administrator->id
            );
            $stmt->execute();
            $stmt->store_result();

            return $playlistSongId;
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function removeFromPlaylist($playlistId, $songId)
    {
        $sql = "DELETE FROM playlist_song WHERE song_id = ? AND playlist_id = ?;";
        $rowsEffected = 0;
        try {
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("ss", $songId, $playlistId);
            $stmt->execute();
            $stmt->store_result();
            $rowsEffected = $stmt->num_rows;
            return $rowsEffected;
        } catch (\mysqli_sql_exception $e) {
            error_log($e->getMessage());
            throw $e;
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