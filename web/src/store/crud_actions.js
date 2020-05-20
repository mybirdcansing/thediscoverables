import { RestConnector } from '../connectors/RestConnector';

export default {
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
}