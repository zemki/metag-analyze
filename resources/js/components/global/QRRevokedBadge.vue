<template>
  <span :style="badgeStyle" title="QR token revoked">QR revoked</span>
</template>

<script setup>
/**
 * QRRevokedBadge — small inline badge shown next to a case's status pill
 * (and on the case-detail strip) when the case's QR token has been
 * revoked. Clicking the QR icon on the row opens the existing QR modal
 * where the researcher can unrevoke or regenerate.
 *
 * Visual spec (design_handoff_metag_redesign/README.md):
 *   padding 2px 7px · radius 4 · font 10.5/600 · uppercase · letterSpacing 0.2
 *   bg #fef2f2 · color #b91c1c (the same warning ramp as the danger
 *   action hover state).
 *
 * Hidden by useProjectCapabilities on mobile-app projects (no QR there).
 */

const badgeStyle = {
  display: 'inline-flex',
  alignItems: 'center',
  gap: '4px',
  padding: '2px 7px',
  borderRadius: '4px',
  background: '#fef2f2',
  color: '#b91c1c',
  fontSize: '10.5px',
  fontWeight: 600,
  letterSpacing: '0.2px',
  textTransform: 'uppercase',
  whiteSpace: 'nowrap',
};
</script>
