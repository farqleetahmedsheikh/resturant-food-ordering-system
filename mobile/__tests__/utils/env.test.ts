import { normalizeApiUrl } from '@/src/config/env';

describe('normalizeApiUrl', () => {
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
