import Vuex from 'vuex';
import catalog from './modules/catalog'
import manage from './modules/manage'

Vue.use(Vuex);

export const store = new Vuex.Store({
    modules: {
        catalog,
        manage
      }
});
