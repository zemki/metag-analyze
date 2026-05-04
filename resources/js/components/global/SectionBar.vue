<template>
  <div :style="barStyle">
    <h2 :style="titleStyle">{{ title }}</h2>
    <span style="flex: 1"></span>
    <span v-if="count !== null && count !== undefined" :style="countStyle">
      {{ count }} {{ count === 1 ? singular : plural }}
    </span>
    <slot name="trailing" />
  </div>
</template>

<script setup>
/**
 * SectionBar — light-tinted thin bar at the top of a list section.
 * Holds a title (e.g. "Cases", "Entries") and a trailing count
 * ("N cases"). Used above filter rows.
 *
 * Visual spec (design_handoff_metag_redesign/README.md → Cases list §2):
 *   padding 12px 20px · bg #fbfcfe · border-bottom #e3e8f3
 *   title 14px/600 · count 12px tabular-nums #6b7795
 */

defineProps({
  title: { type: String, required: true },
  count: { type: [Number, null], default: null },
  singular: { type: String, default: 'item' },
  plural: { type: String, default: 'items' },
});

const barStyle = {
  display: 'flex',
  alignItems: 'center',
  padding: '12px 20px',
  background: '#fbfcfe',
  borderBottom: '1px solid #e3e8f3',
};

const titleStyle = {
  margin: 0,
  fontSize: '14px',
  fontWeight: 600,
  color: '#0f1b3d',
};

const countStyle = {
  fontSize: '12px',
  color: '#6b7795',
  fontVariantNumeric: 'tabular-nums',
};
</script>
