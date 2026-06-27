import type { ExpoConfig } from 'expo/config';

const config: ExpoConfig = {
  name: 'Arcade Kebab House',
  slug: 'arcade-kebab-house',
  version: '1.0.0',
  orientation: 'portrait',
  icon: './assets/images/temporary-app-icon.png',
  scheme: 'arcadekebabhouse',
  userInterfaceStyle: 'light',
  ios: {
    supportsTablet: true,
    bundleIdentifier: 'com.binaryscripters.arcadekebabhouse',
  },
  android: {
    package: 'com.binaryscripters.arcadekebabhouse',
    adaptiveIcon: {
      backgroundColor: '#FFF9F5',
      foregroundImage: './assets/images/android-icon-foreground.png',
      backgroundImage: './assets/images/android-icon-background.png',
      monochromeImage: './assets/images/android-icon-monochrome.png',
    },
    predictiveBackGestureEnabled: false,
  },
  web: {
    bundler: 'metro',
    output: 'static',
    favicon: './assets/images/favicon.png',
  },
  plugins: [
    'expo-router',
    'expo-secure-store',
    'expo-image',
    'expo-status-bar',
    [
      'expo-splash-screen',
      {
        image: './assets/images/temporary-splash-icon.png',
        resizeMode: 'contain',
        backgroundColor: '#FFF9F5',
      },
    ],
  ],
};

export default config;
