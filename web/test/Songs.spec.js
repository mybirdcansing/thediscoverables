import "regenerator-runtime";
import { mount } from "@vue/test-utils"
import axios from 'axios';
import sinon from "sinon";
import MockAdapter from "axios-mock-adapter";
import router from "../src/router.js"
import store from '../src/store/store';
import App from "../src/components/App.vue";
import Songs from "../src/components/Songs.vue";
import catalogData from './mocks/catalog.mock.json';

const getters = store.getters;
const mockAdapter = new MockAdapter(axios);
mockAdapter.onGet('/lib/handlers/catalog').reply(200, catalogData);
const wrapper = mount(App, {
    router,
    store,
});

describe("Songs", async () => {
    let songsComponent;
    let allSongs;
    let firstSong;
    let songRows;
    let firstRow;
    let routerPushStub;

    before(async () => {
        router.push("/songs").catch(() => {});
        await wrapper.vm.$nextTick();        
        songsComponent = wrapper.findComponent(Songs);
        routerPushStub = sinon.stub(router, 'push');
    });
    after(() => {
        routerPushStub.restore();
    })
    it("renders Songs component via routing",  () => {
        expect(songsComponent.exists()).toBe(true);
    });
    
    before(() => {        
        allSongs = getters.getSongsWithAlbums;
        firstSong = allSongs[0];
        expect(firstSong).toBeTruthy();
        songRows = wrapper.findAll('.song-list-row');
        expect(songRows).toBeTruthy();
        firstRow = songRows.at(0);
        expect(firstRow).toBeTruthy();
    });

    it('song count', () => {
        expect(songRows.length).toBe(allSongs.length);
    });

    it('first song title', () => {
        expect(firstRow.find('.song-title').text()).toBe(firstSong.title);
    });

    it('first song duration', () => {             
        const durationString = songsComponent.vm.ticksToTimeString(firstSong.duration);
        expect(firstRow.find('.song-duration-cell').text()).toBe(durationString);
    });

    it('first song bullet', () => {
        expect(firstRow.find('.song-list-bullet img').classes('song-list-album-artwork')).toBe(true);
    });

    it('first song album title', () => {
        expect(firstRow.find('.album-title').text()).toBe(firstSong.album.title);
    });
    
    it('route to album', async () => {
        
        firstRow.find('.album-title').trigger('click');
        await wrapper.vm.$nextTick();
        sinon.assert.calledWith(routerPushStub, `/album/${firstSong.album.id}`);
        routerPushStub.reset();
    });


     
});
