import { Image } from 'expo-image';
import { router } from 'expo-router';
import { Pressable, StyleSheet, View } from 'react-native';

import { AppBadge } from '@/src/components/common/AppBadge';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { PriceText } from '@/src/components/common/PriceText';
import { colors, radius, spacing } from '@/src/theme';
import type { MenuItem } from '@/src/types/menu';
import type { Href } from 'expo-router';

type MenuItemCardProps = {
  item: MenuItem;
  detailHref?: Href;
  variant?: 'default' | 'compact';
  showAddButton?: boolean;
  addDisabled?: boolean;
  addDisabledReason?: string;
  onAdd?: (item: MenuItem) => void;
};

export function MenuItemCard({
  item,
  detailHref,
  variant = 'default',
  showAddButton = false,
  addDisabled = false,
  addDisabledReason,
  onAdd,
}: MenuItemCardProps) {
  const unavailable = !item.is_available;
  const image = item.image_url ? (
    <Image source={{ uri: item.image_url }} style={styles.image} contentFit="cover" />
  ) : (
    <AppText variant="h2" color={colors.brand.primary}>
      {item.name.slice(0, 1).toUpperCase()}
    </AppText>
  );

  if (variant === 'compact') {
    return (
      <Pressable
        accessibilityRole={detailHref ? 'button' : undefined}
        disabled={!detailHref}
        onPress={() => {
          if (detailHref) {
            router.push(detailHref);
          }
        }}
      >
        <AppCard style={[styles.compactCard, unavailable && styles.unavailableCard]}>
          <View style={styles.compactImageShell}>
            {image}
            {item.is_featured ? (
              <View style={styles.compactPopularDot}>
                <AppText variant="caption" color={colors.gold.dark}>
                  Popular
                </AppText>
              </View>
            ) : null}
          </View>
          <View style={styles.compactBody}>
            <View style={styles.badgeRow}>
              <AppBadge label={item.category?.name ?? 'Menu'} tone="neutral" />
              <AppBadge label={unavailable ? 'Unavailable' : 'Available'} tone={unavailable ? 'danger' : 'green'} />
            </View>
            <AppText variant="title" numberOfLines={1}>
              {item.name}
            </AppText>
            <AppText color={colors.text.secondary} numberOfLines={2}>
              {item.description ?? 'Freshly prepared at Arcade Kebab House.'}
            </AppText>
            <View style={styles.footer}>
              <PriceText amount={item.price} />
              {showAddButton ? (
                <AppButton
                  label={unavailable ? 'Sold out' : 'Add'}
                  disabled={unavailable || addDisabled}
                  variant={unavailable || addDisabled ? 'secondary' : 'primary'}
                  onPress={(event) => {
                    event.stopPropagation();
                    onAdd?.(item);
                  }}
                />
              ) : detailHref ? (
                <View style={styles.compactViewPill}>
                  <AppText variant="caption" color={colors.brand.primary}>
                    Details
                  </AppText>
                </View>
              ) : null}
            </View>
            {addDisabled && addDisabledReason ? (
              <AppText variant="caption" color={colors.text.secondary}>
                {addDisabledReason}
              </AppText>
            ) : null}
          </View>
        </AppCard>
      </Pressable>
    );
  }

  return (
    <Pressable
      accessibilityRole={detailHref ? 'button' : undefined}
      disabled={!detailHref}
      onPress={() => {
        if (detailHref) {
          router.push(detailHref);
        }
      }}
    >
      <AppCard style={styles.card}>
        <View style={styles.imageShell}>
          {image}
          {item.is_featured ? (
            <View style={styles.floatingBadge}>
              <AppBadge label="Popular" tone="gold" />
            </View>
          ) : null}
        </View>
        <View style={styles.body}>
          <View style={styles.badgeRow}>
            <AppBadge label={item.category?.name ?? 'Menu'} tone="neutral" />
            <AppBadge label={unavailable ? 'Unavailable' : 'Available'} tone={unavailable ? 'danger' : 'green'} />
          </View>
          <AppText variant="title" numberOfLines={2}>
            {item.name}
          </AppText>
          <AppText color={colors.text.secondary} numberOfLines={2}>
            {item.description ?? 'Freshly prepared at Arcade Kebab House.'}
          </AppText>
          <View style={styles.footer}>
            <PriceText amount={item.price} />
            {showAddButton ? (
              <AppButton
                label={unavailable ? 'Unavailable' : 'Add'}
                disabled={unavailable || addDisabled}
                variant={unavailable || addDisabled ? 'secondary' : 'primary'}
                onPress={(event) => {
                  event.stopPropagation();
                  onAdd?.(item);
                }}
              />
            ) : detailHref ? (
              <View style={styles.viewPill}>
                <AppText variant="caption" color={colors.brand.primary}>
                  View details
                </AppText>
              </View>
            ) : null}
          </View>
          {addDisabled && addDisabledReason ? (
            <AppText variant="caption" color={colors.text.secondary}>
              {addDisabledReason}
            </AppText>
          ) : null}
        </View>
      </AppCard>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  card: {
    overflow: 'hidden',
    padding: 0,
  },
  compactCard: {
    minHeight: 132,
    flexDirection: 'row',
    gap: spacing.md,
    padding: spacing.md,
  },
  unavailableCard: {
    opacity: 0.72,
  },
  imageShell: {
    height: 148,
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
  compactImageShell: {
    width: 86,
    overflow: 'hidden',
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.lg,
    backgroundColor: colors.brand.soft,
  },
  compactPopularDot: {
    position: 'absolute',
    left: spacing.xs,
    top: spacing.xs,
    borderRadius: radius.pill,
    backgroundColor: colors.gold.soft,
    paddingHorizontal: spacing.sm,
    paddingVertical: 2,
  },
  compactBody: {
    flex: 1,
    minWidth: 0,
    gap: spacing.xs,
  },
  floatingBadge: {
    position: 'absolute',
    left: spacing.md,
    top: spacing.md,
  },
  body: {
    gap: spacing.sm,
    padding: spacing.lg,
  },
  badgeRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  footer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  viewPill: {
    minHeight: 40,
    justifyContent: 'center',
    borderRadius: radius.pill,
    backgroundColor: colors.brand.soft,
    paddingHorizontal: spacing.lg,
  },
  compactViewPill: {
    minHeight: 36,
    justifyContent: 'center',
    borderRadius: radius.pill,
    backgroundColor: colors.brand.soft,
    paddingHorizontal: spacing.md,
  },
});
