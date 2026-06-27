import { router } from 'expo-router';
import { Pressable, StyleSheet, View } from 'react-native';

import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { colors, spacing } from '@/src/theme';
import { formatCurrency } from '@/src/utils/currency';
import { formatDate } from '@/src/utils/date';
import type { Order } from '@/src/types/order';
import type { Href } from 'expo-router';

export function OrderRow({ order, href }: { order: Order; href?: Href }) {
  return (
    <Pressable
      accessibilityRole={href ? 'button' : undefined}
      disabled={!href}
      onPress={() => {
        if (href) {
          router.push(href);
        }
      }}
    >
      <AppCard style={styles.card}>
      <View style={styles.top}>
        <View style={styles.flex}>
          <AppText variant="title">{order.order_number}</AppText>
          <AppText color={colors.text.secondary}>{formatDate(order.created_at)}</AppText>
        </View>
        <StatusBadge status={order.order_status_label ?? order.order_status} />
      </View>
      <View style={styles.badges}>
        <StatusBadge status={order.payment_status} />
        {order.delivery?.status ? <StatusBadge status={order.delivery.status} /> : null}
      </View>
      <AppText variant="title" color={colors.gold.dark}>
        {formatCurrency(order.total)}
      </AppText>
      </AppCard>
    </Pressable>
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
  badges: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  flex: {
    flex: 1,
  },
});
