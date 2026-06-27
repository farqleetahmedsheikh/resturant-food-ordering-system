import { useQuery } from '@tanstack/react-query';
import { Linking, StyleSheet, View } from 'react-native';

import { getRestaurant } from '@/src/api/restaurant.api';
import { AppBadge } from '@/src/components/common/AppBadge';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { RestaurantLogo } from '@/src/components/common/RestaurantLogo';
import { SectionTitle } from '@/src/components/common/SectionTitle';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors, radius, spacing } from '@/src/theme';
import { getRestaurantAvailability } from '@/src/utils/restaurant';

export default function ContactScreen() {
  const query = useQuery({
    queryKey: queryKeys.restaurant,
    queryFn: getRestaurant,
  });

  if (query.isLoading) {
    return <LoadingScreen label="Loading contact..." />;
  }

  if (query.isError) {
    return <ErrorState message="Unable to load contact information." onRetry={() => void query.refetch()} />;
  }

  const restaurant = query.data;
  const availability = getRestaurantAvailability(restaurant);
  const address = restaurant?.formatted_address ?? restaurant?.address;
  const hasCoordinates = restaurant?.latitude !== null && restaurant?.longitude !== null;
  const mapsUrl = hasCoordinates
    ? `https://www.google.com/maps/search/?api=1&query=${restaurant?.latitude},${restaurant?.longitude}`
    : address
      ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(address)}`
      : null;

  return (
    <AppScreen>
      <AppHeader title="Contact" subtitle="Restaurant details come directly from Laravel settings." eyebrow="Arcade" />

      <AppCard style={styles.identity}>
        <View style={styles.identityRow}>
          <RestaurantLogo name={restaurant?.name} logoUrl={restaurant?.logo_url} initials={restaurant?.initials} />
          <View style={styles.identityCopy}>
            <AppText variant="title">{restaurant?.name ?? 'Arcade Kebab House'}</AppText>
            <AppBadge label={availability.label} tone={availability.isOpenForOrders ? 'green' : 'gold'} />
          </View>
        </View>
        {availability.reason ? <AppText color={colors.text.secondary}>{availability.reason}</AppText> : null}
      </AppCard>

      <AppCard style={styles.card}>
        <SectionTitle title="Restaurant details" subtitle={availability.timezone ?? 'Restaurant timezone'} />
        <Detail label="Phone" value={restaurant?.phone ?? 'Phone not configured'} />
        <Detail label="Email" value={restaurant?.email ?? 'Email not configured'} />
        <Detail label="Address" value={address ?? 'Address not configured'} />
        <Detail
          label="Opening hours"
          value={restaurant?.opening_time && restaurant?.closing_time ? `${restaurant.opening_time} - ${restaurant.closing_time}` : 'Managed by restaurant settings'}
        />
      </AppCard>

      <View style={styles.mapBox}>
        <AppText variant="title" color={colors.text.inverse}>
          {hasCoordinates ? 'Saved restaurant location' : 'Map location'}
        </AppText>
        <AppText color="#F8F1EB">
          {hasCoordinates
            ? `${restaurant?.latitude}, ${restaurant?.longitude}`
            : address ?? 'Coordinates have not been configured yet.'}
        </AppText>
      </View>

      <View style={styles.actions}>
        <AppButton
          label="Call"
          disabled={!restaurant?.phone}
          onPress={() => {
            if (restaurant?.phone) {
              void Linking.openURL(`tel:${restaurant.phone}`);
            }
          }}
        />
        <AppButton
          label="Email"
          variant="secondary"
          disabled={!restaurant?.email}
          onPress={() => {
            if (restaurant?.email) {
              void Linking.openURL(`mailto:${restaurant.email}`);
            }
          }}
        />
        <AppButton
          label="Open in Maps"
          variant="outline"
          disabled={!mapsUrl}
          onPress={() => {
            if (mapsUrl) {
              void Linking.openURL(mapsUrl);
            }
          }}
        />
      </View>
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
  identity: {
    gap: spacing.lg,
  },
  identityRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.lg,
  },
  identityCopy: {
    flex: 1,
    gap: spacing.sm,
  },
  card: {
    gap: spacing.lg,
  },
  detail: {
    gap: spacing.xs,
  },
  mapBox: {
    minHeight: 170,
    justifyContent: 'flex-end',
    gap: spacing.sm,
    borderRadius: radius.xl,
    backgroundColor: colors.burgundy.dark,
    padding: spacing.xl,
  },
  actions: {
    gap: spacing.md,
  },
});
