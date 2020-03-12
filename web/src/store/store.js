import Vuex from 'vuex';
import catalog from './modules/catalog'
import manage from './modules/manage'

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        catalog,
        manage
      }
});
