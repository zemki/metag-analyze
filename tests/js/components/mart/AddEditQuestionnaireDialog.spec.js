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

// Opens a saved question, lets the test change it as the researcher would,
// then saves it again.
async function savedAfterLoading(backendQuestion, change = () => {}) {
  const question = loadedQuestion(backendQuestion);
  change(question);
  await methods.save.call(dialogWith([question]));

  return window.axios.post.mock.calls[0][1].questions[0];
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

  describe('file upload', () => {
    // The extensions whose real files MartFileUploadTest uploads. No .svg: an
    // inline SVG can run script on our origin.
    const SERVER_FORMATS = [
      '.jpg', '.jpeg', '.png', '.gif', '.webp', '.heic', '.heif',
      '.pdf',
      '.mp4', '.mov', '.webm', '.3gp', '.avi',
      '.mp3', '.m4a', '.ogg', '.flac', '.wav', '.aac',
    ];

    function fileQuestion(fileUploadOptions = {}) {
      return {
        type: 'fileUpload',
        config: { fileUploadOptions: { maxFileSize: 20, allowedFormats: ['.jpg', '.pdf'], ...fileUploadOptions } },
      };
    }

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

    it('saves as the file type', async () => {
      const saved = await savedQuestion(fileQuestion());

      expect(saved.type).toBe('file');
    });

    it('saves the size and the formats as fileUploadOptions', async () => {
      const saved = await savedQuestion(fileQuestion());

      expect(saved.config).toEqual({ fileUploadOptions: { maxFileSize: 20, allowedFormats: ['.jpg', '.pdf'] } });
    });

    it('saves the formats in the order of the list', async () => {
      const saved = await savedQuestion(fileQuestion({ allowedFormats: ['.pdf', '.mp4', '.jpg'] }));

      expect(saved.config.fileUploadOptions.allowedFormats).toEqual(['.jpg', '.pdf', '.mp4']);
    });

    it('drops a saved format the server does not take', async () => {
      const saved = await savedQuestion(fileQuestion({ allowedFormats: ['.jpg', '.svg'] }));

      expect(saved.config.fileUploadOptions.allowedFormats).toEqual(['.jpg']);
    });

    it('restores the type when a saved question is opened', () => {
      const question = loadedQuestion({ type: 'file', config: fileQuestion().config });

      expect(question.type).toBe('fileUpload');
    });

    it('restores the size and the formats', () => {
      const question = loadedQuestion({ type: 'file', config: fileQuestion().config });

      expect(question.config.fileUploadOptions).toEqual({ maxFileSize: 20, allowedFormats: ['.jpg', '.pdf'] });
    });

    it('starts a new question at 20 MB with every format ticked', () => {
      const dialog = dialogWith([]);
      methods.addQuestion.call(dialog);

      expect(dialog.formData.questions[0].config.fileUploadOptions).toEqual({
        maxFileSize: 20,
        allowedFormats: SERVER_FORMATS,
      });
    });

    it('offers exactly the formats the server takes', async () => {
      const wrapper = mount(AddEditQuestionnaireDialog, {
        props: { projectId: 1, nextQuestionnaireId: 1 },
        global: { stubs: { SchedulePreview: true } },
      });
      wrapper.vm.addQuestion();
      wrapper.vm.formData.questions[0].type = 'fileUpload';
      await wrapper.vm.$nextTick();

      const offered = wrapper.findAll('input[type="checkbox"]')
        .map(input => input.element.value)
        .filter(value => value.startsWith('.'));

      expect(offered).toEqual(SERVER_FORMATS);
    });

    it('marks a question without a format', () => {
      expect(questionError(fileQuestion({ allowedFormats: [] }))).toBe(true);
    });

    it('marks a question without a size', () => {
      expect(questionError(fileQuestion({ maxFileSize: '' }))).toBe(true);
    });

    it('marks a size over 50 MB', () => {
      expect(questionError(fileQuestion({ maxFileSize: 51 }))).toBe(true);
    });

    it('marks a size under 1 MB', () => {
      expect(questionError(fileQuestion({ maxFileSize: 0 }))).toBe(true);
    });

    it('does not mark a question with a size of 50 MB and one format', () => {
      expect(questionError(fileQuestion({ maxFileSize: 50, allowedFormats: ['.pdf'] }))).toBe(false);
    });

    it('cannot be saved without a format', () => {
      expect(formIsValid(fileQuestion({ allowedFormats: [] }))).toBe(false);
    });

    it('cannot be saved with only a format the server does not take', () => {
      expect(formIsValid(fileQuestion({ allowedFormats: ['.svg'] }))).toBe(false);
    });

    it('can be saved with a size and a format', () => {
      expect(formIsValid(fileQuestion())).toBe(true);
    });

    // The server takes a size saved as text and formats in another spelling,
    // so a question saved like that has to open without errors.
    describe('saved in another spelling', () => {
      const backendQuestion = {
        text: 'Upload your screenshot',
        type: 'file',
        config: { fileUploadOptions: { maxFileSize: '20', allowedFormats: ['PDF', 'jpg'] } },
      };

      it('opens with the size as a number', () => {
        expect(loadedQuestion(backendQuestion).config.fileUploadOptions.maxFileSize).toBe(20);
      });

      it('opens with each format as the list writes it', () => {
        expect(loadedQuestion(backendQuestion).config.fileUploadOptions.allowedFormats).toEqual(['.pdf', '.jpg']);
      });

      it('shows the size and ticks its formats', async () => {
        const wrapper = mount(AddEditQuestionnaireDialog, {
          props: { projectId: 1, nextQuestionnaireId: 1, schedule: { id: 7, questions: [backendQuestion] } },
          global: { stubs: { SchedulePreview: true } },
        });
        await wrapper.vm.$nextTick();

        const ticked = wrapper.findAll('input[type="checkbox"]')
          .filter(input => input.element.value.startsWith('.') && input.element.checked)
          .map(input => input.element.value);

        expect([wrapper.find('#file_max_size_0').element.value, ticked]).toEqual(['20', ['.jpg', '.pdf']]);
      });

      it('has no question error', () => {
        const dialog = dialogWith([loadedQuestion(backendQuestion)]);

        expect(methods.hasQuestionError.call(dialog, 0)).toBe(false);
      });

      it('saves the size as a number and the formats in list order', async () => {
        const saved = await savedAfterLoading(backendQuestion);

        expect(saved.config.fileUploadOptions).toEqual({ maxFileSize: 20, allowedFormats: ['.jpg', '.pdf'] });
      });
    });

    it('keeps its options when opened and saved', async () => {
      const config = { fileUploadOptions: { maxFileSize: 10, allowedFormats: ['.png', '.mp4', '.flac'] } };

      const saved = await savedAfterLoading({ type: 'file', config });

      expect(saved.config).toEqual(config);
    });

    it('starts at 20 MB with every format when another type is switched to it', async () => {
      const saved = await savedAfterLoading({ type: 'text', config: {} }, question => {
        question.type = 'fileUpload';
      });

      expect(saved.config).toEqual({ fileUploadOptions: { maxFileSize: 20, allowedFormats: SERVER_FORMATS } });
    });

    it('drops its options when switched to another type', async () => {
      const saved = await savedAfterLoading({ type: 'file', config: fileQuestion().config }, question => {
        question.type = 'text';
      });

      expect(saved.config).not.toHaveProperty('fileUploadOptions');
    });

    it('shows its options when a new question is switched to it', async () => {
      const wrapper = mount(AddEditQuestionnaireDialog, {
        props: { projectId: 1, nextQuestionnaireId: 1 },
        global: { stubs: { SchedulePreview: true } },
      });
      wrapper.vm.addQuestion();
      await wrapper.vm.$nextTick();

      const typeSelect = wrapper.findAll('select').find(select => select.find('option[value="fileUpload"]').exists());
      await typeSelect.setValue('fileUpload');

      expect(wrapper.find('#file_max_size_0').element.value).toBe('20');
    });

    it('hides its options when switched to another type', async () => {
      const wrapper = mount(AddEditQuestionnaireDialog, {
        props: { projectId: 1, nextQuestionnaireId: 1 },
        global: { stubs: { SchedulePreview: true } },
      });
      wrapper.vm.addQuestion();
      wrapper.vm.formData.questions[0].type = 'fileUpload';
      await wrapper.vm.$nextTick();

      const typeSelect = wrapper.findAll('select').find(select => select.find('option[value="fileUpload"]').exists());
      await typeSelect.setValue('text');

      expect(wrapper.find('#file_max_size_0').exists()).toBe(false);
    });
  });

  // Directus shows a choice's options, disabled, on a text-only item. The app
  // greys out the options of an item without an itemId and sends no answer.
  describe('display item with answer options', () => {
    const displayQuestion = { type: 'display', displaySelection: 'checkbox', answers: ['Option 1', 'Option 2'] };

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

    function mountedWith(fields) {
      const wrapper = mount(AddEditQuestionnaireDialog, {
        props: { projectId: 1, nextQuestionnaireId: 1 },
        global: { stubs: { SchedulePreview: true } },
      });
      wrapper.vm.addQuestion();
      Object.assign(wrapper.vm.formData.questions[0], fields);

      return wrapper;
    }

    it('saves as a display item with the selection and its options', async () => {
      const saved = await savedQuestion(displayQuestion);

      expect([saved.type, saved.config]).toEqual([
        'display',
        { displaySelection: 'checkbox', options: ['Option 1', 'Option 2'] },
      ]);
    });

    it('saves an "other" option like a choice', async () => {
      const saved = await savedQuestion({
        ...displayQuestion,
        includeOtherOption: true,
        otherTextType: 'textarea',
        otherOptionLabel: 'etwas anders',
      });

      expect(saved.config).toEqual({
        displaySelection: 'checkbox',
        options: ['Option 1', 'Option 2'],
        includeOtherOption: true,
        otherTextType: 'textarea',
        otherOptionLabel: 'etwas anders',
      });
    });

    it('saves no randomize, default or jump', async () => {
      const saved = await savedQuestion({
        ...displayQuestion,
        displaySelection: 'radio',
        randomizeAnswers: true,
        defaultValue: 'Option 1',
        useJump: true,
        jumpCondition: 1,
        jumpOver: 1,
      });

      expect(saved.config).toEqual({ displaySelection: 'radio', options: ['Option 1', 'Option 2'] });
    });

    // The server refuses every submit for a display item marked mandatory,
    // because the app never answers one.
    it('is saved as not mandatory', async () => {
      const saved = await savedQuestion({ ...displayQuestion, mandatory: true });

      expect(saved.mandatory).toBe(false);
    });

    it('saves a display item opened as mandatory as not mandatory', async () => {
      const saved = await savedAfterLoading({ type: 'display', is_mandatory: true, config: {} });

      expect(saved.mandatory).toBe(false);
    });

    it('saves a plain display item when switched off', async () => {
      const saved = await savedQuestion({ ...displayQuestion, displaySelection: '', includeOtherOption: true });

      expect(saved.config).toEqual({});
    });

    it('restores the selection and its options when a saved question is opened', () => {
      const question = loadedQuestion({
        type: 'display',
        config: { displaySelection: 'radio', options: ['Yes', 'No'], includeOtherOption: true, otherTextType: 'textarea' },
      });

      expect([question.type, question.displaySelection, question.answers, question.includeOtherOption, question.otherTextType])
        .toEqual(['display', 'radio', ['Yes', 'No'], true, 'textarea']);
    });

    it('keeps its config when opened and saved', async () => {
      const config = {
        displaySelection: 'checkbox',
        options: ['Option 1', 'Option 2'],
        includeOtherOption: true,
        otherTextType: 'textarea',
        otherOptionLabel: 'etwas anders',
      };

      const saved = await savedAfterLoading({ type: 'display', is_mandatory: false, config });

      expect(saved.config).toEqual(config);
    });

    it('opens a plain display item switched off', () => {
      expect(loadedQuestion({ type: 'display', config: {} }).displaySelection).toBe('');
    });

    it('marks a question with fewer than 2 options', () => {
      expect(questionError({ ...displayQuestion, answers: ['Only'] })).toBe(true);
    });

    it('cannot be saved with fewer than 2 options', () => {
      expect(formIsValid({ ...displayQuestion, answers: ['Only'] })).toBe(false);
    });

    it('does not count a blank option', () => {
      expect(formIsValid({ ...displayQuestion, answers: ['Option 1', ' '] })).toBe(false);
    });

    it('can be saved with 2 options', () => {
      expect(formIsValid(displayQuestion)).toBe(true);
    });

    it('can be saved switched off without options', () => {
      expect(formIsValid({ type: 'display', displaySelection: '', answers: [] })).toBe(true);
    });

    it('shows the options and "other", without randomize, default or jump', async () => {
      const wrapper = mountedWith(displayQuestion);
      await wrapper.vm.$nextTick();

      expect({
        options: wrapper.findAll('input[type="text"]').map(input => input.element.value),
        other: wrapper.find('#include-other-0').exists(),
        randomize: wrapper.find('#randomize-answers-0').exists(),
        defaultValue: wrapper.findAll('select').some(select => select.text().includes('No default')),
        jump: wrapper.find('#use-jump-0').exists(),
      }).toEqual({
        options: expect.arrayContaining(['Option 1', 'Option 2']),
        other: true,
        randomize: false,
        defaultValue: false,
        jump: false,
      });
    });

    it('shows no options until switched on', async () => {
      const wrapper = mountedWith({ type: 'display', answers: ['Option 1', 'Option 2'] });
      await wrapper.vm.$nextTick();

      expect(wrapper.find('#include-other-0').exists()).toBe(false);
    });

    it('starts as single choice when switched on', async () => {
      const wrapper = mountedWith({ type: 'display' });
      await wrapper.vm.$nextTick();

      await wrapper.find('#display-selection-0').setValue(true);

      expect(wrapper.vm.formData.questions[0].displaySelection).toBe('radio');
    });

    it('offers single and multiple choice', async () => {
      const wrapper = mountedWith(displayQuestion);
      await wrapper.vm.$nextTick();

      const offered = wrapper.find('#display-selection-type-0').findAll('option').map(option => option.element.value);

      expect(offered).toEqual(['radio', 'checkbox']);
    });

    it('is not offered on other types', async () => {
      const wrapper = mountedWith({ type: 'radio', answers: ['Yes', 'No'] });
      await wrapper.vm.$nextTick();

      expect(wrapper.find('#display-selection-0').exists()).toBe(false);
    });

    // A plain display item has no scale, so a timer would never reach the app.
    describe('timer', () => {
      const timer = { useTimer: true, timerSeconds: 10, showCountdown: true };

      it('is hidden for a plain display item', async () => {
        const wrapper = mountedWith({ type: 'display' });
        await wrapper.vm.$nextTick();

        expect(wrapper.find('#use-timer-0').exists()).toBe(false);
      });

      it('is shown once answer options are shown', async () => {
        const wrapper = mountedWith(displayQuestion);
        await wrapper.vm.$nextTick();

        expect(wrapper.find('#use-timer-0').exists()).toBe(true);
      });

      it('is not saved for a plain display item', async () => {
        const saved = await savedQuestion({ ...displayQuestion, ...timer, displaySelection: '' });

        expect(saved.config).not.toHaveProperty('timer');
      });

      it('is saved for a display item with answer options', async () => {
        const saved = await savedQuestion({ ...displayQuestion, ...timer });

        expect(saved.config.timer).toEqual({ time: 10, showCountdown: true });
      });
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

  function mountedWithQuestion(fields) {
    const wrapper = mount(AddEditQuestionnaireDialog, {
      props: { projectId: 1, nextQuestionnaireId: 1 },
      global: { stubs: { SchedulePreview: true } },
    });
    wrapper.vm.addQuestion();
    Object.assign(wrapper.vm.formData.questions[0], fields);

    return wrapper;
  }

  // The app shows the poster on a video file until it plays.
  describe('poster image', () => {
    const posterUrl = 'https://example.test/poster.jpg';
    const videoQuestion = { type: 'text', videoUrl: 'https://example.test/video.mp4' };

    it('is saved with a video', async () => {
      const saved = await savedQuestion({ ...videoQuestion, posterUrl });

      expect(saved.config.posterUrl).toBe(posterUrl);
    });

    it('is left out when empty', async () => {
      const saved = await savedQuestion({ ...videoQuestion, posterUrl: '' });

      expect(saved.config).not.toHaveProperty('posterUrl');
    });

    it('is left out without a video', async () => {
      const saved = await savedQuestion({ ...videoQuestion, videoUrl: '', posterUrl });

      expect(saved.config).not.toHaveProperty('posterUrl');
    });

    it('starts empty on a new question', () => {
      const dialog = dialogWith([]);
      methods.addQuestion.call(dialog);

      expect(dialog.formData.questions[0].posterUrl).toBe('');
    });

    it('is restored when a saved question is opened', () => {
      const question = loadedQuestion({ type: 'text', video_url: videoQuestion.videoUrl, config: { posterUrl } });

      expect(question.posterUrl).toBe(posterUrl);
    });

    it('is empty for a question saved without one', () => {
      expect(loadedQuestion({ type: 'text', config: {} }).posterUrl).toBe('');
    });

    it('is kept when opened and saved', async () => {
      const saved = await savedAfterLoading({ type: 'text', video_url: videoQuestion.videoUrl, config: { posterUrl } });

      expect(saved.config).toEqual({ posterUrl });
    });

    it('is dropped when the video is cleared', async () => {
      const saved = await savedAfterLoading(
        { type: 'text', video_url: videoQuestion.videoUrl, config: { posterUrl } },
        question => { question.videoUrl = ''; },
      );

      expect(saved.config).not.toHaveProperty('posterUrl');
    });

    it('is offered with a video', async () => {
      const wrapper = mountedWithQuestion(videoQuestion);
      await wrapper.vm.$nextTick();

      expect(wrapper.find('#poster-url-0').exists()).toBe(true);
    });

    it('is not offered without a video', async () => {
      const wrapper = mountedWithQuestion({ type: 'text' });
      await wrapper.vm.$nextTick();

      expect(wrapper.find('#poster-url-0').exists()).toBe(false);
    });
  });

  describe('random position within questionnaire', () => {
    it('is saved when ticked', async () => {
      const saved = await savedQuestion({ type: 'text', randomPositionWithinQuestionnaire: true });

      expect(saved.config.randomPositionWithinQuestionnaire).toBe(true);
    });

    it('is left out when not ticked', async () => {
      const saved = await savedQuestion({ type: 'text', randomPositionWithinQuestionnaire: false });

      expect(saved.config).not.toHaveProperty('randomPositionWithinQuestionnaire');
    });

    it('starts unticked on a new question', () => {
      const dialog = dialogWith([]);
      methods.addQuestion.call(dialog);

      expect(dialog.formData.questions[0].randomPositionWithinQuestionnaire).toBe(false);
    });

    it('is restored when a saved question is opened', () => {
      const question = loadedQuestion({ type: 'text', config: { randomPositionWithinQuestionnaire: true } });

      expect(question.randomPositionWithinQuestionnaire).toBe(true);
    });

    it('is unticked for a question saved without it', () => {
      expect(loadedQuestion({ type: 'text', config: {} }).randomPositionWithinQuestionnaire).toBe(false);
    });

    it('is kept when opened and saved', async () => {
      const saved = await savedAfterLoading({ type: 'text', config: { randomPositionWithinQuestionnaire: true } });

      expect(saved.config).toEqual({ randomPositionWithinQuestionnaire: true });
    });

    // The app shuffles an item with a randomization group inside its group
    // and never reads its random position.
    it('shows a hint when the question also has a randomization group', async () => {
      const wrapper = mountedWithQuestion({ type: 'text', randomPositionWithinQuestionnaire: true, randomizationGroupId: 2 });
      await wrapper.vm.$nextTick();

      expect(wrapper.find('#random-position-hint-0').exists()).toBe(true);
    });

    it('shows no hint without a randomization group', async () => {
      const wrapper = mountedWithQuestion({ type: 'text', randomPositionWithinQuestionnaire: true, randomizationGroupId: null });
      await wrapper.vm.$nextTick();

      expect(wrapper.find('#random-position-hint-0').exists()).toBe(false);
    });

    it('shows no hint when not ticked', async () => {
      const wrapper = mountedWithQuestion({ type: 'text', randomPositionWithinQuestionnaire: false, randomizationGroupId: 2 });
      await wrapper.vm.$nextTick();

      expect(wrapper.find('#random-position-hint-0').exists()).toBe(false);
    });

    it('can be ticked in the form', async () => {
      const wrapper = mountedWithQuestion({ type: 'text' });
      await wrapper.vm.$nextTick();

      await wrapper.find('#random-position-0').setValue(true);

      expect(wrapper.vm.formData.questions[0].randomPositionWithinQuestionnaire).toBe(true);
    });
  });

  // A question opened, left as it is and saved again keeps both fields,
  // next to the rest of its config.
  it('keeps the poster and the random position when a choice question is opened and saved', async () => {
    const config = {
      options: ['Yes', 'No'],
      randomPositionWithinQuestionnaire: true,
      posterUrl: 'https://example.test/poster.jpg',
    };

    const saved = await savedAfterLoading({
      type: 'one choice',
      video_url: 'https://example.test/video.mp4',
      is_mandatory: true,
      config,
    });

    expect(saved.config).toEqual(config);
  });
});

// The app leaves a data donation questionnaire out of its schedule lists, so
// it always shows the progress bar and never sends a notification for one.
describe('AddEditQuestionnaireDialog data donation questionnaire settings', () => {
  beforeEach(() => {
    window.axios = { post: jest.fn().mockResolvedValue({}), put: jest.fn().mockResolvedValue({}) };
  });

  const settingIds = ['#show_progress_bar', '#show_notifications', '#notification_text'];

  function mountedDialog(schedule = null) {
    return mount(AddEditQuestionnaireDialog, {
      props: { projectId: 1, nextQuestionnaireId: 1, schedule },
      global: { stubs: { SchedulePreview: true } },
    });
  }

  // A new questionnaire that passes isValid, so save() sends it.
  function newQuestionnaire() {
    const wrapper = mountedDialog();
    wrapper.vm.formData.name = 'Donation';
    wrapper.vm.formData.start_date_time.date = '2026-11-11';
    wrapper.vm.addQuestion();
    Object.assign(wrapper.vm.formData.questions[0], { text: 'Question', type: 'text' });

    return wrapper;
  }

  function openedQuestionnaire(fields) {
    return mountedDialog({
      id: 7,
      name: 'Donation',
      questionnaire_id: 3,
      type: 'single',
      questions: [{ uuid: 'q-1', text: 'Question', type: 'text', is_mandatory: true, config: {} }],
      ...fields,
    });
  }

  function shownSettings(wrapper) {
    return settingIds.filter(id => wrapper.find(id).exists());
  }

  function sentSettings(payload) {
    return { show_progress_bar: payload.show_progress_bar, show_notifications: payload.show_notifications };
  }

  function ticked(wrapper) {
    return {
      progressBar: wrapper.find('#show_progress_bar').element.checked,
      notifications: wrapper.find('#show_notifications').element.checked,
    };
  }

  // The date fields are hidden for a data donation questionnaire, and the
  // server never sends its start date to the app.
  describe('without a start date', () => {
    function undatedQuestionnaire() {
      const wrapper = mountedDialog();
      wrapper.vm.formData.name = 'Donation';
      wrapper.vm.addQuestion();
      Object.assign(wrapper.vm.formData.questions[0], { text: 'Question', type: 'text' });

      return wrapper;
    }

    it('can save a new data donation questionnaire whose type was picked before any date', async () => {
      const wrapper = undatedQuestionnaire();
      await wrapper.find('#data_donation_ios').setValue();

      await wrapper.vm.save();

      expect(window.axios.post).toHaveBeenCalledTimes(1);
    });

    it('still needs a start date for a new questionnaire that is not a data donation one', async () => {
      const wrapper = undatedQuestionnaire();
      await wrapper.vm.$nextTick();

      expect(wrapper.vm.isValid).toBe(false);
    });
  });

  describe('in the form', () => {
    it('shows the progress bar and notification settings for a questionnaire that is not a data donation one', async () => {
      const wrapper = mountedDialog();
      await wrapper.vm.$nextTick();

      expect(shownSettings(wrapper)).toEqual(settingIds);
    });

    it('hides the progress bar and notification settings for an iOS data donation questionnaire', async () => {
      const wrapper = mountedDialog();
      await wrapper.find('#data_donation_ios').setValue();

      expect(shownSettings(wrapper)).toEqual([]);
    });

    it('hides the progress bar and notification settings for an Android data donation questionnaire', async () => {
      const wrapper = mountedDialog();
      await wrapper.find('#data_donation_android').setValue();

      expect(shownSettings(wrapper)).toEqual([]);
    });

    it('hides them on an opened data donation questionnaire', async () => {
      const wrapper = openedQuestionnaire({ is_android_data_donation: true });
      await wrapper.vm.$nextTick();

      expect(shownSettings(wrapper)).toEqual([]);
    });

    it('says what the app does instead for a data donation questionnaire', async () => {
      const wrapper = mountedDialog();
      await wrapper.find('#data_donation_ios').setValue();

      expect(wrapper.find('#donation-progress-notifications-note').exists()).toBe(true);
    });

    it('shows no such note for a questionnaire that is not a data donation one', async () => {
      const wrapper = mountedDialog();
      await wrapper.vm.$nextTick();

      expect(wrapper.find('#donation-progress-notifications-note').exists()).toBe(false);
    });
  });

  describe('on save', () => {
    it('sends the progress bar on and notifications off for a new iOS data donation questionnaire', async () => {
      const wrapper = newQuestionnaire();
      await wrapper.find('#show_progress_bar').setValue(false);
      await wrapper.find('#data_donation_ios').setValue();

      await wrapper.vm.save();

      expect(sentSettings(window.axios.post.mock.calls[0][1])).toEqual({ show_progress_bar: true, show_notifications: false });
    });

    it('sends the progress bar on and notifications off for a new Android data donation questionnaire', async () => {
      const wrapper = newQuestionnaire();
      await wrapper.find('#show_progress_bar').setValue(false);
      await wrapper.find('#data_donation_android').setValue();

      await wrapper.vm.save();

      expect(sentSettings(window.axios.post.mock.calls[0][1])).toEqual({ show_progress_bar: true, show_notifications: false });
    });

    it('sends the progress bar on and notifications off for an opened data donation questionnaire saved with other values', async () => {
      const wrapper = openedQuestionnaire({
        is_ios_data_donation: true,
        notification_config: { show_progress_bar: false, show_notifications: true, notification_text: 'Hello' },
      });
      await wrapper.vm.$nextTick();

      await wrapper.vm.save();

      expect(sentSettings(window.axios.put.mock.calls[0][1])).toEqual({ show_progress_bar: true, show_notifications: false });
    });

    it('sends the progress bar on and notifications off when an opened questionnaire becomes a data donation one', async () => {
      const wrapper = openedQuestionnaire({
        notification_config: { show_progress_bar: false, show_notifications: true, notification_text: 'Hello' },
      });
      await wrapper.find('#data_donation_android').setValue();

      await wrapper.vm.save();

      expect(sentSettings(window.axios.put.mock.calls[0][1])).toEqual({ show_progress_bar: true, show_notifications: false });
    });

    it('sends the form values for a questionnaire that is not a data donation one', async () => {
      const wrapper = newQuestionnaire();
      await wrapper.find('#show_progress_bar').setValue(false);

      await wrapper.vm.save();

      expect(sentSettings(window.axios.post.mock.calls[0][1])).toEqual({ show_progress_bar: false, show_notifications: true });
    });
  });

  // The hidden values are left as they were, so switching back shows them.
  describe('when switched back to not a data donation questionnaire', () => {
    it('shows the values the new questionnaire had before', async () => {
      const wrapper = mountedDialog();
      await wrapper.find('#show_progress_bar').setValue(false);
      await wrapper.find('#data_donation_ios').setValue();

      await wrapper.find('#data_donation_none').setValue();

      expect(ticked(wrapper)).toEqual({ progressBar: false, notifications: true });
    });

    // Saved before the editor hid the settings, so they can hold anything.
    it('shows the saved values of an opened data donation questionnaire', async () => {
      const wrapper = openedQuestionnaire({
        is_ios_data_donation: true,
        notification_config: { show_progress_bar: false, show_notifications: true },
      });
      await wrapper.vm.$nextTick();

      await wrapper.find('#data_donation_none').setValue();

      expect(ticked(wrapper)).toEqual({ progressBar: false, notifications: true });
    });

    it('sends the values it shows', async () => {
      const wrapper = newQuestionnaire();
      await wrapper.find('#show_progress_bar').setValue(false);
      await wrapper.find('#data_donation_ios').setValue();
      await wrapper.find('#data_donation_none').setValue();

      await wrapper.vm.save();

      expect(sentSettings(window.axios.post.mock.calls[0][1])).toEqual({ show_progress_bar: false, show_notifications: true });
    });
  });
});
