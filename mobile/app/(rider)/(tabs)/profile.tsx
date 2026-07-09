import { View } from 'react-native';

import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppIcon, appIcons } from '@/src/components/common/AppIcon';
import { AppText } from '@/src/components/common/AppText';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { useAuthStore } from '@/src/auth/auth.store';
import { colors, radius, shadows, spacing } from '@/src/theme';

export default function RiderProfileScreen() {
  const user = useAuthStore((state) => state.session?.user);
  const logout = useAuthStore((state) => state.logout);

  return (
    <AppScreen contentStyle={styles.screen}>
      <AppHeader title="Profile" subtitle="Delivery account details." eyebrow="Rider" />

      <AppCard style={styles.heroCard}>
        <View style={styles.topRow}>
          <View style={styles.avatar}>
            <AppText variant="title" color={colors.brand.primary}>
              {(user?.name ?? 'Rider').slice(0, 1).toUpperCase()}
            </AppText>
          </View>
          <View style={styles.flex}>
            <AppText variant="caption" color={colors.brand.primary}>RIDER PROFILE</AppText>
            <AppText variant="h2" numberOfLines={1}>{user?.name ?? 'Rider'}</AppText>
            <AppText color={colors.text.secondary} numberOfLines={1}>{user?.phone ?? 'No phone added'}</AppText>
          </View>
          <StatusBadge status={user?.is_active ? 'active' : 'inactive'} />
        </View>
      </AppCard>

      <AppCard style={styles.card}>
        <View style={styles.lockedEmailRow}>
          <View style={styles.infoIcon}>
            <AppIcon name={appIcons.login} size={18} />
          </View>
          <View style={styles.flex}>
            <AppText variant="caption" color={colors.text.secondary}>Email</AppText>
            <AppText numberOfLines={1}>{user?.email ?? 'No email available'}</AppText>
          </View>
          <View style={styles.lockedBadge}>
            <AppText variant="caption" color={colors.text.secondary}>Locked</AppText>
          </View>
        </View>
        <AppText color={colors.text.secondary}>Email is used for login and cannot be changed from the rider app.</AppText>
      </AppCard>

      <View style={styles.logoutRow}>
        <View style={styles.infoIcon}>
          <AppIcon name={appIcons.logout} size={18} color={colors.semantic.danger} />
        </View>
        <View style={styles.flex}>
          <AppText variant="title">Logout</AppText>
          <AppText color={colors.text.secondary}>End secure access on this device.</AppText>
        </View>
        <AppButton label="Logout" variant="outline" onPress={() => void logout()} />
      </View>
    </AppScreen>
  );
}

const styles = {
  screen: {
    gap: spacing.sm,
  },
  heroCard: {
    gap: spacing.md,
    padding: spacing.md,
  },
  card: {
    gap: spacing.md,
    padding: spacing.md,
  },
  topRow: {
    flexDirection: 'row' as const,
    alignItems: 'center' as const,
    gap: spacing.sm,
  },
  avatar: {
    width: 48,
    height: 48,
    alignItems: 'center' as const,
    justifyContent: 'center' as const,
    borderRadius: radius.lg,
    backgroundColor: colors.brand.soft,
  },
  lockedEmailRow: {
    minHeight: 58,
    flexDirection: 'row' as const,
    alignItems: 'center' as const,
    gap: spacing.sm,
    borderRadius: radius.lg,
    backgroundColor: colors.surface.muted,
    padding: spacing.md,
  },
  infoIcon: {
    width: 38,
    height: 38,
    alignItems: 'center' as const,
    justifyContent: 'center' as const,
    borderRadius: radius.md,
    backgroundColor: colors.brand.soft,
  },
  lockedBadge: {
    minHeight: 30,
    justifyContent: 'center' as const,
    borderRadius: radius.pill,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.md,
  },
  logoutRow: {
    minHeight: 76,
    flexDirection: 'row' as const,
    alignItems: 'center' as const,
    gap: spacing.sm,
    borderRadius: radius.xl,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.card,
    padding: spacing.md,
    ...shadows.card,
  },
  flex: {
    flex: 1,
    minWidth: 0,
    gap: spacing.xs,
  },
};
