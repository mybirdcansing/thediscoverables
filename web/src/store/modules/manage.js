import {UserConnector} from '../../connectors/UserConnector';
const userConnector = new UserConnector();

const state = {
    users: {},
    userList: [],
}

const getters = {
    userSet: (state) => state.userList.map(id => state.users[id]),
    getUserById: (state) => (id) => state.users.find(user => user.id === id),
}

const actions = {
    async fetchUsers({commit}) {
        try {
            commit('setUsers', await userConnector.getAll());
        } catch (e) {
            console.error(e);
        }
    }
}

const mutations = {
    setUsers(state, userData) {
        let userList = [];
        let users = {};
        userData.forEach(function(user) {
            users[user.id] = user;
            userList.push(user.id);
        });
        state.users = users;
        state.userList = userList;
    }
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}