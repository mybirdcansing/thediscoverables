<?php
declare(strict_types=1);
require_once __dir__ . '/SongGateway.php';
require_once __dir__ . '/PlaylistGateway.php';
require_once __dir__ . '/AlbumGateway.php';
require_once __dir__ . '/UserGateway.php';
require_once __dir__ . '/PasswordResetTokenGateway.php';

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class TestBase extends TestCase
{
	private $_cookieJar;
	private $_httpClient;
    private $_userGateway;
    private $_songGateway;
    private $_playlistGateway;
    private $_albumGateway;
    private $_passwordResetTokenGateway;

    function authenticateUser($username, $password, $expectedStatusCode = 200)
    {
		$client = $this->getHandlerClient();
        $response = $client->post('authenticate.php', [
        	'json' => [
                'username' => $username,
                'password' => $password
            ]
        ]);
        $this->assertEquals($response->getStatusCode(), $expectedStatusCode);
        $json = json_decode($response->getBody()->getContents());
        return $json;
    }

    function validateErrorMessages($json, $expectedErrorMessages)
    {
        $this->assertTrue(isset($json->errorMessages));
        $actualErrorMessages = (array)$json->errorMessages;
        foreach ($expectedErrorMessages as $expectedErrorCode => $expectedErrorMessage) {
            $this->assertArrayHasKey($expectedErrorCode, $actualErrorMessages);
            $this->assertEquals($expectedErrorMessage, $actualErrorMessages[$expectedErrorCode]
            );
        }
    }

    // factory methods
    function getHandlerClient() 
    {
    	if (!$this->_httpClient) {
    		$this->_httpClient = new Client([
	            'base_uri' => ((new Configuration())->getSettings())->host->HANDLER_WEB_ROOT,
	            'timeout'  => 4.0,
	            'cookies' => true,
	            'http_errors' => false
	        ]);
    	}
    	return $this->_httpClient;
    }

    function getUserGateway() 
    {
    	if (!$this->_userGateway) {
    		$this->_userGateway = new UserGateway($this->getHandlerClient(), $this->getCookieJar());
    	}
    	return $this->_userGateway;
    }

    function getPasswordResetTokenGateway() 
    {
    	if (!$this->_passwordResetTokenGateway) {
    		$this->_passwordResetTokenGateway = new PasswordResetTokenGateway($this->getHandlerClient(), $this->getCookieJar());
    	}
    	return $this->_passwordResetTokenGateway;
    }

    function getSongGateway() 
    {
    	if (!$this->_songGateway) {
    		$this->_songGateway = new SongGateway($this->getHandlerClient(), $this->getCookieJar());
    	}
    	return $this->_songGateway;
    }

    function getPlaylistGateway() 
    {
    	if (!$this->_playlistGateway) {
    		$this->_playlistGateway = new PlaylistGateway($this->getHandlerClient(), $this->getCookieJar());
    	}
    	return $this->_playlistGateway;
    }

    function getAlbumGateway() 
    {
    	if (!$this->_albumGateway) {
    		$this->_albumGateway = new AlbumGateway($this->getHandlerClient(), $this->getCookieJar());
    	}
    	return $this->_albumGateway;
    }

    function getCookieJar() 
    {
    	if (!$this->_cookieJar) {
    		$settings = ((new Configuration())->getSettings())->test;
	        // login as the test user (see sql/schema.sql)
	        $json = $this->authenticateUser($settings->TEST_USERNAME, $settings->TEST_PASSWORD);
	        // set the cookie for future requests
	        $this->_cookieJar = CookieJar::fromArray([
	            'login' => $json->cookie
	        ], $settings->TEST_DOMAIN);
    	}
    	return $this->_cookieJar;
    }

    // fixtures 
    public function fleshedOutSong()
    {
        $song = new Song();
        $song->title = Guid::create();
        $song->filename = 'test__' . $song->title . '.mp3';
        $song->description = 'This is a test song!';
        $song->fileInput = "data:audio/mp3;base64,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";
        return $song;
    }

    public function fleshedOutPlaylist()
    {
        $playlist = new Playlist();
        $playlist->title = Guid::create();
        $playlist->description = 'This is a good playlist!';
        return $playlist;
    }

    public function fleshedOutAlbum()
    {
        $album = new Album();
        $album->title = Guid::create();
        $album->description = 'This is a good album!';
        $playlist = $this->getPlaylistGateway()->createPlaylist($this->fleshedOutPlaylist());
        $album->playlist = $playlist->id;
        return $album;
    }

    public function fleshedOutUser()
    {
        $user = new User();
        $user->username = $this->uniqueUsername();
        $user->firstName = 'Ron';
        $user->lastName = 'Snow';
        $user->email = $this->uniqueEmail();
        $user->password = 'dummypassword';
        $user->statusId = ACTIVE_USER_STATUS_ID;
        return $user;
    }
    
    public function uniqueUsername($prefix = 0)
    {
        if ($prefix) {
            return $prefix . '-test-user-' . Guid::create();
        }
        return 'test-user-' . Guid::create();
    }

    public function uniqueEmail()
    {
        return Guid::create() . '@test.com';
    }

}
