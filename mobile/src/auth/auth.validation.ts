import { z } from 'zod';

import { emailSchema, passwordSchema } from '@/src/utils/validation';

export const loginSchema = z.object({
  email: emailSchema,
  password: passwordSchema,
});

export const registerSchema = z
  .object({
    name: z.string().trim().min(2, 'Enter your full name.'),
    email: emailSchema,
    phone: z.string().trim().optional(),
    password: passwordSchema,
    password_confirmation: z.string().min(8, 'Confirm your password.'),
    accepted: z.boolean().refine((value) => value, 'Please acknowledge the account terms placeholder.'),
  })
  .refine((value) => value.password === value.password_confirmation, {
    path: ['password_confirmation'],
    message: 'Passwords do not match.',
  });

export type LoginFormValues = z.infer<typeof loginSchema>;
export type RegisterFormValues = z.infer<typeof registerSchema>;
