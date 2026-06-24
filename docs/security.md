# Security Notes

## Authentication

- Mobile API uses Laravel Sanctum bearer tokens.
- Tokens are issued with role abilities: `customer`, `rider`, or `admin`.
- API route groups enforce authentication, active-account checks, role checks, and token ability checks.
- Blade web authentication remains session-based and unchanged.

## Rate Limiting

Named API limiters:

- `api-public`: public menu and restaurant endpoints
- `api-auth`: login/register
- `api-customer`: customer cart/order/profile routes
- `api-rider`: rider routes
- `api-admin`: admin routes
- `api-checkout`: checkout
- `api-status-update`: rider delivery updates
- `api-upload`: admin upload endpoints

The existing security middleware also tracks excessive requests and temporarily blocks abusive IPs for 10 minutes.

## Request Correlation

Every `/api/*` response includes `X-Request-ID`. If the client sends a safe `X-Request-ID`, the API reuses it; otherwise it generates a UUID.

## Idempotency

Customer checkout supports `Idempotency-Key`.

- Repeating the same checkout payload with the same key returns the same order.
- Reusing the same key with different checkout payload returns `409`.
- Keys expire after 24 hours.

## Authorization Rules

- Customers can only view their own orders, cart, and devices.
- Riders can only view and update assigned deliveries.
- Admins can manage all orders, riders, categories, menu items, and restaurant settings.
- Admin rider deletion is blocked when the rider has active assigned orders.
- Menu items used in old orders are not deleted through the API; mark them unavailable instead.

## Audit Trail

The API records audit logs for:

- Order creation
- Order status changes
- Rider assignment/unassignment
- Rider CRUD
- Restaurant/category/menu item CRUD
- Delivery status changes

Sensitive fields such as passwords and tokens are redacted before being stored.
