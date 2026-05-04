/**
 * inputRenderers — registry mapping a project input's `type` to the
 * Vue component that renders its value in the entry list / edit modal.
 *
 * Single extension point: add a new question type by:
 *   1. Adding `{ key, type, label }` to the project's inputs config.
 *   2. Registering a renderer here.
 *
 * Renderers receive: { value, input, entry, mode } as props
 *   - value: the entry's stored answer for this input (any shape per type)
 *   - input: the input config (`{ name, type, ...typeSpecificFields }`)
 *   - entry: the full entry row — for renderers that need data outside
 *            `entry.inputs[input.name]` (e.g. audio cells, which read
 *            `entry.file_object` / `entry.file_path` because the audio
 *            blob lives on the entry, not under the input key).
 *   - mode:  'display' | 'edit'  (only 'display' is used by the entry table)
 *
 * Unknown types fall back to UnknownTypeStub so a misconfigured project
 * never crashes the table.
 */

import DefaultRenderer from '../components/global/renderers/DefaultRenderer.vue';
import UnknownTypeStub from '../components/global/renderers/UnknownTypeStub.vue';
import ScaleBar from '../components/global/renderers/ScaleBar.vue';
import OneChoiceChip from '../components/global/renderers/OneChoiceChip.vue';
import MultipleChoiceChips from '../components/global/renderers/MultipleChoiceChips.vue';
import AudioCell from '../components/global/renderers/AudioCell.vue';

const RENDERERS = {
  text: DefaultRenderer,
  scale: ScaleBar,
  'one choice': OneChoiceChip,
  'multiple choice': MultipleChoiceChips,
  'audio recording': AudioCell,
};

/**
 * Look up the renderer component for an input type.
 *
 * @param {string} type
 * @returns {object} Vue component
 */
export function rendererFor(type) {
  return RENDERERS[type] ?? UnknownTypeStub;
}

/**
 * The set of registered types. Useful for tests / docs.
 */
export const REGISTERED_TYPES = Object.freeze(Object.keys(RENDERERS));
