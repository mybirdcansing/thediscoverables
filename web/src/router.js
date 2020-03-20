import { UserConnector } from './connectors/UserConnector';

import VueRouter from 'vue-router';
Vue.use(VueRouter);

import Music from './components/Music.vue';
import Dashboard from './components/Dashboard.vue';
import Song from './components/Song.vue';
import Album from './components/Album.vue';
import store from './store/store';

const titlePrefix = 'The Discoverables';
const managerTitlePrefix = `${titlePrefix} Manager`;
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

const router = new VueRouter({
    routes: [
        {   
            path: '', 
            component: Music,
            children: [
                { 
                    path: '',
                    component: Dashboard,
                    meta: {
                        title: `${titlePrefix}: Music`,
                    },
                },
                { path: '/song/:id', component: Song },
                { path: '/album/:id', component: Album },
            ],
            meta: {
                title: titlePrefix,
            }
        },
        { 
            path: '/manager',
            component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/Manager.vue'),
            meta: {
                title: managerTitlePrefix,
            },
            children: [
                { 
                    path: '', 
                    redirect: 'songs'
                },
                { 
                    path: 'songs',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManageSongs.vue'),
                    beforeEnter: authenticateAndGo,
                    meta: {
                        title: `${managerTitlePrefix}: Songs`,
                    },
                },
                { 
                    path: 'song/:id',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManageSong.vue'),
                    beforeEnter: authenticateAndGo,
                    meta: {
                        title: `${managerTitlePrefix}: Song`,
                    },                    
                },
                { 
                    path: 'playlists',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManagePlaylists.vue'),
                    beforeEnter: authenticateAndGo,
                    meta: {
                        title: `${managerTitlePrefix}: Playlists`,
                    },                    
                },
                { 
                    path: 'playlist/:id',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManagePlaylist.vue'),
                    beforeEnter: authenticateAndGo,
                    meta: {
                        title: `${managerTitlePrefix}: Playlist`,
                    },                    
                },
                { 
                    path: 'albums',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManageAlbums.vue'),
                    beforeEnter: authenticateAndGo,
                    meta: {
                        title: `${managerTitlePrefix}: Albums`,
                    },                    
                },
                { 
                    path: 'album/:id',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManageAlbum.vue'),
                    beforeEnter: authenticateAndGo,
                    meta: {
                        title: `${managerTitlePrefix}: Album`,
                    },                    
                },
                { 
                    path: 'users',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManageUsers.vue'),
                    beforeEnter: authenticateAndGo,
                    meta: {
                        title: `${managerTitlePrefix}: Users`,
                    },                    
                },
                { 
                    path: 'user/:id',
                    component: () => import(/* webpackChunkName: "mgmt" */ './components/manage/ManageUser.vue'),
                    beforeEnter: authenticateAndGo,
                    meta: {
                        title: `${managerTitlePrefix}: User`,
                    },                    
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

router.beforeEach((to, from, next) => {
    // This goes through the matched routes from last to first, finding the closest route with a title.
    // eg. if we have /some/deep/nested/route and /some, /deep, and /nested have titles, nested's will be chosen.
    const nearestWithTitle = to.matched.slice().reverse().find(r => r.meta && r.meta.title);
    // Find the nearest route element with meta tags.
    const nearestWithMeta = to.matched.slice().reverse().find(r => r.meta && r.meta.metaTags);
    const previousNearestWithMeta = from.matched.slice().reverse().find(r => r.meta && r.meta.metaTags);
  
    // If a route with a title was found, set the document (page) title to that value.
    if(nearestWithTitle) document.title = nearestWithTitle.meta.title;
  
    // Remove any stale meta tags from the document using the key attribute we set below.
    Array.from(document.querySelectorAll('[data-vue-router-controlled]')).map(el => el.parentNode.removeChild(el));
  
    // Skip rendering meta tags if there are none.
    if(!nearestWithMeta) return next();
  
    // Turn the meta tag definitions into actual elements in the head.
    nearestWithMeta.meta.metaTags.map(tagDef => {
      const tag = document.createElement('meta');
  
      Object.keys(tagDef).forEach(key => {
        tag.setAttribute(key, tagDef[key]);
      });
  
      // We use this to track which meta tags we create, so we don't interfere with other ones.
      tag.setAttribute('data-vue-router-controlled', '');
  
      return tag;
    })
    // Add the meta tags to the document head.
    .forEach(tag => document.head.appendChild(tag));
  
    next();
  });

export default router;