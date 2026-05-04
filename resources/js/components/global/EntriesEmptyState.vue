<template>
  <div :style="containerStyle">
    <div :style="iconBlockStyle">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="18" rx="2" />
        <path d="M16 2v4" />
        <path d="M8 2v4" />
        <path d="M3 10h18" />
      </svg>
    </div>
    <h3 :style="headingStyle">No entries yet</h3>
    <p :style="bodyStyle">
      Once the participant submits an entry from the MeTag App, it will appear here.
    </p>
  </div>
</template>

<script setup>
/**
 * EntriesEmptyState — shown when a consultable case has no entries yet.
 * Same structural pattern as CasesEmptyState (centered icon block +
 * heading + body) with neutral copy and no CTA — entries are submitted
 * from the participant's mobile app, not from the dashboard.
 */

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
  maxWidth: '380px',
  lineHeight: 1.5,
};
</script>
