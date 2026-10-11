import { useRef, useState } from 'react';
import { Alert, StyleSheet, View } from 'react-native';
import * as Print from 'expo-print';
import * as Sharing from 'expo-sharing';
import ViewShot from 'react-native-view-shot';
import type { ViewShotRef } from 'react-native-view-shot';

import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { PriceText } from '@/src/components/common/PriceText';
import { colors, spacing } from '@/src/theme';
import type { Order } from '@/src/types/order';
import { formatCurrency } from '@/src/utils/currency';
import { formatDateTime } from '@/src/utils/date';

export function InvoiceActions({ order }: { order: Order }) {
  const invoiceRef = useRef<ViewShotRef>(null);
  const [busy, setBusy] = useState<'image' | 'pdf' | null>(null);

  const shareImage = async () => {
    if (!invoiceRef.current) return;
    setBusy('image');
    try {
      const uri = await invoiceRef.current?.capture();
      if (!uri) throw new Error('Invoice capture failed');
      if (await Sharing.isAvailableAsync()) await Sharing.shareAsync(uri, { mimeType: 'image/png', dialogTitle: 'Save or share invoice image' });
      else Alert.alert('Image ready', 'Invoice image was created, but sharing is not available on this device.');
    } catch {
      Alert.alert('Unable to export', 'The invoice image could not be created.');
    } finally { setBusy(null); }
  };

  const sharePdf = async () => {
    setBusy('pdf');
    try {
      const result = await Print.printToFileAsync({ html: invoiceHtml(order), base64: false });
      if (await Sharing.isAvailableAsync()) await Sharing.shareAsync(result.uri, { mimeType: 'application/pdf', dialogTitle: 'Save or share invoice PDF' });
      else Alert.alert('PDF ready', 'The invoice PDF was created, but sharing is not available on this device.');
    } catch {
      Alert.alert('Unable to export', 'The invoice PDF could not be created.');
    } finally { setBusy(null); }
  };

  return (
    <AppCard style={styles.card}>
      <AppText variant="title">Invoice</AppText>
      <ViewShot ref={invoiceRef} options={{ format: 'png', quality: 1 }} style={styles.preview}>
        <AppText variant="title">{order.order_number}</AppText>
        <AppText color={colors.text.secondary}>{formatDateTime(order.created_at)}</AppText>
        <View style={styles.rule} />
        {(order.items ?? []).map((item) => (
          <View key={item.id} style={styles.row}>
            <View style={styles.flex}><AppText>{item.quantity} x {item.item_name}</AppText><AppText variant="caption" color={colors.text.secondary}>{item.customizations?.map((group) => `${group.group_name}: ${group.selected?.map((option) => option.name).join(', ') || 'None'}${group.removed?.length ? ` (Removed: ${group.removed.map((option) => option.name).join(', ')})` : ''}`).join(' · ')}</AppText></View>
            <PriceText amount={item.total} />
          </View>
        ))}
        <View style={styles.rule} />
        <Row label="Subtotal" amount={order.subtotal ?? 0} />
        <Row label="Delivery" amount={order.delivery_fee ?? 0} />
        <Row label="Total" amount={order.total} strong />
      </ViewShot>
      <View style={styles.actions}>
        <AppButton label={busy === 'image' ? 'Preparing image...' : 'Save / share image'} variant="outline" onPress={() => void shareImage()} disabled={Boolean(busy)} />
        <AppButton label={busy === 'pdf' ? 'Preparing PDF...' : 'Save / share PDF'} onPress={() => void sharePdf()} disabled={Boolean(busy)} />
      </View>
    </AppCard>
  );
}

function Row({ label, amount, strong = false }: { label: string; amount: number; strong?: boolean }) {
  return <View style={styles.row}><AppText variant={strong ? 'title' : 'body'}>{label}</AppText><AppText variant={strong ? 'title' : 'body'}>{formatCurrency(amount)}</AppText></View>;
}

function invoiceHtml(order: Order): string {
  const items = (order.items ?? []).map((item) => `<tr><td>${item.quantity} x ${escapeHtml(item.item_name)}</td><td>${formatCurrency(item.total)}</td></tr>`).join('');
  return `<html><body style="font-family:Arial;padding:28px;color:#211a16"><h1>Invoice ${escapeHtml(order.order_number)}</h1><p>${escapeHtml(formatDateTime(order.created_at))}</p><hr><table style="width:100%;border-collapse:collapse">${items}</table><hr><p>Subtotal: ${formatCurrency(order.subtotal ?? 0)}</p><p>Delivery: ${formatCurrency(order.delivery_fee ?? 0)}</p><h2>Total: ${formatCurrency(order.total)}</h2></body></html>`;
}

function escapeHtml(value: string): string { return value.replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character] ?? character); }

const styles = StyleSheet.create({
  card: { gap: spacing.md }, preview: { gap: spacing.sm, backgroundColor: colors.surface.card, padding: spacing.md }, actions: { gap: spacing.sm }, row: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', gap: spacing.md }, flex: { flex: 1 }, rule: { height: 1, backgroundColor: colors.border.light, marginVertical: spacing.xs },
});
