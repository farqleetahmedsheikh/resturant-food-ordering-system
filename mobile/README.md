# Arcade Kebab House Mobile

Expo React Native app for Arcade Kebab House. The Laravel website remains the backend and primary admin system. Mobile uses Expo Router, TypeScript, TanStack Query, Zustand, React Hook Form, Zod, Axios, SecureStore, and the shared Arcade Kebab House theme tokens.

## Implemented Screens

- Public: home, menu, menu item detail, contact/location.
- Auth: login, register, forgot password support state.
- Customer: dashboard, menu tab, cart tab, checkout, orders tab, order detail, profile.
- Admin: protected unavailable screen by default.
- Rider: existing protected scaffold remains in place.

## API Endpoints Used

- `GET /api/v1/restaurant`
- `GET /api/v1/categories`
- `GET /api/v1/menu-items`
- `GET /api/v1/menu-items/{menuItem}`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/register`
- `GET /api/v1/auth/me`
- `POST /api/v1/auth/logout`
- `GET /api/v1/customer/cart`
- `DELETE /api/v1/customer/cart`
- `POST /api/v1/customer/cart/items/{menuItem}`
- `POST /api/v1/customer/checkout`
- `GET /api/v1/customer/orders`
- `GET /api/v1/customer/orders/{order}`
- `GET /api/health` for development diagnostics

## Auth Flow

Laravel Sanctum returns bearer tokens from login/register. Tokens are stored only in Expo SecureStore. On app boot the auth store restores the token, fetches `/auth/me`, and routes by role without flashing protected screens.

Role routing:

- `customer` -> customer tabs
- `rider` -> rider scaffold
- `admin` -> admin route, which shows the disabled mobile admin screen unless `EXPO_PUBLIC_ENABLE_ADMIN_MOBILE=true`
- inactive or unsupported roles -> safe unavailable screen

Logout calls the backend when a session exists, clears SecureStore, and resets auth state. A `401` response also clears the session.

## Customer Flow

Customer dashboard combines real restaurant availability, latest orders, and featured menu data. Customer menu uses the same Laravel menu API as public browsing and supports search, category filters, pull-to-refresh, and add-to-cart actions.

## Cart Persistence

Cart items are stored locally in Zustand and persisted with AsyncStorage. Stored data is non-sensitive: menu item id, name, image URL, unit price, quantity, notes, and availability. Auth tokens are never stored in AsyncStorage.

The cart survives app reload, prevents quantities below one, calculates subtotal/total in AUD, applies backend restaurant delivery fee/minimum order settings, and clears only after successful order creation.

## Checkout Status

Checkout is integrated with the real Laravel customer checkout flow. Before submitting, mobile syncs the local cart into Laravel's authenticated cart endpoints, then calls `/api/v1/customer/checkout` with an idempotency key.

Laravel remains the authority for item pricing, availability, minimum order validation, restaurant open/closed state, order ownership, and order creation. Current location is requested only after tapping "Use my current location"; manual address remains required.

## Configuration

Copy the example environment file:

```bash
cd mobile
cp .env.example .env
```

Required values:

```bash
EXPO_PUBLIC_API_URL=http://YOUR_COMPUTER_LAN_IP:8000/api/v1
EXPO_PUBLIC_ENABLE_ADMIN_MOBILE=false
EXPO_PUBLIC_WEB_ADMIN_URL=https://example.com/admin
```

Use your computer LAN IP for physical phone testing. Do not point a physical phone at a loopback address because that resolves to the phone itself.

## Physical Phone Testing

Run Laravel from the repository root:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Start Expo:

```bash
cd mobile
npm run start
```

Phone and computer must be on the same network. If Expo is started with tunnel mode, Laravel still needs to be reachable by the API URL configured in `.env`.

## Validation Commands

```bash
cd mobile
npm install
npx expo install --fix
npx expo-doctor
npm run lint
npm run typecheck
npm test -- --runInBand
```

Backend validation after API changes:

```bash
composer install
composer dump-autoload
php artisan optimize:clear
php artisan route:list --path=api
php artisan test
```

## Known Limitations

- Forgot password mobile API is not available yet; the screen directs users to the existing web/contact path.
- Item notes are kept locally and folded into order notes during checkout because backend order items do not yet have a dedicated per-item note field.
- Add-ons and sizes are displayed by the API types but not selected in this mobile phase.
- Admin mobile is intentionally disabled by default and does not expose admin data.
- Rider workflow remains the existing scaffold and is not expanded in this phase.
- No paid map SDK is configured; contact and checkout use links/coordinates only.

## Next Phase Recommendations

- Add mobile password reset OTP endpoints.
- Add size/add-on selectors and backend-supported item notes.
- Add saved addresses and customer profile editing.
- Add push notifications and device registration UX.
- Build the rider delivery workflow as a separate phase.
- Design a dedicated admin mobile phase only after the web admin parity requirements are clear.
