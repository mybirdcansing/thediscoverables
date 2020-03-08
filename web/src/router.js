import { UserConnector } from './connectors/UserConnector';

import VueRouter from 'vue-router';
Vue.use(VueRouter);

import Music from './components/Music.vue';
import Song from './components/Song.vue';
import Album from './components/Album.vue';

import Manager from './components/manage/Manager.vue';
import ManageSongs from './components/manage/ManageSongs.vue';
import ManageSong from './components/manage/ManageSong.vue';
import ManagePlaylists from './components/manage/ManagePlaylists.vue';
import ManagePlaylist from './components/manage/ManagePlaylist.vue';
import ManageAlbums from './components/manage/ManageAlbums.vue';
import ManageAlbum from './components/manage/ManageAlbum.vue';
import ManageUsers from './components/manage/ManageUsers.vue';
import ManageUser from './components/manage/ManageUser.vue';

import Login from './components/manage/Login.vue';
import PasswordHelp from './components/manage/PasswordHelp.vue';
import PasswordReset from './components/manage/PasswordReset.vue';

export const router = new VueRouter({
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
            component: Manager,
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
                { path: 'songs', component: ManageSongs },
                { path: 'song/:id', component: ManageSong },
                { path: 'playlists', component: ManagePlaylists },
                { path: 'playlist/:id', component: ManagePlaylist },
                { path: 'albums', component: ManageAlbums },
                { path: 'album/:id', component: ManageAlbum },
                { path: 'users', component: ManageUsers },
                { path: 'user/:id', component: ManageUser },
            ]
        },
        { 
            path: '/login',
            name: 'login',
            component: Login,
            children: [
                { path: 'passwordhelp', component: PasswordHelp },
            ]
        },
        { path: '/passwordhelp', component: PasswordHelp },
        { path: '/passwordreset', component: PasswordReset },
    ],
    mode: 'history',
});
