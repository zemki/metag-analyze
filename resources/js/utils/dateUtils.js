/**
 * Sanitize malformed date strings from the database.
 * Handles dates with double decimal format like "2025-11-26 15:20:34.273357.000000"
 *
 * @param {string|Date|number} dateValue - The date value to sanitize
 * @returns {string|Date|number} - The sanitized date value
 */
export function sanitizeDate(dateValue) {
  if (!dateValue) return dateValue;
  if (typeof dateValue === 'string') {
    // Fix double decimal format: "2025-11-26 15:20:34.273357.000000" -> "2025-11-26 15:20:34.273357"
    const doubleDotMatch = dateValue.match(/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+)\.\d+$/);
    if (doubleDotMatch) {
      return doubleDotMatch[1];
    }
  }
  return dateValue;
}

/**
 * Parse a date value that may be malformed, returning a Date object.
 *
 * @param {string|Date|number} dateValue - The date value to parse
 * @returns {Date} - A valid Date object
 */
export function parseDate(dateValue) {
  return new Date(sanitizeDate(dateValue));
}
