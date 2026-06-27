import { useState } from 'react';
import { StyleSheet, View } from 'react-native';

import { useAuthStore } from '@/src/auth/auth.store';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { ConfirmModal } from '@/src/components/common/ConfirmModal';
import { SectionTitle } from '@/src/components/common/SectionTitle';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { DiagnosticsCard } from '@/src/components/feedback/DiagnosticsCard';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { colors, spacing } from '@/src/theme';

export default function CustomerProfileScreen() {
  const user = useAuthStore((state) => state.session?.user);
  const logout = useAuthStore((state) => state.logout);
  const [confirmVisible, setConfirmVisible] = useState(false);
  const [loggingOut, setLoggingOut] = useState(false);

  async function confirmLogout() {
    setLoggingOut(true);
    try {
      await logout();
    } finally {
      setLoggingOut(false);
      setConfirmVisible(false);
    }
  }

  return (
    <AppScreen>
      <AppHeader title="Profile" subtitle="Customer account details." eyebrow="Customer" />
      <AppCard style={styles.card}>
        <View style={styles.topRow}>
          <View style={styles.avatar}>
            <AppText variant="title" color={colors.brand.primary}>
              {(user?.name ?? 'Customer').slice(0, 1).toUpperCase()}
            </AppText>
          </View>
          <View style={styles.flex}>
            <AppText variant="title" numberOfLines={2}>
              {user?.name}
            </AppText>
            <StatusBadge status={user?.role ?? 'customer'} />
          </View>
        </View>
        <Detail label="Email" value={user?.email ?? 'Not available'} />
        <Detail label="Phone" value={user?.phone ?? 'No phone added'} />
        <Detail label="Account status" value={user?.is_active ? 'Active' : 'Inactive'} />
      </AppCard>

      <AppCard style={styles.card}>
        <SectionTitle title="Account" />
        <FeedbackMessage tone="info" message="Mobile password changes are not enabled yet. Use the web account security page if you need to update it now." />
        <AppButton label="Logout" variant="danger" fullWidth onPress={() => setConfirmVisible(true)} />
      </AppCard>

      {__DEV__ ? <DiagnosticsCard /> : null}

      <ConfirmModal
        visible={confirmVisible}
        title="Logout?"
        message="Your secure session token will be removed from this device."
        confirmLabel="Logout"
        destructive
        loading={loggingOut}
        onConfirm={() => void confirmLogout()}
        onCancel={() => setConfirmVisible(false)}
      />
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

const styles = StyleSheet.create({
  card: {
    gap: spacing.lg,
  },
  topRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
  },
  avatar: {
    height: 58,
    width: 58,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 18,
    backgroundColor: colors.brand.soft,
  },
  flex: {
    flex: 1,
    gap: spacing.sm,
  },
  detail: {
    gap: spacing.xs,
  },
});
