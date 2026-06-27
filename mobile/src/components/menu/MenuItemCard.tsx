import { Image } from 'expo-image';
import { Link } from 'expo-router';
import { StyleSheet, View } from 'react-native';

import { AppBadge } from '@/src/components/common/AppBadge';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { formatCurrency } from '@/src/utils/currency';
import { colors, radius, spacing } from '@/src/theme';
import type { MenuItem } from '@/src/types/menu';

export function MenuItemCard({ item }: { item: MenuItem }) {
  return (
    <Link href={`/(public)/item/${item.id}`} asChild>
      <AppCard style={styles.card}>
        <View style={styles.imageShell}>
          {item.image_url ? (
            <Image source={{ uri: item.image_url }} style={styles.image} contentFit="cover" />
          ) : (
            <AppText variant="h2" color={colors.brand.primary}>
              {item.name.slice(0, 1)}
            </AppText>
          )}
        </View>
        <View style={styles.body}>
          <AppBadge label={item.category?.name ?? 'Menu'} tone={item.is_featured ? 'gold' : 'neutral'} />
          <AppText variant="title" numberOfLines={2}>
            {item.name}
          </AppText>
          <AppText color={colors.text.secondary} numberOfLines={2}>
            {item.description ?? 'Freshly prepared at Arcade Kebab House.'}
          </AppText>
          <AppText variant="title" color={colors.gold.dark}>
            {formatCurrency(item.price)}
          </AppText>
        </View>
      </AppCard>
    </Link>
  );
}

const styles = StyleSheet.create({
  card: {
    padding: 0,
    overflow: 'hidden',
  },
  imageShell: {
    height: 150,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.gold.pale,
    borderTopLeftRadius: radius.xl,
    borderTopRightRadius: radius.xl,
  },
  image: {
    height: '100%',
    width: '100%',
  },
  body: {
    gap: spacing.sm,
    padding: spacing.lg,
  },
});
