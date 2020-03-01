import {CatalogConnector} from '../../connectors/CatalogConnector';
import { SongConnector } from '../../connectors/SongConnector';
const catalogConnector = new CatalogConnector();
const songConnector = new SongConnector();

const state = {
    songs: {},
    songList: [],
    playlists: {},
    playlistList: [],
    albums: {},
    albumList: [],
}

const getters = {
    songSet: (state) => state.songList.map(id => state.songs[id]),
    getSongById: (state) => (id) => state.songs[id],
    playlistSet: (state) => state.playlistList.map(id => state.playlists[id]),
    getPlaylistById: (state) => (id) => state.playlists[id],
    albumSet: (state) => state.albumList.map(id => state.albums[id]),
    getAlbumById: (state) => (id) => state.albums[id],
}

const actions = {
    async fetchCatalog({commit}) {
        try {
            commit('SET_CATALOG', await catalogConnector.get());
        } catch (e) {
            console.error(e);
        }
    },
    deleteSong({commit}, songId) {
        return new Promise(async (resolve, reject) => {
            try {
                await songConnector.delete(songId);
                commit('DELETE_ITEM', {
                    id: songId,
                    categoryList: 'songList',
                    category: 'songs'
                });
                resolve(true);
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
    // createSong({commit}, song) {
    //     return new Promise(async (resolve, reject) => {
    //         try {
    //             const response = await songConnector.create(song);
    //             if (response.songCreated) {
    //                 commit('CREATE_ITEM', {
    //                     data: song,
    //                     categoryList: 'songList',
    //                     category: 'songs'
    //                 });
    //                 resolve(response);
    //             } else {
    //                 reject(response);
    //             }
    //         } catch (response) {
    //             reject(response);
    //         }
    //     });
    // }
}

const mutations = {
    SET_CATALOG(state, catalog) {
        Object.keys(catalog).forEach(function(key) {
            state[key] = catalog[key];
        });
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
    }
}

export default {
    state,
    getters,
    actions,
    mutations
}