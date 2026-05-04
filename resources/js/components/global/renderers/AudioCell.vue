<template>
  <span v-if="!hasAudio" :style="placeholderStyle">—</span>
  <span v-else :style="cellStyle">
    <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor">
      <polygon points="6 4 20 12 6 20 6 4" />
    </svg>
    Audio recorded
  </span>
</template>

<script setup>
import { computed } from 'vue';

/**
 * AudioCell — renderer for `type === 'audio recording'`. Compact "Audio
 * recorded" badge with a small triangle so it reads as a play affordance
 * even though the table cell itself doesn't play (use the Edit dialog or
 * a future audio popover for actual playback).
 *
 * Audio entries store the recording on the row itself (`entry.file_object`
 * / `entry.file_path`) — `entry.inputs[input.name]` may be empty. So we
 * detect "has audio" from either source and only fall back to placeholder
 * when both are missing.
 */

const props = defineProps({
  value: { type: null, required: false, default: null },
  input: { type: Object, required: false, default: null },
  entry: { type: Object, required: false, default: null },
  mode: { type: String, default: 'display' },
});

const hasAudio = computed(() => {
  if (props.value !== null && props.value !== undefined && props.value !== '') return true;
  if (props.entry?.file_object) return true;
  if (props.entry?.file_path) return true;
  return false;
});

const cellStyle = {
  display: 'inline-flex',
  alignItems: 'center',
  gap: '5px',
  padding: '2px 10px 2px 8px',
  background: '#eef1f8',
  color: '#3b4768',
  borderRadius: '12px',
  fontSize: '11.5px',
  fontWeight: 500,
  whiteSpace: 'nowrap',
};

const placeholderStyle = {
  color: '#c4cce0',
  fontSize: '12.5px',
};
</script>
