import { canAccessRole, hasAbility, isSupportedRole } from '@/src/auth/permissions';

describe('permissions', () => {
  it('knows supported roles', () => {
    expect(isSupportedRole('customer')).toBe(true);
    expect(isSupportedRole('manager')).toBe(false);
  });

  it('checks abilities and active account role access', () => {
    expect(hasAbility(['customer'], 'customer')).toBe(true);
    expect(
      canAccessRole(
        {
          id: 1,
          name: 'Rider',
          email: 'rider@example.com',
          phone: null,
          role: 'rider',
          is_active: true,
        },
        'rider',
      ),
    ).toBe(true);
  });
});
