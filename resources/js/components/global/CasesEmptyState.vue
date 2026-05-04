<template>
  <div :style="containerStyle">
    <div :style="iconBlockStyle">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
        <circle cx="9" cy="7" r="4" />
        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
      </svg>
    </div>
    <h3 :style="headingStyle">
      {{ filtered ? 'No cases match your filters' : 'No cases yet' }}
    </h3>
    <p :style="bodyStyle">
      {{ filtered
          ? 'Try clearing your search or status filter to see all cases in this project.'
          : 'Create your first case to invite a participant. Each case generates a unique QR code so the participant can join via the MeTag App.'
      }}
    </p>
    <a v-if="!filtered && createUrl"
       :href="createUrl"
       :style="ctaStyle">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 5v14" />
        <path d="M5 12h14" />
      </svg>
      Create your first case
    </a>
  </div>
</template>

<script setup>
import { computed } from 'vue';

/**
 * CasesEmptyState — shown in the cases list when there are no rows to
 * display. Two variants based on whether filters are active:
 *   - filtered=false: "No cases yet" with a CTA to create one
 *   - filtered=true:  "No cases match your filters" — no CTA, asks the
 *                     researcher to clear filters
 *
 * Visual spec (design_handoff_metag_redesign/README.md → Empty state):
 *   centered column · padding 56px 24px · 56×56 rounded square icon block
 *   in accent palette (radius 14, bg #eff4ff, fg #1d4ed8)
 *   heading 15/600 · body 13/#6b7795 max-w 380 · primary button when not filtered
 */

defineProps({
  /** True when search/filter is active — switches the copy + hides the CTA. */
  filtered: { type: Boolean, default: false },
  /** Href for the "Create your first case" CTA. Required when filtered=false. */
  createUrl: { type: String, default: '' },
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
  margin: '0 0 16px',
  fontSize: '13px',
  color: '#6b7795',
  maxWidth: '380px',
  lineHeight: 1.5,
};

const ctaStyle = {
  display: 'inline-flex',
  alignItems: 'center',
  gap: '6px',
  padding: '8px 14px',
  borderRadius: '8px',
  background: '#2563eb',
  color: '#ffffff',
  fontSize: '13px',
  fontWeight: 600,
  textDecoration: 'none',
  border: '1px solid #2563eb',
};
</script>
