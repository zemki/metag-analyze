import { ref } from 'vue';
import { useProjectCapabilities } from '@/utils/useProjectCapabilities';

// CaseRow, CaseDetailStrip and EntryTable pass the project as a getter,
// `() => props.project`, so the composable must read getters too.
describe('useProjectCapabilities', () => {
  it('reads a MART project passed as a getter', () => {
    expect(useProjectCapabilities(() => ({ is_mart_project: true })).value.showQRAction).toBe(false);
  });

  it('reads a MART project passed as a ref', () => {
    expect(useProjectCapabilities(ref({ is_mart_project: true })).value.showQRAction).toBe(false);
  });

  it('reads a MART project passed as a plain object', () => {
    expect(useProjectCapabilities({ is_mart_project: true }).value.showQRAction).toBe(false);
  });

  it('keeps the QR code for a MeTag project passed as a getter', () => {
    expect(useProjectCapabilities(() => ({ is_mart_project: false })).value.showQRAction).toBe(true);
  });
});
