import { env } from '@/src/config/env';
import { useAuthStore } from './auth.store';
import { canAccessRole, isSupportedRole } from './permissions';

export function useRoleGuards() {
  const { session, status } = useAuthStore();
  const user = session?.user ?? null;
  const authenticated = status === 'authenticated' && Boolean(user);

  return {
    ready: status !== 'loading',
    authenticated,
    guest: status === 'guest',
    customer: authenticated && canAccessRole(user, 'customer'),
    rider: authenticated && canAccessRole(user, 'rider'),
    admin: authenticated && canAccessRole(user, 'admin') && env.enableAdminMobile,
    adminUnavailable: authenticated && canAccessRole(user, 'admin') && !env.enableAdminMobile,
    unsupported: authenticated && (!user?.is_active || !isSupportedRole(user.role)),
  };
}
