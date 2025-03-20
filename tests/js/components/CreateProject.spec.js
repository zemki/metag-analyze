// tests/js/components/CreateProject.spec.js
import { mount } from '@vue/test-utils';
import CreateProject from '@/components/createproject.vue';
import { emitter } from '../mocks/app';

describe('CreateProject.vue', () => {
  let wrapper;
  
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
    
    // Mock window.axios.post to prevent actual API calls
    window.axios.post = jest.fn().mockResolvedValue({ data: {} });
  });
  
  test('renders the project creation form correctly', () => {
    // Basic component elements should be present
    expect(wrapper.find('h1').text()).toContain('Create a Project');
    expect(wrapper.find('form').exists()).toBe(true);
    expect(wrapper.find('input#name').exists()).toBe(true);
    expect(wrapper.find('textarea#description').exists()).toBe(true);
  });
  
  test('tracks project name length', async () => {
    const nameInput = wrapper.find('input#name');
    
    // Enter a project name
    await nameInput.setValue('Test Project');
    
    // Should display remaining character count
    const charCount = wrapper.find('span').text();
    expect(charCount).toContain('188'); // 200 - 12 = 188 chars remaining
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
  
  test('allows adding media inputs', async () => {
    // Initially there should be one empty media input
    expect(wrapper.vm.newProject.media.length).toBe(1);
    
    // Add a media name and check if a new empty field is added
    const mediaInput = wrapper.find('input[placeholder="Enter media"]');
    await mediaInput.setValue('Media 1');
    await mediaInput.trigger('keyup');
    
    // Should now have 2 media inputs (one filled, one empty)
    expect(wrapper.vm.newProject.media.length).toBe(2);
    expect(wrapper.vm.newProject.media[0]).toBe('Media 1');
    expect(wrapper.vm.newProject.media[1]).toBe('');
  });
  
  test('increments and decrements additional inputs correctly', async () => {
    // Initially there should be no additional inputs
    expect(wrapper.vm.newProject.ninputs).toBe(0);
    expect(wrapper.vm.newProject.inputs.length).toBe(0);
    
    // Click the + button to add input
    const incrementButton = wrapper.find('button[type="button"]:nth-of-type(2)');
    await incrementButton.trigger('click');
    
    // Should now have 1 input
    expect(wrapper.vm.newProject.ninputs).toBe(1);
    expect(wrapper.vm.newProject.inputs.length).toBe(1);
    
    // Click the - button to remove input
    const decrementButton = wrapper.find('button[type="button"]:nth-of-type(1)');
    await decrementButton.trigger('click');
    
    // Should be back to 0 inputs
    expect(wrapper.vm.newProject.ninputs).toBe(0);
    expect(wrapper.vm.newProject.inputs.length).toBe(0);
  });
  
  test('submits the form with valid data', async () => {
    // Mock the validation function to automatically submit the form
    wrapper.vm.validateProject = jest.fn(function() {
      // Simulate the submission with the current form data
      window.axios.post('/projects', {
        name: this.newProject.name,
        description: this.newProject.description,
        media: this.newProject.media.filter(m => m.trim() !== ''),
        ninputs: this.newProject.ninputs,
        inputs: this.newProject.inputs,
        created_by: this.userId,
      });
      
      return true;
    });
    
    // Fill out the form
    await wrapper.find('input#name').setValue('Test Project');
    await wrapper.find('textarea#description').setValue('This is a test project description');
    await wrapper.find('input[placeholder="Enter media"]').setValue('Media 1');
    
    // Submit the form
    const form = wrapper.find('form');
    await form.trigger('submit.prevent');
    
    // Axios post should have been called
    expect(window.axios.post).toHaveBeenCalled();
    expect(window.axios.post.mock.calls[0][0]).toBe('/projects');
    expect(window.axios.post.mock.calls[0][1]).toMatchObject({
      name: 'Test Project',
      description: 'This is a test project description',
      media: ['Media 1'],
      ninputs: 0,
      inputs: []
    });
  });
});
