<template>
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm text-gray-900">
      <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
        <tr>
          <th class="w-10 px-2 py-2"><span class="sr-only">Answers</span></th>
          <th class="px-3 py-2 text-left font-medium">Start</th>
          <th class="px-3 py-2 text-left font-medium">End</th>
          <th class="px-3 py-2 text-left font-medium">Duration</th>
          <th class="px-3 py-2 text-left font-medium">Questionnaire</th>
          <th class="px-3 py-2 text-left font-medium">Answered</th>
        </tr>
      </thead>
      <tbody>
        <template v-for="entry in entries" :key="entry.id">
          <tr class="border-t border-gray-100 hover:bg-gray-50">
            <td class="px-2 py-2 align-top">
              <button
                type="button"
                class="rounded p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                :aria-expanded="isOpen(entry) ? 'true' : 'false'"
                :aria-label="isOpen(entry) ? 'Hide answers' : 'Show answers'"
                @click="toggle(entry)"
              >
                <svg
                  width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                  :class="['transition-transform', isOpen(entry) ? 'rotate-90' : '']"
                >
                  <polyline points="9 18 15 12 9 6" />
                </svg>
              </button>
            </td>
            <td class="px-3 py-2 align-top">
              <div class="font-medium">{{ formatTime(entry.begin) }}</div>
              <div class="text-xs text-gray-500">{{ formatDate(entry.begin) }}</div>
            </td>
            <td class="px-3 py-2 align-top">
              <div>{{ formatTime(entry.end) }}</div>
              <div v-if="!sameDay(entry.begin, entry.end)" class="text-xs text-gray-500">{{ formatDate(entry.end) }}</div>
            </td>
            <td class="px-3 py-2 align-top font-medium">{{ formatDuration(entry) }}</td>
            <td class="px-3 py-2 align-top">{{ entry.mart_questionnaire || '—' }}</td>
            <td class="px-3 py-2 align-top">{{ answeredCount(entry) }} / {{ answers(entry).length }}</td>
          </tr>
          <tr v-if="isOpen(entry)" class="bg-gray-50">
            <td></td>
            <td colspan="5" class="px-3 pb-4 pt-1">
              <dl v-if="answers(entry).length" class="grid grid-cols-1 gap-x-8 gap-y-2 md:grid-cols-2">
                <div v-for="(answer, index) in answers(entry)" :key="index" class="min-w-0">
                  <dt class="text-xs text-gray-500">{{ answer.question }}</dt>
                  <dd class="break-words font-medium" :data-question="answer.question">
                    <span v-if="answer.value === ''" class="text-gray-400">—</span>
                    <template v-else-if="answer.label">{{ answer.label }} <span class="font-normal text-gray-500">({{ answer.value }})</span></template>
                    <template v-else>{{ answer.value }}</template>
                  </dd>
                </div>
              </dl>
              <p v-else class="text-sm text-gray-500">No answers stored for this submission.</p>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>

<script setup>
/**
 * MartEntryTable: the submissions of one MART case, as Cases::loadMartEntries
 * sends them (mart_questionnaire, mart_duration_ms, mart_answers). Read only,
 * since MART answers come from the app. Opening a row lists its answers: the
 * option label where there is one, with the raw value the Excel export writes
 * in brackets, so both can be compared. MeTag cases keep EntryTable.
 */
import { ref } from 'vue';

defineProps({
  entries: { type: Array, required: true },
});

const openId = ref(null);

function isOpen(entry) {
  return openId.value === entry.id;
}
function toggle(entry) {
  openId.value = isOpen(entry) ? null : entry.id;
}
function answers(entry) {
  return Array.isArray(entry.mart_answers) ? entry.mart_answers : [];
}
function answeredCount(entry) {
  return answers(entry).filter((answer) => answer.value !== '').length;
}

function pad(n) {
  return String(n).padStart(2, '0');
}
function parseDate(raw) {
  if (!raw) return null;
  const d = new Date(typeof raw === 'string' ? raw.replace(' ', 'T') : raw);
  return Number.isNaN(d.getTime()) ? null : d;
}
function formatTime(raw) {
  const d = parseDate(raw);
  return d ? `${pad(d.getHours())}:${pad(d.getMinutes())}` : '—';
}
function formatDate(raw) {
  const d = parseDate(raw);
  return d ? `${pad(d.getDate())}.${pad(d.getMonth() + 1)}.${d.getFullYear()}` : '';
}
function sameDay(a, b) {
  const da = parseDate(a);
  const db = parseDate(b);
  return !da || !db || da.toDateString() === db.toDateString();
}
// MART questionnaires take seconds, so seconds are shown. The app's own
// measurement first; start and end as a fallback.
function formatDuration(entry) {
  let ms = Number.isFinite(entry.mart_duration_ms) ? entry.mart_duration_ms : null;
  if (ms === null) {
    const a = parseDate(entry.begin);
    const b = parseDate(entry.end);
    ms = a && b ? b.getTime() - a.getTime() : null;
  }
  if (ms === null || ms < 0) return '—';
  const totalSeconds = Math.round(ms / 1000);
  const minutes = Math.floor(totalSeconds / 60);
  const seconds = totalSeconds % 60;
  return minutes ? `${minutes}m ${pad(seconds)}s` : `${seconds}s`;
}
</script>
