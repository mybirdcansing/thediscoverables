import {CatalogConnector} from '../../connectors/CatalogConnector';
import { RestConnector } from '../../connectors/RestConnector';
import { StatusEnum } from '../StatusEnum';
import QueuePositionEnum from '../QueuePositionEnum';

const state = {
    catalogState: StatusEnum.INIT,
    songs: {},
    songList: [],
    songsWithAlbums: [],
    playlists: {},
    playlistList: [],    
    albums: {},
    albumList: [],
    queue: [],
    activeSongQueueIndex: -1
}

const getters = {
    songsWithAlbums: (state) => state.songsWithAlbums,
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
    getQueue: (state) =>  state.queue,
    activeStateSong: (state) => {
        if (state.activeSongQueueIndex !== -1 && state.queue.length < state.activeSongQueueIndex) {
            return state.queue[state.activeSongQueueIndex];
        } else {
            return {};
        }
    }
}

const actions = {
    async fetchCatalog({commit, getters}) {
        commit('SET_CATALOG_STATE', StatusEnum.LOADING);
        const catalogConnector = new CatalogConnector();
        try {
            commit('SET_CATALOG', await catalogConnector.get());
            const songsWithAlbums = getters.songSet.map(song => {
                const clone = Vue.util.extend({}, song);
                clone.album = getters.getSongAlbum(song);                    
                return clone;
            });         
            commit('SET_SONGS_WITH_ALBUMS', songsWithAlbums);
        } catch (e) {
            commit('SET_CATALOG_STATE', StatusEnum.ERROR);
            console.error(e);
        }
    },
    createItem({commit}, options) {
        return new Promise(async (resolve, reject) => {
            try {
                const dataKey = options.handler + 'Id';
                const statusKey = options.handler + 'Created';
                options.category = `${options.handler}s`;
                options.categoryList = `${options.handler}List`;
                const connector = new RestConnector(options.handler);        
                const data = await connector.create(options.data);
                if (
                    data.hasOwnProperty(dataKey)
                    && data.hasOwnProperty(statusKey) 
                    && data[statusKey]
                ) {
                    options.data.id = data[dataKey];
                    commit('CREATE_ITEM', options);
                    resolve(data);
                } else {
                    reject(data);
                }
            } catch(response) {
                reject(response);
            }
        });
    },    
    updateItem({commit}, options) {
        return new Promise(async (resolve, reject) => {
            try {
                const statusKey = `${options.handler}Updated`;
                options.categoryList = `${options.handler}List`;
                options.category = `${options.handler}s`;
                const connector = new RestConnector(options.handler);
                const data = await connector.update(options.data);
                if (data.hasOwnProperty(statusKey) && data[statusKey]) {
                    commit('UPDATE_ITEM', options);
                    resolve(data);
                } else {
                    reject(data);
                }
            } catch(error) {
                reject(error);
            }
        });
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
    addToStateQueue ({commit}, payload) {
        commit('ADD_TO_QUEUE', payload);
    },

}

const mutations = {
    SET_CATALOG_STATE(state, catalogState) {
        state.catalogState = catalogState;
    },
    SET_SONGS_WITH_ALBUMS(state, songsWithAlbums) {
        state.songsWithAlbums = songsWithAlbums;
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
    ADD_TO_QUEUE(state, payload) {
        let queue = state.queue;
        let indexOfFirstSongAdded;
        const activeIndex = state.activeSongQueueIndex;
        if (activeIndex === -1) {
            payload.position = QueuePositionEnum.START;            
        } else if (
            payload.position === QueuePositionEnum.NEXT
            && queue.length === activeIndex + 1 
        ) {
            payload.position = QueuePositionEnum.END;
        }

        switch (payload.position) {
            case QueuePositionEnum.START:
                queue = [].concat(payload.songs, queue);
                if (activeIndex > -1) {
                    activeIndex += payload.songs.length;
                }
                indexOfFirstSongAdded = 0;
                break;
            case QueuePositionEnum.NEXT:
                const head = queue.slice(0, activeIndex + 1);
                const tail = queue.slice(activeIndex + 1);
                queue = [].concat(head, payload.songs, tail);
                indexOfFirstSongAdded = activeIndex + 1;
                break;
            case QueuePositionEnum.END:
                indexOfFirstSongAdded = queue.length;
                queue = queue.concat(payload.songs);
                break;
            default:
                break;
        }
        return indexOfFirstSongAdded;
    }
}



export default {
    state,
    getters,
    actions,
    mutations
}