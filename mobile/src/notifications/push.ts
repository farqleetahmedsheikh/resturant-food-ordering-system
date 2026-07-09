import Constants from 'expo-constants';
import * as Device from 'expo-device';
import { Platform } from 'react-native';

import { runtimeInfo } from '@/src/config/env';
import type { DeviceRegistrationPayload } from '@/src/types/device';

type PushRegistrationSupport = {
  supported: boolean;
  reason?: string;
};

export function getPushRegistrationSupport(): PushRegistrationSupport {
  if (Platform.OS === 'web') {
    return {
      supported: false,
      reason: 'Push notifications are available in installed iOS and Android app builds, not the web preview.',
    };
  }

  if (!Device.isDevice) {
    return {
      supported: false,
      reason: 'Use a physical iOS or Android device to enable order notifications.',
    };
  }

  if (!getExpoProjectId()) {
    return {
      supported: false,
      reason: 'Push notifications need an Expo/EAS project id before production release.',
    };
  }

  return { supported: true };
}

export async function createPushRegistrationPayload(): Promise<DeviceRegistrationPayload> {
  const support = getPushRegistrationSupport();

  if (!support.supported) {
    throw new Error(support.reason ?? 'Push notifications are not available on this device.');
  }

  const Notifications = await import('expo-notifications');

  Notifications.setNotificationHandler({
    handleNotification: async () => ({
      shouldPlaySound: true,
      shouldSetBadge: false,
      shouldShowBanner: true,
      shouldShowList: true,
    }),
  });

  const permission = await Notifications.getPermissionsAsync();
  const finalPermission = permission.granted ? permission : await Notifications.requestPermissionsAsync();

  if (!finalPermission.granted) {
    throw new Error('Notification permission was not granted.');
  }

  if (Platform.OS === 'android') {
    await Notifications.setNotificationChannelAsync('orders', {
      name: 'Order updates',
      importance: Notifications.AndroidImportance.MAX,
      vibrationPattern: [0, 250, 250, 250],
      lightColor: '#E60C1A',
    });
  }

  const projectId = getExpoProjectId();
  if (!projectId) {
    throw new Error('Push notifications need an Expo/EAS project id before production release.');
  }

  const token = await Notifications.getExpoPushTokenAsync({ projectId });

  return {
    platform: Platform.OS === 'ios' ? 'ios' : 'android',
    push_token: token.data,
    device_name: Device.deviceName ?? Device.modelName ?? `${Platform.OS} device`,
    device_uuid: Constants.sessionId ?? null,
    app_version: runtimeInfo.appVersion,
  };
}

function getExpoProjectId(): string | null {
  const extra = Constants.expoConfig?.extra as { eas?: { projectId?: string } } | undefined;
  const projectId = Constants.easConfig?.projectId ?? extra?.eas?.projectId;

  return typeof projectId === 'string' && projectId.trim().length > 0 ? projectId : null;
}
