import { formatCurrency } from '@/src/utils/currency';

describe('formatCurrency', () => {
  it('formats values as AUD using en-AU', () => {
    expect(formatCurrency(14.5)).toBe('$14.50');
  });
});
