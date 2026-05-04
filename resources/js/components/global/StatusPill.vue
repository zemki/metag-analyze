<template>
  <span :style="pillStyle" :title="info.note">{{ info.label }}</span>
</template>

<script setup>
import { computed } from 'vue';

/**
 * StatusPill — case status badge in four states.
 *
 * Used in: cases list rows, case-detail strip, status-guide legend.
 *
 * Visual spec (design_handoff_metag_redesign/README.md):
 *   padding 2px 8px · radius 4 · font 11.5/500 · whitespace-nowrap
 *
 * The "backend" status only applies to regular projects. Mobile-app
 * projects suppress it via useProjectCapabilities; this component
 * does not enforce that.
 */

const props = defineProps({
  status: {
    type: String,
    required: true,
    validator: (v) => ['pending', 'active', 'completed', 'backend'].includes(v),
  },
});

const STATUS = {
  pending: {
    label: 'Pending',
    bg: '#fef3c7',
    fg: '#92400e',
    note: 'Case not yet started by user',
  },
  active: {
    label: 'Active',
    bg: '#dcfce7',
    fg: '#166534',
    note: 'Case currently in progress',
  },
  completed: {
    label: 'Completed',
    bg: '#f1f5f9',
    fg: '#334155',
    note: 'Case has ended',
  },
  backend: {
    label: 'Backend',
    bg: '#ede9fe',
    fg: '#5b21b6',
    note: 'Backend-only case',
  },
};

const info = computed(() => STATUS[props.status] ?? STATUS.pending);

const pillStyle = computed(() => ({
  display: 'inline-flex',
  alignItems: 'center',
  padding: '2px 8px',
  borderRadius: '4px',
  background: info.value.bg,
  color: info.value.fg,
  fontSize: '11.5px',
  fontWeight: 500,
  lineHeight: 1.5,
  whiteSpace: 'nowrap',
}));
</script>
