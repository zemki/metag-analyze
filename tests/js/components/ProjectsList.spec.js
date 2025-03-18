import { shallowMount } from '@vue/test-utils';
import ProjectsList from '@/components/projects-list.vue';
import Modal from '@/components/global/modal.vue';

// Mock data for testing
const mockProjects = JSON.stringify([
  {
    id: 1,
    name: 'Test Project',
    description: 'This is a test project',
    authiscreator: true,
    entries: 5,
    casescount: 2,
    editable: true,
    cases: []
  },
  {
    id: 2,
    name: 'Test Project 2',
    description: 'This is another test project',
    authiscreator: false,
    owner: 'John Doe',
    entries: 3,
    casescount: 1,
    editable: false,
    cases: []
  }
]);

const mockUser = JSON.stringify({
  id: 1,
  email: 'test@example.com'
});

// Create a component stub
const ProjectsListStub = {
  template: ProjectsList.template,
  props: ProjectsList.props,
  components: { Modal },
  data() {
    return {
      search: "",
      loggedUser: JSON.parse(this.user),
      onlyInvitation: false,
      leaveProjectStudyId: null,
      leaveProjectUserId: null,
      duplicateProjectId: null,
      duplicateProjectName: "",
      showDeleteProjectModal: false,
      deleteProjectId: null,
      deleteProjectName: "",
      showLeaveProjectModal: false,
      showDuplicateProjectModal: false,
      loading: false,
      message: ""
    };
  },
  computed: {
    filteredList() {
      return JSON.parse(this.projects).filter((project) => {
        if (this.onlyInvitation) {
          return (
            project.name.toLowerCase().includes(this.search.toLowerCase()) &&
            !project.authiscreator
          );
        } else {
          return project.name.toLowerCase().includes(this.search.toLowerCase());
        }
      });
    },
    invitesExists() {
      return this.filteredList.some((s) => !s.authiscreator);
    }
  },
  methods: {
    trans(key) { return key; },
    showSnackbarMessage: jest.fn(),
    confirmLeaveProject: jest.fn(),
    detachUser: jest.fn(),
    closeLeaveProjectModal: jest.fn(),
    closeDuplicateModal: jest.fn(),
    confirmduplicate: jest.fn(),
    duplicatestudy: jest.fn(),
    confirmDelete: jest.fn(),
    deleteStudy: jest.fn(),
    closeDeleteProjectModal: jest.fn()
  }
};

describe('ProjectsList.vue', () => {
  let wrapper;
  
  beforeEach(() => {
    // Create a fresh wrapper before each test
    wrapper = shallowMount(ProjectsListStub, {
      props: {
        projects: mockProjects,
        user: mockUser
      },
      global: {
        provide: {
          productionUrl: ''
        }
      }
    });
  });
  
  test('renders correctly', () => {
    expect(wrapper.html()).toContain('Test Project');
    expect(wrapper.html()).toContain('Test Project 2');
    expect(wrapper.html()).toContain('This is a test project');
  });

  test('filters projects when searching', async () => {
    // Initially show all projects
    expect(wrapper.findAll('li')).toHaveLength(2);
    
    // Set search to filter projects
    await wrapper.setData({ search: 'Project 2' });
    
    // Should now only show one project
    const listItems = wrapper.findAll('li');
    expect(listItems).toHaveLength(1);
    expect(listItems[0].html()).toContain('Test Project 2');
  });

  test('shows "Invited By" for projects where user is not creator', () => {
    const invitedByElement = wrapper.find('.bg-blue-100.text-blue-700');
    expect(invitedByElement.exists()).toBe(true);
    expect(invitedByElement.text()).toContain('Invited By John Doe');
  });

  test('shows "Delete Project" option only for projects created by user', () => {
    const deleteButtons = wrapper.findAll('a').filter(node => 
      node.text().includes('Delete Project')
    );
    
    // We should only have one delete button (for the project where authiscreator is true)
    expect(deleteButtons).toHaveLength(1);
  });

  test('shows "Leave Project" option only for projects not created by user', () => {
    const leaveButtons = wrapper.findAll('a').filter(node => 
      node.text().includes('Leave Project')
    );
    
    // We should only have one leave button (for the project where authiscreator is false)
    expect(leaveButtons).toHaveLength(1);
  });

  test('toggles the Invitation filter when "Only Invitations" is checked', async () => {
    // Initially show all projects
    expect(wrapper.findAll('li')).toHaveLength(2);
    
    // Check the "Only Invitations" checkbox
    const checkbox = wrapper.find('input[id="invites"]');
    await checkbox.setValue(true);
    
    // Should now only show projects where user is not the creator
    const listItems = wrapper.findAll('li');
    expect(listItems).toHaveLength(1);
    expect(listItems[0].html()).toContain('Test Project 2');
    expect(listItems[0].html()).toContain('Invited By John Doe');
  });

  test('calls confirmDelete when clicking "Delete Project"', async () => {
    const spy = jest.spyOn(wrapper.vm, 'confirmDelete');
    // Find and click the delete button
    const deleteButton = wrapper.findAll('a').find(node => 
      node.text().includes('Delete Project')
    );
    await deleteButton.trigger('click');
    
    // Check that confirmDelete was called
    expect(spy).toHaveBeenCalled();
  });
});
