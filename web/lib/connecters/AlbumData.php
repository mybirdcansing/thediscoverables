<?php
require_once __dir__ . '/../objects/Album.php';
require_once __dir__ . '/../objects/User.php';
require_once __dir__ . '/PlaylistData.php';
require_once __dir__ . '/../objects/DuplicateTitleException.php';

class AlbumData
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
				album_id,
				title,
				description,
                playlist_id
		  	FROM
		  		album;";

        try {
			$stmt = $this->dbConnection->query($sql);
            $albums = [];
			while ($row = $stmt->fetch_assoc()) {
			    $albums[] = $this->rowToAlbum($row);
			}
            return $albums;
        } catch (\mysqli_sql_exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function find($id)
    {
        $sql = "
            SELECT
                album_id,
                title,
                description,
                playlist_id
		  	FROM
		  		album
		  	WHERE album_id = ?;
        ";

		$stmt = $this->dbConnection->prepare($sql);
		$stmt->bind_param("s", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows == 1) {
		    $row = $result->fetch_assoc();
		    $album = $this->rowToAlbum($row);
            $playlistData = new PlaylistData($this->dbConnection);
            $album->playlist = $playlistData->find($row["playlist_id"]);
		    return $album;
		} else {
			return 0;
		}
    }

    public function insert(Album $album, User $administrator)
    {

        $sql = "
			INSERT INTO
				album (
                    album_id,
                    title,
                    description,
                    playlist_id,
                    modified_date,
                    modified_by_id,
                    created_date,
                    created_by_id
                )
			VALUES (?, ?, ?, ?, now(), ?, now(), ?);
        ";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $albumId = GUID();
            $stmt->bind_param("ssssss",
                $albumId,
				$album->title,
				$album->description,
				$album->playlist->id,
                $administrator->id,
                $administrator->id
            );
            $stmt->execute();
            $stmt->store_result();
            return $albumId;
        } catch (mysqli_sql_exception $e) {
           $mysqliErrorMessage = $e->getMessage();
           if (strpos($mysqliErrorMessage, 'title') !== false) {
                throw new DuplicateTitleException(
                    sprintf(TITLE_TAKEN_MESSAGE, $album->title), TITLE_TAKEN_CODE);
            } else {
                error_log($e->getMessage());
                throw $e;
            }
        }
    }

    public function update(Album $album, User $administrator)
    {
        $sql = "
            UPDATE album
                SET
                    title = ?,
                    description = ?,
                    playlist_id = ?,
                    modified_date = now(),
                    modified_by_id = ?
                WHERE album_id = ?;
        ";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("sssss",
                $album->title,
                $album->description,
                $album->playlist->id,
                $administrator->id,
                $album->id
            );
            $stmt->execute();
            $stmt->store_result();
            return $album->id;
        } catch (mysqli_sql_exception $e) {
           $mysqliErrorMessage = $e->getMessage();
           if (strpos($mysqliErrorMessage, 'title') !== false) {
                throw new DuplicateTitleException(
                    sprintf(TITLE_TAKEN_MESSAGE, $album->title), TITLE_TAKEN_CODE);
            } else {
                error_log($e->getMessage());
                throw $e;
            }
        }
    }

    public function delete($albumId)
    {
        $sql = "DELETE FROM album WHERE album_id = ?;";

        try {
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("s", $albumId);
            $stmt->execute();
            $stmt->store_result();
            $rowsEffected = $stmt->num_rows;
            return $rowsEffected;
        } catch (\mysqli_sql_exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

	public function rowToAlbum($row)
    {
	    $album = new Album();
	    $album->id = $row["album_id"];
	    $album->title = $row["title"];
	    $album->description = $row["description"];
	    $album->playlistId  = $row["playlist_id"];
	    return $album;
	}

}
