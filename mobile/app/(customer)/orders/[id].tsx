import { useQuery } from '@tanstack/react-query';
import { useLocalSearchParams } from 'expo-router';
import { StyleSheet, View } from 'react-native';

import { normalizeApiError } from '@/src/api/api-error';
import { getCustomerOrder } from '@/src/api/orders.api';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { PriceText } from '@/src/components/common/PriceText';
import { SectionTitle } from '@/src/components/common/SectionTitle';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { colors, spacing } from '@/src/theme';
import type { OrderItem, OrderStatusHistory } from '@/src/types/order';
import { formatCurrency } from '@/src/utils/currency';
import { formatDate } from '@/src/utils/date';

export default function CustomerOrderDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const query = useQuery({
    queryKey: ['customer', 'orders', id],
    queryFn: () => getCustomerOrder(id),
    enabled: Boolean(id),
  });

  if (query.isLoading) {
    return <LoadingScreen label="Loading order..." />;
  }

  if (query.isError || !query.data) {
    const error = normalizeApiError(query.error);

    return (
      <ErrorState
        message={error.status === 403 || error.status === 404 ? 'This order could not be found for your account.' : error.message}
        onRetry={() => void query.refetch()}
      />
    );
  }

  const order = query.data;

  return (
    <AppScreen refreshing={query.isRefetching} onRefresh={() => void query.refetch()}>
      <AppHeader title={order.order_number} subtitle={formatDate(order.created_at)} eyebrow="Order detail" />

      <AppCard style={styles.card}>
        <View style={styles.badges}>
          <StatusBadge status={order.order_status_label ?? order.order_status} />
          <StatusBadge status={order.payment_status} />
          {order.delivery?.status ? <StatusBadge status={order.delivery.status_label ?? order.delivery.status} /> : null}
        </View>
        <Detail label="Delivery address" value={order.delivery_address ?? 'Not available'} />
        <Detail label="Customer" value={order.customer?.name ?? 'Not available'} />
        <Detail label="Phone" value={order.customer?.phone ?? 'Not available'} />
        {order.order_notes ? <Detail label="Notes" value={order.order_notes} /> : null}
      </AppCard>

      <AppCard style={styles.card}>
        <SectionTitle title="Items" subtitle={`${order.items?.length ?? 0} item line${order.items?.length === 1 ? '' : 's'}`} />
        {(order.items ?? []).map((item) => (
          <OrderItemRow key={item.id} item={item} />
        ))}
      </AppCard>

      <AppCard style={styles.card}>
        <SectionTitle title="Totals" />
        <SummaryRow label="Subtotal" amount={order.subtotal ?? 0} />
        <SummaryRow label="Delivery fee" amount={order.delivery_fee ?? 0} />
        <View style={styles.divider} />
        <SummaryRow label="Total" amount={order.total} strong />
      </AppCard>

      {order.status_history && order.status_history.length > 0 ? (
        <AppCard style={styles.card}>
          <SectionTitle title="Progress" />
          {order.status_history.map((history) => (
            <TimelineRow key={history.id} history={history} />
          ))}
        </AppCard>
      ) : null}
    </AppScreen>
  );
}

function Detail({ label, value }: { label: string; value: string }) {
  return (
    <View style={styles.detail}>
      <AppText variant="caption" color={colors.text.secondary}>
        {label}
      </AppText>
      <AppText>{value}</AppText>
    </View>
  );
}

function OrderItemRow({ item }: { item: OrderItem }) {
  return (
    <View style={styles.itemRow}>
      <View style={styles.flex}>
        <AppText variant="title">
          {item.quantity} x {item.item_name}
        </AppText>
        {item.size_name ? <AppText color={colors.text.secondary}>{item.size_name}</AppText> : null}
        {item.addons && item.addons.length > 0 ? (
          <AppText color={colors.text.secondary}>
            {item.addons.map((addon) => addon.name).join(', ')}
          </AppText>
        ) : null}
      </View>
      <PriceText amount={item.total} />
    </View>
  );
}

function SummaryRow({ label, amount, strong = false }: { label: string; amount: number; strong?: boolean }) {
  return (
    <View style={styles.summaryRow}>
      <AppText variant={strong ? 'title' : 'body'}>{label}</AppText>
      <AppText variant={strong ? 'title' : 'body'} color={strong ? colors.gold.dark : colors.text.primary}>
        {formatCurrency(amount)}
      </AppText>
    </View>
  );
}

function TimelineRow({ history }: { history: OrderStatusHistory }) {
  return (
    <View style={styles.timelineRow}>
      <StatusBadge status={history.new_status} />
      <View style={styles.flex}>
        <AppText>{history.reason ?? 'Status updated'}</AppText>
        <AppText variant="caption" color={colors.text.secondary}>
          {formatDate(history.created_at)}
        </AppText>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    gap: spacing.lg,
  },
  badges: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  detail: {
    gap: spacing.xs,
  },
  itemRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  summaryRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  timelineRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: spacing.md,
  },
  flex: {
    flex: 1,
  },
  divider: {
    height: 1,
    backgroundColor: colors.border.light,
  },
});
