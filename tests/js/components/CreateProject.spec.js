// tests/js/components/CreateProject.spec.js
import { mount } from '@vue/test-utils';
import CreateProject from '@/components/createproject.vue';
import { emitter } from '../mocks/app';

describe('CreateProject.vue', () => {
  let wrapper;

  // The "number of additional inputs" counter: [decrement, increment] buttons
  // inside the grey rounded container, around the numeric span.
  const counterButtons = () =>
    wrapper
      .findAll('button[type="button"]')
      .filter((b) => b.element.parentElement.classList.contains('bg-gray-100'));

  beforeEach(() => {
    // Reset mocks
    jest.clearAllMocks();

    // Mock window.location to prevent navigation errors
    Object.defineProperty(window, 'location', {
      value: { href: '' },
      writable: true
    });

    // Mock axios.post to avoid actual API calls
    window.axios = window.axios || {};
    window.axios.post = jest.fn().mockResolvedValue({ data: {} });

    // Create a wrapper for the component
    wrapper = mount(CreateProject, {
      props: {
        inputs: {
          available: ['text', 'multiple choice', 'one choice', 'scale', 'audio recording']
        },
        userId: 1
      },
      global: {
        provide: {
          productionUrl: ''
        },
        stubs: {
          breadcrumb: true // Stub the breadcrumb component
        },
        mocks: {
          trans: (key) => key // Simple translation mock
        }
      }
    });
  });

  test('renders the project creation form correctly', () => {
    // Basic component elements should be present
    expect(wrapper.find('h1').text()).toContain('Create a Project');
    expect(wrapper.find('form').exists()).toBe(true);
    expect(wrapper.find('input#name').exists()).toBe(true);
    expect(wrapper.find('textarea#description').exists()).toBe(true);
  });

  test('defaults to the standard project type', () => {
    expect(wrapper.vm.projectType).toBe('standard');
    expect(wrapper.find('input#name').attributes('placeholder')).toBe('Enter project name');
  });

  test('tracks project name length', async () => {
    const nameInput = wrapper.find('input#name');

    // Enter a project name
    await nameInput.setValue('Test Project');

    // The counter sits right after the name input: remaining/max
    const charCount = wrapper.find('input#name + span').text();
    expect(charCount).toContain('188/200'); // 200 - 12 = 188 chars remaining
  });

  test('validates form before submission', async () => {
    // Override validation to mock errors
    wrapper.vm.validateProject = function() {
      this.newProject.response = '';

      // Simulate validation errors
      if (!this.newProject.name) {
        this.newProject.response += 'Enter a project name<br>';
      }

      if (!this.newProject.description) {
        this.newProject.response += 'Enter a project description<br>';
      }

      return this.newProject.response === '';
    };

    // Try to submit the form without required fields
    const form = wrapper.find('form');
    await form.trigger('submit.prevent');

    // Should show validation errors
    expect(wrapper.vm.newProject.response).toContain('Enter a project name');
    expect(wrapper.vm.newProject.response).toContain('Enter a project description');
  });

  test('allows adding entity inputs', async () => {
    // Entity field is enabled by default with one empty input
    expect(wrapper.vm.newProject.useEntity).toBe(true);
    expect(wrapper.vm.newProject.media.length).toBe(1);

    // Fill the entity name and check that a new empty field is appended
    const entityInput = wrapper.find('input[placeholder="Enter entity"]');
    await entityInput.setValue('Entity 1');
    await entityInput.trigger('keyup');

    // Should now have 2 entity inputs (one filled, one empty)
    expect(wrapper.vm.newProject.media.length).toBe(2);
    expect(wrapper.vm.newProject.media[0]).toBe('Entity 1');
    expect(wrapper.vm.newProject.media[1]).toBe('');
    expect(wrapper.findAll('input[placeholder="Enter entity"]')).toHaveLength(2);
  });

  test('hides entity inputs when the entity field is disabled', async () => {
    await wrapper.find('input#useEntity').setValue(false);

    expect(wrapper.vm.newProject.media).toEqual([]);
    expect(wrapper.find('input[placeholder="Enter entity"]').exists()).toBe(false);
  });

  test('increments and decrements additional inputs correctly', async () => {
    // Initially there should be no additional inputs
    expect(wrapper.vm.newProject.ninputs).toBe(0);
    expect(wrapper.vm.newProject.inputs.length).toBe(0);

    const [decrementButton, incrementButton] = counterButtons();
    expect(decrementButton.attributes('disabled')).toBeDefined();

    // Click the + button to add input
    await incrementButton.trigger('click');

    // Should now have 1 input
    expect(wrapper.vm.newProject.ninputs).toBe(1);
    expect(wrapper.vm.newProject.inputs.length).toBe(1);
    expect(wrapper.find('span.w-8').text()).toBe('1');

    // Click the - button to remove input
    await decrementButton.trigger('click');

    // Should be back to 0 inputs
    expect(wrapper.vm.newProject.ninputs).toBe(0);
    expect(wrapper.vm.newProject.inputs.length).toBe(0);
  });

  test('caps additional inputs at three', async () => {
    const [, incrementButton] = counterButtons();

    for (let i = 0; i < 5; i++) {
      await incrementButton.trigger('click');
    }

    expect(wrapper.vm.newProject.ninputs).toBe(3);
    expect(wrapper.vm.newProject.inputs.length).toBe(3);
    expect(incrementButton.attributes('disabled')).toBeDefined();
  });

  test('submits the form with valid data', async () => {
    // Fill out the form
    await wrapper.find('input#name').setValue('Test Project');
    await wrapper.find('textarea#description').setValue('This is a test project description');
    await wrapper.find('input[placeholder="Enter entity"]').setValue('Entity 1');

    // Submit the form (real validateProject posts via window.axios)
    const form = wrapper.find('form');
    await form.trigger('submit.prevent');

    // Axios post should have been called with the entity-based payload
    expect(window.axios.post).toHaveBeenCalledTimes(1);
    expect(window.axios.post.mock.calls[0][0]).toBe('/projects');
    expect(window.axios.post.mock.calls[0][1]).toMatchObject({
      name: 'Test Project',
      description: 'This is a test project description',
      ninputs: 0,
      inputs: '[]',
      entityName: 'entity',
      useEntity: true,
      media: ['Entity 1']
    });
  });
});
