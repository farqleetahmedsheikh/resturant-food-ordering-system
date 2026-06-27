import { getRoleRedirect } from '@/src/auth/role-redirect';
import type { AuthUser } from '@/src/types/auth';

const baseUser: AuthUser = {
  id: 1,
  name: 'Demo',
  email: 'demo@example.com',
  phone: null,
  role: 'customer',
  is_active: true,
};

describe('getRoleRedirect', () => {
  it('routes customers to customer tabs', () => {
    expect(getRoleRedirect(baseUser, false)).toBe('/(customer)/(tabs)');
  });

  it('routes riders to rider tabs', () => {
    expect(getRoleRedirect({ ...baseUser, role: 'rider' }, false)).toBe('/(rider)/(tabs)');
  });

  it('routes admins to the admin shell where the feature flag controls available UI', () => {
    expect(getRoleRedirect({ ...baseUser, role: 'admin' }, false)).toBe('/(admin)');
    expect(getRoleRedirect({ ...baseUser, role: 'admin' }, true)).toBe('/(admin)');
  });

  it('does not silently map unknown roles to admin', () => {
    expect(getRoleRedirect({ ...baseUser, role: 'manager' }, true)).toBe('/unsupported-role');
  });
});
