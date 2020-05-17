import { mount } from '@vue/test-utils';
import PageBottom from '../src/components/layout/PageBottom.vue';

const wrapper = mount(PageBottom);

describe('PageBottom test', () => {
    it('Displays copywrite info with current year', () => {
          const year = new Date().getFullYear().toString();
          expect(wrapper.vm.$data.copyright).toEqual(expect.stringContaining(year));
    });
});