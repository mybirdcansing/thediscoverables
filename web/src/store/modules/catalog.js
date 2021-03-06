import {CatalogConnector} from '../../connectors/CatalogConnector';
import StatusEnum from '../StatusEnum';
import crudActions from '../crud_actions';

const state = {
    catalogState: StatusEnum.INIT,
    songs: {},
    songList: [],
    playlists: {},
    playlistList: [],    
    albums: {},
    albumList: [],
}

const getters = {
    catalogState: (state) => state.catalogState,
    songSet: (state) => state.songList.map(id => state.songs[id]),
    getSongById: (state) => (id) => state.songs[id],
    playlistSet: (state) => state.playlistList.map(id => state.playlists[id]),
    getPlaylistById: (state) => (id) => state.playlists[id],
    albumSet: (state) => state.albumList.map(id => state.albums[id]),
    getAlbumById: (state) => (id) => state.albums[id],
    getPlaylistSongs: (state) => (playlist) => playlist.songs.map(id => state.songs[id]),
    getSongAlbums: (state, getters) => (song) => {
        return getters.playlistSet
            .filter(playlist => playlist.songs.includes(song.id))
            .map(playlist => getters.albumSet.find(album => album.playlist === playlist.id));
    },
    getSongAlbum: (state, getters) => (song) => {
        //if the song is in more than one album, get the most recent
        const albums = getters.getSongAlbums(song);
        return albums.sort((a, b) => (a.publishDate > b.publishDate) ? 1 : -1)[0]; 
    },
    getAlbumSongs: (state, getters) => (album) => {
        return getters.getPlaylistSongs(state.playlists[album.playlist]);
    },
    homepagePlaylist: (state, getters) => {
        return getters.playlistSet.find(playlist => playlist.title === 'homepage');        
    },
    getSongsWithAlbums: (state, getters) => {
        return getters.songSet.map(song => {
            const clone = Vue.util.extend({}, song);
            clone.album = getters.getSongAlbum(song);                    
            return clone;
        });
    },
}

const actions = {
    async fetchCatalog({commit}) {
        commit('SET_CATALOG_STATE', StatusEnum.LOADING);
        const catalogConnector = new CatalogConnector();
        try {
            commit('SET_CATALOG', await catalogConnector.get());
        } catch (e) {
            commit('SET_CATALOG_STATE', StatusEnum.ERROR);
        }
    },
    ...crudActions    
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
    CREATE_ITEM(state, obj) {
        state[obj.category][obj.data.id] = obj.data;
        state[obj.categoryList].push(obj.data.id);
    },
    UPDATE_ITEM(state, obj) {        
        state[obj.category][obj.data.id] = obj.data;
    },
    DELETE_ITEM(state, obj) {
        const index = state[obj.categoryList].findIndex((id) => id === obj.id);
        state[obj.categoryList].splice(index, 1);
        delete state[obj.category][obj.id];
    },
}

export default {
    state,
    getters,
    actions,
    mutations
}