# Arcade Kebab House Mobile API Plan

Date: 2026-06-24

## Proposed Architecture

Arcade Kebab House will keep the existing Blade website and role dashboards while adding a versioned JSON API for React Native apps:

```text
Blade website + React Native apps
        -> shared Laravel services/actions
        -> shared database and business rules
```

The web app continues using session authentication and the existing session cart. Mobile clients use Sanctum bearer tokens and database-backed carts.

## API Modules

All mobile routes live under `/api/v1`.

- Public: restaurant, categories, menu items.
- Auth: register, login, me, logout, logout-all.
- Customer: profile, cart, checkout, orders.
- Rider: profile, assigned deliveries, delivery status updates.
- Admin: dashboard, orders, rider management, category/menu management, restaurant settings.
- Devices: device/push-token registration and revocation.

## Authentication Flow

- Mobile login accepts `email`, `password`, and `device_name`.
- Sanctum creates one token per device.
- Token abilities are assigned based on the current role.
- Every protected request also verifies `users.is_active` and the current database role.
- Logout deletes only the current token.
- Logout-all deletes all tokens for the user.
- Disabled accounts cannot receive new tokens.

## Role Authorization

Middleware:

- `api.active`: reject inactive users and revoke their active token.
- `api.role`: require one or more current database roles.
- `api.ability`: require a Sanctum token ability and still rely on role checks.
- `request.id`: attach an `X-Request-ID` response header.

Ownership:

- Customer order/cart access is scoped to authenticated customer.
- Rider delivery access is scoped to `orders.rider_id`.
- Admin endpoints require admin role.

## Cart Migration Strategy

- Preserve `App\Support\Cart` for web/session carts.
- Add `carts`, `cart_items`, and `cart_item_addons` for mobile.
- Use `DatabaseCartService` for authenticated mobile carts.
- Use `PricingService` for server-side validation and totals.
- Client submits item, size, add-on IDs, and quantity only.
- Server calculates all prices and totals.

## Order Workflow

- Use `CheckoutService` for mobile checkout.
- Checkout runs inside a transaction.
- Revalidates restaurant, open state, minimum order, menu availability, size/add-on ownership, and authoritative prices.
- Uses `Idempotency-Key` header to prevent duplicate order creation.
- Creates order and item snapshots.
- Converts the active database cart after successful order creation.

## Rider Workflow

- Rider sees only assigned orders.
- Rider status updates use `DeliveryStatusService`.
- Valid statuses: `picked_up`, `out_for_delivery`, `delivered`, `failed`.
- Delivered/cancelled orders cannot be updated again.
- Stripe orders are marked paid only after verified Stripe webhook confirmation.
- Status changes create audit records and order status history where applicable.

## Admin Workflow

- Admin dashboard uses aggregate queries.
- Admin order actions use `OrderStatusService` and `RiderAssignmentService`.
- Admin rider/category/menu/settings endpoints validate input and never trust client roles or prices.
- Menu/category delete endpoints are conservative and avoid breaking historical order snapshots.

## Security Controls

- Sanctum bearer tokens.
- Token abilities by role.
- Active account middleware.
- Role middleware.
- Request correlation ID.
- Standard JSON error format.
- Rate limiters for public, auth, customer, rider, admin, checkout, status update, and uploads.
- Idempotent checkout.
- Transactions and row locks for checkout/status/assignment.
- Audit logs for sensitive actions.
- Redaction of passwords, tokens, authorization headers, and push tokens.

## Testing Strategy

Priority tests:

- Customer registration cannot create admin/rider.
- Login creates Sanctum token.
- Logout revokes current token.
- Wrong role receives 403.
- Public menu endpoints work.
- Customer database cart ignores client price.
- Invalid size/add-on is rejected.
- Checkout idempotency creates one order.
- Customer cannot see another customer's order.
- Rider cannot update another rider's delivery.
- Admin can assign rider.

## Migration Strategy

- Add new tables only.
- Do not drop existing columns.
- Add indexes defensively.
- Keep migrations reversible.
- Backfill is not required for database carts because carts are mobile-only.

## Backward Compatibility Strategy

- Existing web routes remain unchanged.
- Existing Blade views remain unchanged except links already added for security.
- Existing session cart remains active.
- New services are introduced for API first; web controllers can be refactored gradually.
- Existing order and delivery statuses remain compatible with current values.
