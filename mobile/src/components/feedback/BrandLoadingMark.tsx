import { useEffect, useState } from 'react';
import { Animated, Image, StyleSheet, View } from 'react-native';

import { colors, radius, shadows, spacing } from '@/src/theme';

const logo = require('../../../assets/adaptive-icon.png');

export function BrandLoadingMark() {
  const [scale] = useState(() => new Animated.Value(1));

  useEffect(() => {
    const pulse = Animated.loop(
      Animated.sequence([
        Animated.timing(scale, { toValue: 1.045, duration: 900, useNativeDriver: true }),
        Animated.timing(scale, { toValue: 1, duration: 900, useNativeDriver: true }),
      ]),
    );
    pulse.start();
    return () => pulse.stop();
  }, [scale]);

  return (
    <Animated.View style={[styles.logoShell, { transform: [{ scale }] }]}>
      <Image source={logo} style={styles.logo} resizeMode="cover" accessibilityLabel="Arcade Kebab House" />
    </Animated.View>
  );
}

export function LoadingDots() {
  const [dots] = useState(() => [new Animated.Value(0.35), new Animated.Value(0.35), new Animated.Value(0.35)]);

  useEffect(() => {
    const animations = dots.map((value, index) => Animated.loop(
      Animated.sequence([
        Animated.delay(index * 170),
        Animated.timing(value, { toValue: 1, duration: 420, useNativeDriver: true }),
        Animated.timing(value, { toValue: 0.35, duration: 420, useNativeDriver: true }),
        Animated.delay((dots.length - index - 1) * 170),
      ]),
    ));
    animations.forEach((animation) => animation.start());
    return () => animations.forEach((animation) => animation.stop());
  }, [dots]);

  return (
    <View style={styles.dots} accessibilityLabel="Loading" accessibilityRole="progressbar">
      {dots.map((opacity, index) => (
        <Animated.View key={index} style={[styles.dot, { opacity }]} />
      ))}
    </View>
  );
}

const styles = StyleSheet.create({
  logoShell: {
    width: 88,
    height: 88,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.surface.card,
    borderWidth: 1,
    borderColor: colors.border.light,
    borderRadius: radius.xl,
    padding: spacing.xs,
    ...shadows.card,
  },
  logo: {
    width: '100%',
    height: '100%',
    borderRadius: radius.lg,
  },
  dots: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.sm,
  },
  dot: {
    width: 7,
    height: 7,
    borderRadius: radius.pill,
    backgroundColor: colors.brand.primary,
  },
});
