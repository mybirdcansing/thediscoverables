import axios from 'axios';
import regeneratorRuntime from "regenerator-runtime";

export default () => {
    return axios.create({
        baseURL: '/lib/handlers',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json'
        }
    });
}
