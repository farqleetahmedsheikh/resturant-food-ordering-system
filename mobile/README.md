# Arcade Kebab House Mobile

One Expo React Native app for Arcade Kebab House customers, riders, and admins. The Laravel application remains the backend and primary admin system. Mobile talks to the existing Laravel REST API using Sanctum bearer tokens.

## Architecture

- Laravel backend: `/api/v1/*`
- Auth: Laravel Sanctum personal access tokens
- Mobile app: one Expo managed workflow app in `mobile/`
- Public module: home, menu, contact, item detail
- Customer module: dashboard, menu, cart shell, orders, profile, checkout shell
- Rider module: dashboard, assigned deliveries, history shell, profile
- Admin module: feature-flagged mobile overview only

## Requirements

- Node LTS compatible with Expo SDK 56 and React Native 0.85
- npm
- VS Code
- Physical Android phone
- Expo Go for compatible JS-only screens, or a development APK for native-module testing
- No Android Studio, emulator, local Android SDK, or local native build required

## Install

```bash
cd mobile
npm install
cp .env.example .env
```

Set:

```bash
EXPO_PUBLIC_API_URL=http://YOUR_COMPUTER_LAN_IP:8000/api/v1
EXPO_PUBLIC_ENABLE_ADMIN_MOBILE=false
```

Do not use `localhost` or `127.0.0.1` for a physical phone. Those point to the phone itself.

## Find Your LAN IP

macOS:

```bash
ipconfig getifaddr en0
```

Windows:

```bash
ipconfig
```

Linux:

```bash
hostname -I
```

## Run Laravel For Phone Access

From the repository root:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Your phone and computer must be on the same network. Tunnel mode for Expo does not automatically expose a Laravel server that only listens on localhost.

## Start Expo

LAN:

```bash
cd mobile
npm run start
```

Tunnel:

```bash
npm run start:tunnel
```

Clear cache:

```bash
npm run start:clear
```

## API Diagnostics

The public home screen shows a development-only diagnostics card with:

- API URL
- health check status
- response time
- app version
- Expo version

The health endpoint is:

```text
GET /api/health
```

It exposes only a safe status and application name.

## Expo Go vs Development APK

Expo Go is useful for early UI work. A development APK is better once native modules and EAS-like runtime behavior matter.

Development APK:

```bash
npx eas-cli@latest login
npx eas-cli@latest build --platform android --profile development
```

Preview APK:

```bash
npx eas-cli@latest build --platform android --profile preview
```

Production Android build:

```bash
npx eas-cli@latest build --platform android --profile production
```

Install the generated APK from the EAS build link on your Android phone.

## Scripts

```bash
npm run lint
npm run typecheck
npm test -- --runInBand
npm run doctor
npm run export:check
```

No script runs `expo run:android`, `expo run:ios`, `expo prebuild`, or local native builds.

## Role Routing

The root layout uses Expo Router protected route groups:

- guests: public and auth routes
- customer: customer tabs and customer detail routes
- rider: rider tabs and delivery detail routes
- admin: admin routes only when `EXPO_PUBLIC_ENABLE_ADMIN_MOBILE=true`
- unsupported/inactive roles: safe access message and logout

Client-side guards are navigation controls only. Laravel remains the authority for permissions.

## Token Security

- Tokens are stored in Expo SecureStore only.
- AsyncStorage is used only for non-sensitive cart UI state.
- Tokens are never placed in URLs.
- Tokens are cleared after logout and unrecoverable 401 responses.

## Troubleshooting

Cannot reach Laravel:

- Confirm Laravel is running with `--host=0.0.0.0`.
- Confirm phone and computer are on the same Wi-Fi.
- Confirm `EXPO_PUBLIC_API_URL` uses the computer LAN IP.

HTTP cleartext issue on Android:

- Expo Go/development builds may differ by Android version and network security behavior.
- Prefer HTTPS for production.

CORS issue:

- Native app bearer-token requests are not browser SPA session requests.
- The Laravel API is configured for API paths and bearer authentication.

Invalid environment variable:

- Remove trailing slash.
- Do not use placeholders, `localhost`, or `127.0.0.1`.

Expo cache problem:

```bash
npm run start:clear
```

EAS credentials issue:

- Run `npx eas-cli@latest login`.
- Link/create the EAS project when prompted.

## Intentionally Left For Later

- Full mobile cart mutation UI
- Production checkout form
- Rider delivery status action buttons
- Admin write actions
- Push notifications
- Paid map provider integration
- Final app icon/splash replacement with approved Arcade Kebab House source asset
