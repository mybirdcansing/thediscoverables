import "regenerator-runtime";
import { mount } from "@vue/test-utils"
import axios from 'axios';
import sinon from "sinon";
import MockAdapter from "axios-mock-adapter";
import router from "../src/router.js"
import store from '../src/store/store';
import App from "../src/components/App.vue";
import Dashboard from "../src/components/Dashboard.vue";
import catalogData from './mocks/catalog.mock.json';

const getters = store.getters;
const mockAdapter = new MockAdapter(axios);
mockAdapter.onGet('/lib/handlers/catalog').reply(200, catalogData);
const wrapper = mount(App, {
    router,
    store,
});

describe("Dashboard",  () => {
    let dashboardComponent;
    let routerPushStub;
    before(async () => {
        router.push("/").catch(() => {});
        await wrapper.vm.$nextTick();
        dashboardComponent = wrapper.findComponent(Dashboard);
        routerPushStub = sinon.stub(router, 'push');
    });
    after(() => {
        routerPushStub.restore();
    })
    it("renders Dashboard component via routing",  () => {
        expect(dashboardComponent.exists()).toBe(true);
    });
    describe("top songs", () => {
        let topSongs;
        let firstSong;
        let songRows;
        let firstRow;
        before(() => {
            const playlist = getters.homepagePlaylist;
            expect(playlist).toBeTruthy();
            topSongs = playlist.songs;
            expect(playlist.songs).toBeTruthy();
            firstSong = getters.getSongsWithAlbums.find(song => song.id === topSongs[0]);
            expect(firstSong).toBeTruthy();
            songRows = wrapper.findAll('.song-list-row');
            expect(songRows).toBeTruthy();
            firstRow = songRows.at(0);
            expect(firstRow).toBeTruthy();
        });

        it('top songs song limit', () => {
            expect(songRows.length).toBe(dashboardComponent.vm.settings.songLimit);
        });

        it('first song title', () => {
            expect(firstRow.find('.song-title').text()).toBe(firstSong.title);
        });

        it('first song duration', () => {             
            const durationString = dashboardComponent.vm.ticksToTimeString(firstSong.duration);
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

        it('route to all songs', async () => {
            dashboardComponent.find('.all-songs-link').trigger('click');
            await wrapper.vm.$nextTick();
            expect(routerPushStub.getCall(0).args[0].path).toStrictEqual('/songs');
            // sinon.assert.calledWith(routerPushStub, '/songs');
            routerPushStub.reset();
        }); 
    });


    describe("albums", () => {
        let albums;
        let firstAlbum;
        let albumCards;
        let firstAlbumCard;
        before(() => {
            albums = getters.albumSet;
            expect(albums).toBeTruthy();
            firstAlbum = albums[0];
                        
            albumCards = wrapper.findAll('.album.card');
            expect(albumCards).toBeTruthy();
            firstAlbumCard = albumCards.at(0);
            expect(firstAlbumCard).toBeTruthy();
        });

        it('albums length', () => {
            expect(albumCards.length).toBe(albums.length);
        });

        it('first album title', () => {
            expect(firstAlbumCard.find('.album-title').text()).toBe(firstAlbum.title);
        });

        it('first album art', () => {
            expect(firstAlbumCard.find('img').attributes('src')).toStrictEqual(expect.stringContaining(firstAlbum.artworkFilename));
        });

        
        it('route to album', async () => {
            firstAlbumCard.trigger('click');
            await wrapper.vm.$nextTick();
            sinon.assert.calledWith(routerPushStub, `/album/${firstAlbum.id}`);
            routerPushStub.reset();
        });            
    });        
});
