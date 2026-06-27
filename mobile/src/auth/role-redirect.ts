import type { AuthUser } from '@/src/types/auth';

export function getRoleRedirect(user: AuthUser | null, enableAdminMobile: boolean): string {
  void enableAdminMobile;

  if (!user) {
    return '/(public)';
  }

  if (!user.is_active) {
    return '/unsupported-role';
  }

  if (user.role === 'customer') {
    return '/(customer)/(tabs)';
  }

  if (user.role === 'rider') {
    return '/(rider)/(tabs)';
  }

  if (user.role === 'admin') {
    return '/(admin)';
  }

  return '/unsupported-role';
}
