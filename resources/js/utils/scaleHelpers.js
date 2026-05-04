/**
 * Shared helpers for "scale" input types. Used by EntryTable's column
 * header, EditEntryModal's chip-button group, and ScaleBar's display
 * renderer — they all need to derive the same min/max/steps/label from
 * an input config.
 *
 * Source order:
 *   1. input.martMetadata.{minValue, maxValue, steps}  (MART projects)
 *   2. input.{minValue, maxValue, steps}               (non-MART scale)
 *   3. Defaults: 1..5 with step 1
 */

/** Resolved minimum value (default 1). */
export function scaleMin(input) {
  return input?.martMetadata?.minValue ?? input?.minValue ?? 1;
}

/** Resolved maximum value (default 5). */
export function scaleMax(input) {
  return input?.martMetadata?.maxValue ?? input?.maxValue ?? 5;
}

/** Resolved step (default 1). */
export function scaleStep(input) {
  return input?.martMetadata?.steps ?? input?.steps ?? 1;
}

/**
 * Raw `{ min, max }` with `null` for missing — useful for renderers that
 * need to know whether the range was actually configured (vs. defaulted).
 * `ScaleBar` uses this to suppress the bar when the range is unknown.
 */
export function scaleRange(input) {
  if (!input) return { min: null, max: null };
  const md = input.martMetadata || {};
  const min = md.minValue ?? input.minValue ?? null;
  const max = md.maxValue ?? input.maxValue ?? null;
  return { min, max };
}

/**
 * Pretty "min–max" label (en dash). Returns '' when the range can't be
 * derived — callers typically conditionally render the hint based on
 * the empty string check.
 */
export function scaleRangeLabel(input) {
  const { min, max } = scaleRange(input);
  if (min === null || min === undefined || max === null || max === undefined) return '';
  return `${min}–${max}`;
}

/**
 * The integer (or step-spaced) ladder from min to max. Falls back to
 * `[1, 2, 3, 4, 5]` when the configured range is unusable.
 */
export function scaleOptions(input) {
  const min = scaleMin(input);
  const max = scaleMax(input);
  const step = scaleStep(input);
  if (max <= min || step <= 0) return [1, 2, 3, 4, 5];
  const out = [];
  for (let n = min; n <= max; n += step) out.push(n);
  return out;
}
