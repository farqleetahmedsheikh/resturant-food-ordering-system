export function normalizePhone(value: string): string {
  return value.replace(/[^\d+]/g, '').trim();
}
