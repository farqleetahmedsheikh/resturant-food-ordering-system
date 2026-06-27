import { AppText } from './AppText';
import { colors } from '@/src/theme';
import { formatCurrency } from '@/src/utils/currency';

type PriceTextProps = {
  amount: number | string | null | undefined;
  variant?: 'h1' | 'h2' | 'title' | 'body' | 'caption';
  color?: string;
};

export function PriceText({ amount, variant = 'title', color = colors.gold.dark }: PriceTextProps) {
  return (
    <AppText variant={variant} color={color}>
      {formatCurrency(amount)}
    </AppText>
  );
}
