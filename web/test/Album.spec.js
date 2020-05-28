import "regenerator-runtime";
import { mount } from "@vue/test-utils"
import axios from 'axios';
import sinon from "sinon";
import MockAdapter from "axios-mock-adapter";
import router from "../src/router.js"
import store from '../src/store/store';
import App from "../src/components/App.vue";
import Album from "../src/components/Album.vue";
import catalogData from './mocks/catalog.mock.json';

const getters = store.getters;
const mockAdapter = new MockAdapter(axios);
mockAdapter.onGet('/lib/handlers/catalog').reply(200, catalogData);
const wrapper = mount(App, {
    router,
    store,
});

describe("Album", () => {
    let albumComponent;
    let album;
    let routerPushStub;

    before(async () => {
        album = getters.albumSet[0];
        router.push(`/album/${album.id}`).catch(() => {});
        await wrapper.vm.$nextTick();        
        albumComponent = wrapper.findComponent(Album);
        routerPushStub = sinon.stub(router, 'push');
    });
    
    after(() => {
        routerPushStub.restore();
    });

    describe("header", () => {
        let albumHeader;
        before(() => {
            albumHeader = wrapper.find(".album-header");
        });        
        
        it('album title', () => {
            expect(albumHeader.find('.album-title').text()).toBe(album.title);
        });
        
        it('album year', () => {
            const yearString = new Date(album.publishDate).getUTCFullYear().toString()
            expect(albumHeader.find('.publish-year').text()).toBe(yearString);
        });
        
        it('album details', () => {
            expect(albumHeader.find('.count-and-timing').text()).toBe(`${albumComponent.vm.songCount} songs • ${albumComponent.vm.totalMinutes} minutes`)
        });

        it('link to dashboard', async () => {
            const link = albumHeader.find('.album-details .dashboard-link');
            link.trigger('click');
            await wrapper.vm.$nextTick();
            expect(routerPushStub.getCall(0).args[0].path).toBe('/');
            routerPushStub.reset();
        });
    });
    describe("songs", () => {
        let albumSongs;
        let firstSong;
        let songRows;
        let firstRow;
        before(() => {  
            albumSongs = getters.getAlbumSongs(album);
            firstSong = albumSongs[0];
            expect(firstSong).toBeTruthy();
            songRows = wrapper.findAll('.song-list-row');
            expect(songRows).toBeTruthy();
            firstRow = songRows.at(0);
            expect(firstRow).toBeTruthy();        
        });
        it("renders Album component via routing",  () => {
            expect(albumComponent.exists()).toBe(true);
        });
    
        it('song count', () => {
            expect(songRows).toHaveLength(albumSongs.length);
        });
    
        it('first song title', () => {
            expect(firstRow.find('.song-title').text()).toBe(firstSong.title);
        });
    
        it('first song duration', () => {             
            const durationString = albumComponent.vm.ticksToTimeString(firstSong.duration);
            expect(firstRow.find('.song-duration-cell').text()).toBe(durationString);
        });
    
        it('first song bullet', () => {
            expect(firstRow.find('.song-list-index').text()).toBe('1');
        });
    });

    


});
