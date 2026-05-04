<template>
  <div :style="stripStyle">
    <div style="flex: 1; min-width: 0;">
      <!-- Top line: name · ID · status · QR-revoked badge -->
      <div :style="topLineStyle">
        <h2 :style="nameStyle">{{ caseItem.name }}</h2>
        <IDPill :id="caseItem.id" />
        <StatusPill v-if="resolvedStatus" :status="resolvedStatus" />
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
        <span style="font-variant-numeric: tabular-nums;" v-if="formattedCreatedAt">
          Created {{ formattedCreatedAt }}
        </span>
      </div>
    </div>

    <!-- Right side: secondary actions -->
    <div style="display: flex; gap: 8px; flex-shrink: 0; flex-wrap: wrap; align-items: center;">
      <!-- Distinct / Grouped Entries Graph buttons — regular projects only -->
      <a
        v-if="capabilities.showAnalyticsButtons && distinctGraphHref"
        :href="distinctGraphHref"
        :style="secondaryBtnStyle"
        title="Distinct Entries Graph"
      >
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 3v18h18" />
          <rect x="7" y="12" width="3" height="6" />
          <rect x="12" y="8" width="3" height="10" />
          <rect x="17" y="5" width="3" height="13" />
        </svg>
        Distinct entries graph
      </a>
      <a
        v-if="capabilities.showAnalyticsButtons && groupedGraphHref"
        :href="groupedGraphHref"
        :style="secondaryBtnStyle"
        title="Grouped Entries Graph"
      >
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21.21 15.89A10 10 0 1 1 8 2.83" />
          <path d="M22 12A10 10 0 0 0 12 2v10z" />
        </svg>
        Grouped entries graph
      </a>

      <!-- Action icon group (Export / QR / Close Early / Delete). Mirrors the
           per-row action group on the cases list so researchers can do these
           operations from either place. -->
      <span v-if="canExport || (capabilities.showQRAction && caseItem.user) || canCloseEarly"
            :style="actionDividerStyle"></span>

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

      <CaseAction
        title="Delete case"
        tone="danger"
        @click="$emit('delete', caseItem)"
      >
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 6h18" />
          <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
          <path d="M10 11v6" />
          <path d="M14 11v6" />
          <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
        </svg>
      </CaseAction>

      <!-- Optional primary "Add entry" — backend-only cases on the existing app -->
      <button
        v-if="canAddEntry"
        type="button"
        :style="primaryBtnStyle"
        @click="$emit('add-entry')"
      >
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 5v14" />
          <path d="M5 12h14" />
        </svg>
        Add entry
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useProjectCapabilities } from '../../utils/useProjectCapabilities.js';
import StatusPill from './StatusPill.vue';
import IDPill from './IDPill.vue';
import QRRevokedBadge from './QRRevokedBadge.vue';
import CaseAction from './CaseAction.vue';

/**
 * CaseDetailStrip — horizontal strip above the entry list when a case is
 * selected. Mirrors the shape of CaseRow but with bigger typography and
 * project-level analytics actions on the right.
 *
 * Visual spec (design_handoff_metag_redesign/README.md → Case detail header):
 *   padding 14px 20px · bg white · border-bottom #e3e8f3 · gap 14px
 *   name h2 16/700 · meta row 12/#6b7795 · secondary buttons grouped right
 *
 * Project-type rules read from useProjectCapabilities:
 *   - "Distinct Entries Graph" / "Grouped Entries Graph" only on regular
 *   - QR revoked badge only on regular
 */

const props = defineProps({
  caseItem: { type: Object, required: true },
  project: { type: Object, required: true },
  /** Whether the current viewer is the project creator (controls Close Early visibility). */
  isCreator: { type: Boolean, default: false },
  /**
   * Optional override for status. Falls back to caseItem.status which
   * the backend computes via Cases::getStatus().
   */
  statusOverride: { type: String, default: null },
  distinctGraphHref: { type: String, default: '' },
  groupedGraphHref: { type: String, default: '' },
  /** When true, render the primary "Add entry" button. Used for backend-only cases. */
  canAddEntry: { type: Boolean, default: false },
});

defineEmits(['add-entry', 'export', 'qr', 'close-early', 'delete']);

const capabilities = useProjectCapabilities(() => props.project);

const resolvedStatus = computed(() => props.statusOverride || props.caseItem.status || null);

const userDisplayName = computed(() => {
  const u = props.caseItem.user;
  if (!u) return 'No user assigned';
  return u.email || u.name || `User #${u.id}`;
});

const entryCount = computed(() => {
  const e = props.caseItem.entries;
  if (Array.isArray(e)) return e.length;
  return props.caseItem.entries_count ?? 0;
});

const formattedCreatedAt = computed(() => {
  const raw = props.caseItem.created_at;
  if (!raw) return '';
  const d = new Date(raw);
  if (Number.isNaN(d.getTime())) return raw;
  const dd = String(d.getDate()).padStart(2, '0');
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  return `${dd}.${mm}.${d.getFullYear()}`;
});

const canExport = computed(() => {
  // Match the existing rule used on the cases list rows.
  if (!props.caseItem.consultable) return false;
  const list = props.caseItem.entries;
  const count = Array.isArray(list) ? list.length : (props.caseItem.entries_count ?? 0);
  return count > 0;
});

const canCloseEarly = computed(() =>
  capabilities.value.showCloseEarlyAction
    && props.isCreator
    && (resolvedStatus.value !== 'completed')
);

const stripStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '14px',
  padding: '14px 20px',
  background: '#ffffff',
  borderBottom: '1px solid #e3e8f3',
};

const topLineStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  marginBottom: '4px',
  flexWrap: 'wrap',
};

const nameStyle = {
  margin: 0,
  fontSize: '16px',
  fontWeight: 700,
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

const actionDividerStyle = {
  width: '1px',
  height: '24px',
  background: '#e3e8f3',
  margin: '0 2px',
};

const secondaryBtnStyle = {
  display: 'inline-flex',
  alignItems: 'center',
  gap: '6px',
  padding: '7px 12px',
  border: '1px solid #e3e8f3',
  borderRadius: '8px',
  background: '#ffffff',
  color: '#3b4768',
  fontSize: '13px',
  fontWeight: 500,
  textDecoration: 'none',
  cursor: 'pointer',
  whiteSpace: 'nowrap',
};

const primaryBtnStyle = {
  display: 'inline-flex',
  alignItems: 'center',
  gap: '6px',
  padding: '7px 14px',
  border: '1px solid #2563eb',
  borderRadius: '8px',
  background: '#2563eb',
  color: '#ffffff',
  fontSize: '13px',
  fontWeight: 600,
  cursor: 'pointer',
  whiteSpace: 'nowrap',
};
</script>
