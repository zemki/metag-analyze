import { mount } from '@vue/test-utils';
import EntryTable from '@/components/global/EntryTable.vue';

// MART rows are built from mart_entries, so their ids mean nothing to the
// entries routes: editing or deleting one would hit an unrelated row.
describe('EntryTable', () => {
  const entries = [{ id: 7, begin: '2026-05-01 10:00:00', end: '2026-05-01 10:05:00', inputs: {} }];

  const mountTable = (project) => mount(EntryTable, {
    props: { entries, projectInputs: [], project },
  });

  it('offers Edit and Delete on a MeTag case', () => {
    const wrapper = mountTable({ is_mart_project: false });

    expect(wrapper.find('[title="Edit entry"]').exists()).toBe(true);
    expect(wrapper.find('[title="Delete entry"]').exists()).toBe(true);
  });

  it('hides Edit on a MART case', () => {
    const wrapper = mountTable({ is_mart_project: true });

    expect(wrapper.find('[title="Edit entry"]').exists()).toBe(false);
  });

  it('hides Delete on a MART case', () => {
    const wrapper = mountTable({ is_mart_project: true });

    expect(wrapper.find('[title="Delete entry"]').exists()).toBe(false);
  });

  it('does not open the editor on double click on a MART case', async () => {
    const wrapper = mountTable({ is_mart_project: true });

    await wrapper.find('tbody tr').trigger('dblclick');

    expect(wrapper.emitted('edit')).toBeUndefined();
  });

  it('still opens the editor on double click on a MeTag case', async () => {
    const wrapper = mountTable({ is_mart_project: false });

    await wrapper.find('tbody tr').trigger('dblclick');

    expect(wrapper.emitted('edit')).toHaveLength(1);
  });
});
