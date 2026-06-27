export const queryKeys = {
  restaurant: ['restaurant'] as const,
  categories: ['categories'] as const,
  menuItems: (params?: Record<string, unknown>) => ['menu-items', params ?? {}] as const,
  customerOrders: ['customer', 'orders'] as const,
  riderDeliveries: ['rider', 'deliveries'] as const,
  riderDashboard: ['rider', 'dashboard'] as const,
  adminOrders: ['admin', 'orders'] as const,
  health: ['health'] as const,
};
