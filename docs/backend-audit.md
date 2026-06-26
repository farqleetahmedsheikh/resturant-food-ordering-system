# Arcade Kebab House Backend Audit

Date: 2026-06-24

## Current Architecture

Arcade Kebab House is a Laravel 13.16.1 application running on PHP 8.4 locally. The existing product is a Blade-based food ordering website with role-specific web dashboards for customers, admins, and riders.

Current route files:

- `routes/web.php` for public pages, auth, customer cart, and checkout.
- `routes/customer.php` for customer dashboard and orders.
- `routes/admin.php` for custom admin dashboard, order management, riders, menu items, categories, and restaurant settings.
- `routes/rider.php` for rider dashboard and delivery workflow.

Authentication is session-based for the web app. Roles are stored as strings on `users.role` with values such as `customer`, `rider`, and `admin`. Role access is enforced through custom middleware classes.

The app has strong Blade coverage for demo flows and uses models for `Restaurant`, `Category`, `MenuItem`, `MenuItemSize`, `MenuItemAddon`, `Order`, `OrderItem`, `Delivery`, and `User`.

## Existing Strengths

- Blade website and dashboards are already functional.
- Admin can manage menu/category/restaurant/rider/order data.
- Customer checkout records order item snapshots for size/add-on data.
- Rider workflow restricts web delivery updates to the assigned rider.
- Restaurant settings control open/closed state, delivery fee, and minimum order amount.
- Image uploads use Laravel storage helpers.
- Recent rate limiting and temporary IP blocking middleware exists for web traffic.
- Performance indexes exist for common order/menu/user queries.

## Existing Security Weaknesses

- No mobile bearer-token authentication existed before this phase.
- No `/api/v1` routes existed before this phase.
- Session cart is not suitable for React Native clients.
- Web checkout logic lives mostly inside controllers.
- Admin and rider status updates are controller-driven and allow direct status updates without an append-only status history.
- There are no formal policies for API ownership checks yet.
- No request correlation ID existed before this phase.
- No audit log existed for sensitive backend actions.
- Public API response shape was not standardized because APIs did not exist.

## Existing Duplicated Logic

- Cart item validation and pricing exists in the session cart/web cart controller.
- Checkout validation and order creation exist in `Customer\CheckoutController`.
- Admin order status changes and rider delivery status changes both update order/delivery fields directly.
- Restaurant open/minimum-order checks are repeated in checkout methods.

## Session-Dependent Features

- `App\Support\Cart` stores cart state in PHP session.
- Existing Blade checkout depends on session cart state.
- Web login/register/logout are session-based.

The web session cart must remain intact for guest/customer website behavior, but mobile apps need a database-backed cart.

## Missing API Functionality

- Mobile registration/login/logout/me endpoints.
- Public restaurant/category/menu APIs.
- Customer profile/cart/checkout/orders APIs.
- Rider deliveries APIs.
- Admin dashboard/order/rider/category/menu/restaurant APIs.
- Device-token persistence APIs.
- Standard API resources and consistent JSON envelope.

## Missing Database Structures

- Database carts and cart item add-ons for mobile.
- Idempotency keys for duplicate checkout protection.
- Order status history.
- User devices for push-token registration.
- Audit logs.
- Personal access tokens for Sanctum.

## Existing Indexes

The app already has useful indexes on:

- `users.role`, `users.role + is_active`, `users.is_active`
- `orders.user_id + created_at`
- `orders.rider_id + order_status`
- `orders.order_status + created_at`
- `orders.payment_method + payment_status + order_status`
- `deliveries.rider_id + status`
- `categories` and `menu_items` availability/sort lookups

Mobile cart, device, idempotency, audit, status history, and Sanctum tables need their own indexes.

## Potential Race Conditions

- Duplicate mobile checkout requests could create duplicate orders without idempotency.
- Admin rider assignment can be submitted twice in quick succession.
- Rider status updates can be submitted repeatedly.
- Menu prices/availability may change after a cart is built unless checkout revalidates every item.

## Potential Authorization Problems

- API route model binding must still check ownership.
- Token abilities alone are not enough; database role and `is_active` must be checked on every protected API request.
- Public registration must never accept a user role.
- Rider APIs must only return orders assigned to the authenticated rider.
- Customer APIs must only return orders owned by the authenticated customer.

## Potential N+1 Query Problems

- Menu APIs must eager load category, sizes, and add-ons.
- Order detail APIs must eager load items, delivery, rider, and customer only when authorized.
- Admin list APIs must paginate and eager load compact relations.
- Dashboard statistics should use aggregate queries, not load entire tables.

## Changes Required For Mobile Support

- Install and configure Sanctum.
- Add `/api/v1` route file.
- Add active-user, role, token-ability, and request-ID middleware.
- Add standardized API responses/resources.
- Add database cart schema and service.
- Add pricing and checkout services shared by API and future web refactors.
- Add idempotency support for checkout.
- Add centralized order/delivery status services and status history.
- Add audit logs.
- Add user device storage.
- Add API documentation and tests.
