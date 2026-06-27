export const endpoints = {
  auth: {
    login: '/auth/login',
    register: '/auth/register',
    me: '/auth/me',
    logout: '/auth/logout',
  },
  restaurant: '/restaurant',
  categories: '/categories',
  menuItems: '/menu-items',
  customer: {
    profile: '/customer/profile',
    cart: '/customer/cart',
    orders: '/customer/orders',
    checkout: '/customer/checkout',
  },
  rider: {
    dashboard: '/rider/dashboard',
    deliveries: '/rider/deliveries',
    profile: '/rider/profile',
  },
  admin: {
    dashboard: '/admin/dashboard',
    orders: '/admin/orders',
    riders: '/admin/riders',
  },
} as const;
