<template>
  <span :style="pillStyle">ID: {{ id }}</span>
</template>

<script setup>
/**
 * IDPill — the case's numeric primary key shown inline next to the
 * participant's name. Distinct from the participantId (case.name).
 *
 * Visual spec (design_handoff_metag_redesign/README.md):
 *   padding 2px 7px · radius 4 · font 11/600 · tabular-nums
 *   bg #eff4ff · color #1d4ed8
 */

defineProps({
  id: {
    type: [Number, String],
    required: true,
  },
});

const pillStyle = {
  display: 'inline-flex',
  alignItems: 'center',
  padding: '2px 7px',
  borderRadius: '4px',
  background: '#eff4ff',
  color: '#1d4ed8',
  fontSize: '11px',
  fontWeight: 600,
  fontVariantNumeric: 'tabular-nums',
};
</script>
