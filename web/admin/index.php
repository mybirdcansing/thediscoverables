<?php
require_once __DIR__ . '/../lib/objects/AuthCookie.php';
require_once __dir__ . '/../lib/connecters/DataAccess.php';
require_once __dir__ . '/../lib/connecters/UserData.php';

$blankUser = json_encode(new User());
if (AuthCookie::isValid()) {
	$isAuthenticated = 'true';
	$dataConnection = (new DataAccess())->getConnection();
	$userData = new UserData($dataConnection);
	$administrator = json_encode($userData->getByUsername(AuthCookie::getUsername()));
} else {
	$isAuthenticated = 'false';
	$administrator = $blankUser;
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>The Discoverables Admin</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
	<link rel="stylesheet" href="/admin/styles.css">
	<script type="text/javascript">
		let administrator = <?php echo $administrator ?>;
		let isAuthenticated = <?php echo $isAuthenticated ?>;
	</script>
</head>
<body>
	<p>
	The Discoverables Administration
	</p>
	<div id='mainMenue'
		data-bind="visible:isAuthenticated" style="display: none;">
		<div id="administratorName">
			<span data-bind="text: administrator.firstName"></span> <span data-bind="text: administrator.lastName"></span>
		</div>
		<div>MENUE</div>
		<div>
			<button data-bind="click: goToPage.bind($root, 'songs')">
				Songs
			</button>
		</div>
		<div>
			<button data-bind="click: goToPage.bind($root, 'playlists')">
				Playlists
			</button>
		</div>
		<div>
			<button data-bind="click:  goToPage.bind($root, 'albums')">
				Albums
			</button>
		</div>
		<div>
			<button data-bind="click: goToPage.bind($root, 'users')">
				Users
			</button>
		</div>
		<br/>
		<div>
			<button data-bind="click: logout">
				Logout
			</button>
		</div>
	</div>

	<div id='page' data-bind="template: {name: templateName, data: $data}"></div>

	<script type="text/html" id="blank-template">[blank]</script>

	<!-- Users -->

	<script type="text/html" id="login-template">
		<form data-bind="submit: login, with:administrator">
			<ul data-bind="foreach: $root.validationErrors" class="loginErrors">
				<li data-bind='text:$data'></li>
			</ul>
			<label class="for-text-input" for="username">Username</label>
			<input name="username" autocomplete="username" data-bind="value:username"><br>
			<label class="for-text-input" for="password">Password</label>
			<input type="password" name="password" autocomplete="current-password" data-bind="value:password"><br>
			<input type="submit" name="submit" value="submit" class="button" />
		</form>
		<p><a href="javascript://" data-bind='click:openPasswordResetForm'>Trouble siging in?</a></p>
	</script>

	<script type="text/html" id="passwordrecovery-template">
		<form data-bind="submit: requestPasswordReset, with:administrator">
			<p>Enter your username or email to reset your password</p>
			<ul data-bind="foreach: $root.validationErrors" class="loginErrors">
				<li data-bind='text:$data'></li>
			</ul>
			<label class="for-text-input" for="username">Username</label>
			<input name="username" autocomplete="username" data-bind="value:username"><br>
			<label class="for-text-input" for="email">Email</label>
			<input name="email" autocomplete="email" data-bind="value:email"><br>
			<input type="submit" name="submit" value="submit" class="button" />
			<input type="button" data-bind="click:$root.cancelPasswordResetRequest" name="cancel" value="cancel" class="button" />
		</form>
	</script>

	<script type="text/html" id="users-template">
		<div style="text-align: left">
			<button data-bind="click:goToPage.bind($root, 'createuser')" class="button">New User</button>
		</div>
		<table>
		    <thead class='userCells'>
		        <tr>
		        	<th>Username</th>
		        	<th>Email</th>
		        	<th>Name</th>
		        	<th>&nbsp;</th>
		        	<th>&nbsp;</th>
		        	<th>&nbsp;</th>
		        </tr>
		    </thead>
		    <tbody data-bind="foreach: users" class='userCells'>
		        <tr>
		            <td>
		            	<span data-bind="text: username"></span>
		            </td>
		            <td>
		            	<span data-bind="text: email"></span>
		            </td>
		            <td>
		            	<span data-bind="text: firstName"></span>
		            	<span data-bind="text: lastName"></span>
		            </td>
		            <td>
		            	<span class="link"
		            		data-bind='click: $root.goToPage.bind($root, "user", id())'>
							<img class='editButton' src="/src/assets/images/edit-button.svg" />
		            	</span>
		            </td>
		            <td>
		            	<span class="link"
		            		data-bind="click: $root.sendPasswordReset.bind($parent)">
		            		<img src="/src/assets/images/reset-password-button.svg" />
		            	</span>
		            </td>
		            <td>
		            	<span class="link" data-bind="click: $root.deleteUser.bind($parent)">
		            		<img src="/src/assets/images/delete-button.svg" />
		            	</span>
		            </td>
		        </tr>
		    </tbody>
		</table>
	</script>

	<script type="text/html" id="createuser-template">
		<form data-bind="submit: createUser, with: userToUpdate" autocomplete="off">
			<ul data-bind="foreach: $parent.validationErrors" class="loginErrors">
				<li data-bind='text:$data'></li>
			</ul>
			<label class="for-text-input" for="firstName">First Name</label>
			<input name="firstName" placeholder="First Name" data-bind="value: firstName"/><br>
			<label class="for-text-input" for="lastName">Last Name</label>
			<input name="lastName" placeholder="Last Name" data-bind="value: lastName" /><br>
			<label class="for-text-input" for="username">Username</label>
			<input name="username" placeholder="Username" autocomplete="username" data-bind="value: username"/><br>
			<label class="for-text-input" for="email">Email</label>
			<input name="email" placeholder="Email" autocomplete="email" data-bind="value: email" /><br>
			<label class="for-text-input" for="password">Password</label>
			<input name="password" type="password" placeholder="Password" autocomplete="new-password"  data-bind="value: password" /><br>
			<label class="for-text-input" for="confirmPassword">Confirm Password</label>
			<input name="confirmPassword" type="password" placeholder="Confirm Password" autocomplete="new-password" data-bind="value: confirmPassword" /><br>
			<input type="submit" name="submit" value="submit" class="button" />
			<input type="button" data-bind="click:$root.goToPage.bind($root, 'users')" name="cancel" value="cancel" class="button" />
		</form>
	</script>

	<script type="text/html" id="user-template">
		<form data-bind="submit: updateUser, with: userToUpdate">
			<ul data-bind="foreach: $parent.validationErrors" class="loginErrors">
				<li data-bind='text:$data'></li>
			</ul>
			<label class="for-text-input" for="firstName">First Name</label>
			<input name="firstName" data-bind="value: firstName" /><br>
			<label class="for-text-input" for="lastName">Last Name</label>
			<input name="lastName" data-bind="value: lastName" /><br>
			<label class="for-text-input" for="username">Username</label>
			<input name="username" data-bind="value: username" /><br>
			<label class="for-text-input" for="email">Email</label>
			<input name="email" data-bind="value: email" /><br>
			<input type="submit" name="submit" value="submit" class="button" />
			<input type="button" data-bind="click:$root.goToPage.bind($root, 'users')" name="cancel" value="cancel" class="button" />
		</form>
	</script>

	<!-- Songs -->

	<script type="text/html" id="songs-template">
		<div style="text-align: left">
			<button data-bind="click:goToPage.bind($root, 'createsong')" class="button">New Song</button>
		</div>
		<table>
		    <thead class='userCells'>
		        <tr>
		        	<th>Title</th>
		        	<th>&nbsp;</th>
		        	<th>&nbsp;</th>
		        </tr>
		    </thead>
		    <tbody data-bind="foreach: songs" class='userCells'>
		        <tr>
		            <td>
		            	<span data-bind="text: title"></span>
		            </td>
		            <td>
		            	<span class="link"
		            		data-bind="click: $root.goToPage.bind($parent, 'song', id())">
							<img src="/src/assets/images/edit-button.svg" src="/src/assets/images/edit-button.svg" />
		            	</span>
		            </td>
		            <td>
		            	<span class="link" data-bind="click: $root.deleteSong.bind($parent)">
		            		<img src="/src/assets/images/delete-button.svg" />
		            	</span>
		            </td>
		        </tr>
		    </tbody>
		</table>
	</script>

	<script type="text/html" id="createsong-template">
		<form data-bind="submit:createSong, with:songToUpdate">
			<ul data-bind="foreach: $parent.validationErrors" class="loginErrors">
				<li data-bind='text:$data'></li>
			</ul>
			<label class="for-text-input" for="title">Title</label>
			<input name="title" data-bind="value: title" /><br>
			<label class="for-text-input" for="description">Description</label>
			<input name="description" data-bind="value: description" /><br>
			<label class="for-text-input" for="fileInput">Upload</label>
			<input type="file" data-bind="file: {data: fileInput, name: filename, reader: reader}"><br>

			<input type="submit" name="submit" value="submit" class="button" />
			<input type="button" data-bind="click:$root.goToPage.bind($root, 'songs')" name="cancel" value="cancel" class="button" />
		</form>
	</script>

	<script type="text/html" id="song-template">
		<form data-bind="submit:updateSong, with:songToUpdate">
			<ul data-bind="foreach: $parent.validationErrors" class="loginErrors">
				<li data-bind='text:$data'></li>
			</ul>
			<input type="hidden" name="id" data-bind="value: id" /><br>
			<label class="for-text-input" for="title">Title</label>
			<input name="title" data-bind="value: title" /><br>
			<label class="for-text-input" for="description">Description</label>
			<input name="description" data-bind="value: description" /><br>
			<label class="for-text-input" for="fileInput">Upload</label>
			<input type="file" data-bind="file: {data: fileInput, name: filename, reader: reader}"><br>
			<input type="submit" name="submit" value="submit" class="button" />
			<input type="button" data-bind="click:$root.goToPage.bind($root, 'songs')" name="cancel" value="cancel" class="button" />
		</form>
	</script>

	<!-- Playlists -->

	<script type="text/html" id="playlists-template">
		<div style="text-align: left">
			<button data-bind="click:goToPage.bind($root, 'createplaylist')" class="button">New Playlist</button>
		</div>
		<table>
		    <thead class='userCells'>
		        <tr>
		        	<th>Title</th>
		        	<th>&nbsp;</th>
		        	<th>&nbsp;</th>
		        </tr>
		    </thead>
		    <tbody data-bind="foreach: playlists" class='userCells'>
		        <tr>
		            <td>
		            	<span data-bind="text: title"></span>
		            </td>
		            <td>
		            	<span class="link"
		            		data-bind="click: $root.goToPage.bind($root, 'playlist', id())">
							<img src="/src/assets/images/edit-button.svg" src="/src/assets/images/edit-button.svg" />
		            	</span>
		            </td>
		            <td>
		            	<span class="link" data-bind="click: $root.deletePlaylist.bind($parent)">
		            		<img src="/src/assets/images/delete-button.svg" />
		            	</span>
		            </td>
		        </tr>
		    </tbody>
		</table>
	</script>

	<script type="text/html" id="createplaylist-template">
		<form data-bind="submit:createPlaylist, with: $root.playlistToUpdate">
			<ul data-bind="foreach: $root.validationErrors" class="loginErrors">
				<li data-bind='text:$data'></li>
			</ul>
			<label class="for-text-input" for="title">Title</label>
			<input name="title" data-bind="value: title" /><br />
			<label class="for-text-input" for="description">Description</label>
			<input name="description" data-bind="value: description" /><br />
			<label class="for-text-input" for="songs">Songs</label><br />
			<div data-bind="foreach: $root.songs" style="text-align: left;margin-left: 160px;">
			    <div>
			        <input
			        	type="checkbox"
			        	name="songs"
			        	data-bind="
		        			checkedValue: id,
		        			attr: { id: id },
			        		click:$root.togglePlaylistSongAssociation.bind($root, $data)
			        		">
			        <label data-bind="text: title, attr: { for: id }"></label>
			    </div>
			</div><br>
			<input type="submit" name="submit" value="submit" class="button" />
			<input type="button" data-bind="click:$root.goToPage.bind($root, 'playlists')" name="cancel" value="cancel" class="button" />
		</form>
	</script>

	<script type="text/html" id="playlist-template">
		<form data-bind="submit: $root.updatePlaylist, with: $root.playlistToUpdate">
			<ul data-bind="foreach: $root.validationErrors" class="loginErrors">
				<li data-bind='text:$data'></li>
			</ul>
			<input name="id" type="hidden" data-bind="value: id" />
			<label class="for-text-input" for="title">Title</label>
			<input name="title" data-bind="value: title" /><br>
			<label class="for-text-input" for="description">Description</label>
			<input name="description" data-bind="value: description" /><br>
			<label class="for-text-input" for="songs">Songs</label><br />
			<div data-bind="foreach: $root.songs" style="text-align: left;margin-left: 160px;">
			    <div>
			        <input
			        	type="checkbox"
			        	name="songs"
			        	data-bind="
			        		checkedValue: id,
			        		attr: {
			        			id: id,
			        			checked:$parent.songs().find(pls => id() == pls.id())
			        		},
			        		click:$root.togglePlaylistSongAssociation.bind($root, $parent, $data)
			        		">
			        <label data-bind="text: title, attr: { for: id }"></label>
			    </div>
			</div><br>
			<input type="submit" name="submit" value="submit" class="button" />
			<input type="button" data-bind="click:$root.goToPage.bind($root, 'playlists')" name="cancel" value="cancel" class="button" />
		</form>
	</script>


	<!-- Album -->

	<script type="text/html" id="albums-template">
		<div style="text-align: left">
			<button data-bind="click: goToPage.bind($parent, 'createalbum')" class="button">New Album</button>
		</div>
		<table>
		    <thead class='userCells'>
		        <tr>
		        	<th>Title</th>
		        	<th>&nbsp;</th>
		        	<th>&nbsp;</th>
		        </tr>
		    </thead>
		    <tbody data-bind="foreach: albums" class='userCells'>
		        <tr>
		            <td>
		            	<span data-bind="text: title"></span>
		            </td>
		            <td>
		            	<span class="link"
		            		data-bind="click: $root.goToPage.bind($parent, 'album', id())">
							<img src="/src/assets/images/edit-button.svg" src="/src/assets/images/edit-button.svg" />
		            	</span>
		            </td>
		            <td>
		            	<span class="link" data-bind="click: $root.deleteAlbum.bind($parent)">
		            		<img src="/src/assets/images/delete-button.svg" />
		            	</span>
		            </td>
		        </tr>
		    </tbody>
		</table>
	</script>

	<script type="text/html" id="createalbum-template">
		<form data-bind="submit: createAlbum, with: albumToUpdate">
			<ul data-bind="foreach: $root.validationErrors" class="loginErrors">
				<li data-bind='text:$data'></li>
			</ul>
			<label class="for-text-input" for="title">Title</label>
			<input name="title" data-bind="value: title" /><br />
			<label class="for-text-input" for="description">Description</label>
			<input name="description"  data-bind="value: description" /><br />
			<label class="for-text-input">Playlist</label><br />
			<div data-bind="foreach: $root.playlists" style="text-align: left;margin-left: 160px;">
			    <div>
			        <input type="radio" name="playlistId" data-bind="checkedValue: id, attr: { id: id }">
			        <label data-bind="
			        	text: title,
			        	attr: { for: id },
	        			click: $root.setPlaylistAlbumAssociation.bind($root, $parent, $data)"></label>
			    </div>
			</div><br>
			<input type="submit" name="submit" value="submit" class="button" />
			<input type="button" data-bind="click:$root.goToPage.bind($root, 'albums')" name="cancel" value="cancel" class="button" />
		</form>
	</script>

	<script type="text/html" id="album-template">
		<form data-bind="submit: $root.updateAlbum, with: albumToUpdate">
			<ul data-bind="foreach: $root.validationErrors" class="loginErrors">
				<li data-bind='text:$data'></li>
			</ul>
			<input name="id" type="hidden" data-bind="value: id" />
			<label class="for-text-input" for="title">Title</label>
			<input name="title" data-bind="value: title" /><br>
			<label class="for-text-input" for="description">Description</label>
			<input name="description" data-bind="value: description" /><br>
			<label class="for-text-input">Playlist</label><br />

			<div data-bind="foreach: $root.playlists" style="text-align: left;margin-left: 160px;">
			    <div>
			        <input
			        	type="radio"
			        	name="playlistId"
			        	data-bind="
			        		checkedValue: id,
			        		attr: {
			        			id: id,
			        			checked:$root.isAlbumPlaylist($parent, $data),
			        			click: $root.setPlaylistAlbumAssociation.bind($root, $parent, $data)
			        		}
			        		">
			        <label data-bind="text: title, attr: { for: id }"></label>
			    </div>
			</div><br>

			<input type="submit" name="submit" value="submit" class="button" />
			<input type="button" data-bind="click:$root.goToPage.bind($root, 'albums')" name="cancel" value="cancel" class="button" />
		</form>
	</script>
</body>
<!-- <script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js'></script> -->
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.js'></script>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js'></script>
<!-- <script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.0/knockout-min.js'></script> -->
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/knockout/3.2.0/knockout-debug.js'></script>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/knockout.mapping/2.4.1/knockout.mapping.min.js'></script>
<script type='text/javascript' src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

<script type='text/javascript' src='/admin/src/bindings/knockout-file-bind.js'></script>
<script type='text/javascript' src='/admin/src/Router.js'></script>

<script type='text/javascript' src='/admin/src/connectors/ConnectorBase.js'></script>
<script type='text/javascript' src='/admin/src/connectors/UserConnector.js'></script>
<script type='text/javascript' src='/admin/src/connectors/SongConnector.js'></script>
<script type='text/javascript' src='/admin/src/connectors/PlaylistConnector.js'></script>
<script type='text/javascript' src='/admin/src/connectors/AlbumConnector.js'></script>

<script type='text/javascript' src='/admin/src/SongViewModel.js'></script>
<script type='text/javascript' src='/admin/src/UserViewModel.js'></script>

<script type='text/javascript' src='/admin/src/AdminViewModel.js'></script>
<script type='text/javascript' src='/admin/src/app.js'></script>
<script type='text/javascript' src='/admin/dist/main.js'></script>
</html>
