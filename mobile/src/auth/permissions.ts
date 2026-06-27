import type { AuthUser, UserRole } from '@/src/types/auth';

export function isSupportedRole(role: string | undefined | null): role is UserRole {
  return role === 'customer' || role === 'rider' || role === 'admin';
}

export function hasAbility(abilities: string[], ability: string): boolean {
  return abilities.includes(ability);
}

export function canAccessRole(user: AuthUser | null, role: UserRole): boolean {
  return Boolean(user?.is_active && user.role === role);
}
