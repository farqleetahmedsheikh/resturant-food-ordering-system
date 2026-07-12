import type { ExpoConfig } from 'expo/config';
import { withAndroidManifest, type ConfigPlugin } from 'expo/config-plugins';

const withAndroidCleartextTraffic: ConfigPlugin = (config) =>
  withAndroidManifest(config, (androidConfig) => {
    const application = androidConfig.modResults.manifest.application?.[0];

    if (application?.$) {
      application.$['android:usesCleartextTraffic'] = 'true';
    }

    return androidConfig;
  });

const config: ExpoConfig = {
  name: "Arcade Kebab House",
  slug: "arcade-kebab-house",
  version: "1.0.0",
  orientation: "portrait",
  icon: "./assets/adaptive-icon.png",
  scheme: "arcadekebabhouse",
  userInterfaceStyle: "light",

  ios: {
    bundleIdentifier: "com.arcadekebab.app",
    buildNumber: "1",
    supportsTablet: false,
  },

  android: {
    package: "com.arcadekebab.app",
    versionCode: 1,
    adaptiveIcon: {
      foregroundImage: "./assets/adaptive-icon.png",
      backgroundColor: "#E60C1A",
    },
  },

  web: {
    bundler: "metro",
    output: "static",
    favicon: "./assets/adaptive-icon.png",
  },

  extra: {
    eas: {
      projectId: "6a66b381-22af-4126-acfb-cbb554078ecd",
    },
  },

  plugins: [
    withAndroidCleartextTraffic,
    "expo-router",
    "expo-secure-store",
    "expo-notifications",
    "expo-image",
    "expo-status-bar",
    [
      "expo-splash-screen",
      {
        image: "./assets/adaptive-icon.png",
        resizeMode: "contain",
        backgroundColor: "#FFF9F5",
      },
    ],
  ] as any,
};

export default config;
