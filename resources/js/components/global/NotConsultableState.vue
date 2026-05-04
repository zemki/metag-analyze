<template>
  <div :style="containerStyle">
    <div :style="iconBlockStyle">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2" />
        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
      </svg>
    </div>
    <h3 :style="headingStyle">Case is not consultable yet</h3>
    <p :style="bodyStyle">
      {{ message }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

/**
 * NotConsultableState — shown when a case exists but its entries can't be
 * inspected mid-stream. Two flavors via the `reason` prop:
 *
 *   reason="active"        — regular project, participant is actively
 *                            entering data; entries become visible after
 *                            the case ends (matches existing behavior)
 *   reason="mart-active"   — mobile-app project, MART case is still in
 *                            progress; only completed cases drill into
 *                            the entry list
 *
 * Use the same centered icon block + heading + body pattern as the empty
 * states. No CTA — researcher just has to wait.
 */

const props = defineProps({
  reason: {
    type: String,
    default: 'active',
    validator: (v) => ['active', 'mart-active'].includes(v),
  },
});

const message = computed(() => {
  if (props.reason === 'mart-active') {
    return 'Mobile-app cases can only be inspected after the participant completes them.';
  }
  return "Entries become visible once the case ends. While the participant is filling it in, the dashboard hides them to avoid showing partial data.";
});

const containerStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  padding: '56px 24px',
  textAlign: 'center',
  color: '#3b4768',
};

const iconBlockStyle = {
  width: '56px',
  height: '56px',
  borderRadius: '14px',
  background: '#eff4ff',
  color: '#1d4ed8',
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  marginBottom: '14px',
};

const headingStyle = {
  margin: '0 0 6px',
  fontSize: '15px',
  fontWeight: 600,
  color: '#0f1b3d',
};

const bodyStyle = {
  margin: 0,
  fontSize: '13px',
  color: '#6b7795',
  maxWidth: '420px',
  lineHeight: 1.5,
};
</script>
