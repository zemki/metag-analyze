<template>
  <button
    :title="title"
    :disabled="disabled"
    :style="buttonStyle"
    @click="$emit('click', $event)"
    @mouseenter="hover = true"
    @mouseleave="hover = false"
    type="button"
  >
    <slot />
  </button>
</template>

<script setup>
import { computed, ref } from 'vue';

/**
 * CaseAction — small (28×28) icon button used in the per-row action
 * group on the cases list and on the case-detail strip.
 *
 * Three visual tones:
 *   neutral  — default; blue hover ramp
 *   warning  — for "close early" and similar reversible-but-cautious actions
 *   danger   — for delete / destructive actions
 *
 * Visual spec (design_handoff_metag_redesign/README.md → Cases list § Case row):
 *   28×28 · transparent base · on hover: tinted bg + 1px tone-border
 */

const props = defineProps({
  title: { type: String, default: '' },
  tone: {
    type: String,
    default: 'neutral',
    validator: (v) => ['neutral', 'warning', 'danger'].includes(v),
  },
  disabled: { type: Boolean, default: false },
});

defineEmits(['click']);

const hover = ref(false);

const TONES = {
  neutral: { hoverBg: '#eff4ff', hoverBorder: '#c7d2fe', hoverFg: '#1d4ed8' },
  warning: { hoverBg: '#fffbeb', hoverBorder: '#fde68a', hoverFg: '#a16207' },
  danger: { hoverBg: '#fef2f2', hoverBorder: '#fecaca', hoverFg: '#b91c1c' },
};

const buttonStyle = computed(() => {
  const t = TONES[props.tone] ?? TONES.neutral;
  const isHover = hover.value && !props.disabled;
  return {
    width: '28px',
    height: '28px',
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: '6px',
    border: '1px solid ' + (isHover ? t.hoverBorder : 'transparent'),
    background: isHover ? t.hoverBg : 'transparent',
    color: isHover ? t.hoverFg : '#6b7795',
    cursor: props.disabled ? 'not-allowed' : 'pointer',
    opacity: props.disabled ? 0.4 : 1,
    transition: 'background .12s, border-color .12s, color .12s',
    padding: 0,
  };
});
</script>
