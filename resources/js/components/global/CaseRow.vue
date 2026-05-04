<template>
  <div
    :style="rowStyle"
    :title="disabledReason || ''"
    @click="onRowClick"
    @mouseenter="hover = true"
    @mouseleave="hover = false"
  >
    <div style="flex: 1; min-width: 0;">
      <!-- Top line: name · ID · status · QR-revoked badge -->
      <div :style="topLineStyle">
        <span :style="nameStyle">{{ caseItem.name }}</span>
        <IDPill :id="caseItem.id" />
        <StatusPill :status="resolvedStatus" />
        <QRRevokedBadge v-if="capabilities.showQRAction && caseItem.qr_token_revoked_at" />
      </div>
      <!-- Bottom line: email · entry count · created date -->
      <div :style="metaRowStyle">
        <span :style="metaItemStyle">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="8" r="4" />
            <path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8" />
          </svg>
          {{ userDisplayName }}
        </span>
        <span :style="metaItemStyle">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M8 6h13" />
            <path d="M8 12h13" />
            <path d="M8 18h13" />
            <circle cx="3.5" cy="6" r="1" />
            <circle cx="3.5" cy="12" r="1" />
            <circle cx="3.5" cy="18" r="1" />
          </svg>
          {{ entryCount }} {{ entryCount === 1 ? 'entry' : 'entries' }}
        </span>
        <span style="font-variant-numeric: tabular-nums;">
          Created {{ formattedDate }}
        </span>
      </div>
    </div>

    <!-- Right side: action icons -->
    <div :style="actionsStyle" @click.stop>
      <!-- View (eye) — same as clicking the row -->
      <CaseAction title="View case" tone="neutral" :disabled="!consultable" @click="onSelect">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
          <circle cx="12" cy="12" r="3" />
        </svg>
      </CaseAction>

      <!-- Export -->
      <CaseAction
        v-if="canExport"
        title="Export entries"
        tone="neutral"
        @click="$emit('export', caseItem)"
      >
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
          <polyline points="7 10 12 15 17 10" />
          <line x1="12" y1="15" x2="12" y2="3" />
        </svg>
      </CaseAction>

      <!-- QR (regular projects only) -->
      <CaseAction
        v-if="capabilities.showQRAction && caseItem.user"
        :title="caseItem.qr_token_revoked_at ? 'QR code (revoked) — manage' : 'QR code'"
        tone="neutral"
        @click="$emit('qr', caseItem)"
      >
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="7" height="7" rx="1" />
          <rect x="14" y="3" width="7" height="7" rx="1" />
          <rect x="3" y="14" width="7" height="7" rx="1" />
          <path d="M14 14h3v3" />
          <path d="M21 14v3" />
          <path d="M14 21h7" />
          <path d="M17 17h4" />
        </svg>
      </CaseAction>

      <!-- Close Early (regular projects, creator-only, non-completed) -->
      <CaseAction
        v-if="canCloseEarly"
        title="Close case early"
        tone="warning"
        @click="$emit('close-early', caseItem)"
      >
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10" />
          <rect x="9" y="9" width="6" height="6" rx="1" />
        </svg>
      </CaseAction>

      <!-- Delete -->
      <CaseAction title="Delete case" tone="danger" @click="$emit('delete', caseItem)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 6h18" />
          <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
          <path d="M10 11v6" />
          <path d="M14 11v6" />
          <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
        </svg>
      </CaseAction>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useProjectCapabilities } from '../../utils/useProjectCapabilities.js';
import StatusPill from './StatusPill.vue';
import IDPill from './IDPill.vue';
import QRRevokedBadge from './QRRevokedBadge.vue';
import CaseAction from './CaseAction.vue';

/**
 * CaseRow — single row in the cases list.
 *
 * Visual spec (design_handoff_metag_redesign/README.md → Case row):
 *   padding 12px 20px · bottom border #eef1f8 · 3px left border (transparent
 *   default, accent when selected) · selected fill #eff4ff · hover fill
 *   #fbfcfe · action group fades 0.55→1 on hover/selected.
 *
 * Project-type rules are read from useProjectCapabilities(project) so we
 * never re-derive them inline. Mobile-app rows that are not in `completed`
 * status are dimmed and not clickable.
 *
 * Emits:
 *   select(caseItem)      — row click (or eye-icon click) when consultable
 *   export(caseItem)      — export-row download
 *   qr(caseItem)          — open QR modal (regular only)
 *   close-early(caseItem) — open close-early confirm (regular, creator only)
 *   delete(caseItem)      — open delete confirm
 */

const props = defineProps({
  caseItem: { type: Object, required: true },
  project: { type: Object, required: true },
  selected: { type: Boolean, default: false },
  isCreator: { type: Boolean, default: false },
  /**
   * Optional override for the case's status. If omitted we read from
   * caseItem.status (server-computed) and fall back to "pending".
   */
  statusOverride: { type: String, default: null },
});

const emit = defineEmits(['select', 'export', 'qr', 'close-early', 'delete']);

const hover = ref(false);

const capabilities = useProjectCapabilities(() => props.project);

const resolvedStatus = computed(() => {
  if (props.statusOverride) return props.statusOverride;
  return props.caseItem.status || 'pending';
});

const consultable = computed(() => capabilities.value.isCaseClickable(resolvedStatus.value));

const disabledReason = computed(() => capabilities.value.caseClickDisabledReason(resolvedStatus.value));

const canExport = computed(() => {
  // Match existing rule: only show export when the case is consultable AND has entries.
  const entryList = props.caseItem.entries;
  const count = Array.isArray(entryList) ? entryList.length : (props.caseItem.entries_count ?? 0);
  return props.caseItem.consultable && count > 0;
});

const canCloseEarly = computed(() =>
  capabilities.value.showCloseEarlyAction
    && props.isCreator
    && resolvedStatus.value !== 'completed'
);

const userDisplayName = computed(() => {
  const u = props.caseItem.user;
  if (!u) return '—';
  return u.email || u.name || `User #${u.id}`;
});

const entryCount = computed(() => {
  const e = props.caseItem.entries;
  if (Array.isArray(e)) return e.length;
  return props.caseItem.entries_count ?? 0;
});

const formattedDate = computed(() => {
  const raw = props.caseItem.created_at;
  if (!raw) return '';
  const d = new Date(raw);
  if (Number.isNaN(d.getTime())) return raw;
  // Match the existing display format DD.MM.YYYY
  const dd = String(d.getDate()).padStart(2, '0');
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  return `${dd}.${mm}.${d.getFullYear()}`;
});

function onRowClick() {
  if (!consultable.value) return;
  onSelect();
}

function onSelect() {
  if (!consultable.value) return;
  emit('select', props.caseItem);
}

const rowStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '14px',
  padding: '12px 20px',
  borderBottom: '1px solid #eef1f8',
  borderLeft: '3px solid '
    + (props.selected && consultable.value ? '#2563eb' : 'transparent'),
  background:
    props.selected && consultable.value
      ? '#eff4ff'
      : hover.value && consultable.value
      ? '#fbfcfe'
      : '#ffffff',
  cursor: consultable.value ? 'pointer' : 'not-allowed',
  opacity: consultable.value ? 1 : 0.55,
  transition: 'background .1s, opacity .1s',
}));

const topLineStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  marginBottom: '4px',
  flexWrap: 'wrap',
};

const nameStyle = {
  fontSize: '14px',
  fontWeight: 600,
  color: '#0f1b3d',
};

const metaRowStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '14px',
  fontSize: '12px',
  color: '#6b7795',
  flexWrap: 'wrap',
};

const metaItemStyle = {
  display: 'inline-flex',
  alignItems: 'center',
  gap: '5px',
};

const actionsStyle = computed(() => ({
  display: 'inline-flex',
  // 6px gap is comfortable: the design's 2px collapses the icons too
  // tightly once you account for our 28×28 hit-area.
  gap: '6px',
  opacity: hover.value || props.selected ? 1 : 0.55,
  transition: 'opacity .12s',
}));
</script>
