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
