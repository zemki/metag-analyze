import { mount, flushPromises } from '@vue/test-utils';
import MartQuestionnaireManager from '@/components/mart/MartQuestionnaireManager.vue';

jest.mock('@/emitter.js', () => ({ emitter: { emit: jest.fn() } }));

function schedule(fields) {
  return {
    id: 1,
    questionnaire_id: 1,
    name: 'Questionnaire',
    type: 'single',
    is_ios_data_donation: false,
    is_android_data_donation: false,
    timing_config: {},
    notification_config: { show_progress_bar: true, show_notifications: true },
    questions: [],
    ...fields,
  };
}

async function listOf(schedules) {
  window.axios = { get: jest.fn().mockResolvedValue({ data: { questionnaires: schedules } }) };
  const wrapper = mount(MartQuestionnaireManager, {
    props: { projectId: 1 },
    global: { stubs: { AddEditQuestionnaireDialog: true, VersionHistoryModal: true, SchedulePreview: true } },
  });
  await flushPromises();

  return wrapper;
}

function badges(wrapper, label) {
  return wrapper.findAll('span').filter(span => span.text() === label);
}

// The app always shows the progress bar for a data donation questionnaire and
// never sends a notification for one, so the list has no badges to show.
describe('MartQuestionnaireManager progress bar and notification badges', () => {
  it('shows both badges for a questionnaire that is not a data donation one', async () => {
    const wrapper = await listOf([schedule({})]);

    expect([badges(wrapper, 'Notifications').length, badges(wrapper, 'Progress bar').length]).toEqual([1, 1]);
  });

  it('hides both badges for an iOS data donation questionnaire', async () => {
    const wrapper = await listOf([schedule({ is_ios_data_donation: true })]);

    expect([badges(wrapper, 'Notifications').length, badges(wrapper, 'Progress bar').length]).toEqual([0, 0]);
  });

  it('hides both badges for an Android data donation questionnaire', async () => {
    const wrapper = await listOf([schedule({ is_android_data_donation: true })]);

    expect([badges(wrapper, 'Notifications').length, badges(wrapper, 'Progress bar').length]).toEqual([0, 0]);
  });

  it('keeps the badges of the other questionnaires next to a data donation one', async () => {
    const wrapper = await listOf([
      schedule({ id: 1, questionnaire_id: 1, is_ios_data_donation: true }),
      schedule({ id: 2, questionnaire_id: 2 }),
    ]);

    expect([badges(wrapper, 'Notifications').length, badges(wrapper, 'Progress bar').length]).toEqual([1, 1]);
  });
});
