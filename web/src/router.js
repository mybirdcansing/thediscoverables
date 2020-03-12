import { UserConnector } from './connectors/UserConnector';

import VueRouter from 'vue-router';
Vue.use(VueRouter);

import Music from './components/Music.vue';
import Song from './components/Song.vue';
import Album from './components/Album.vue';

// import Manager from './components/manage/Manager.vue';
// import ManageSongs from './components/manage/ManageSongs.vue';
// import ManageSong from './components/manage/ManageSong.vue';
// import ManagePlaylists from './components/manage/ManagePlaylists.vue';
// import ManagePlaylist from './components/manage/ManagePlaylist.vue';
// import ManageAlbums from './components/manage/ManageAlbums.vue';
// import ManageAlbum from './components/manage/ManageAlbum.vue';
// import ManageUsers from './components/manage/ManageUsers.vue';
// import ManageUser from './components/manage/ManageUser.vue';

// import Login from './components/manage/Login.vue';
// import PasswordHelp from './components/manage/PasswordHelp.vue';
// import PasswordReset from './components/manage/PasswordReset.vue';

export default new VueRouter({
    routes: [
        {   
            path: '', 
            component: Music,
            children: [
                { path: '/song/:id', component: Song },
                { path: '/album/:id', component: Album },
            ]
        },
        { 
            path: '/manager',
            component: () => import('./components/manage/Manager.vue'),
            beforeEnter: (to, from, next) => {
                const uc = new UserConnector();
                uc.authorize()
                    .then(() => next())
                    .catch(() => next({
                        name: 'login',
                        query: { redirect: to.fullPath }
                    }));
            },
            children: [
                { path: '', redirect: 'songs' },
                { path: 'songs', component: () => import('./components/manage/ManageSongs.vue') },
                { path: 'song/:id', component: () => import('./components/manage/ManageSong.vue') },
                { path: 'playlists', component: () => import('./components/manage/ManagePlaylists.vue') },
                { path: 'playlist/:id', component: () => import('./components/manage/ManagePlaylist.vue') },
                { path: 'albums', component: () => import('./components/manage/ManageAlbums.vue') },
                { path: 'album/:id', component: () => import('./components/manage/ManageAlbum.vue') },
                { path: 'users', component: () => import('./components/manage/ManageUsers.vue') },
                { path: 'user/:id', component: () => import('./components/manage/ManageUser.vue') },
            ]
        },
        { 
            path: '/login',
            name: 'login',
            component: () => import('./components/manage/Login.vue'),
        },
        { path: '/passwordhelp', component: () => import('./components/manage/PasswordHelp.vue') },
        { path: '/passwordreset', component: () => import('./components/manage/PasswordReset.vue') },
    ],
    mode: 'history',
});
