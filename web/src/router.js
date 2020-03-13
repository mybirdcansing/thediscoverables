import { UserConnector } from './connectors/UserConnector';

import VueRouter from 'vue-router';
Vue.use(VueRouter);

import Music from './components/Music.vue';
import Song from './components/Song.vue';
import Album from './components/Album.vue';
import store from './store/store';

const authenticate = async (to, from, next) => {
    if (!store.state.manage.manager) {
        const uc = new UserConnector();
        try {
            const authResponse = await uc.authorize();
            store.dispatch('manage/setManager', authResponse.username);
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
                    beforeEnter: authenticate,
                },
                { 
                    path: 'song/:id',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManageSong.vue'),
                    beforeEnter: authenticate,
                },
                { 
                    path: 'playlists',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManagePlaylists.vue'),
                    beforeEnter: authenticate,
                },
                { 
                    path: 'playlist/:id',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManagePlaylist.vue'),
                    beforeEnter: authenticate,
                },
                { 
                    path: 'albums',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManageAlbums.vue'),
                    beforeEnter: authenticate,
                },
                { 
                    path: 'album/:id',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManageAlbum.vue'),
                    beforeEnter: authenticate,
                },
                { 
                    path: 'users',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManageUsers.vue'),
                    beforeEnter: authenticate,
                },
                { 
                    path: 'user/:id',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManageUser.vue'),
                    beforeEnter: authenticate,
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
