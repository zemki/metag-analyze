import { computed, toValue } from 'vue';

/**
 * useProjectCapabilities — single source of truth for the regular vs.
 * mobile-app (MART) project conditional rendering rules. Every screen
 * in the redesigned dashboard reads its conditionals from here, so the
 * type-aware behavior is defined exactly once.
 *
 * Pass the project (plain object, ref/reactive, or a getter such as
 * `() => props.project`) and read the returned computed for capability
 * flags + helper predicates. toValue() reads all three forms.
 *
 * Project shape this expects:
 *   {
 *     is_mart_project: boolean,
 *     ...other fields
 *   }
 *
 * @param  {object|import('vue').Ref<object>|(() => object)} project
 * @return {import('vue').ComputedRef<{
 *   isMartProject: boolean,
 *   showBackendStatus: boolean,
 *   showQRAction: boolean,
 *   showCloseEarlyAction: boolean,
 *   showAnalyticsButtons: boolean,
 *   showMediaColumn: boolean,
 *   showMediaInputInModal: boolean,
 *   isCaseClickable: (status: string) => boolean,
 *   caseClickDisabledReason: (status: string) => string|null,
 *   visibleStatuses: string[]
 * }>}
 */
export function useProjectCapabilities(project) {
  return computed(() => {
    const p = toValue(project) ?? {};
    const isMart = !!p.is_mart_project;

    return {
      isMartProject: isMart,

      // Status set
      showBackendStatus: !isMart,
      visibleStatuses: isMart
        ? ['pending', 'active', 'completed']
        : ['pending', 'active', 'completed', 'backend'],

      // Per-row actions
      showQRAction: !isMart,
      showCloseEarlyAction: !isMart,

      // Case-detail strip
      showAnalyticsButtons: !isMart,

      // Entry list / edit modal
      showMediaColumn: !isMart,
      showMediaInputInModal: !isMart,

      /**
       * Is a case row clickable (drill into entry list)?
       * - Regular: every row clickable.
       * - Mobile-app: only `completed` cases are clickable; others must
       *   render dimmed with a tooltip explaining why.
       */
      isCaseClickable(status) {
        return isMart ? status === 'completed' : true;
      },

      /**
       * Tooltip copy for non-clickable mobile-app rows. Returns null
       * when the row is clickable.
       */
      caseClickDisabledReason(status) {
        if (!isMart || status === 'completed') return null;
        return 'Mobile-app cases can only be inspected after the participant completes them.';
      },
    };
  });
}
