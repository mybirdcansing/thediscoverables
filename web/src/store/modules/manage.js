import {UserConnector} from '../../connectors/UserConnector';
import {RestConnector} from '../../connectors/RestConnector';
import {StatusEnum} from '../StatusEnum';

const state = {
    manageState: StatusEnum.INIT,
    users: {},
    userList: [],
}

const getters = {
    userSet: (state) => state.userList.map(id => state.users[id]),
    getUserById: (state) => (id) => state.users[id],
}

const actions = {
    async fetchUsers({commit}) {
        try {
            commit('SET_MANAGE_STATE', StatusEnum.LOADING);
            const userConnector = new UserConnector();
            commit('SET_USERS', await userConnector.getAll());
            commit('SET_MANAGE_STATE', StatusEnum.LOADED);
        } catch (e) {
            commit('SET_MANAGE_STATE', StatusEnum.ERROR);
            console.error(e);
        }
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
    createItem({commit}, options) {
        return new Promise(
            async (resolve, reject) => {
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
            }
        );
    },    
}

const mutations = {
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
    SET_MANAGE_STATE(state, manageState) {
        state.manageState = manageState;
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}