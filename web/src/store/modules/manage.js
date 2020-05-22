import {UserConnector} from '../../connectors/UserConnector';
import StatusEnum from '../StatusEnum';
import crudActions from '../crud_actions';

const state = {
    manageState: StatusEnum.INIT,
    users: {},
    userList: [],
    manager: null
}
const userConnector = new UserConnector();

const getters = {
    manageState: (state) => state.manageState,
    userSet: (state) => state.userList.map(id => state.users[id]),
    getUserById: (state) => (id) => state.users[id],
    getManager: (state) => Object.values(state.users).find(user => state.manager === user.username),
}

const actions = {
    async fetchData({commit}) {
        commit('SET_MANAGE_STATE', StatusEnum.LOADING);
        try {
            const response = await userConnector.getAll();
            commit('SET_USERS', response);
            commit('SET_MANAGE_STATE', StatusEnum.LOADED);
        } catch (e) {
            commit('SET_MANAGE_STATE', StatusEnum.ERROR);
        }        
    },
    logout({commit}) {
        return new Promise(async (resolve, reject) => {
            try {
                const response = await userConnector.logout();
                resolve(response);
            } catch (response) {
                reject(response);
            } finally {
                commit('SET_MANAGER', null);
            }
        });        
    },
    login({commit}, {username, password}) {
        return new Promise(async (resolve, reject) => {
            try {
                const response = await userConnector.authenticate(username, password);
                commit('SET_MANAGER', response.user.username);
                resolve(response);
            } catch (response) {
                reject(response);
            } 
        });        
    },
    authorize({commit}) {
        return new Promise(async (resolve, reject) => {
            try {
                const response = await userConnector.authorize();
                commit('SET_MANAGER', response.username);
                resolve(response);
            } catch (response) {
                reject(response);
            }
        });        
    },
    ...crudActions
}

const mutations = {
    SET_MANAGE_STATE(state, manageState) {
        state.manageState = manageState;
    },
    SET_USERS(state, userData) {
        let userList = [];
        let users = {};
        userData.forEach(function(user) {
            users[user.id] = user;
            userList.push(user.id);
        });
        state.users = users;
        state.userList = userList;
    },
    SET_MANAGER(state, username) {
        state.manager = username;
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
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}