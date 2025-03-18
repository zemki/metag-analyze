import { mount } from '@vue/test-utils';
import Snackbar from '@/components/global/snackbar.vue';

describe('Snackbar.vue', () => {
  it('does not display when first mounted', () => {
    const wrapper = mount(Snackbar);
    expect(wrapper.find('div').exists()).toBe(false);
  });

  it('displays when show() is called', async () => {
    const wrapper = mount(Snackbar, {
      props: {
        message: 'Test Message',
        duration: 100 // Short duration for testing
      }
    });
    
    // Call the show method
    wrapper.vm.show();
    await wrapper.vm.$nextTick();
    
    // Snackbar should be visible
    expect(wrapper.find('div').exists()).toBe(true);
    expect(wrapper.find('div').text()).toBe('Test Message');
    
    // Wait for it to disappear (duration + small buffer)
    await new Promise(resolve => setTimeout(resolve, 150));
    await wrapper.vm.$nextTick();
    
    // Snackbar should be hidden again
    expect(wrapper.find('div').exists()).toBe(false);
  });

  it('uses the default duration when no duration is provided', () => {
    const wrapper = mount(Snackbar);
    expect(wrapper.props('duration')).toBe(3000);
  });

  it('accepts a custom duration', () => {
    const wrapper = mount(Snackbar, {
      props: {
        duration: 5000
      }
    });
    expect(wrapper.props('duration')).toBe(5000);
  });

  it('displays the provided message', async () => {
    const wrapper = mount(Snackbar, {
      props: {
        message: 'Custom Message'
      }
    });
    
    wrapper.vm.show();
    await wrapper.vm.$nextTick();
    
    expect(wrapper.find('div').text()).toBe('Custom Message');
  });
});
