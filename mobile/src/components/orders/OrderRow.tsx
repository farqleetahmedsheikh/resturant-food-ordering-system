import { router } from 'expo-router';
import { Pressable, StyleSheet, View } from 'react-native';

import { AppIcon, appIcons } from '@/src/components/common/AppIcon';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { PriceText } from '@/src/components/common/PriceText';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { colors, radius, spacing } from '@/src/theme';
import { formatDate, formatDateTime } from '@/src/utils/date';
import type { Order } from '@/src/types/order';
import type { Href } from 'expo-router';

type OrderRowProps = {
  order: Order;
  href?: Href;
  compact?: boolean;
};

export function OrderRow({ order, href, compact = false }: OrderRowProps) {
  const customerName = order.customer?.name ?? 'Guest customer';
  const deliveryStatus = order.delivery?.status_label ?? order.delivery?.status;

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
      <AppCard style={[styles.card, compact && styles.compactCard]}>
        <View style={styles.top}>
          <View style={styles.iconShell}>
            <AppIcon name={appIcons.orders} size={compact ? 18 : 22} />
          </View>
          <View style={styles.flex}>
            <AppText variant="title" numberOfLines={1}>
              {order.order_number}
            </AppText>
            <AppText variant="caption" color={colors.text.secondary}>
              {customerName} · {compact ? formatDateTime(order.created_at) : formatDate(order.created_at)}
            </AppText>
          </View>
          <StatusBadge status={order.order_status_label ?? order.order_status} />
        </View>
        <AppText color={colors.text.secondary} numberOfLines={1}>
          {order.delivery_address ?? 'No delivery address'}
        </AppText>
        <View style={styles.footer}>
          <View style={styles.metaRow}>
            <PriceText amount={order.total} />
            <View style={styles.dot} />
            <AppText variant="caption" color={paymentColor(order.payment_status)}>
              {order.payment_status.replaceAll('_', ' ')}
            </AppText>
            {deliveryStatus ? (
              <>
                <View style={styles.dot} />
                <AppText variant="caption" color={colors.text.secondary} numberOfLines={1}>
                  {deliveryStatus.replaceAll('_', ' ')}
                </AppText>
              </>
            ) : null}
          </View>
          {href ? (
            <View style={styles.viewPill}>
              <AppIcon name={appIcons.chevronRight} size={16} color={colors.brand.primary} />
            </View>
          ) : null}
        </View>
      </AppCard>
    </Pressable>
  );
}

function paymentColor(status: string): string {
  if (status === 'paid') {
    return colors.green.dark;
  }

  if (['failed', 'cancelled'].includes(status)) {
    return colors.semantic.danger;
  }

  return colors.gold.dark;
}

const styles = StyleSheet.create({
  card: {
    gap: spacing.sm,
  },
  compactCard: {
    padding: spacing.md,
    borderRadius: radius.lg,
  },
  top: {
    flexDirection: 'row',
    gap: spacing.sm,
    alignItems: 'center',
  },
  iconShell: {
    width: 34,
    height: 34,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.md,
    backgroundColor: colors.brand.soft,
  },
  footer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.sm,
  },
  metaRow: {
    flex: 1,
    minWidth: 0,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  dot: {
    width: 4,
    height: 4,
    borderRadius: radius.pill,
    backgroundColor: colors.border.strong,
  },
  viewPill: {
    width: 32,
    height: 32,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.pill,
    backgroundColor: colors.brand.soft,
  },
  flex: {
    flex: 1,
    minWidth: 0,
  },
});
