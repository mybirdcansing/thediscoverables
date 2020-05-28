import "regenerator-runtime";
import { mount } from '@vue/test-utils';
import SongList from '../src/components/layout/SongList.vue';
import store from '../src/store/store';
import catalogData from './mocks/catalog.mock.json';

const wrapperFactory = (propsData) => {
    return mount(SongList, {
        propsData: propsData
    })
}

describe('SongList', async () => {
    store.commit('SET_CATALOG', catalogData);
    const songs = await store.getters.getSongsWithAlbums;
    describe('Displays active song with index bullet',  () => {
        const activeSong = songs[1];
        let propsData = {
            songs: songs,
            activeSong: activeSong,
            loadingState: false,
            playing: true,
            bullet: 'index',
            showAlbumLink: false
        };
        let wrapper = wrapperFactory(propsData);
        const activeSongSelector = ".active-song";
        it('title', () => {
            expect(wrapper.find(`${activeSongSelector} .song-title`).text()).toBe(activeSong.title);
        });
        it('duration', () => {
            const durationString = wrapper.vm.ticksToTimeString(activeSong.duration)
            expect(wrapper.find(`${activeSongSelector} .song-duration-cell`).text()).toBe(durationString);
        });
        it('bullet', () => {
            expect(wrapper.find(`${activeSongSelector} .song-list-bullet`).text()).toBe('2');
        })

    });
});