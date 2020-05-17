import StatusEnum from '../src/store/StatusEnum';
import catalogData from './mocks/catalog.mock.json';
import blankCatalogData from './mocks/blank_catalog.mock.json';
import store from '../src/store/store';
import regeneratorRuntime from 'regenerator-runtime';
import axios from 'axios';
import MockAdapter from "axios-mock-adapter";

const state = store.state.catalog;
const getters = store.getters;

const mock = new MockAdapter(axios);
mock.onGet('/lib/handlers/catalog').reply(200, catalogData);
mock.onPost('/lib/handlers/song/create').reply(201, {
    songCreated: true, 
    songId: '12345'
});
mock.onPost('/lib/handlers/song/12345').reply(200, {
    songUpdated: true, 
    songId: '12345'
});
mock.onPost('/lib/handlers/song/12345/delete').reply(200, {});


describe('actions', () => {
    beforeEach(async () => {
        await store.dispatch('fetchCatalog');
    });    
    afterEach(() => {
        store.commit('SET_CATALOG', blankCatalogData);
        store.commit('SET_CATALOG_STATE', StatusEnum.INIT);
    }); 

    it('fetchCatalog', async () => {
        expect(getters.catalogState).toEqual(StatusEnum.LOADED);
        stateToState(state, catalogData);     
    });

    it('createItem', async () => {
        const song = dummySong(null);
        const payload = { data: song, handler: 'song' };
        const response = await store.dispatch('createItem', payload);
        expect(response.songCreated).toBeTruthy();
        song.id = response.songId;
        songEqualToSong(song, getters.getSongById(song.id));
    });

    it('updateItem', async () => {
        const song = dummySong(null);
        const payload = { data: song, handler: 'song' };
        const createResponse = await store.dispatch('createItem', payload);
        expect(createResponse.songCreated).toBeTruthy();
        const updatedSong = dummySong(createResponse.songId);
        updatedSong.title = 'Updated song title';
        payload.data = updatedSong;
        const updateResponse = await store.dispatch('updateItem', payload);
        expect(updateResponse.songUpdated).toBeTruthy();
        songEqualToSong(updatedSong, getters.getSongById(song.id));
    });

    it('deleteItem', async () => {
        const song = dummySong(null);
        const payload = { data: song, handler: 'song' };
        const createResponse = await store.dispatch('createItem', payload);
        expect(createResponse.songCreated).toBeTruthy();
        payload.id = createResponse.songId;
        await store.dispatch('deleteItem', payload);
        const deletedSongById = getters.getSongById(createResponse.songId);
        expect(deletedSongById).toBeUndefined();
    });    
});

describe('getters', () => {
    before(() => {
        store.commit('SET_CATALOG', catalogData);
        expect(getters.catalogState).toEqual(StatusEnum.LOADED);
    });
    after(() => {
        store.commit('SET_CATALOG', blankCatalogData);
        store.commit('SET_CATALOG_STATE', StatusEnum.INIT);
    });
    it('catalogState', () => {
        expect(getters.catalogState).toEqual(StatusEnum.LOADED);
    });

    it('songSet', () => {
        const songSet = getters.songSet;
        expect(songSet.length).toEqual(state.songList.length);
        state.songList.forEach(id => {
            const song = state.songs[id];
            const songSetSong = songSet.find(s => s.id === id);            
            songEqualToSong(song, songSetSong);
        });
    });

    it('getSongById', () => {
        state.songList.forEach(id => {
            const song = state.songs[id];
            const songById = getters.getSongById(id);
            songEqualToSong(song, songById);
        });
    });

    it('getSongByIdNoMatch', () => {
        const songById = getters.getSongById('');
        expect(songById).toBeUndefined();        
    });

    it('playlistSet', () => {
        const playlistSet = getters.playlistSet;
        expect(playlistSet.length).toEqual(state.playlistList.length);        
        state.playlistList.forEach(id => {
            const playlist = state.playlists[id];
            const playlistSetPlaylist = playlistSet.find(pl => pl.id === id);
            playlistEqualToPlaylist(playlist, playlistSetPlaylist);
        });
    });

    it('playlistById', () => {
        state.playlistList.forEach(id => {
            const playlist = state.playlists[id];
            const playlistById = getters.getPlaylistById(id);
            playlistEqualToPlaylist(playlist, playlistById);
        });
    });

    it('playlistByIdNoMatch', () => {
        const playlistById = getters.getPlaylistById('');
        expect(playlistById).toBeUndefined();        
    });

    it('getPlaylistSongs', () => {
        state.playlistList.forEach(id => {
            const playlist = state.playlists[id];
            const playlistSongs = getters.getPlaylistSongs(playlist);
            expect(playlist.songs.length).toEqual(playlistSongs.length);
            playlist.songs.forEach(songId => {
                const playlistSong = playlistSongs.find(playlistSong => playlistSong.id === songId);
                const song = getters.getSongById(songId);
                songEqualToSong(song, playlistSong);
            });
        });
    });

    it('albumSet', () => {
        const albumSet = getters.albumSet;
        expect(albumSet.length).toEqual(state.albumList.length);        
        state.albumList.forEach(id => {
            const album = state.albums[id];
            const albumSetAlbum = albumSet.find(pl => pl.id === id);
            albumEqualToAlbum(album, albumSetAlbum);
        });
    });

    it('albumById', () => {
        state.albumList.forEach(id => {
            const album = state.albums[id];
            const albumById = getters.getAlbumById(id);
            albumEqualToAlbum(album, albumById);
        });
    });

    it('getSongByIdNoMatch', () => {
        const albumById = getters.getAlbumById('');
        expect(albumById).toBeUndefined();        
    });

    it('getSongsWithAlbums', () => {
        const songsWithAlbums = getters.getSongsWithAlbums;
        songsWithAlbums.forEach(song => {
            if (song.album) {
                albumEqualToAlbum(getters.getSongAlbum(song), song.album)
            }
        });
    });
});


describe('mutations', () => {
    before(() => {
        store.commit('SET_CATALOG', catalogData);
    });
    
    after(() => {
        store.commit('SET_CATALOG', blankCatalogData);
        store.commit('SET_CATALOG_STATE', StatusEnum.INIT);
    });

    it('SET_CATALOG', () => {    
        expect(state.catalogState).toEqual(StatusEnum.LOADED);
        stateToState(state, catalogData); 
    });

    it('CREATE_ITEM', () => {
        const id = '1234';
        const song = dummySong(id);
        const options = {
            category: "songs",
            categoryList: "songList",
            data: song
        };
        store.commit('CREATE_ITEM', options);
        const songById = getters.getSongById(id);
        songEqualToSong(song, songById);
    });

    it('UPDATE_ITEM', () => {
        const id = '2345';
        const song = dummySong(id);
        const options = {
            category: "songs",
            categoryList: "songList",
            data: song
        };
        store.commit('CREATE_ITEM', options);

        const updatedSong = dummySong(id);
        updatedSong.title = 'new title';
        updatedSong.description = 'new description';

        options.data = updatedSong;

        store.commit('UPDATE_ITEM', options);        
        const updatedSongById = getters.getSongById(id);
        songEqualToSong(updatedSong, updatedSongById);        
    });

    it('DELETE_ITEM', () => {
        const id = '3456';
        const song = dummySong(id);
        const options = {
            category: "songs",
            categoryList: "songList",
            data: song
        };
        store.commit('CREATE_ITEM', options);        
        const songById = getters.getSongById(id);
        songEqualToSong(song, songById);

        const updatedSong = dummySong(id);
        updatedSong.title = 'new title';
        updatedSong.description = 'new description';
        options.data = updatedSong;

        store.commit('DELETE_ITEM', {
            category: "songs",
            categoryList: "songList",
            id: id
        });        
        const deletedSongById = getters.getSongById(id);        
        expect(deletedSongById).toBeUndefined(); 
    });
});

function songEqualToSong(songA, songB) {
    expect(songA.title).toEqual(songB.title);
    expect(songA.filename).toEqual(songB.filename);
    expect(songA.duration).toEqual(songB.duration);
    expect(songA.description).toEqual(songB.description);
}

function playlistEqualToPlaylist(playlistA, playlistB) {
    expect(playlistA.title).toEqual(playlistB.title);
    expect(playlistA.description).toEqual(playlistB.description);
    expect(JSON.stringify(playlistA.songs)).toEqual(JSON.stringify(playlistB.songs));
}

function albumEqualToAlbum(albumA, albumB) {
    expect(albumA.title).toEqual(albumB.title);
    expect(albumA.description).toEqual(albumB.description);
    expect(albumA.playlist).toEqual(albumB.playlist);
    expect(albumA.artworkFilename).toEqual(albumB.artworkFilename);
    expect(albumA.publishDate).toEqual(albumB.publishDate);
}

function stateToState(stateA, stateB) {
    expect(JSON.stringify(stateA.songs)).toEqual(JSON.stringify(stateB.songs));
    expect(JSON.stringify(stateA.songList)).toEqual(JSON.stringify(stateB.songList));
    expect(JSON.stringify(stateA.playlists)).toEqual(JSON.stringify(stateB.playlists));
    expect(JSON.stringify(stateA.playlistList)).toEqual(JSON.stringify(stateB.playlistList));
    expect(JSON.stringify(stateA.albums)).toEqual(JSON.stringify(stateB.albums));
    expect(JSON.stringify(stateA.albumList)).toEqual(JSON.stringify(stateB.albumList));   
}

function dummySong(id) {
    return { 
        id: id,
        title: 'dummy song',
        filename: 'dummy_song.mp3',
        description: 'a dummy song ',
        fileInput: null,
        duration: 199.99
    };
}

function uuidv4() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
      return v.toString(16);
    });
  }
