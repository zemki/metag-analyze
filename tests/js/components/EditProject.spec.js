import { mount } from '@vue/test-utils';
import EditProject from '@/components/editproject.vue';

// The questionnaire manager loads its own data and imports the real app.js;
// these specs cover the project settings around it.
jest.mock('@/components/mart/MartQuestionnaireManager.vue', () => ({
  name: 'MartQuestionnaireManager',
  render: () => null,
}));

const martInputs = (projectOptions) => JSON.stringify([{
  type: 'mart',
  name: 'MART Configuration',
  answers: [],
  projectOptions,
}]);

const metagInputs = JSON.stringify([{ name: 'Mood', type: 'text', mandatory: true, answers: [] }]);

function mountWith(inputs) {
  return mount(EditProject, {
    props: {
      editable: true,
      project: {
        id: 1,
        name: 'Study',
        description: 'A study',
        created_at: '2026-01-01',
        inputs,
      },
      config: { available: ['text'] },
    },
  });
}

const androidBreak = (wrapper) => wrapper.find('input#min-break-android-stats');
const donationBreak = (wrapper) => wrapper.find('input#min-break-data-donation');

async function savedProjectOptions(wrapper) {
  await wrapper.vm.save();
  const inputs = JSON.parse(window.axios.patch.mock.calls[0][1].inputs);

  return inputs.find((input) => input.type === 'mart').projectOptions;
}

describe('EditProject minimum breaks', () => {
  beforeEach(() => {
    window.axios = { patch: jest.fn().mockResolvedValue({ data: 'Project updated' }) };
  });

  it('shows both minimum breaks on a MART project', () => {
    const wrapper = mountWith(martInputs({ collectAndroidStats: true }));

    expect(androidBreak(wrapper).exists()).toBe(true);
    expect(donationBreak(wrapper).exists()).toBe(true);
  });

  it('shows the data donation break while Android stats are off', () => {
    const wrapper = mountWith(martInputs({ collectAndroidStats: false }));

    expect(androidBreak(wrapper).exists()).toBe(false);
    expect(donationBreak(wrapper).exists()).toBe(true);
  });

  it('shows neither minimum break on a MeTag project', () => {
    const wrapper = mountWith(metagInputs);

    expect(androidBreak(wrapper).exists()).toBe(false);
    expect(donationBreak(wrapper).exists()).toBe(false);
  });

  it('leaves both minimum breaks blank on a project without them', () => {
    const wrapper = mountWith(martInputs({ collectAndroidStats: true }));

    expect(androidBreak(wrapper).element.value).toBe('');
    expect(donationBreak(wrapper).element.value).toBe('');
  });

  it('loads the stored minimum breaks', () => {
    const wrapper = mountWith(martInputs({
      collectAndroidStats: true,
      minBreakBetweenDataDonationQuestionnaires: 1,
      minBreakBetweenAndroidStats: 6,
    }));

    expect(donationBreak(wrapper).element.value).toBe('1');
    expect(androidBreak(wrapper).element.value).toBe('6');
  });

  it('saves the minimum breaks as numbers', async () => {
    const wrapper = mountWith(martInputs({ collectAndroidStats: true }));
    await donationBreak(wrapper).setValue('1.5');
    await androidBreak(wrapper).setValue('6');

    const projectOptions = await savedProjectOptions(wrapper);

    expect(projectOptions.minBreakBetweenDataDonationQuestionnaires).toBe(1.5);
    expect(projectOptions.minBreakBetweenAndroidStats).toBe(6);
  });

  it('saves a zero minimum break as zero', async () => {
    const wrapper = mountWith(martInputs({ collectAndroidStats: true }));
    await androidBreak(wrapper).setValue('0');

    const projectOptions = await savedProjectOptions(wrapper);

    expect(projectOptions.minBreakBetweenAndroidStats).toBe(0);
  });

  it('removes a minimum break whose field was emptied', async () => {
    const wrapper = mountWith(martInputs({
      collectAndroidStats: true,
      minBreakBetweenDataDonationQuestionnaires: 1,
      minBreakBetweenAndroidStats: 6,
    }));
    await donationBreak(wrapper).setValue('');
    await androidBreak(wrapper).setValue('');

    const projectOptions = await savedProjectOptions(wrapper);

    expect(projectOptions).not.toHaveProperty('minBreakBetweenDataDonationQuestionnaires');
    expect(projectOptions).not.toHaveProperty('minBreakBetweenAndroidStats');
  });

  it('loads back what it saved', async () => {
    const wrapper = mountWith(martInputs({ collectAndroidStats: true }));
    await donationBreak(wrapper).setValue('2.5');
    await androidBreak(wrapper).setValue('4');
    await wrapper.vm.save();

    const reopened = mountWith(window.axios.patch.mock.calls[0][1].inputs);

    expect(donationBreak(reopened).element.value).toBe('2.5');
    expect(androidBreak(reopened).element.value).toBe('4');
  });

  it('loads a stored zero minimum break', () => {
    const wrapper = mountWith(martInputs({ collectAndroidStats: true, minBreakBetweenAndroidStats: 0 }));

    expect(androidBreak(wrapper).element.value).toBe('0');
  });

  it('saves a minimum break stored as a string as a number', async () => {
    const wrapper = mountWith(martInputs({ collectAndroidStats: true, minBreakBetweenAndroidStats: '6' }));

    const projectOptions = await savedProjectOptions(wrapper);

    expect(projectOptions.minBreakBetweenAndroidStats).toBe(6);
  });

  // The API leaves a negative break out, so the editor doesn't keep one it
  // would show but never send.
  it('removes a negative minimum break on save', async () => {
    const wrapper = mountWith(martInputs({ collectAndroidStats: true, minBreakBetweenAndroidStats: 6 }));
    await androidBreak(wrapper).setValue('-1');

    const projectOptions = await savedProjectOptions(wrapper);

    expect(projectOptions).not.toHaveProperty('minBreakBetweenAndroidStats');
  });

  it('removes a stored minimum break that is not a number on save', async () => {
    const wrapper = mountWith(martInputs({ collectAndroidStats: true, minBreakBetweenDataDonationQuestionnaires: '1,5' }));

    const projectOptions = await savedProjectOptions(wrapper);

    expect(projectOptions).not.toHaveProperty('minBreakBetweenDataDonationQuestionnaires');
  });

  it('sends neither minimum break when saving a MeTag project', async () => {
    const wrapper = mountWith(metagInputs);

    await wrapper.vm.save();

    const payload = window.axios.patch.mock.calls[0][1];
    expect(payload.inputs).not.toContain('minBreakBetween');
  });
});
