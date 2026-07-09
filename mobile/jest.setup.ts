/// <reference types="jest" />

jest.mock('expo-secure-store', () => ({
  getItemAsync: jest.fn(),
  setItemAsync: jest.fn(),
  deleteItemAsync: jest.fn(),
}));

jest.mock('@react-native-async-storage/async-storage', () =>
  // eslint-disable-next-line @typescript-eslint/no-require-imports
  require('@react-native-async-storage/async-storage/jest/async-storage-mock'),
);

jest.mock('expo-router', () => {
  // eslint-disable-next-line @typescript-eslint/no-require-imports
  const React = require('react');

  return {
    Link: ({ children }: { children?: unknown }) => React.createElement(React.Fragment, null, children as never),
    Redirect: () => null,
    Stack: Object.assign(({ children }: { children?: unknown }) => React.createElement(React.Fragment, null, children as never), {
      Screen: () => null,
      Protected: ({ children }: { children?: unknown }) => React.createElement(React.Fragment, null, children as never),
    }),
    Tabs: Object.assign(({ children }: { children?: unknown }) => React.createElement(React.Fragment, null, children as never), {
      Screen: () => null,
    }),
    router: {
      push: jest.fn(),
      replace: jest.fn(),
      back: jest.fn(),
    },
    useLocalSearchParams: jest.fn(() => ({ id: '1' })),
  };
});

jest.mock('expo-network', () => ({
  getNetworkStateAsync: jest.fn(async () => ({
    isConnected: true,
    isInternetReachable: true,
    type: 'wifi',
  })),
}));

jest.mock('expo-location', () => ({
  requestForegroundPermissionsAsync: jest.fn(async () => ({ status: 'granted' })),
  getCurrentPositionAsync: jest.fn(async () => ({
    coords: {
      latitude: -33.8688,
      longitude: 151.2093,
    },
  })),
  PermissionStatus: {
    GRANTED: 'granted',
    DENIED: 'denied',
    UNDETERMINED: 'undetermined',
  },
}));

jest.mock('expo-notifications', () => ({
  AndroidImportance: {
    MAX: 'max',
  },
  getExpoPushTokenAsync: jest.fn(async () => ({ data: 'ExponentPushToken[test]' })),
  getPermissionsAsync: jest.fn(async () => ({ granted: true })),
  requestPermissionsAsync: jest.fn(async () => ({ granted: true })),
  setNotificationChannelAsync: jest.fn(async () => null),
  setNotificationHandler: jest.fn(),
}));
