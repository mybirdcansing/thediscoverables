import {UserConnector} from '../../connectors/UserConnector';
const userConnector = new UserConnector();

const state = {
    users: {},
    userList: [],
}

const getters = {
    users: (state) => state.users,
    userList: (state) => state.userList,
    userSet: (state) => state.userList.map(id => state.users[id]),
    getUserById: (state) => (id) => state.users.find(user => user.id === id),
}

const actions = {
    async fetchUsers({commit, state}) {
        try {
            commit('setUsers', await userConnector.getAll());
        } catch (e) {
            console.error(e);
        }
    }
}

const mutations = {
    setUsers(state, users) {
        let userList = [];
        let usersObj = {};
        users.forEach(function(user) {
            usersObj[user.id] = user;
            userList.push(user.id);
        });
        state.users = usersObj;
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