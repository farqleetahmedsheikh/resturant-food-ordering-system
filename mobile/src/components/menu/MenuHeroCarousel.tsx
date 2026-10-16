import { useEffect, useState } from 'react';
import { Image } from 'expo-image';
import { Link } from 'expo-router';
import type { Href } from 'expo-router';
import { Pressable, StyleSheet, View } from 'react-native';

import { AppBadge } from '@/src/components/common/AppBadge';
import { AppButton } from '@/src/components/common/AppButton';
import { AppText } from '@/src/components/common/AppText';
import type { Restaurant } from '@/src/types/restaurant';
import { colors, radius, spacing } from '@/src/theme';

type MenuHeroCarouselProps = {
  restaurant: Restaurant;
  statusLabel: string;
  isOpen: boolean;
  actionHref: Href;
  actionLabel: string;
  testID?: string;
};

export function MenuHeroCarousel({
  restaurant,
  statusLabel,
  isOpen,
  actionHref,
  actionLabel,
  testID,
}: MenuHeroCarouselProps) {
  const slides = restaurant.menu_hero_slides?.filter((slide) => slide.image_url) ?? [];
  const [activeIndex, setActiveIndex] = useState(0);
  const activeSlide = slides[activeIndex];
  const imageUrl = activeSlide?.image_url ?? restaurant.cover_image_url;

  useEffect(() => {
    if (slides.length < 2) return;
    const interval = setInterval(() => {
      setActiveIndex((index) => (index + 1) % slides.length);
    }, 3000);
    return () => clearInterval(interval);
  }, [slides.length]);

  return (
    <View testID={testID} style={styles.hero}>
      {imageUrl ? (
        <Image
          key={imageUrl}
          source={{ uri: imageUrl }}
          style={StyleSheet.absoluteFill}
          contentFit="cover"
          transition={450}
          accessibilityRole="image"
          accessibilityLabel={activeSlide ? `${activeSlide.title} ${activeSlide.highlight}` : `${restaurant.name} food`}
        />
      ) : null}
      <View pointerEvents="none" style={[StyleSheet.absoluteFill, styles.overlay]} />
      <View style={styles.content}>
        <View style={styles.statusRow}>
          <AppText variant="caption" color="#FFE1E4" numberOfLines={1} style={styles.eyebrow}>
            {activeSlide?.eyebrow || 'FRESH FOOD · GREAT TASTE · DELIVERED TO YOU'}
          </AppText>
          <AppBadge label={statusLabel} tone={isOpen ? 'green' : 'gold'} />
        </View>
        <View style={styles.copy}>
          <AppText variant="h1" color={colors.text.inverse} style={styles.title}>
            {activeSlide ? `${activeSlide.title} ${activeSlide.highlight}` : 'Authentic kebabs and meals, made fresh for you.'}
          </AppText>
          <AppText color="#FFF7F3" style={styles.description}>
            {activeSlide?.description || restaurant.short_description || 'Fresh ingredients, bold flavours and your favourite meals, ready to order.'}
          </AppText>
        </View>
        <View style={styles.actions}>
          <Link href={actionHref} asChild>
            <AppButton label={actionLabel} />
          </Link>
          {slides.length > 1 ? (
            <View style={styles.dots} accessibilityLabel={`Hero slide ${activeIndex + 1} of ${slides.length}`}>
              {slides.map((slide, index) => (
                <Pressable
                  key={slide.id || index}
                  accessibilityRole="button"
                  accessibilityLabel={`Show slide ${index + 1}`}
                  accessibilityState={{ selected: index === activeIndex }}
                  onPress={() => setActiveIndex(index)}
                  style={[styles.dot, index === activeIndex && styles.activeDot]}
                />
              ))}
            </View>
          ) : null}
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  hero: {
    minHeight: 360,
    justifyContent: 'flex-end',
    overflow: 'hidden',
    backgroundColor: colors.surface.dark,
    marginHorizontal: -spacing.lg,
  },
  overlay: {
    backgroundColor: 'rgba(23, 11, 8, 0.58)',
  },
  content: {
    minHeight: 360,
    justifyContent: 'space-between',
    gap: spacing.lg,
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.xl,
  },
  statusRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  eyebrow: {
    minWidth: 0,
    flex: 1,
  },
  copy: {
    maxWidth: 660,
    gap: spacing.sm,
  },
  title: {
    fontSize: 32,
    lineHeight: 38,
    maxWidth: 620,
  },
  description: {
    maxWidth: 560,
    lineHeight: 22,
  },
  actions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.lg,
  },
  dots: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  dot: {
    width: 8,
    height: 8,
    borderRadius: radius.pill,
    backgroundColor: 'rgba(255,255,255,0.55)',
  },
  activeDot: {
    width: 22,
    backgroundColor: colors.text.inverse,
  },
});
