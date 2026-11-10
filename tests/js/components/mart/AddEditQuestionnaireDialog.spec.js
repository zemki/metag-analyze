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

  describe('range values', () => {
    const rangeValuesQuestion = { type: 'rangeValues', answers: ['Low', 'Mid', 'High'] };

    function questionError(fields) {
      return methods.hasQuestionError.call(dialogWith([formQuestion(fields)]), 0);
    }

    // isEditMode skips the schedule fields, so only the question is checked.
    function formIsValid(fields) {
      return AddEditQuestionnaireDialog.computed.isValid.call({
        ...dialogWith([formQuestion(fields)]),
        isEditMode: true,
      });
    }

    it('saves as the range values type', async () => {
      const saved = await savedQuestion(rangeValuesQuestion);

      expect(saved.type).toBe('range values');
    });

    // A slider has a step for every label, so a blank label is a step too.
    it('keeps blank labels as empty steps, in order', async () => {
      const saved = await savedQuestion({ ...rangeValuesQuestion, answers: ['gar nicht', '', ' ', '', 'sehr'] });

      expect(saved.config.options).toEqual(['gar nicht', '', '', '', 'sehr']);
    });

    // The form holds the default's position, so repeated labels can't mix it up.
    it('saves the default as its position', async () => {
      const saved = await savedQuestion({ ...rangeValuesQuestion, defaultValue: 1 });

      expect(saved.config.defaultValue).toBe(1);
    });

    it('saves a default on the first step as 0', async () => {
      const saved = await savedQuestion({ ...rangeValuesQuestion, defaultValue: 0 });

      expect(saved.config.defaultValue).toBe(0);
    });

    it('saves a default on a repeated label at its own position', async () => {
      const saved = await savedQuestion({ ...rangeValuesQuestion, answers: ['-', '-', '-'], defaultValue: 2 });

      expect(saved.config.defaultValue).toBe(2);
    });

    it('saves a default on a blank step', async () => {
      const saved = await savedQuestion({ ...rangeValuesQuestion, answers: ['gar nicht', '', 'sehr'], defaultValue: 1 });

      expect(saved.config.defaultValue).toBe(1);
    });

    // removeOption changes the answers in place, so each test has its own list.
    function afterRemovingStep(defaultValue, removedIndex) {
      const dialog = dialogWith([formQuestion({ type: 'rangeValues', answers: ['Low', 'Mid', 'High'], defaultValue })]);
      methods.removeOption.call(dialog, 0, removedIndex);

      return dialog.formData.questions[0].defaultValue;
    }

    it('moves the default with its step when an earlier step is removed', () => {
      expect(afterRemovingStep(2, 0)).toBe(1);
    });

    it('clears the default when its step is removed', () => {
      expect(afterRemovingStep(1, 1)).toBe('');
    });

    it('keeps the default when a later step is removed', () => {
      expect(afterRemovingStep(0, 2)).toBe(0);
    });

    it('leaves the default out when none is set', async () => {
      const saved = await savedQuestion({ ...rangeValuesQuestion, defaultValue: '' });

      expect(saved.config).not.toHaveProperty('defaultValue');
    });

    it('saves no "other" option', async () => {
      const saved = await savedQuestion({
        ...rangeValuesQuestion,
        includeOtherOption: true,
        otherOptionLabel: 'Something else',
        otherTextType: 'text',
      });

      expect(saved.config).toEqual({ options: ['Low', 'Mid', 'High'] });
    });

    it('saves no jump', async () => {
      const saved = await savedQuestion({ ...rangeValuesQuestion, useJump: true, jumpCondition: 1, jumpOver: 1 });

      expect(saved.config).not.toHaveProperty('jump');
    });

    it('restores the type when a saved question is opened', () => {
      const question = loadedQuestion({ type: 'range values', config: { options: ['Low', 'Mid'] } });

      expect(question.type).toBe('rangeValues');
    });

    it('restores the labels', () => {
      const question = loadedQuestion({ type: 'range values', config: { options: ['Low', 'Mid', 'High'] } });

      expect(question.answers).toEqual(['Low', 'Mid', 'High']);
    });

    it('restores the default as its position', () => {
      const question = loadedQuestion({
        type: 'range values',
        config: { options: ['-', '-', '-'], defaultValue: 2 },
      });

      expect(question.defaultValue).toBe(2);
    });

    it('restores a default on the first step', () => {
      const question = loadedQuestion({
        type: 'range values',
        config: { options: ['Low', 'High'], defaultValue: 0 },
      });

      expect(question.defaultValue).toBe(0);
    });

    // Laravel turns an empty string into null, so a blank label comes back as null.
    it('restores blank labels as empty steps', () => {
      const question = loadedQuestion({ type: 'range values', config: { options: ['gar nicht', null, 'sehr'] } });

      expect(question.answers).toEqual(['gar nicht', '', 'sehr']);
    });

    it('marks a question with fewer than 2 steps', () => {
      expect(questionError({ ...rangeValuesQuestion, answers: ['Only'] })).toBe(true);
    });

    it('cannot be saved with fewer than 2 steps', () => {
      expect(formIsValid({ ...rangeValuesQuestion, answers: ['Only'] })).toBe(false);
    });

    it('can be saved with 2 labels', () => {
      expect(formIsValid({ ...rangeValuesQuestion, answers: ['Low', 'High'] })).toBe(true);
    });

    it('counts a blank label as a step', () => {
      expect(formIsValid({ ...rangeValuesQuestion, answers: ['gar nicht', ''] })).toBe(true);
    });

    it('does not mark a question whose second step is blank', () => {
      expect(questionError({ ...rangeValuesQuestion, answers: ['gar nicht', ' '] })).toBe(false);
    });

    it('offers every step as a default, blank ones too', async () => {
      const wrapper = mount(AddEditQuestionnaireDialog, {
        props: { projectId: 1, nextQuestionnaireId: 1 },
        global: { stubs: { SchedulePreview: true } },
      });
      wrapper.vm.addQuestion();
      Object.assign(wrapper.vm.formData.questions[0], { ...rangeValuesQuestion, answers: ['-', '', '-'] });
      await wrapper.vm.$nextTick();

      const defaultSelect = wrapper.findAll('select').find(select => select.text().includes('No default'));

      expect(defaultSelect.findAll('option').map(option => option.element.value)).toEqual(['', '0', '1', '2']);
    });

    it('shows the labels without randomize or "other" options', async () => {
      const wrapper = mount(AddEditQuestionnaireDialog, {
        props: { projectId: 1, nextQuestionnaireId: 1 },
        global: { stubs: { SchedulePreview: true } },
      });
      wrapper.vm.addQuestion();
      Object.assign(wrapper.vm.formData.questions[0], { ...rangeValuesQuestion });
      await wrapper.vm.$nextTick();

      expect(wrapper.findAll('input[type="text"]').map(input => input.element.value)).toEqual(
        expect.arrayContaining(['Low', 'Mid', 'High']),
      );
      expect(wrapper.find('#randomize-answers-0').exists()).toBe(false);
      expect(wrapper.find('#include-other-0').exists()).toBe(false);
    });
  });

  // The app only shuffles radio and checkbox options.
  describe('randomize answers', () => {
    it('is saved for single choice', async () => {
      const saved = await savedQuestion({ type: 'radio', answers: ['Yes', 'No'], randomizeAnswers: true });

      expect(saved.config.randomizeAnswers).toBe(true);
    });

    it('is saved for multiple choice', async () => {
      const saved = await savedQuestion({ type: 'checkbox', answers: ['Yes', 'No'], randomizeAnswers: true });

      expect(saved.config.randomizeAnswers).toBe(true);
    });

    it('is left out for a slider with labels, where it has no checkbox', async () => {
      const saved = await savedQuestion({ type: 'rangeValues', answers: ['Low', 'High'], randomizeAnswers: true });

      expect(saved.config).not.toHaveProperty('randomizeAnswers');
    });

    it('is left out for types without options', async () => {
      const saved = await savedQuestion({ type: 'text', randomizeAnswers: true });

      expect(saved.config).not.toHaveProperty('randomizeAnswers');
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
