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
});
