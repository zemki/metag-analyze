<template>
  <span v-if="items.length === 0" :style="placeholderStyle">—</span>
  <span v-else :style="containerStyle">
    <span v-for="(v, i) in items" :key="i" :style="chipStyle" :title="String(v)">{{ v }}</span>
  </span>
</template>

<script setup>
import { computed } from 'vue';

/**
 * MultipleChoiceChips — renderer for `type === 'multiple choice'`. Each
 * picked answer becomes a small neutral pill. Wraps within the cell.
 * Tolerates non-array shapes (single string -> single chip).
 */

const props = defineProps({
  value: { type: null, required: false, default: null },
  input: { type: Object, required: false, default: null },
  entry: { type: Object, required: false, default: null },
  mode: { type: String, default: 'display' },
});

const items = computed(() => {
  const v = props.value;
  if (Array.isArray(v)) return v.filter((x) => x !== null && x !== undefined && x !== '');
  if (v === null || v === undefined || v === '') return [];
  return [v];
});

const containerStyle = {
  display: 'inline-flex',
  flexWrap: 'wrap',
  gap: '4px',
  maxWidth: '260px',
};

const chipStyle = {
  display: 'inline-block',
  padding: '2px 10px',
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
