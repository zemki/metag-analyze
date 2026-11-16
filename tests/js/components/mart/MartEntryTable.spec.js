import { mount } from '@vue/test-utils';
import MartEntryTable from '@/components/mart/MartEntryTable.vue';

// A MART submission as Cases::loadMartEntries sends it to the case page.
const submission = (overrides = {}) => ({
  id: 7,
  begin: '2025-01-01 10:00:00',
  end: '2025-01-01 10:01:05',
  mart_questionnaire: 'Morning check-in',
  mart_duration_ms: 65000,
  mart_answers: [
    { question: 'How are you?', value: '2', label: 'Gut' },
    { question: 'Anything else?', value: '', label: null },
    { question: 'Your name?', value: 'Ada', label: null },
  ],
  ...overrides,
});

const table = (entries = [submission()]) => mount(MartEntryTable, { props: { entries } });

describe('MartEntryTable', () => {
  it('names the questionnaire of each submission', () => {
    expect(table().text()).toContain('Morning check-in');
  });

  it('shows the duration the app measured, with seconds', () => {
    expect(table().text()).toContain('1m 05s');
  });

  it('counts the answered questions', () => {
    expect(table().text()).toContain('2 / 3');
  });

  it('hides the answers until the row is opened', () => {
    expect(table().text()).not.toContain('How are you?');
  });

  it('shows a choice answer as its label with the value the export writes', async () => {
    const wrapper = table();
    await wrapper.find('button[aria-expanded]').trigger('click');

    expect(wrapper.text()).toContain('Gut (2)');
  });

  it('shows an answer without a label as its value', async () => {
    const wrapper = table();
    await wrapper.find('button[aria-expanded]').trigger('click');

    expect(wrapper.find('[data-question="Your name?"]').text()).toBe('Ada');
  });

  it('marks a skipped question with a dash', async () => {
    const wrapper = table();
    await wrapper.find('button[aria-expanded]').trigger('click');

    expect(wrapper.find('[data-question="Anything else?"]').text()).toBe('—');
  });

  it('says so when a submission has no stored answers', async () => {
    const wrapper = table([submission({ mart_answers: [] })]);
    await wrapper.find('button[aria-expanded]').trigger('click');

    expect(wrapper.text()).toContain('No answers stored');
  });
});
