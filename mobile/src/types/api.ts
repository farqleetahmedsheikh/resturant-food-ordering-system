export type ApiEnvelope<T> = {
  success: boolean;
  message: string;
  data: T;
  meta?: Record<string, unknown>;
  errors?: Record<string, string[]>;
};

export type PaginationMeta = {
  current_page?: number | null;
  per_page?: number | null;
  total?: number | null;
  last_page?: number | null;
};
