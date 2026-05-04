<template>
  <span :style="stubStyle" :title="`Unknown input type: ${input?.type ?? '?'}`">
    No renderer registered for input type "{{ input?.type ?? '?' }}"
  </span>
</template>

<script setup>
/**
 * UnknownTypeStub — fallback when an input has a type with no registered
 * renderer. Per the design's robustness rule, this must never crash the
 * entry list — it surfaces the missing renderer with a dashed border so
 * a developer adding a new input type sees the gap immediately.
 */

defineProps({
  value: { type: null, required: false, default: null },
  input: { type: Object, required: false, default: null },
  entry: { type: Object, required: false, default: null },
  mode: { type: String, default: 'display' },
});

const stubStyle = {
  display: 'inline-block',
  padding: '2px 8px',
  border: '1px dashed #c4cce0',
  borderRadius: '4px',
  color: '#6b7795',
  fontSize: '11px',
  fontStyle: 'italic',
  whiteSpace: 'nowrap',
};
</script>
