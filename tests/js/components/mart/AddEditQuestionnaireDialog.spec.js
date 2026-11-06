import { mount } from '@vue/test-utils';
import AddEditQuestionnaireDialog from '@/components/mart/AddEditQuestionnaireDialog.vue';

jest.mock('@/app.js', () => ({ emitter: { emit: jest.fn() } }));

const { methods } = AddEditQuestionnaireDialog;

// save() and loadScheduleData() run on a plain object, so these specs cover the
// data going to and from the API without mounting the whole dialog.
function dialogWith(questions) {
  return {
    ...methods,
    isValid: true,
    isEditMode: false,
    projectId: 1,
    formData: { questions },
    $emit: jest.fn(),
  };
}

function formQuestion(fields) {
  return { text: 'Question', mandatory: true, answers: [], config: {}, ...fields };
}

async function savedQuestion(fields) {
  await methods.save.call(dialogWith([formQuestion(fields)]));

  return window.axios.post.mock.calls[0][1].questions[0];
}

function loadedQuestion(backendQuestion) {
  const dialog = dialogWith([]);
  dialog.schedule = { questions: [backendQuestion] };
  methods.loadScheduleData.call(dialog);

  return dialog.formData.questions[0];
}

describe('AddEditQuestionnaireDialog questions', () => {
  beforeEach(() => {
    window.axios = { post: jest.fn().mockResolvedValue({}) };
  });

  describe('"other" option', () => {
    const otherQuestion = {
      type: 'radio',
      answers: ['Yes', 'No'],
      includeOtherOption: true,
    };

    it('saves a multi-line text field', async () => {
      const saved = await savedQuestion({ ...otherQuestion, otherTextType: 'textarea' });

      expect(saved.config.otherTextType).toBe('textarea');
    });

    it('saves a single-line text field', async () => {
      const saved = await savedQuestion({ ...otherQuestion, otherTextType: 'text' });

      expect(saved.config.otherTextType).toBe('text');
    });

    it('leaves the field out without an "other" option', async () => {
      const saved = await savedQuestion({
        ...otherQuestion,
        includeOtherOption: false,
        otherTextType: 'textarea',
      });

      expect(saved.config).not.toHaveProperty('otherTextType');
    });

    it('restores the field when a saved question is opened', () => {
      const question = loadedQuestion({
        type: 'one choice',
        config: { options: ['Yes', 'No'], includeOtherOption: true, otherTextType: 'textarea' },
      });

      expect(question.otherTextType).toBe('textarea');
    });

    it('defaults to a single line for questions saved before this choice existed', () => {
      const question = loadedQuestion({
        type: 'one choice',
        config: { options: ['Yes', 'No'], includeOtherOption: true },
      });

      expect(question.otherTextType).toBe('text');
    });
  });

  describe('default value', () => {
    const choiceQuestion = { type: 'radio', answers: ['Yes', 'No', 'Maybe'] };

    it('saves a range default', async () => {
      const saved = await savedQuestion({ type: 'range', defaultValue: 55 });

      expect(saved.config.defaultValue).toBe(55);
    });

    it('saves a default of 0', async () => {
      const saved = await savedQuestion({ type: 'range', defaultValue: 0 });

      expect(saved.config.defaultValue).toBe(0);
    });

    it('saves a number default', async () => {
      const saved = await savedQuestion({ type: 'number', defaultValue: 4 });

      expect(saved.config.defaultValue).toBe(4);
    });

    it('saves a text default', async () => {
      const saved = await savedQuestion({ type: 'text', defaultValue: 'Nothing yet' });

      expect(saved.config.defaultValue).toBe('Nothing yet');
    });

    it('saves a textarea default', async () => {
      const saved = await savedQuestion({ type: 'textarea', defaultValue: 'Nothing yet' });

      expect(saved.config.defaultValue).toBe('Nothing yet');
    });

    it('saves a choice default as the key of the option', async () => {
      const saved = await savedQuestion({ ...choiceQuestion, defaultValue: 'No' });

      expect(saved.config.defaultValue).toBe(1);
    });

    it('saves a multiple choice default as the key of the option', async () => {
      const saved = await savedQuestion({ ...choiceQuestion, type: 'checkbox', defaultValue: 'Maybe' });

      expect(saved.config.defaultValue).toBe(2);
    });

    it('counts option keys after blank options are dropped', async () => {
      const saved = await savedQuestion({
        ...choiceQuestion,
        answers: ['Yes', ' ', 'No'],
        defaultValue: 'No',
      });

      expect(saved.config.defaultValue).toBe(1);
    });

    it('leaves the default out when none is set', async () => {
      const saved = await savedQuestion({ type: 'range', defaultValue: '' });

      expect(saved.config).not.toHaveProperty('defaultValue');
    });

    it('leaves the default out when its option is gone', async () => {
      const saved = await savedQuestion({ ...choiceQuestion, defaultValue: 'Removed' });

      expect(saved.config).not.toHaveProperty('defaultValue');
    });

    it('leaves the default out on item types without one', async () => {
      const saved = await savedQuestion({ type: 'display', defaultValue: 'Left over' });

      expect(saved.config).not.toHaveProperty('defaultValue');
    });

    it('restores a range default when a saved question is opened', () => {
      const question = loadedQuestion({ type: 'range', config: { defaultValue: 55 } });

      expect(question.defaultValue).toBe(55);
    });

    it('restores a default of 0', () => {
      const question = loadedQuestion({ type: 'range', config: { defaultValue: 0 } });

      expect(question.defaultValue).toBe(0);
    });

    it('restores a choice default as the text of the option', () => {
      const question = loadedQuestion({
        type: 'one choice',
        config: { options: ['Yes', 'No'], defaultValue: 1 },
      });

      expect(question.defaultValue).toBe('No');
    });

    it('restores a default on the first option', () => {
      const question = loadedQuestion({
        type: 'one choice',
        config: { options: ['Yes', 'No'], defaultValue: 0 },
      });

      expect(question.defaultValue).toBe('Yes');
    });

    it('is empty for questions saved without a default', () => {
      const question = loadedQuestion({ type: 'one choice', config: { options: ['Yes', 'No'] } });

      expect(question.defaultValue).toBe('');
    });

    it('is cleared when the question type changes', async () => {
      const wrapper = mount(AddEditQuestionnaireDialog, {
        props: { projectId: 1, nextQuestionnaireId: 1 },
        global: { stubs: { SchedulePreview: true } },
      });
      wrapper.vm.addQuestion();
      Object.assign(wrapper.vm.formData.questions[0], { type: 'text', defaultValue: 'Nothing yet' });
      await wrapper.vm.$nextTick();

      const typeSelect = wrapper.findAll('select').find(select => select.find('option[value="display"]').exists());
      await typeSelect.setValue('range');

      expect(wrapper.vm.formData.questions[0].defaultValue).toBe('');
    });
  });

  // config keeps the option's key. The API adds 1, which gives the option number.
  describe('jump', () => {
    const jumpQuestion = { type: 'radio', answers: ['Yes', 'No'], useJump: true, jumpOver: 1 };

    it('saves the first option as key 0', async () => {
      const saved = await savedQuestion({ ...jumpQuestion, jumpCondition: 1 });

      expect(saved.config.jump.jumpCondition).toBe('0');
    });

    it('saves a later option as its key', async () => {
      const saved = await savedQuestion({ ...jumpQuestion, jumpCondition: 2 });

      expect(saved.config.jump.jumpCondition).toBe('1');
    });

    it('restores the option number when a saved question is opened', () => {
      const question = loadedQuestion({
        type: 'one choice',
        config: { options: ['Yes', 'No'], jump: { jumpCondition: '1', jumpOver: 1 } },
      });

      expect(question.jumpCondition).toBe(2);
    });
  });
});
