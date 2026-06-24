# FreshBite Mobile API

Base URL:

```text
https://your-domain.com/api/v1
```

All responses use this shape:

```json
{
  "success": true,
  "message": "OK",
  "data": {},
  "meta": {}
}
```

Errors use:

```json
{
  "success": false,
  "message": "The provided data is invalid.",
  "errors": {}
}
```

Send `X-Request-ID` on every request when possible. The API also returns `X-Request-ID`.

## Auth

Use Laravel Sanctum bearer tokens.

| Method | Endpoint | Notes |
| --- | --- | --- |
| POST | `/auth/register` | Creates a customer account |
| POST | `/auth/login` | Returns bearer token and role abilities |
| GET | `/auth/me` | Current user |
| POST | `/auth/logout` | Revoke current token |
| POST | `/auth/logout-all` | Revoke all tokens |

Authorization header:

```text
Authorization: Bearer {token}
```

## Public Menu

| Method | Endpoint | Notes |
| --- | --- | --- |
| GET | `/restaurant` | Active restaurant settings |
| GET | `/categories` | Active categories |
| GET | `/menu-items` | Available menu items |
| GET | `/menu-items/{menuItem}` | Menu item detail |

Useful menu filters:

```text
/menu-items?category=pizza&featured=1&search=burger&per_page=20
```

## Customer

Requires a customer token.

| Method | Endpoint | Notes |
| --- | --- | --- |
| GET | `/customer/profile` | Customer profile |
| PUT | `/customer/profile` | Update profile |
| GET | `/customer/cart` | DB-backed mobile cart |
| POST | `/customer/cart/items/{menuItem}` | Add item with `quantity`, optional `size_id`, optional `addon_ids[]` |
| PUT | `/customer/cart/items/{cartItem}` | Update quantity |
| DELETE | `/customer/cart/items/{cartItem}` | Remove item |
| DELETE | `/customer/cart` | Clear cart |
| POST | `/customer/checkout` | Place COD order. Send `Idempotency-Key` |
| GET | `/customer/orders` | Customer order list |
| GET | `/customer/orders/{order}` | Customer order detail and progress |

Checkout body:

```json
{
  "customer_name": "Demo Customer",
  "customer_phone": "03001234567",
  "customer_email": "customer@example.com",
  "delivery_address": "Demo delivery address",
  "order_notes": "No onions",
  "payment_method": "cod"
}
```

## Rider

Requires a rider token.

| Method | Endpoint | Notes |
| --- | --- | --- |
| GET | `/rider/profile` | Rider profile |
| PUT | `/rider/profile` | Update profile |
| GET | `/rider/dashboard` | Rider metrics |
| GET | `/rider/deliveries` | Assigned orders only |
| GET | `/rider/deliveries/{order}` | Assigned order detail |
| POST | `/rider/deliveries/{order}/status` | `picked_up`, `out_for_delivery`, `delivered`, or `failed` |

Failed status requires `notes`.

## Admin

Requires an admin token.

| Method | Endpoint | Notes |
| --- | --- | --- |
| GET | `/admin/dashboard` | Dashboard metrics |
| GET/PUT | `/admin/restaurant` | Restaurant settings |
| GET | `/admin/orders` | Filter with `status` |
| GET | `/admin/orders/{order}` | Order detail |
| PATCH | `/admin/orders/{order}/status` | Update order status |
| POST | `/admin/orders/{order}/assign-rider` | Assign active rider |
| DELETE | `/admin/orders/{order}/unassign-rider` | Unassign rider |
| API resource | `/admin/riders` | Manage riders |
| API resource | `/admin/categories` | Manage categories |
| API resource | `/admin/menu-items` | Manage menu items |

Uploads must be `jpg`, `jpeg`, `png`, or `webp`, max 2 MB.

## Devices

Authenticated users can register or revoke mobile push tokens.

| Method | Endpoint | Notes |
| --- | --- | --- |
| POST | `/devices` | Register/update push token |
| DELETE | `/devices/{device}` | Revoke device |

Push delivery is not implemented yet; tokens are stored for the next phase.
