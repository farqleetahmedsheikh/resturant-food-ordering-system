import { router } from 'expo-router';
import { Pressable, StyleSheet, View } from 'react-native';

import { AppIcon, appIcons } from '@/src/components/common/AppIcon';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { colors, radius, spacing } from '@/src/theme';
import type { Order } from '@/src/types/order';
import { formatCurrency } from '@/src/utils/currency';
import { formatDateTime } from '@/src/utils/date';
import type { Href } from 'expo-router';

export function DeliveryRow({ delivery, href, compact = false }: { delivery: Order; href?: Href; compact?: boolean }) {
  const status = delivery.delivery?.status_label ?? delivery.delivery?.status ?? delivery.order_status_label ?? delivery.order_status;

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
            <AppIcon name={appIcons.delivery} size={18} />
          </View>
          <View style={styles.flex}>
            <AppText variant="title" numberOfLines={1}>{delivery.order_number}</AppText>
            <AppText variant="caption" color={colors.text.secondary}>
              {delivery.customer?.name ?? 'Guest customer'} · {formatDateTime(delivery.assigned_at ?? delivery.created_at)}
            </AppText>
          </View>
          <StatusBadge status={status} />
        </View>
        <AppText color={colors.text.secondary} numberOfLines={1}>
          {delivery.delivery_address ?? 'No delivery address'}
        </AppText>
        <View style={styles.footer}>
          <AppText variant="title" color={colors.gold.dark}>
            {formatCurrency(delivery.total)}
          </AppText>
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
