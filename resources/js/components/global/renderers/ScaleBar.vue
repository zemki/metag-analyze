<template>
  <span v-if="!isValid" :style="placeholderStyle">—</span>
  <span v-else :style="containerStyle">
    <span :style="trackStyle">
      <span :style="fillStyle"></span>
    </span>
    <span :style="valueStyle">{{ numericValue }}</span>
  </span>
</template>

<script setup>
import { computed } from 'vue';
import { scaleRange } from '../../../utils/scaleHelpers.js';

/**
 * ScaleBar — renderer for `type === 'scale'` input columns. Shows a small
 * horizontal track with the value's position rendered as a fill, plus the
 * numeric value to the right of it. Bias-agnostic: a single neutral grey
 * for the fill (no semantic colors).
 *
 * Range source order is shared with EntryTable / EditEntryModal — see
 * `utils/scaleHelpers.js`. If the range can't be resolved, the bar is
 * suppressed (a number alone is meaningless without a known scale).
 */

const props = defineProps({
  value: { type: null, required: false, default: null },
  input: { type: Object, required: false, default: null },
  entry: { type: Object, required: false, default: null },
  mode: { type: String, default: 'display' },
});

const numericValue = computed(() => {
  const v = props.value;
  if (v === null || v === undefined || v === '') return null;
  const n = typeof v === 'number' ? v : parseFloat(v);
  return Number.isNaN(n) ? null : n;
});

const range = computed(() => scaleRange(props.input));

const isValid = computed(() => {
  if (numericValue.value === null) return false;
  const { min, max } = range.value;
  if (min === null || min === undefined || max === null || max === undefined) return false;
  return max > min;
});

const fraction = computed(() => {
  if (!isValid.value) return 0;
  const { min, max } = range.value;
  const f = (numericValue.value - min) / (max - min);
  return Math.max(0, Math.min(1, f));
});

const containerStyle = {
  display: 'inline-flex',
  alignItems: 'center',
  gap: '8px',
};

const trackStyle = {
  position: 'relative',
  display: 'inline-block',
  width: '60px',
  height: '6px',
  background: '#e3e8f3',
  borderRadius: '3px',
  overflow: 'hidden',
};

const fillStyle = computed(() => ({
  position: 'absolute',
  top: 0,
  left: 0,
  height: '100%',
  width: `${fraction.value * 100}%`,
  background: '#94a3b8',
  borderRadius: '3px',
}));

const valueStyle = {
  fontSize: '12.5px',
  color: '#0f1b3d',
  fontVariantNumeric: 'tabular-nums',
  minWidth: '20px',
  textAlign: 'right',
};

const placeholderStyle = {
  color: '#c4cce0',
  fontSize: '12.5px',
};
</script>
