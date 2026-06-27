import { StyleSheet, View } from 'react-native';

import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { colors, spacing } from '@/src/theme';
import { formatCurrency } from '@/src/utils/currency';
import type { Delivery } from '@/src/types/delivery';

export function DeliveryRow({ delivery }: { delivery: Delivery }) {
  return (
    <AppCard style={styles.card}>
      <View style={styles.top}>
        <View style={styles.flex}>
          <AppText variant="title">{delivery.order_number}</AppText>
          <AppText color={colors.text.secondary}>{delivery.customer_name}</AppText>
        </View>
        <StatusBadge status={delivery.delivery_status ?? delivery.order_status} />
      </View>
      <AppText color={colors.text.secondary} numberOfLines={2}>
        {delivery.delivery_address}
      </AppText>
      <AppText variant="title" color={colors.gold.dark}>
        {formatCurrency(delivery.total)}
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
