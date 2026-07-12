import { createEnv, DEFAULT_API_URL, normalizeApiUrl, safeEnv } from '@/src/config/env';

function envSource(values: Partial<NodeJS.ProcessEnv>): NodeJS.ProcessEnv {
  return values as unknown as NodeJS.ProcessEnv;
}

describe('normalizeApiUrl', () => {
  it('defaults missing native API URL to the production backend', () => {
    expect(createEnv(envSource({})).apiUrl).toBe(DEFAULT_API_URL);
    expect(createEnv(envSource({ EXPO_PUBLIC_API_URL: '   ' })).apiUrl).toBe(DEFAULT_API_URL);
  });

  it('keeps a safe API URL when returning a configuration error', () => {
    const env = safeEnv(envSource({ EXPO_PUBLIC_API_URL: 'ftp://example.com/api/v1' }));

    expect(env.apiUrl).toBe(DEFAULT_API_URL);
    expect(env.configError).toContain('http:// or https://');
  });

  it('removes trailing slashes', () => {
    expect(normalizeApiUrl('http://192.168.1.20:8000/api/v1/')).toBe('http://192.168.1.20:8000/api/v1');
  });

  it('rejects loopback hosts for physical phone usage', () => {
    const loopbackHost = 'local'.concat('host');

    expect(() => normalizeApiUrl(`http://${loopbackHost}:8000/api/v1`)).toThrow('LAN IP');
  });

  it('allows loopback hosts for explicit web API usage', () => {
    expect(normalizeApiUrl('http://127.0.0.1:8000/api/v1', { allowLoopback: true })).toBe(
      'http://127.0.0.1:8000/api/v1',
    );
  });

  it('rejects the Expo dev server port as an API URL', () => {
    expect(() => normalizeApiUrl('http://192.168.1.20:8081/api/v1')).toThrow('Expo dev server port');
  });
});
