<template>
  <div :style="outerWrapStyle">
    <!-- Scroll wrapper. Always-visible horizontal scrollbar (CSS, see <style>)
         plus edge gradient shadows so it's unmistakable that there's
         off-screen content. -->
    <div
      ref="scrollWrap"
      class="entry-table-scroll"
      :style="scrollWrapStyle"
      @scroll="updateScrollState"
    >
      <table :style="tableStyle">
        <thead>
          <tr>
            <th :style="thChevronStyle"></th>

            <th :style="thStyleFor('start')" :title="'Start'">
              <div :style="thInnerStyle">
                <span :style="thLabelStyle">Start</span>
              </div>
              <div
                :class="resizeHandleClass('start')"
                @mousedown.stop.prevent="startColumnResize($event, 'start')"
              ></div>
            </th>

            <th :style="thStyleFor('end')" :title="'End'">
              <div :style="thInnerStyle">
                <span :style="thLabelStyle">End</span>
              </div>
              <div
                :class="resizeHandleClass('end')"
                @mousedown.stop.prevent="startColumnResize($event, 'end')"
              ></div>
            </th>

            <th :style="thStyleFor('duration')" :title="'Duration'">
              <div :style="thInnerStyle">
                <span :style="thLabelStyle">Duration</span>
              </div>
              <div
                :class="resizeHandleClass('duration')"
                @mousedown.stop.prevent="startColumnResize($event, 'duration')"
              ></div>
            </th>

            <th
              v-if="showMediaColumn"
              :style="thStyleFor('media')"
              :title="entityLabel"
            >
              <div :style="thInnerStyle">
                <span :style="thLabelStyle">{{ entityLabel }}</span>
              </div>
              <div
                :class="resizeHandleClass('media')"
                @mousedown.stop.prevent="startColumnResize($event, 'media')"
              ></div>
            </th>

            <th
              v-for="input in inputColumns"
              :key="`th-${input.name}`"
              :style="thStyleFor('input-' + input.name)"
              :title="input.name"
            >
              <div :style="thInnerStyle">
                <span :style="thLabelStyle">{{ input.name }}</span>
                <span
                  v-if="input.type === 'scale' && scaleRangeLabel(input)"
                  :style="thScaleHintStyle"
                >
                  {{ scaleRangeLabel(input) }}
                </span>
              </div>
              <div
                :class="resizeHandleClass('input-' + input.name)"
                @mousedown.stop.prevent="startColumnResize($event, 'input-' + input.name)"
              ></div>
            </th>

            <!-- Actions column: not resizable, fixed width. -->
            <th :style="thActionsStyle"></th>
          </tr>
        </thead>
        <tbody>
          <template v-for="(entry, index) in entries" :key="entry.id ?? index">
            <tr
              @mouseenter="hoveredId = entry.id ?? index"
              @mouseleave="hoveredId = null"
              @dblclick="$emit('edit', entry)"
              :style="rowStyle(entry, index)"
            >
              <td :style="tdChevronStyle">
                <button
                  v-if="hasPreviousVersion(entry)"
                  type="button"
                  :title="expandedId === (entry.id ?? index) ? 'Hide previous version' : 'Show previous version'"
                  :style="chevronBtnStyle(entry, index)"
                  @click="toggleExpand(entry.id ?? index)"
                >
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                       stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6" />
                  </svg>
                </button>
              </td>
              <td :style="tdStartStyle">
                <div :style="stackedCellStyle">
                  <span>{{ formatTime(entry.begin) }}</span>
                  <span :style="stackedSubStyle">{{ formatDate(entry.begin) }}</span>
                </div>
              </td>
              <td :style="tdEndStyle">
                <div :style="stackedCellStyle">
                  <span>{{ formatTime(entry.end) }}</span>
                  <span :style="stackedSubStyle">
                    {{ sameDay(entry.begin, entry.end) ? '' : formatDate(entry.end) }}
                  </span>
                </div>
              </td>
              <td :style="tdDurationStyle">{{ formatDuration(entry.begin, entry.end) }}</td>
              <td v-if="showMediaColumn" :style="tdStyle">
                <span v-if="entry.media" :style="mediaCellStyle" :title="entry.media">
                  {{ entry.media }}
                </span>
                <span v-else :style="placeholderCellStyle">—</span>
              </td>
              <td v-for="input in inputColumns" :key="`td-${entry.id ?? index}-${input.name}`" :style="tdStyle">
                <component
                  :is="rendererFor(input.type)"
                  :value="getInputValue(entry, input.name)"
                  :input="input"
                  :entry="entry"
                  mode="display"
                />
              </td>
              <td :style="tdActionsStyle">
                <span style="display: inline-flex; gap: 6px;">
                  <CaseAction
                    title="Edit entry"
                    tone="neutral"
                    @click="$emit('edit', entry)"
                  >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M12 20h9" />
                      <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z" />
                    </svg>
                  </CaseAction>
                  <CaseAction
                    title="Delete entry"
                    tone="danger"
                    @click="$emit('delete', entry)"
                  >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M3 6h18" />
                      <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                      <path d="M10 11v6" />
                      <path d="M14 11v6" />
                      <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                    </svg>
                  </CaseAction>
                </span>
              </td>
            </tr>

            <!-- Re-submission expander (sub-row) -->
            <tr
              v-if="hasPreviousVersion(entry) && expandedId === (entry.id ?? index)"
              :style="prevRowStyle"
            >
              <td :colspan="totalColumns" :style="prevCellStyle">
                <div :style="prevHeaderStyle">
                  <span :style="prevHeaderLabelStyle">Previous version</span>
                  <span :style="prevHeaderMetaStyle">
                    Submitted {{ entry.created_at_readable || '' }}
                  </span>
                </div>
                <div :style="prevGridStyle">
                  <template v-if="showMediaColumn && entry.mediaforFirstValue">
                    <span :style="prevLabelStyle">{{ entityLabel }}</span>
                    <span>{{ entry.mediaforFirstValue }}</span>
                  </template>
                  <span :style="prevLabelStyle">Begin</span>
                  <span :style="prevValueStyle">{{ entry.inputs?.firstValue?.begin_readable || '—' }}</span>
                  <span :style="prevLabelStyle">End</span>
                  <span :style="prevValueStyle">{{ entry.inputs?.firstValue?.end_readable || '—' }}</span>
                  <template v-for="input in inputColumns" :key="`prev-${entry.id ?? index}-${input.name}`">
                    <span :style="prevLabelStyle">{{ input.name }}</span>
                    <component
                      :is="rendererFor(input.type)"
                      :value="getPrevInputValue(entry, input.name)"
                      :input="input"
                      :entry="entry.inputs?.firstValue || entry"
                      mode="display"
                    />
                  </template>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>

      <!-- End-of-list footer: clear visual stop so the researcher knows
           there's nothing further to scroll to vertically. -->
      <div :style="endOfListStyle">
        <span :style="endOfListLineStyle"></span>
        <span :style="endOfListTextStyle">
          End of list · {{ entries.length }} {{ entries.length === 1 ? 'entry' : 'entries' }}
        </span>
        <span :style="endOfListLineStyle"></span>
      </div>
    </div>

    <!-- Edge gradient shadows hint at horizontal overflow. They fade in
         only on the side that has scrolled-off content (Stripe-style). -->
    <div :style="leftShadowStyle"></div>
    <div :style="rightShadowStyle"></div>
  </div>
</template>

<script setup>
import {
  computed,
  reactive,
  ref,
  onMounted,
  onBeforeUnmount,
  nextTick,
  watch,
} from 'vue';
import { rendererFor } from '../../utils/inputRenderers.js';
import { useProjectCapabilities } from '../../utils/useProjectCapabilities.js';
import { scaleRangeLabel } from '../../utils/scaleHelpers.js';
import CaseAction from './CaseAction.vue';

/**
 * EntryTable — flat compact table, one row per entry, columns derived
 * from the project's input config.
 *
 * Features layered on top of the base table:
 *   - Resizable columns (drag the right edge of any header).
 *   - Truncating headers (ellipsis when narrower than label text).
 *   - Always-visible horizontal scrollbar + edge gradient shadows so
 *     overflow is unmistakable.
 *   - Inline previous-version expander (chevron in the first cell).
 *   - End-of-list footer.
 *
 * Renderers receive `value`, `input`, `entry`, `mode` — see
 * `inputRenderers.js`. Mobile-app projects suppress the Media/Entity
 * column entirely.
 */

const props = defineProps({
  entries: { type: Array, required: true },
  /**
   * Array of `{ name, type, mandatory, ... }` objects from the project
   * config. The MART config marker (`type === 'mart'`) is filtered out
   * — it's not a question, just a project-type marker.
   */
  projectInputs: { type: Array, required: true, default: () => [] },
  project: { type: Object, required: true },
  entityLabel: { type: String, default: 'Media' },
});

defineEmits(['edit', 'delete']);

// ---- General state ----
const capabilities = useProjectCapabilities(() => props.project);
const expandedId = ref(null);
const hoveredId = ref(null);

// ---- Input columns derived from project config ----
const inputColumns = computed(() =>
  (props.projectInputs ?? []).filter((i) => i && i.name && i.type !== 'mart')
);

// MART detection — same approach as EditEntryModal: use the inputs
// config as the source of truth so a missing `project.is_mart_project`
// flag doesn't leak the Media column into MART projects.
const isMartFromInputs = computed(() => {
  const inputs = props.projectInputs ?? [];
  return inputs.length > 0 && inputs[0]?.type === 'mart';
});
const showMediaColumn = computed(
  () => !isMartFromInputs.value && capabilities.value.showMediaColumn
);

// ---- Column widths (resizable) ----
const columnWidths = reactive({});
const defaultWidths = {
  chevron: 28,
  start: 110,
  end: 110,
  duration: 80,
  media: 160,
  // input-<name>: dynamic via DEFAULT_INPUT_WIDTH below
  actions: 88,
};
const DEFAULT_INPUT_WIDTH = 170;
const MIN_COL_WIDTH = 56;
const MAX_COL_WIDTH = 600;

function colWidth(key) {
  if (columnWidths[key] != null) return columnWidths[key];
  if (defaultWidths[key] != null) return defaultWidths[key];
  return DEFAULT_INPUT_WIDTH;
}

const totalTableWidth = computed(() => {
  let w = colWidth('chevron') + colWidth('start') + colWidth('end') + colWidth('duration');
  if (showMediaColumn.value) w += colWidth('media');
  inputColumns.value.forEach((i) => {
    w += colWidth(`input-${i.name}`);
  });
  w += colWidth('actions');
  return w;
});

const totalColumns = computed(() =>
  // chevron + start + end + duration + (media?) + inputs + actions
  4 + (showMediaColumn.value ? 1 : 0) + inputColumns.value.length + 1
);

// ---- Column resize ----
const resizingKey = ref(null);

function startColumnResize(e, key) {
  resizingKey.value = key;
  const startX = e.clientX;
  const startWidth = colWidth(key);

  const onMove = (ev) => {
    const next = Math.max(
      MIN_COL_WIDTH,
      Math.min(MAX_COL_WIDTH, startWidth + (ev.clientX - startX))
    );
    columnWidths[key] = next;
  };
  const onUp = () => {
    resizingKey.value = null;
    document.removeEventListener('mousemove', onMove);
    document.removeEventListener('mouseup', onUp);
    document.body.style.cursor = '';
    document.body.style.userSelect = '';
  };

  document.body.style.cursor = 'col-resize';
  document.body.style.userSelect = 'none';
  document.addEventListener('mousemove', onMove);
  document.addEventListener('mouseup', onUp);
}

function resizeHandleClass(key) {
  return [
    'col-resize-handle',
    resizingKey.value === key ? 'col-resize-handle--active' : '',
  ];
}

// ---- Horizontal scroll detection (for edge shadows) ----
const scrollWrap = ref(null);
const canScrollLeft = ref(false);
const canScrollRight = ref(false);

function updateScrollState() {
  const el = scrollWrap.value;
  if (!el) {
    canScrollLeft.value = false;
    canScrollRight.value = false;
    return;
  }
  canScrollLeft.value = el.scrollLeft > 1;
  canScrollRight.value = el.scrollLeft + el.clientWidth < el.scrollWidth - 1;
}

onMounted(() => {
  nextTick(updateScrollState);
  window.addEventListener('resize', updateScrollState);
});
onBeforeUnmount(() => {
  window.removeEventListener('resize', updateScrollState);
});

// Recompute on data / column-width changes (column changes affect scrollWidth).
watch(
  () => [props.entries.length, props.projectInputs.length, totalTableWidth.value],
  () => nextTick(updateScrollState)
);

// ---- Cell helpers ----
function hasPreviousVersion(entry) {
  return !!(entry?.inputs && typeof entry.inputs === 'object' && entry.inputs.firstValue);
}
function getInputValue(entry, name) {
  if (!entry || !entry.inputs || typeof entry.inputs !== 'object') return null;
  return entry.inputs[name] ?? null;
}
function getPrevInputValue(entry, name) {
  const prev = entry?.inputs?.firstValue;
  if (!prev || typeof prev !== 'object') return null;
  return prev.inputs?.[name] ?? prev[name] ?? null;
}
function toggleExpand(id) {
  expandedId.value = expandedId.value === id ? null : id;
}

// ---- Date / time formatting ----
function pad(n) {
  return String(n).padStart(2, '0');
}
function parseEntryDate(raw) {
  if (!raw) return null;
  const iso = typeof raw === 'string' ? raw.replace(' ', 'T') : raw;
  const d = new Date(iso);
  return Number.isNaN(d.getTime()) ? null : d;
}
function formatTime(raw) {
  const d = parseEntryDate(raw);
  if (!d) return '—';
  return `${pad(d.getHours())}:${pad(d.getMinutes())}`;
}
function formatDate(raw) {
  const d = parseEntryDate(raw);
  if (!d) return '';
  return `${pad(d.getDate())}.${pad(d.getMonth() + 1)}.${d.getFullYear()}`;
}
function sameDay(a, b) {
  const da = parseEntryDate(a);
  const db = parseEntryDate(b);
  if (!da || !db) return true;
  return da.toDateString() === db.toDateString();
}
function formatDuration(begin, end) {
  const a = parseEntryDate(begin);
  const b = parseEntryDate(end);
  if (!a || !b) return '—';
  const ms = b.getTime() - a.getTime();
  if (ms < 0) return '—';
  const totalMin = Math.round(ms / 60000);
  const hh = Math.floor(totalMin / 60);
  const mm = totalMin % 60;
  if (hh === 0) return `${mm}m`;
  return `${hh}h ${pad(mm)}m`;
}

// ---- Inline styles ----
const outerWrapStyle = {
  position: 'relative',
};

const scrollWrapStyle = {
  overflowX: 'auto',
  overflowY: 'visible',
};

const tableStyle = computed(() => ({
  width: `${totalTableWidth.value}px`,
  minWidth: '100%',
  borderCollapse: 'collapse',
  fontSize: '13px',
  color: '#0f1b3d',
  tableLayout: 'fixed',
}));

const thStaticStyle = {
  position: 'relative',
  textAlign: 'left',
  padding: '10px 12px',
  fontWeight: 600,
  fontSize: '11px',
  letterSpacing: '0.6px',
  textTransform: 'uppercase',
  color: '#6b7795',
  borderBottom: '1px solid #e3e8f3',
  // Always-visible right divider — clearly marks the column boundary
  // and signals "you can grab here" to the user.
  borderRight: '1px solid #cbd5e1',
  background: '#fbfcfe',
  whiteSpace: 'nowrap',
  // No overflow: hidden here — truncation happens inside `thInnerStyle`,
  // and clipping the th would cut off the resize-handle's hover state
  // (which sits right at the column boundary).
};
function thStyleFor(key) {
  return { ...thStaticStyle, width: `${colWidth(key)}px` };
}

const thChevronStyle = {
  position: 'relative',
  textAlign: 'left',
  padding: '10px 4px 10px 12px',
  width: `${colWidth('chevron')}px`,
  borderBottom: '1px solid #e3e8f3',
  borderRight: '1px solid #cbd5e1',
  background: '#fbfcfe',
};

const thActionsStyle = computed(() => ({
  ...thStaticStyle,
  textAlign: 'right',
  width: `${colWidth('actions')}px`,
  // Last column — no right divider (would draw a stray line at the
  // table's outer right edge).
  borderRight: 'none',
}));

// Header inner — flex column for label + optional scale-range subline,
// constrained so children can ellipsize.
const thInnerStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'flex-start',
  gap: '2px',
  overflow: 'hidden',
  // Reserve a sliver on the right so the resize handle's hit area
  // doesn't visually overlap the label.
  paddingRight: '6px',
};
const thLabelStyle = {
  display: 'block',
  maxWidth: '100%',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  whiteSpace: 'nowrap',
};
const thScaleHintStyle = {
  fontSize: '10px',
  fontWeight: 400,
  letterSpacing: 'normal',
  textTransform: 'none',
  color: '#94a3b8',
  fontVariantNumeric: 'tabular-nums',
  display: 'block',
  maxWidth: '100%',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  whiteSpace: 'nowrap',
};

// Rows
function rowStyle(entry, index) {
  const id = entry.id ?? index;
  const isHover = hoveredId.value === id;
  return {
    background: isHover ? '#eff4ff' : index % 2 === 1 ? '#fbfcfe' : '#ffffff',
    transition: 'background .1s',
  };
}

const tdStyle = {
  padding: '10px 12px',
  borderBottom: '1px solid #eef1f8',
  fontSize: '13px',
  color: '#0f1b3d',
  verticalAlign: 'middle',
  // Clip cell content so renderers respect the column width and
  // ellipsize cleanly when the column is narrow.
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  whiteSpace: 'nowrap',
};
const tdChevronStyle = {
  ...tdStyle,
  width: `${colWidth('chevron')}px`,
  padding: '10px 4px 10px 12px',
  textAlign: 'center',
};
const tdStartStyle = {
  ...tdStyle,
  fontWeight: 500,
  fontVariantNumeric: 'tabular-nums',
};
const tdEndStyle = {
  ...tdStyle,
  color: '#3b4768',
  fontVariantNumeric: 'tabular-nums',
};
const tdDurationStyle = {
  ...tdStyle,
  fontWeight: 500,
  fontVariantNumeric: 'tabular-nums',
};
const tdActionsStyle = {
  ...tdStyle,
  textAlign: 'right',
  whiteSpace: 'nowrap',
  overflow: 'visible',
};

const stackedCellStyle = {
  display: 'flex',
  flexDirection: 'column',
  lineHeight: 1.25,
  overflow: 'hidden',
};
const stackedSubStyle = {
  fontSize: '11px',
  color: '#6b7795',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  whiteSpace: 'nowrap',
};
const mediaCellStyle = {
  fontSize: '12.5px',
  fontWeight: 500,
  color: '#0f1b3d',
  whiteSpace: 'nowrap',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  display: 'inline-block',
  maxWidth: '100%',
  verticalAlign: 'middle',
};
const placeholderCellStyle = {
  color: '#c4cce0',
};

function chevronBtnStyle(entry, index) {
  const id = entry.id ?? index;
  return {
    width: '20px',
    height: '20px',
    padding: 0,
    border: 'none',
    background: 'transparent',
    color: '#6b7795',
    cursor: 'pointer',
    borderRadius: '4px',
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    transform: expandedId.value === id ? 'rotate(90deg)' : 'none',
    transition: 'transform .15s',
  };
}

// Previous-version sub-row
const prevRowStyle = { background: '#fbfcfe' };
const prevCellStyle = {
  padding: '14px 20px 18px 48px',
  borderBottom: '1px solid #eef1f8',
};
const prevHeaderStyle = {
  display: 'flex',
  alignItems: 'baseline',
  gap: '14px',
  flexWrap: 'wrap',
  marginBottom: '10px',
};
const prevHeaderLabelStyle = {
  fontSize: '11px',
  fontWeight: 600,
  letterSpacing: '0.6px',
  textTransform: 'uppercase',
  color: '#6b7795',
};
const prevHeaderMetaStyle = {
  fontSize: '12px',
  color: '#6b7795',
  fontVariantNumeric: 'tabular-nums',
};
const prevGridStyle = {
  display: 'grid',
  gridTemplateColumns: '120px 1fr',
  rowGap: '8px',
  columnGap: '16px',
  fontSize: '12.5px',
  color: '#0f1b3d',
};
const prevLabelStyle = { color: '#6b7795' };
const prevValueStyle = { fontVariantNumeric: 'tabular-nums' };

// Edge gradient shadows — fade in/out via opacity so they don't sit
// awkwardly when there's no overflow on that side.
const leftShadowStyle = computed(() => ({
  position: 'absolute',
  top: 0,
  bottom: 0,
  left: 0,
  width: '32px',
  pointerEvents: 'none',
  background:
    'linear-gradient(to right, rgba(15, 27, 61, 0.10), rgba(15, 27, 61, 0))',
  opacity: canScrollLeft.value ? 1 : 0,
  transition: 'opacity .15s',
  zIndex: 2,
}));
const rightShadowStyle = computed(() => ({
  position: 'absolute',
  top: 0,
  bottom: 0,
  right: 0,
  width: '32px',
  pointerEvents: 'none',
  background:
    'linear-gradient(to left, rgba(15, 27, 61, 0.10), rgba(15, 27, 61, 0))',
  opacity: canScrollRight.value ? 1 : 0,
  transition: 'opacity .15s',
  zIndex: 2,
}));

// End-of-list footer
const endOfListStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '12px',
  padding: '20px 16px 24px',
  color: '#94a3b8',
};
const endOfListLineStyle = {
  flex: 1,
  height: '1px',
  background: '#e3e8f3',
};
const endOfListTextStyle = {
  fontSize: '11px',
  fontWeight: 500,
  letterSpacing: '0.6px',
  textTransform: 'uppercase',
  color: '#94a3b8',
  fontVariantNumeric: 'tabular-nums',
  whiteSpace: 'nowrap',
};
</script>

<style scoped>
/* Always-visible thin horizontal scrollbar so the user knows there's
   content past the right edge. Subtle styling to match the rest of the
   redesign tokens. */
.entry-table-scroll {
  scrollbar-color: #cbd5e1 #f8fafc; /* Firefox: thumb track */
  scrollbar-width: thin;
}
.entry-table-scroll::-webkit-scrollbar {
  height: 10px;
  background: #f8fafc;
}
.entry-table-scroll::-webkit-scrollbar-track {
  background: #f8fafc;
  border-top: 1px solid #e3e8f3;
}
.entry-table-scroll::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 5px;
  border: 2px solid #f8fafc;
}
.entry-table-scroll::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Column resize handle: a 6px-wide capture strip pinned to the right
   edge of each header. The visible line is always shown (1px slate) so
   the user can SEE the column edges and discover them as resize handles;
   on hover / active drag it widens to 2px and turns blue for a clear
   interaction signal. The strip slightly overlaps the next column (-3px)
   so it's easy to grab without precision aiming. */
.col-resize-handle {
  position: absolute;
  top: 0;
  right: -3px;
  bottom: 0;
  width: 6px;
  cursor: col-resize;
  user-select: none;
  z-index: 1;
}
/* The always-visible column divider is a real CSS border on the th itself
   (see thStaticStyle.borderRight). The handle adds a stronger blue bar
   on hover / active drag for a clear interaction signal. */
.col-resize-handle::before {
  content: '';
  position: absolute;
  top: 0;
  right: 2px;
  bottom: 0;
  width: 0;
  background: transparent;
  transition: background .12s, width .12s;
}
.col-resize-handle:hover::before,
.col-resize-handle--active::before {
  background: #2563eb;
  width: 2px;
}
</style>
