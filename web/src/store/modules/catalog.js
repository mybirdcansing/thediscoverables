import {CatalogConnector} from '../../connectors/CatalogConnector';
import { SongConnector } from '../../connectors/SongConnector';
import { PlaylistConnector } from '../../connectors/PlaylistConnector';
import { RestConnector } from '../../connectors/RestConnector';
import { StatusEnum } from '../StatusEnum';
const catalogConnector = new CatalogConnector();
const songConnector = new SongConnector();
const playlistConnector = new PlaylistConnector();

const filterInPlace = (array, predicate) => {
    let end = 0;

    for (let i = 0; i < array.length; i++) {
        const obj = array[i];

        if (predicate(obj)) {
            array[end++] = obj;
        }
    }

    array.length = end;
};

const state = {
    catalogState: StatusEnum.INIT,
    songs: {},
    songList: [],
    playlists: {},
    playlistList: [],
    playlistSongIndex: [],
    albums: {},
    albumList: [],
}

const getters = {
    catalogState: (state) => state.catalogState,
    songSet: (state) => state.songList.map(id => state.songs[id]),
    getSongById: (state) => (id) => state.songs[id],
    playlistSet: (state) => state.playlistList.map(id => state.playlists[id]),
    playlistSongIndex: (state) => state.playlistSongIndex,
    getPlaylistById: (state) => (id) => state.playlists[id],
    albumSet: (state) => state.albumList.map(id => state.albums[id]),
    getAlbumById: (state) => (id) => state.albums[id],
}

const actions = {
    async fetchCatalog({commit}) {
        try {
            commit('SET_CATALOG_STATE', StatusEnum.LOADING);
            commit('SET_CATALOG', await catalogConnector.get());
        } catch (e) {
            commit('SET_CATALOG_STATE', StatusEnum.ERROR);
            console.error(e);
        }
    },
    deleteItem({commit}, options) {
        
        options.categoryList = `${options.handler}List`;
        options.category = `${options.handler}s`;
        const connector = new RestConnector(options.handler);
        return new Promise(async (resolve, reject) => {
            try {
                const response = await connector.delete(options.id);
                commit('DELETE_ITEM', options);
                resolve(response);
            } catch (response) {
                reject(response);
            }
        });
    },
    updateSong({commit}, song) {
        return new Promise((resolve, reject) => {
            songConnector.update(song)
                .then(function(response) {
                    if (response.songUpdated) {
                        commit('UPDATE_ITEM', {
                            data: song,
                            categoryList: 'songList',
                            category: 'songs'
                        });
                        resolve(response);
                    } else {
                        reject(response);
                    }
                })
                .catch(function(response) {
                    reject(response);
                }
            );
        });
    },
    createSong({commit}, song) {
        return new Promise((resolve, reject) => {
            songConnector.create(song)
                .then(function(response) {
                    if (response.songCreated) {
                        song.id = response.songId;
                        commit('CREATE_ITEM', {
                            data: song,
                            categoryList: 'songList',
                            category: 'songs'
                        });
                        resolve(response);
                    } else {
                        reject(response);
                    }
                })
                .catch(function(response) {
                    reject(response);
                }
            );
        });
    },
    updatePlaylist({commit}, playlist) {
        return new Promise((resolve, reject) => {
            playlistConnector.update(playlist)
                .then(function(response) {
                    if (response.playlistUpdated) {
                        commit('UPDATE_ITEM', {
                            data: playlist,
                            categoryList: 'playlistList',
                            category: 'playlists'
                        });
                        resolve(response);
                    } else {
                        reject(response);
                    }
                })
                .catch(function(response) {
                    reject(response);
                }
            );
        });
    },
    createPlaylist({commit}, options) {
        const playlist = options.playlist;
        const songsIndex = options.songsIndex;
        return new Promise((resolve, reject) => {
            playlistConnector.create(playlist)
                .then(function(response) {
                    if (response.playlistCreated) {
                        playlist.id = response.playlistId;
                        commit('CREATE_ITEM', {
                            data: playlist,
                            categoryList: 'playlistList',
                            category: 'playlists'
                        });
                        resolve(response);
                    } else {
                        reject(response);
                    }
                })
                .catch(function(response) {
                    reject(response);
                }
            );
        });
    },

}

const mutations = {
    SET_CATALOG_STATE(state, catalogState) {
        state.catalogState = catalogState;
    },
    SET_CATALOG(state, catalog) {
        Object.keys(catalog).forEach(function(key) {
            state[key] = catalog[key];
        });
        state.catalogState = StatusEnum.LOADED;
    },
    DELETE_ITEM(state, obj) {
        const index = state[obj.categoryList].findIndex((id) => id === obj.id);
        state[obj.categoryList].splice(index, 1);
        delete state[obj.category][obj.id];
    },
    UPDATE_ITEM(state, obj) {
        state[obj.category][obj.data.id] = obj.data;
    },
    CREATE_ITEM(state, obj) {
        state[obj.category][obj.data.id] = obj.data;
        state[obj.categoryList].push(obj.data.id);
    },
    // REFRESH_PLAYLIST_SONG_INDEX(state, options) {
        // debugger;
        // // remove the existing entries
        // const playlistSongIndex = state.playlistSongIndex;
        // const playlistSongs = options.playlist.songs;
        // const playlistId = options.playlist.id;
        // const playlistSongsToDelete = playlistSongIndex.filter(value => {
        //     return value.playlistId === playlistId && !playlistSongs.includes(value.songId)
        // });
        // const setToDelete = new Set(playlistSongsToDelete);
        // if (setToDelete.length) {
        //     // filterInPlace(playlistSongIndex, obj => !setToDelete.has(obj.id));
        //     let end = 0;

        //     for (let i = 0; i < playlistSongIndex.length; i++) {
        //         const entry = playlistSongIndex[i];
        //         const f = obj => !setToDelete.has(obj.id)
        //         if (f(entry)) {
        //             playlistSongIndex[end++] = entry;
        //         }
        //     }
        
        //     playlistSongIndex.length = end;
        // }
        
        //update or add the remaining entries

        
    // }
}



export default {
    state,
    getters,
    actions,
    mutations
}