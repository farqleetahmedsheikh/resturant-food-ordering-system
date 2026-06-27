import { AxiosError } from 'axios';

import { normalizeApiError, shouldRetryApiError } from '@/src/api/api-error';

describe('normalizeApiError', () => {
  it('maps Laravel validation errors', () => {
    const error = new AxiosError('Validation failed', undefined, undefined, undefined, {
      status: 422,
      statusText: 'Unprocessable Entity',
      headers: {},
      config: {} as never,
      data: {
        message: 'The email field is required.',
        errors: {
          email: ['The email field is required.'],
        },
      },
    });

    const normalized = normalizeApiError(error);

    expect(normalized.status).toBe(422);
    expect(normalized.validationErrors.email[0]).toBe('The email field is required.');
    expect(shouldRetryApiError(error)).toBe(false);
  });
});
