import {CatalogConnector} from '../../connectors/CatalogConnector';
import { SongConnector } from '../../connectors/SongConnector';
const catalogConnector = new CatalogConnector();

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
    getSongById: (state) => (id) => state.songs.find(song => song.id === id),
    playlistSet: (state) => state.playlistList.map(id => state.playlists[id]),
    getPlaylistById: (state) => (id) => state.playlists.find(playlist => playlist.id === id),
    albumSet: (state) => state.albumList.map(id => state.albums[id]),
    getAlbumById: (state) => (id) => state.albums.find(album => album.id === id),
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
            const songConnector = new SongConnector()
            try {
                await songConnector.delete(songId);
                commit('DELETE_ITEM', {
                    id: songId,
                    categoryList: 'songList',
                    category: 'songs'
                });
                resolve(true);
            } catch (data) {
                reject(data);
            }
        });

    }
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
    }
}

export default {
    state,
    getters,
    actions,
    mutations
}