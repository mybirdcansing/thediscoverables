import Vuex from 'vuex';
Vue.use(Vuex);
import {SongConnector} from '../connectors/SongConnector';

const songConnector = new SongConnector();

export const store = new Vuex.Store({
    state: {
        users: [
            {id: '1', username: 'adam1'},
            {id: '2', username: 'adam2'},
            {id: '3', username: 'adam3'},
            {id: '3', username: 'adam3'}
        ],
        songsToPlaylist: [
            {playlistId: '1', songId: '1'},
            {playlistId: '1', songId: '2'},
            {playlistId: '2', songId: '3'},
            {playlistId: '2', songId: '4'},
            {playlistId: '2', songId: '5'},
        ],
        songs: [
            {id: '1', title: 'For The Songs (song)'},
            {id: '2', title: "It's Hard to Say"},
            {id: '3', title: 'Running in Place'},
            {id: '4', title: 'Jen'},
            {id: '5', title: 'Running in Place'},
        ],
        playlists: [
            {id: '1', title: 'For The Songs  (playlist)'},
            {id: '2', title: 'Running in Place'},
        ],
        albums: [
            {id: '1', title: 'For The Songs  (album)', playlistId: '1'},
            {id: '2', title: 'Running in Place', playlistId: '2'},
        ],
    },
    getters: {
        getAlbum: (state) => (id) => {
            const album = state.albums.find(album => album.id === id);
            album.playlist = state.playlists.find(playlist => playlist.id == album.playlistId);
            if (state.songsToPlaylist.length > 0) {
                const reducer = (acc, entry) => {
                    if (entry.playlistId === album.playlistId) {
                        acc.push(entry.songId);
                    }
                    return acc;
                };
                const keys = state.songsToPlaylist.reduce(reducer, []);
                album.playlist.songs = state.songs.filter(s => keys.includes(s.id));
            }
            return album;
        },
        songs: (state) => state.songs
    },
    mutations: {
        setSongs(state, songs) {
            state.songs = songs;
        }
    },
    actions: {
        initStore({commit, state}) {
            return new Promise((resolve, reject) => {

            });
        },
        initSongs({commit, state}) {
            return new Promise((resolve, reject) => {
                    songConnector.getAll().then((data) => {
                        commit('setSongs', data);
                        resolve(state.songs);
                    }).catch((error) => {
                        reject(error);
                    });
                }
            );
        }
    }
});
