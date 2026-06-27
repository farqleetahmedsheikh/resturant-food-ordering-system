import { appConfig } from '@/src/config/app';

export function formatCurrency(amount: number | string | null | undefined): string {
  const value = Number(amount ?? 0);

  return new Intl.NumberFormat(appConfig.locale, {
    style: 'currency',
    currency: appConfig.currency,
  }).format(Number.isFinite(value) ? value : 0);
}
