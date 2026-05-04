<template>
  <div :style="rowStyle">
    <slot />
  </div>
</template>

<script setup>
/**
 * FilterRow — the search + selects + sort-toggle row that sits below
 * SectionBar. Generic wrapper: callers compose their own controls
 * (search input, status select, sort field select, asc/desc button,
 * page-size select) into the default slot.
 *
 * Visual spec (design_handoff_metag_redesign/README.md → Cases list §4):
 *   padding 12px 20px · bg white · border-bottom #e3e8f3 · gap 8px
 */

const rowStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '12px 20px',
  borderBottom: '1px solid #e3e8f3',
  background: '#ffffff',
};
</script>
