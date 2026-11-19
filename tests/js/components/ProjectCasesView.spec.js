import { shallowMount } from '@vue/test-utils';
import ProjectCasesView from '@/components/ProjectCasesView.vue';

// The server refuses the treemap for MART projects, so the menu must not offer
// it. A project's questions can come before the MART marker in `inputs`.
describe('ProjectCasesView actions menu', () => {
  beforeEach(() => {
    window.axios = { get: jest.fn(() => new Promise(() => {})) };
  });

  const treemapLink = async (projectInputs) => {
    const wrapper = shallowMount(ProjectCasesView, {
      props: { project: { id: 1, name: 'Study', description: 'A study' }, projectInputs },
    });
    await wrapper.setData({ actionsDropdownOpen: true });

    return wrapper.find('a[href$="/treemap"]');
  };

  it('offers the treemap on a MeTag project', async () => {
    const link = await treemapLink(JSON.stringify([{ name: 'Mood', type: 'text' }]));

    expect(link.exists()).toBe(true);
  });

  it('hides the treemap on a MART project', async () => {
    const link = await treemapLink(JSON.stringify([{ type: 'mart' }]));

    expect(link.exists()).toBe(false);
  });

  it('hides the treemap wherever the MART marker sits', async () => {
    const link = await treemapLink(JSON.stringify([{ name: 'Mood', type: 'text' }, { type: 'mart' }]));

    expect(link.exists()).toBe(false);
  });
});

// MART questions live in the MART database, so the project page offers their
// list, with the values the answers are stored as. MeTag keeps its own views.
describe('ProjectCasesView MART questions', () => {
  beforeEach(() => {
    window.axios = { get: jest.fn(() => new Promise(() => {})) };
  });

  const questionsButton = (projectInputs) => shallowMount(ProjectCasesView, {
    props: { project: { id: 1, name: 'Study', description: 'A study' }, projectInputs },
  }).find('[data-test="mart-questions"]');

  it('offers the questions on a MART project', () => {
    expect(questionsButton(JSON.stringify([{ type: 'mart' }])).exists()).toBe(true);
  });

  it('offers no MART questions on a MeTag project', () => {
    expect(questionsButton(JSON.stringify([{ name: 'Mood', type: 'text' }])).exists()).toBe(false);
  });

  it('opens the list from the button', async () => {
    const wrapper = shallowMount(ProjectCasesView, {
      props: { project: { id: 1, name: 'Study', description: 'A study' }, projectInputs: JSON.stringify([{ type: 'mart' }]) },
    });

    await wrapper.find('[data-test="mart-questions"]').trigger('click');

    expect(wrapper.findComponent({ name: 'MartQuestionsModal' }).exists()).toBe(true);
  });
});

// The case rows and the case strip take their MeTag-only actions (QR code login,
// close early) from these capabilities. MART participants sign in with email and
// password, so a MART project offers no QR code.
describe('ProjectCasesView capabilities', () => {
  beforeEach(() => {
    window.axios = { get: jest.fn(() => new Promise(() => {})) };
  });

  const capabilities = (projectInputs) => shallowMount(ProjectCasesView, {
    props: { project: { id: 1, name: 'Study', description: 'A study' }, projectInputs },
  }).vm.capabilities;

  it('offers the QR code on a MeTag project', () => {
    expect(capabilities(JSON.stringify([{ name: 'Mood', type: 'text' }])).showQRAction).toBe(true);
  });

  it('offers no QR code on a MART project', () => {
    expect(capabilities(JSON.stringify([{ type: 'mart' }])).showQRAction).toBe(false);
  });

  it('offers no early close on a MART project', () => {
    expect(capabilities(JSON.stringify([{ type: 'mart' }])).showCloseEarlyAction).toBe(false);
  });

  it('marks the project it gives the case rows as MART', () => {
    const wrapper = shallowMount(ProjectCasesView, {
      props: { project: { id: 1, name: 'Study', description: 'A study' }, projectInputs: JSON.stringify([{ type: 'mart' }]) },
    });

    expect(wrapper.vm.localProject.is_mart_project).toBe(true);
  });
});

// Participants type the project ID into the MART app to log in.
describe('ProjectCasesView project ID', () => {
  beforeEach(() => {
    window.axios = { get: jest.fn(() => new Promise(() => {})) };
  });

  const projectId = (projectInputs) => shallowMount(ProjectCasesView, {
    props: { project: { id: 42, name: 'Study', description: 'A study' }, projectInputs },
  }).find('[data-test="project-id"]');

  it('shows the project ID on a MART project', () => {
    expect(projectId(JSON.stringify([{ type: 'mart' }])).text()).toBe('42');
  });

  it('shows no project ID on a MeTag project', () => {
    expect(projectId(JSON.stringify([{ name: 'Mood', type: 'text' }])).exists()).toBe(false);
  });
});
