<template>
  <span v-if="isEmpty" :style="placeholderStyle">—</span>
  <span v-else-if="Array.isArray(value)" :style="textStyle">{{ value.join(', ') }}</span>
  <span v-else :style="textStyle">{{ value }}</span>
</template>

<script setup>
import { computed } from 'vue';

/**
 * DefaultRenderer — stage 3a placeholder for every input type. Renders
 * the value as plain text (or comma-joined for arrays). Stage 3b will
 * swap each type for a purpose-built renderer (ScaleBar, FeelingControl,
 * MultipleChoiceChips, AudioCell, …) and this file becomes the genuine
 * fallback for "type with no specialized renderer".
 *
 * Bias-agnostic by construction: no glyphs, no semantic colors.
 */

const props = defineProps({
  value: { type: null, required: false, default: null },
  input: { type: Object, required: false, default: null },
  entry: { type: Object, required: false, default: null },
  mode: {
    type: String,
    default: 'display',
    validator: (v) => ['display', 'edit'].includes(v),
  },
});

const isEmpty = computed(() => {
  const v = props.value;
  if (v === null || v === undefined) return true;
  if (typeof v === 'string' && v.trim() === '') return true;
  if (Array.isArray(v) && v.length === 0) return true;
  return false;
});

const textStyle = {
  fontSize: '12.5px',
  color: '#0f1b3d',
  whiteSpace: 'nowrap',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  maxWidth: '220px',
  display: 'inline-block',
  verticalAlign: 'middle',
};

const placeholderStyle = {
  color: '#c4cce0',
  fontSize: '12.5px',
};
</script>
