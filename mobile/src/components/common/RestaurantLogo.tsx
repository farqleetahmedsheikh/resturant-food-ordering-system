import { Image } from 'expo-image';
import { StyleSheet, View } from 'react-native';

import { AppText } from './AppText';
import { colors, radius, spacing } from '@/src/theme';
import { restaurantInitials } from '@/src/utils/restaurant';

type RestaurantLogoProps = {
  name: string | null | undefined;
  logoUrl?: string | null;
  initials?: string | null;
  size?: number;
  inverse?: boolean;
};

export function RestaurantLogo({ name, logoUrl, initials, size = 76, inverse = false }: RestaurantLogoProps) {
  return (
    <View
      style={[
        styles.shell,
        {
          width: size,
          height: size,
          borderRadius: Math.min(radius.xl, size / 3),
          backgroundColor: inverse ? colors.surface.card : colors.gold.pale,
        },
      ]}
    >
      {logoUrl ? (
        <Image source={{ uri: logoUrl }} style={styles.image} contentFit="cover" />
      ) : (
        <AppText variant="h2" color={colors.brand.primary}>
          {initials || restaurantInitials(name)}
        </AppText>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  shell: {
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: colors.border.light,
    padding: spacing.xs,
  },
  image: {
    height: '100%',
    width: '100%',
  },
});
