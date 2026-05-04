<template>
  <Modal
    :visible="visible"
    :title="isAdding ? trans('Add Entry') : trans('Edit Entry')"
    :confirm-text="isAdding ? trans('Add Entry') : trans('Save Changes')"
    :cancel-text="trans('Cancel')"
    size="lg"
    @confirm="onConfirm"
    @cancel="$emit('close')"
  >
    <div :style="formStyle">
      <!-- Begin -->
      <div :style="fieldStyle">
        <div :style="labelRowStyle">
          <label :style="labelStyle">
            {{ trans('Start') }} <span :style="reqStyle">*</span>
          </label>
        </div>
        <input
          type="datetime-local"
          v-model="form.begin"
          @input="onBeginChange"
          :style="inputStyle"
        />
      </div>

      <!-- End -->
      <div :style="fieldStyle">
        <div :style="labelRowStyle">
          <label :style="labelStyle">
            {{ trans('End') }} <span :style="reqStyle">*</span>
          </label>
          <span v-if="durationLabel" :style="hintRightStyle">{{ durationLabel }}</span>
        </div>
        <input type="datetime-local" v-model="form.end" :style="inputStyle" />
      </div>

      <!-- Media / Entity (regular projects only) -->
      <div v-if="!isMartProject" :style="fieldStyle">
        <div :style="labelRowStyle">
          <label :style="labelStyle">
            {{ entityLabel }} <span :style="reqStyle">*</span>
          </label>
        </div>
        <input type="text" v-model="form.media" :style="inputStyle" />
      </div>

      <!-- Inputs section header (only when there are inputs to fill) -->
      <div v-if="visibleInputs.length > 0" :style="sectionHeaderStyle">
        {{ trans('Inputs') }}
      </div>

      <div v-for="input in visibleInputs" :key="input.name" :style="fieldStyle">
        <div :style="labelRowStyle">
          <label :style="labelStyle">
            {{ input.name }}
            <span v-if="input.mandatory" :style="reqStyle">*</span>
          </label>
          <span v-if="rightHint(input)" :style="hintRightStyle">{{ rightHint(input) }}</span>
        </div>

        <!-- Text -->
        <input
          v-if="input.type === 'text'"
          type="text"
          v-model="form.inputs[input.name]"
          :style="inputStyle"
        />

        <!-- One choice — segmented chip buttons -->
        <div
          v-else-if="input.type === 'one choice'"
          :style="chipGroupStyle"
          role="radiogroup"
        >
          <button
            v-for="ans in cleanAnswers(input)"
            :key="ans"
            type="button"
            role="radio"
            :aria-checked="oneChoiceSelected(input, ans)"
            :style="oneChoiceSelected(input, ans) ? segmentedChipSelectedStyle : segmentedChipStyle"
            @click="selectOneChoice(input, ans)"
          >
            {{ ans }}
          </button>
        </div>

        <!-- Multiple choice — wrapping toggle chips -->
        <div
          v-else-if="input.type === 'multiple choice'"
          :style="chipGroupStyle"
          role="group"
        >
          <button
            v-for="ans in cleanAnswers(input)"
            :key="ans"
            type="button"
            role="checkbox"
            :aria-checked="multipleSelected(input, ans)"
            :style="multipleSelected(input, ans) ? wrapChipSelectedStyle : wrapChipStyle"
            @click="toggleMultiple(input, ans)"
          >
            {{ ans }}
          </button>
        </div>

        <!-- Scale — segmented chip buttons (small ranges) or number input (large ranges) -->
        <template v-else-if="input.type === 'scale'">
          <div v-if="useScaleChips(input)" :style="chipGroupStyle" role="radiogroup">
            <button
              v-for="n in scaleOptions(input)"
              :key="n"
              type="button"
              role="radio"
              :aria-checked="scaleSelected(input, n)"
              :style="scaleSelected(input, n) ? segmentedChipSelectedStyle : segmentedChipStyle"
              @click="selectScale(input, n)"
            >
              {{ n }}
            </button>
          </div>
          <input
            v-else
            type="number"
            :min="scaleMin(input)"
            :max="scaleMax(input)"
            :step="scaleStep(input)"
            v-model.number="form.inputs[input.name]"
            :style="inputStyle"
          />
        </template>

        <!-- Fallback for unknown types -->
        <div v-else :style="readOnlyStyle">
          <em>{{ trans('No editor for type') }} "{{ input.type }}"</em>
        </div>
      </div>

      <!-- Inline error message -->
      <div v-if="errorMessage" :style="errorStyle" role="alert">
        {{ errorMessage }}
      </div>

      <div :style="legendStyle">* {{ trans('required') }}</div>
    </div>
  </Modal>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import moment from 'moment';
import Modal from './modal.vue';
import {
  scaleMin,
  scaleMax,
  scaleStep,
  scaleOptions,
} from '../../utils/scaleHelpers.js';

/**
 * EditEntryModal — modal for adding or editing an entry on a case.
 *
 * Form fields are styled to match the rest of the redesign:
 *   - text inputs: rounded with #e3e8f3 borders
 *   - one choice / scale: segmented chip buttons (radio-group semantics)
 *   - multiple choice: wrapping toggle chips (checkbox-group semantics)
 *
 * Each label row has an optional right-aligned hint:
 *   - End: "Xm" duration
 *   - scale: "value / max" once chosen, else "min–max" range
 *   - multiple choice: "N selected" when any are picked
 *
 * Save/error handling is local to this component — on success it emits
 * `saved`; on failure it shows an inline error and stays open so the user
 * doesn't lose their form state.
 */

const props = defineProps({
  visible: { type: Boolean, default: false },
  entry: { type: Object, default: null },
  caseId: { type: [Number, String], required: true },
  projectInputs: { type: Array, default: () => [] },
  isMartProject: { type: Boolean, default: false },
  entityLabel: { type: String, default: 'Media' },
});

const emit = defineEmits(['close', 'saved']);

const isAdding = computed(() => !props.entry || !props.entry.id);

const form = reactive({
  begin: '',
  end: '',
  media: '',
  inputs: {},
});

const errorMessage = ref('');

// Skip the MART config marker + items with no name (defensive — same
// hygiene rule used in EntryTable's `inputColumns`).
const visibleInputs = computed(() =>
  (props.projectInputs ?? []).filter((i) => i && i.name && i.type !== 'mart')
);

function trans(key) {
  if (typeof window.trans === 'undefined' || typeof window.trans[key] === 'undefined') {
    return key;
  }
  if (window.trans[key] === '') return key;
  return window.trans[key];
}

function cleanAnswers(input) {
  return (input.answers || []).filter((a) => a && String(a).trim() !== '');
}

// Use chips when the option count is reasonable; otherwise fall back to
// a number input. 11 covers the common 0-10 and 1-10 cases neatly.
function useScaleChips(input) {
  return scaleOptions(input).length <= 11;
}

// ---- Selection state helpers ----
function oneChoiceSelected(input, ans) {
  const cur = form.inputs[input.name];
  return Array.isArray(cur) && cur[0] === ans;
}
function selectOneChoice(input, ans) {
  // Toggle off when the same chip is clicked again on a non-mandatory
  // input. Mandatory inputs always stay set once chosen.
  if (oneChoiceSelected(input, ans) && !input.mandatory) {
    form.inputs[input.name] = [];
  } else {
    form.inputs[input.name] = [ans];
  }
}

function multipleSelected(input, ans) {
  const arr = form.inputs[input.name];
  return Array.isArray(arr) && arr.includes(ans);
}
function toggleMultiple(input, ans) {
  if (!Array.isArray(form.inputs[input.name])) form.inputs[input.name] = [];
  const arr = form.inputs[input.name];
  const idx = arr.indexOf(ans);
  if (idx >= 0) arr.splice(idx, 1);
  else arr.push(ans);
}

function scaleSelected(input, n) {
  return form.inputs[input.name] === n;
}
function selectScale(input, n) {
  if (scaleSelected(input, n) && !input.mandatory) {
    form.inputs[input.name] = '';
  } else {
    form.inputs[input.name] = n;
  }
}

// ---- Right-aligned hints (small grey text) ----
function rightHint(input) {
  if (input.type === 'multiple choice') {
    const arr = form.inputs[input.name];
    const n = Array.isArray(arr) ? arr.filter((x) => x !== '' && x != null).length : 0;
    return n > 0 ? `${n} selected` : '';
  }
  if (input.type === 'scale') {
    const v = form.inputs[input.name];
    const max = scaleMax(input);
    const min = scaleMin(input);
    if (v !== '' && v !== null && v !== undefined) {
      return `${v} / ${max}`;
    }
    return `${min}–${max}`;
  }
  return '';
}

const durationLabel = computed(() => {
  if (!form.begin || !form.end) return '';
  const start = moment(form.begin);
  const end = moment(form.end);
  if (!start.isValid() || !end.isValid()) return '';
  const mins = end.diff(start, 'minutes');
  if (mins <= 0) return '';
  if (mins < 60) return `${mins}m`;
  const h = Math.floor(mins / 60);
  const m = mins % 60;
  return m === 0 ? `${h}h` : `${h}h ${m}m`;
});

// ---- Form lifecycle ----
function defaultBegin() {
  return moment().format('YYYY-MM-DDTHH:mm');
}
function defaultEnd() {
  return moment().add(5, 'minutes').format('YYYY-MM-DDTHH:mm');
}
function fmtDateForInput(raw) {
  if (!raw) return '';
  return moment(raw).add(moment(raw).utcOffset(), 'minutes').toISOString().slice(0, 16);
}
function safeParse(s) {
  try { return JSON.parse(s); } catch { return {}; }
}

function resetFromEntry() {
  errorMessage.value = '';
  Object.keys(form.inputs).forEach((k) => delete form.inputs[k]);

  if (isAdding.value) {
    form.begin = defaultBegin();
    form.end = defaultEnd();
    form.media = '';
    visibleInputs.value.forEach((i) => {
      if (i.type === 'multiple choice' || i.type === 'one choice') {
        form.inputs[i.name] = [];
      } else {
        form.inputs[i.name] = '';
      }
    });
    return;
  }

  const e = props.entry;
  form.begin = fmtDateForInput(e.begin);
  form.end = fmtDateForInput(e.end);
  form.media = e.media ?? '';
  const parsed = typeof e.inputs === 'string' ? safeParse(e.inputs) : (e.inputs || {});

  visibleInputs.value.forEach((i) => {
    const v = parsed?.[i.name];
    if (i.type === 'one choice') {
      form.inputs[i.name] = Array.isArray(v) ? v : [v ?? ''];
    } else if (i.type === 'multiple choice') {
      form.inputs[i.name] = Array.isArray(v) ? v : (v ? [v] : []);
    } else if (i.type === 'scale') {
      if (v === undefined || v === null || v === '') {
        form.inputs[i.name] = '';
      } else {
        form.inputs[i.name] = typeof v === 'number' ? v : parseInt(v, 10);
      }
    } else {
      form.inputs[i.name] = v ?? '';
    }
  });
}

function onBeginChange() {
  if (form.begin) {
    form.end = moment(form.begin).add(5, 'minutes').format('YYYY-MM-DDTHH:mm');
  }
}

function cleanInputsForSave(inputs) {
  if (!inputs || typeof inputs !== 'object') return inputs;
  return Object.fromEntries(
    Object.entries(inputs).filter(
      ([key]) => key && key !== 'undefined' && key !== 'null' && key !== 'firstValue',
    ),
  );
}

function isValid() {
  if (!form.begin || !form.end) return false;
  if (!props.isMartProject && !form.media) return false;
  const missing = visibleInputs.value.find((i) => {
    if (!i.mandatory) return false;
    const v = form.inputs[i.name];
    if (Array.isArray(v)) return v.filter((x) => x !== '' && x !== null && x !== undefined).length === 0;
    return v === '' || v === null || v === undefined;
  });
  return !missing;
}

function onConfirm() {
  errorMessage.value = '';
  if (!isValid()) {
    errorMessage.value = trans('Check your mandatory entries.');
    return;
  }

  const payload = {
    case_id: props.caseId,
    inputs: cleanInputsForSave(form.inputs),
    begin: moment(form.begin).format('YYYY-MM-DD HH:mm:ss.SSSSSS'),
    end: moment(form.end).format('YYYY-MM-DD HH:mm:ss.SSSSSS'),
    media_id: form.media,
  };

  const req = isAdding.value
    ? window.axios.post(`/cases/${props.caseId}/entries`, payload)
    : window.axios.patch(`/cases/${props.caseId}/entries/${props.entry.id}`, payload);

  req
    .then(() => emit('saved', { isAdding: isAdding.value }))
    .catch(() => {
      errorMessage.value = trans(
        'There was an error during the request - double check your data or contact the support.',
      );
    });
}

watch(
  () => [props.visible, props.entry],
  ([v]) => {
    if (v) resetFromEntry();
  },
  { immediate: true },
);

// ---- Inline styles ----
const formStyle = { display: 'flex', flexDirection: 'column', gap: '14px', paddingTop: '6px' };
const fieldStyle = { display: 'flex', flexDirection: 'column', gap: '6px' };

const labelRowStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'baseline',
  gap: '12px',
};
const labelStyle = {
  fontSize: '11px',
  fontWeight: 600,
  letterSpacing: '0.6px',
  textTransform: 'uppercase',
  color: '#6b7795',
  display: 'inline-flex',
  alignItems: 'center',
  gap: '6px',
};
const reqStyle = { color: '#dc2626', fontWeight: 600 };
const hintRightStyle = {
  fontSize: '11px',
  color: '#94a3b8',
  letterSpacing: 'normal',
  textTransform: 'none',
  fontWeight: 400,
  fontVariantNumeric: 'tabular-nums',
};

const inputStyle = {
  width: '100%',
  padding: '8px 11px',
  border: '1px solid #e3e8f3',
  borderRadius: '8px',
  background: '#ffffff',
  fontSize: '13.5px',
  color: '#0f1b3d',
  outline: 'none',
  fontFamily: 'inherit',
  boxSizing: 'border-box',
};

// Chip group container (wraps for both segmented and free-floating chips)
const chipGroupStyle = {
  display: 'flex',
  flexWrap: 'wrap',
  gap: '6px',
};

// Segmented (one-choice + scale): each chip stretches to share the row.
// Text is allowed to wrap inside a chip so long labels (e.g.
// "Email/communication") stay legible — the chip just grows vertically.
const segmentedChipBase = {
  flex: '1 1 0',
  minWidth: '64px',
  textAlign: 'center',
  padding: '8px 12px',
  borderRadius: '8px',
  fontSize: '13px',
  lineHeight: 1.3,
  cursor: 'pointer',
  fontFamily: 'inherit',
  transition: 'background .1s, border-color .1s',
  boxSizing: 'border-box',
  whiteSpace: 'normal',
  overflowWrap: 'break-word',
  wordBreak: 'break-word',
  hyphens: 'auto',
};
const segmentedChipStyle = {
  ...segmentedChipBase,
  border: '1px solid #e3e8f3',
  background: '#ffffff',
  color: '#3b4768',
  fontWeight: 500,
};
const segmentedChipSelectedStyle = {
  ...segmentedChipBase,
  border: '1.5px solid #2563eb',
  // Trim padding by 0.5px on each side to keep total size constant when
  // the border thickens — otherwise chips visibly grow on selection.
  padding: '7.5px 11.5px',
  background: '#eff4ff',
  color: '#1d4ed8',
  fontWeight: 600,
};

// Wrapping (multi-choice): chips sized to content. Whole chips wrap to
// new lines as needed; text inside a single chip stays on one line.
const wrapChipBase = {
  padding: '8px 14px',
  borderRadius: '8px',
  fontSize: '13px',
  lineHeight: 1.3,
  cursor: 'pointer',
  fontFamily: 'inherit',
  whiteSpace: 'nowrap',
  transition: 'background .1s, border-color .1s',
  boxSizing: 'border-box',
};
const wrapChipStyle = {
  ...wrapChipBase,
  border: '1px solid #e3e8f3',
  background: '#ffffff',
  color: '#3b4768',
  fontWeight: 500,
};
const wrapChipSelectedStyle = {
  ...wrapChipBase,
  border: '1.5px solid #2563eb',
  padding: '7.5px 13.5px',
  background: '#eff4ff',
  color: '#1d4ed8',
  fontWeight: 600,
};

const sectionHeaderStyle = {
  marginTop: '4px',
  fontSize: '11px',
  fontWeight: 600,
  letterSpacing: '0.6px',
  textTransform: 'uppercase',
  color: '#0f1b3d',
  borderBottom: '1px solid #e3e8f3',
  paddingBottom: '6px',
};
const legendStyle = { fontSize: '11.5px', color: '#6b7795', marginTop: '2px' };
const readOnlyStyle = {
  padding: '8px 11px',
  border: '1px dashed #e3e8f3',
  borderRadius: '8px',
  background: '#fbfcfe',
  color: '#94a3b8',
  fontSize: '12.5px',
};
const errorStyle = {
  padding: '8px 11px',
  border: '1px solid #fecaca',
  borderRadius: '8px',
  background: '#fef2f2',
  color: '#b91c1c',
  fontSize: '12.5px',
};
</script>
