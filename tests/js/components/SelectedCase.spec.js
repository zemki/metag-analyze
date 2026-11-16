import { shallowMount } from '@vue/test-utils';
import SelectedCase from '@/components/selected-case.vue';

// The server decides whether a project is MART. A project's questions can come
// before the MART marker in `inputs`, so the marker's position says nothing.
describe('selected-case', () => {
  const mountCase = (isMart) => shallowMount(SelectedCase, {
    props: {
      cases: {
        id: 1,
        name: 'P1',
        consultable: false,
        entries: [],
        project: { id: 1, is_mart_project: isMart },
      },
      projectInputs: [{ name: 'Mood', type: 'text' }, { type: 'mart' }],
    },
  });

  it('uses the server flag on a MART case, wherever the marker sits', () => {
    const state = mountCase(true).findComponent({ name: 'NotConsultableState' });

    expect(state.props('reason')).toBe('mart-active');
  });

  it('uses the server flag on a MeTag case', () => {
    const state = mountCase(false).findComponent({ name: 'NotConsultableState' });

    expect(state.props('reason')).toBe('active');
  });
});

// MART submissions get their own read-only table with the answers; MeTag
// entries keep EntryTable with its columns, Edit and Delete.
describe('selected-case entries table', () => {
  const mountConsultable = (isMart) => shallowMount(SelectedCase, {
    props: {
      cases: {
        id: 1,
        name: 'P1',
        consultable: true,
        entries: [{ id: 5, begin: '2025-01-01 10:00:00', end: '2025-01-01 10:01:00', inputs: {} }],
        project: { id: 1, is_mart_project: isMart },
      },
      projectInputs: [{ name: 'Mood', type: 'text' }],
    },
  });

  it('shows a MART case in the MART table', () => {
    expect(mountConsultable(true).findComponent({ name: 'MartEntryTable' }).exists()).toBe(true);
  });

  it('keeps the MeTag table away from a MART case', () => {
    expect(mountConsultable(true).findComponent({ name: 'EntryTable' }).exists()).toBe(false);
  });

  it('shows a MeTag case in the MeTag table', () => {
    expect(mountConsultable(false).findComponent({ name: 'EntryTable' }).exists()).toBe(true);
  });
});
