import { loginSchema, registerSchema } from '@/src/auth/auth.validation';

describe('auth validation', () => {
  it('validates login email and password', () => {
    expect(loginSchema.safeParse({ email: 'bad', password: 'short' }).success).toBe(false);
    expect(loginSchema.safeParse({ email: 'customer@example.com', password: 'password123' }).success).toBe(true);
  });

  it('validates customer registration and matching password confirmation', () => {
    expect(
      registerSchema.safeParse({
        name: 'Customer User',
        email: 'customer@example.com',
        phone: '+61 400 000 000',
        password: 'password123',
        password_confirmation: 'different123',
        accepted: true,
      }).success,
    ).toBe(false);

    expect(
      registerSchema.safeParse({
        name: 'Customer User',
        email: 'customer@example.com',
        phone: '+61 400 000 000',
        password: 'password123',
        password_confirmation: 'password123',
        accepted: true,
      }).success,
    ).toBe(true);
  });
});
