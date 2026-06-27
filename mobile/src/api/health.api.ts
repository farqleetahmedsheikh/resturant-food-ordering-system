import axios from 'axios';

import { env, runtimeInfo } from '@/src/config/env';

export type HealthResult = {
  ok: boolean;
  apiUrl: string;
  responseTimeMs: number;
  status?: string;
  application?: string;
  message?: string;
  appVersion: string;
  expoVersion: string;
};

function healthBaseUrl(): string {
  return env.apiUrl.replace(/\/v1$/, '');
}

export async function checkHealth(): Promise<HealthResult> {
  const startedAt = Date.now();
  const url = `${healthBaseUrl()}/health`;

  try {
    const response = await axios.get<{ status: string; application: string }>(url, {
      timeout: 8000,
      headers: { Accept: 'application/json' },
    });

    return {
      ok: response.data.status === 'ok',
      apiUrl: env.apiUrl,
      responseTimeMs: Date.now() - startedAt,
      status: response.data.status,
      application: response.data.application,
      appVersion: runtimeInfo.appVersion,
      expoVersion: runtimeInfo.expoVersion,
    };
  } catch (error) {
    return {
      ok: false,
      apiUrl: env.apiUrl,
      responseTimeMs: Date.now() - startedAt,
      message: error instanceof Error ? error.message : 'Health check failed.',
      appVersion: runtimeInfo.appVersion,
      expoVersion: runtimeInfo.expoVersion,
    };
  }
}
