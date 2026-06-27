import { Redirect } from 'expo-router';

import { getRoleRedirect } from '@/src/auth/role-redirect';
import { useAuthStore } from '@/src/auth/auth.store';
import { env } from '@/src/config/env';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';

export default function IndexScreen() {
  const status = useAuthStore((state) => state.status);
  const user = useAuthStore((state) => state.session?.user ?? null);

  if (status === 'loading') {
    return <LoadingScreen />;
  }

  return <Redirect href={getRoleRedirect(user, env.enableAdminMobile)} />;
}
