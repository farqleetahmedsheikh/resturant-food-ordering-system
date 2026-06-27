export function createIdempotencyKey(prefix = 'mobile'): string {
  return `${prefix}-${Date.now()}-${Math.random().toString(16).slice(2)}`;
}
