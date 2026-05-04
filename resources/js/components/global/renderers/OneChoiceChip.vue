<template>
  <span v-if="isEmpty" :style="placeholderStyle">—</span>
  <span v-else :style="chipStyle" :title="display">{{ display }}</span>
</template>

<script setup>
import { computed } from 'vue';

/**
 * OneChoiceChip — renderer for `type === 'one choice'`. Single neutral
 * pill so a glance shows the selected answer without any color-coded
 * implied ranking (bias-agnostic). Tolerates the historical pattern
 * where one-choice values may be stored as a single-element array.
 */

const props = defineProps({
  value: { type: null, required: false, default: null },
  input: { type: Object, required: false, default: null },
  entry: { type: Object, required: false, default: null },
  mode: { type: String, default: 'display' },
});

const display = computed(() => {
  const v = props.value;
  if (Array.isArray(v)) return v.find((x) => x !== null && x !== undefined && x !== '');
  return v;
});

const isEmpty = computed(() => {
  const d = display.value;
  return d === null || d === undefined || d === '';
});

const chipStyle = {
  display: 'inline-block',
  padding: '2px 10px',
  background: '#eef1f8',
  color: '#3b4768',
  borderRadius: '12px',
  fontSize: '11.5px',
  fontWeight: 500,
  whiteSpace: 'nowrap',
  maxWidth: '180px',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
};

const placeholderStyle = {
  color: '#c4cce0',
  fontSize: '12.5px',
};
</script>
