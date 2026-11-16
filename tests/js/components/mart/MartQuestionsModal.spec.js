import { mount, flushPromises } from '@vue/test-utils';
import MartQuestionsModal from '@/components/mart/MartQuestionsModal.vue';

// A questionnaire as MartCodebook sends it to the project page.
const questionnaire = (questions) => ({ questionnaire_id: 1, name: 'Evening check-in', questions });

const question = (overrides = {}) => ({
  item: 1,
  text: 'Where did you spend most of your day?',
  type: 'one choice',
  type_label: 'Single choice',
  required: true,
  version: 1,
  values: [{ value: 1, label: 'At home' }, { value: 2, label: 'At work' }],
  answer: '1 = At home; 2 = At work',
  ...overrides,
});

async function modalWith(questionnaires) {
  window.axios = { get: jest.fn().mockResolvedValue({ data: { questionnaires } }) };
  const wrapper = mount(MartQuestionsModal, { props: { projectId: 11 } });
  await flushPromises();

  return wrapper;
}

describe('MartQuestionsModal', () => {
  it("loads the project's questions", async () => {
    await modalWith([]);

    expect(window.axios.get).toHaveBeenCalledWith('/projects/11/questionnaires/codebook');
  });

  it('names each questionnaire', async () => {
    const wrapper = await modalWith([questionnaire([question()])]);

    expect(wrapper.text()).toContain('Evening check-in');
  });

  it('shows each question with its type', async () => {
    const wrapper = await modalWith([questionnaire([question()])]);

    expect(wrapper.find('[data-question]').text()).toContain('Single choice');
  });

  it('lists each option with the value its answers are stored as', async () => {
    const wrapper = await modalWith([questionnaire([question()])]);

    expect(wrapper.findAll('[data-value]').map((option) => option.text())).toEqual(['1 At home', '2 At work']);
  });

  it('says the typed answer follows the "other" option', async () => {
    const wrapper = await modalWith([questionnaire([question({ values: [{ value: 'text', label: 'Somewhere else' }] })])]);

    expect(wrapper.find('[data-value]').text()).toBe('text Somewhere else, followed by the typed answer');
  });

  it('describes a question without options in one line', async () => {
    const wrapper = await modalWith([questionnaire([question({ type: 'range', type_label: 'Slider', values: [], answer: 'From 1 to 10' })])]);

    expect(wrapper.find('[data-question]').text()).toContain('From 1 to 10');
  });

  it('marks an optional question', async () => {
    const wrapper = await modalWith([questionnaire([question({ required: false })])]);

    expect(wrapper.find('[data-question]').text()).toContain('Optional');
  });

  it('does not call a text-only item optional', async () => {
    const wrapper = await modalWith([questionnaire([question({ type: 'display', type_label: 'Text only', required: false, values: [], answer: 'No answer (text only)' })])]);

    expect(wrapper.find('[data-question]').text()).not.toContain('Optional');
  });

  it('warns that earlier answers to an edited question may use earlier options', async () => {
    const wrapper = await modalWith([questionnaire([question({ version: 3 })])]);

    expect(wrapper.find('[data-question]').text()).toContain('Edited (version 3)');
  });

  it('says so when loading fails', async () => {
    window.axios = { get: jest.fn().mockRejectedValue(new Error('500')) };
    const wrapper = mount(MartQuestionsModal, { props: { projectId: 11 } });
    await flushPromises();

    expect(wrapper.text()).toContain('The questions could not be loaded.');
  });

  it('closes from its close button', async () => {
    const wrapper = await modalWith([]);

    await wrapper.find('button[aria-label="Close"]').trigger('click');

    expect(wrapper.emitted('close')).toHaveLength(1);
  });
});
