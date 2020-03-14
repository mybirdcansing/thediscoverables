import { UserConnector } from './connectors/UserConnector';

import VueRouter from 'vue-router';
Vue.use(VueRouter);

import Music from './components/Music.vue';
import Song from './components/Song.vue';
import Album from './components/Album.vue';
import store from './store/store';

const authenticateAndGo = async (to, from, next) => {
    if (!store.state.manage.manager) {
        try {
            await store.dispatch('manage/authorize');
            next();
        } catch (e) {
            next({
                path: '/login',
                query: { redirect: to.fullPath }
            });
        }
    } else {
        next();
    }
};

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
            component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/Manager.vue'),
            children: [
                { path: '', redirect: 'songs' },
                { 
                    path: 'songs',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManageSongs.vue'),
                    beforeEnter: authenticateAndGo,
                },
                { 
                    path: 'song/:id',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManageSong.vue'),
                    beforeEnter: authenticateAndGo,
                },
                { 
                    path: 'playlists',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManagePlaylists.vue'),
                    beforeEnter: authenticateAndGo,
                },
                { 
                    path: 'playlist/:id',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManagePlaylist.vue'),
                    beforeEnter: authenticateAndGo,
                },
                { 
                    path: 'albums',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManageAlbums.vue'),
                    beforeEnter: authenticateAndGo,
                },
                { 
                    path: 'album/:id',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManageAlbum.vue'),
                    beforeEnter: authenticateAndGo,
                },
                { 
                    path: 'users',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManageUsers.vue'),
                    beforeEnter: authenticateAndGo,
                },
                { 
                    path: 'user/:id',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManageUser.vue'),
                    beforeEnter: authenticateAndGo,
                },             
            ]
        },
        { 
            path: '/login',
            name: 'login',
            component: () => import(/* webpackChunkName: "login" */ './components/manage/Login.vue'),
        },
        { 
            path: '/passwordhelp', 
            component: () => import(/* webpackChunkName: "login" */ './components/manage/PasswordHelp.vue'),
        },
        { 
            path: '/passwordreset', 
            component: () => import(/* webpackChunkName: "login" */ './components/manage/PasswordReset.vue'),
        },
    ],
    mode: 'history',
});
