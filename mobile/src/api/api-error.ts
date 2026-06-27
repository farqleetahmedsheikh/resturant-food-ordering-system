import { AxiosError, isAxiosError } from 'axios';

export type NormalizedApiError = {
  status: number | null;
  message: string;
  validationErrors: Record<string, string[]>;
  isNetworkError: boolean;
  isRateLimited: boolean;
};

type LaravelErrorPayload = {
  message?: string;
  errors?: Record<string, string[]>;
};

export class ApiError extends Error implements NormalizedApiError {
  status: number | null;
  validationErrors: Record<string, string[]>;
  isNetworkError: boolean;
  isRateLimited: boolean;

  constructor(error: NormalizedApiError) {
    super(error.message);
    this.name = 'ApiError';
    this.status = error.status;
    this.validationErrors = error.validationErrors;
    this.isNetworkError = error.isNetworkError;
    this.isRateLimited = error.isRateLimited;
  }
}

export function normalizeApiError(error: unknown): NormalizedApiError {
  if (error instanceof ApiError) {
    return error;
  }

  if (!isAxiosError(error)) {
    return {
      status: null,
      message: 'Something went wrong. Please try again.',
      validationErrors: {},
      isNetworkError: false,
      isRateLimited: false,
    };
  }

  const axiosError = error as AxiosError<LaravelErrorPayload>;
  const status = axiosError.response?.status ?? null;
  const payload = axiosError.response?.data;
  const networkError = !axiosError.response;

  let message = payload?.message;

  if (!message) {
    if (networkError) {
      message = 'Cannot reach Arcade Kebab House API. Check your internet connection and API URL.';
    } else if (status === 401) {
      message = 'Your session has expired. Please login again.';
    } else if (status === 403) {
      message = 'You are not allowed to access this area.';
    } else if (status === 404) {
      message = 'The requested information was not found.';
    } else if (status === 422) {
      message = 'Please review the highlighted fields.';
    } else if (status === 429) {
      message = 'Too many requests. Please wait a moment and try again.';
    } else if (status && status >= 500) {
      message = 'The server is having trouble. Please try again shortly.';
    } else {
      message = 'Something went wrong. Please try again.';
    }
  }

  return {
    status,
    message,
    validationErrors: payload?.errors ?? {},
    isNetworkError: networkError,
    isRateLimited: status === 429,
  };
}

export function shouldRetryApiError(error: unknown): boolean {
  const normalized = normalizeApiError(error);

  if (normalized.status && [401, 403, 404, 422, 429].includes(normalized.status)) {
    return false;
  }

  return normalized.isNetworkError || (normalized.status !== null && normalized.status >= 500);
}
