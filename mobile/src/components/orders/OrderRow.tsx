import { StyleSheet, View } from 'react-native';

import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { colors, spacing } from '@/src/theme';
import { formatCurrency } from '@/src/utils/currency';
import { formatDate } from '@/src/utils/date';
import type { Order } from '@/src/types/order';

export function OrderRow({ order }: { order: Order }) {
  return (
    <AppCard style={styles.card}>
      <View style={styles.top}>
        <View style={styles.flex}>
          <AppText variant="title">{order.order_number}</AppText>
          <AppText color={colors.text.secondary}>{formatDate(order.created_at)}</AppText>
        </View>
        <StatusBadge status={order.order_status} />
      </View>
      <AppText variant="title" color={colors.gold.dark}>
        {formatCurrency(order.total)}
      </AppText>
    </AppCard>
  );
}

const styles = StyleSheet.create({
  card: {
    gap: spacing.md,
  },
  top: {
    flexDirection: 'row',
    gap: spacing.md,
  },
  flex: {
    flex: 1,
  },
});
