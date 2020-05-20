import 'regenerator-runtime';
import axios from 'axios';
import MockAdapter from "axios-mock-adapter";
import StatusEnum from '../../src/store/StatusEnum';
import store from '../../src/store/store';
import catalogData from '../mocks/catalog.mock.json';
import blankCatalogData from '../mocks/blank_catalog.mock.json';
import usersData from '../mocks/users.mock.json';
import blankUsersData from '../mocks/blank_users.mock.json';

const state = store.state.catalog;
const getters = store.getters;

const goodAuthorizeResponse = {
    authorized: true,
    message: "You have permission to be here",
    username: 'beno'
};

const goodAuthenticateResponse = {
    authenticated: true,
	user: usersData.find(u => u.username === 'adam'), 
    cookie: null
};

const goodLogoutResponse = {
    authorized: false, 
    message: "You have successfully logged out."
};

const handlerBasePath = '/lib/handlers';

const mockAdapter = new MockAdapter(axios);
mockAdapter.onGet(`${handlerBasePath}/catalog`).reply(200, catalogData);
mockAdapter.onPost(`${handlerBasePath}/song/create`).reply(201, {
    songCreated: true, 
    songId: '12345'
});
mockAdapter.onPost(`${handlerBasePath}/song/12345`).reply(200, {
    songUpdated: true, 
    songId: '12345'
});
mockAdapter.onPost(`${handlerBasePath}/song/12345/delete`).reply(200, {});
mockAdapter.onPost(`${handlerBasePath}/song/23456/delete`).reply(404, {});

mockAdapter.onGet(`${handlerBasePath}/user`).reply(200, usersData);
mockAdapter.onPost(`${handlerBasePath}/user/create`).reply(201, {
    userCreated: true, 
    userId: '12345'
});
mockAdapter.onPost(`${handlerBasePath}/user/12345`).reply(200, {
    userUpdated: true, 
    userId: '12345'
});
mockAdapter.onPost(`${handlerBasePath}/user/12345/delete`).reply(200, {});
mockAdapter.onPost(`${handlerBasePath}/user/23456/delete`).reply(404, {});

mockAdapter.onGet(`${handlerBasePath}/authorize.php`).reply(200, goodAuthorizeResponse);
mockAdapter.onPost(`${handlerBasePath}/authenticate.php`).reply(200, goodAuthenticateResponse);
mockAdapter.onPost(`${handlerBasePath}/logout.php`).reply(200, goodLogoutResponse);

const createUser = async (payload) => {
    const response = await store.dispatch('manage/createItem', payload);
    expect(response.userCreated).toBeTruthy();
    return response;
}

describe('actions', () => {
    describe('manager', () => {
        beforeEach(async () => {
            await store.dispatch('manage/fetchData');
        });

        afterEach(() => {
            store.commit('manage/SET_USERS', blankUsersData);
            store.commit('manage/SET_MANAGE_STATE', StatusEnum.INIT);
        });

        it('fetchData', () => {
            expect(getters['manage/manageState']).toBe(StatusEnum.LOADED);
            expect(Object.values(store.state.manage.users)).toEqual(usersData);
        });

        it('createItem', async () => {
            const user = dummyUser(null);
            const payload = { data: user, handler: 'user' };
            const response = await createUser(payload);
            user.id = response.userId;
            expect(user).toEqual(getters['manage/getUserById'](user.id));
        });
                  
        it('updateItem', async () => {
            const user = dummyUser(null);
            const payload = { data: user, handler: 'user' };
            const createResponse = await createUser(payload);
            const updatedUser = dummyUser(createResponse.userId);
            updatedUser.title = 'Updated user title';
            payload.data = updatedUser;
            const updateResponse = await store.dispatch('manage/updateItem', payload);
            expect(updateResponse.userUpdated).toBeTruthy();
            expect(updatedUser).toEqual(getters['manage/getUserById'](user.id));
        });
    
        it('deleteItem', async () => {
            const user = dummyUser(null);
            const payload = { data: user, handler: 'user' };
            const createResponse = await createUser(payload);
            payload.id = createResponse.userId;
            await store.dispatch('manage/deleteItem', payload);
            const deletedUserById = getters['manage/getUserById'](createResponse.userId);
            expect(deletedUserById).toBeUndefined();
        });

        it('authorize', async () => {
            const response = await store.dispatch('manage/authorize');
            expect(response.authorized).toBeTruthy();
        });

        it('login', async () => {
            const response = await store.dispatch('manage/login', {
                username:'adam', password:'12345'
            });
            expect(response.authenticated).toBeTruthy();
        });

        it('logout', async () => {
            const response = await store.dispatch('manage/logout');
            expect(response.authorized).toBeFalsy();
        });        
    });

    describe('catalog', () => { 
        beforeEach(async () => {
            await store.dispatch('fetchCatalog');
        });
  
        afterEach(() => {
            store.commit('SET_CATALOG', blankCatalogData);
            store.commit('SET_CATALOG_STATE', StatusEnum.INIT);
        });

        it('fetchCatalog', () => {
            expect(getters.catalogState).toBe(StatusEnum.LOADED);
            stateToState(state, catalogData);     
        });
    
        it('createItem', async () => {
            const song = dummySong(null);
            const payload = { data: song, handler: 'song' };
            const response = await store.dispatch('createItem', payload);
            expect(response.songCreated).toBeTruthy();
            song.id = response.songId;
            expect(song).toEqual(getters.getSongById(song.id));
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
            expect(updatedSong).toEqual(getters.getSongById(song.id));
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
    
        // it('deleteItemNotFound', async () => {
        //     const payload = { id: '23456', handler: 'song' };
        //     let err = false;
        //     try {
        //         await store.dispatch('deleteItem', payload);
        //     } catch (e) {
        //         err = true;
        //         expect(e.message).toEqual('Request failed with status code 404');
        //     } finally {
        //         expect(err).toBeTruthy();
        //     }
        // });  

    });    

});

describe('getters', () => {
    describe('manager', () => {
        beforeEach(async () => {
            await store.dispatch('manage/fetchData');
        });

        afterEach(() => {
            store.commit('manage/SET_USERS', blankUsersData);
            store.commit('manage/SET_MANAGE_STATE', StatusEnum.INIT);
        });

        it('manageState', () => {      
            expect(getters['manage/manageState']).toBe(StatusEnum.LOADED);     
        });

        it('userSet', () => {
            const userSet = getters['manage/userSet'];
            expect(userSet).toEqual(usersData);
        });
    
        it('getUserById', () => {
            state.songList.forEach(id => {
                const song = state.songs[id];
                const songById = getters.getSongById(id);
                expect(song).toEqual(songById);
            });
        });

        it('getManager', async () => {
            await store.dispatch('manage/authorize');
            let manager = getters['manage/getManager'];
            expect(manager.username).toEqual(goodAuthorizeResponse.username);
            
            await store.dispatch('manage/login', {
                username:'adam', password:'12345'
            });
            manager = getters['manage/getManager'];
            expect(manager.username).toEqual('adam');

            await store.dispatch('manage/logout');
            manager = getters['manage/getManager'];
            expect(manager).toBeUndefined();
            
        });
    });
    describe('catalog', () => {
        before(() => {
            store.commit('SET_CATALOG', catalogData);
            expect(getters.catalogState).toBe(StatusEnum.LOADED);
        });
        after(() => {
            store.commit('SET_CATALOG', blankCatalogData);
            store.commit('SET_CATALOG_STATE', StatusEnum.INIT);
        });
        it('catalogState', () => {
            expect(getters.catalogState).toBe(StatusEnum.LOADED);
        });
    
        it('songSet', () => {
            const songSet = getters.songSet;
            expect(songSet.length).toBe(state.songList.length);
            state.songList.forEach(id => {
                const song = state.songs[id];
                const songSetSong = songSet.find(s => s.id === id);            
                expect(song).toEqual(songSetSong);
            });
        });
    
        it('getSongById', () => {
            state.songList.forEach(id => {
                const song = state.songs[id];
                const songById = getters.getSongById(id);
                expect(song).toEqual(songById);
            });
        });
    
        it('getSongByIdNoMatch', () => {
            const songById = getters.getSongById('');
            expect(songById).toBeUndefined();        
        });
    
        it('playlistSet', () => {
            const playlistSet = getters.playlistSet;
            expect(playlistSet.length).toBe(state.playlistList.length);        
            state.playlistList.forEach(id => {
                const playlist = state.playlists[id];
                const playlistSetPlaylist = playlistSet.find(pl => pl.id === id);
                expect(playlist).toEqual(playlistSetPlaylist);
            });
        });
    
        it('playlistById', () => {
            state.playlistList.forEach(id => {
                const playlist = state.playlists[id];
                const playlistById = getters.getPlaylistById(id);
                expect(playlist).toEqual(playlistById);
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
                expect(playlist.songs.length).toBe(playlistSongs.length);
                playlist.songs.forEach(songId => {
                    const playlistSong = playlistSongs.find(playlistSong => playlistSong.id === songId);
                    const song = getters.getSongById(songId);
                    expect(song).toEqual(playlistSong);
                });
            });
        });
    
        it('albumSet', () => {
            const albumSet = getters.albumSet;
            expect(albumSet.length).toBe(state.albumList.length);        
            state.albumList.forEach(id => {
                const album = state.albums[id];
                const albumSetAlbum = albumSet.find(pl => pl.id === id);
                expect(album).toEqual(albumSetAlbum);
            });
        });
    
        it('albumById', () => {
            state.albumList.forEach(id => {
                const album = state.albums[id];
                const albumById = getters.getAlbumById(id);
                expect(album).toEqual(albumById);
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
                    expect(getters.getSongAlbum(song)).toEqual(song.album);
                }
            });
        });
    });
});


describe('mutations', () => {
    describe('manager', () => {
        beforeEach(async () => {
            await store.dispatch('manage/fetchData');
        });

        afterEach(() => {
            store.commit('manage/SET_USERS', blankUsersData);
            store.commit('manage/SET_MANAGE_STATE', StatusEnum.INIT);
        });

        it('SET_USERS', () => {    
            expect(store.state.manage.manageState).toEqual(StatusEnum.LOADED);
        });
    });

    describe('catalog', () => {
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
            expect(song).toEqual(songById);
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
            expect(updatedSong).toEqual(updatedSongById);  
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
            expect(song).toEqual(songById);

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
});


function stateToState(stateA, stateB) {
    expect(stateA.songs).toEqual(stateB.songs);
    expect(stateA.songList).toEqual(stateB.songList);
    expect(stateA.playlists).toEqual(stateB.playlists);
    expect(stateA.playlistList).toEqual(stateB.playlistList);
    expect(stateA.albums).toEqual(stateB.albums);
    expect(stateA.albumList).toEqual(stateB.albumList);   
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

function dummyUser(id) {
    return { 
        id: id,
        username: "dummyusername",
        firstName: "Dummy",
        lastName: "User",
        email: "dummy.user@gmail.com",
        password: 'pass',
        statusId: "1"
    };
}
