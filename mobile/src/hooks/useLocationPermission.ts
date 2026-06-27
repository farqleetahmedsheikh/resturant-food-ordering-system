import * as Location from 'expo-location';
import { useState } from 'react';

type LocationState = {
  loading: boolean;
  error: string | null;
  coords: { latitude: number; longitude: number } | null;
};

export function useLocationPermission() {
  const [state, setState] = useState<LocationState>({
    loading: false,
    error: null,
    coords: null,
  });

  async function requestCurrentLocation(): Promise<void> {
    setState((current) => ({ ...current, loading: true, error: null }));

    const permission = await Location.requestForegroundPermissionsAsync();

    if (permission.status !== Location.PermissionStatus.GRANTED) {
      setState({
        loading: false,
        error: 'Location permission was denied. You can still enter your address manually.',
        coords: null,
      });
      return;
    }

    const position = await Location.getCurrentPositionAsync({});

    setState({
      loading: false,
      error: null,
      coords: {
        latitude: position.coords.latitude,
        longitude: position.coords.longitude,
      },
    });
  }

  return {
    ...state,
    requestCurrentLocation,
  };
}
