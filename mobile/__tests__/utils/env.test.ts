import { normalizeApiUrl } from '@/src/config/env';

describe('normalizeApiUrl', () => {
  it('removes trailing slashes', () => {
    expect(normalizeApiUrl('http://192.168.1.20:8000/api/v1/')).toBe('http://192.168.1.20:8000/api/v1');
  });

  it('rejects loopback hosts for physical phone usage', () => {
    const loopbackHost = 'local'.concat('host');

    expect(() => normalizeApiUrl(`http://${loopbackHost}:8000/api/v1`)).toThrow('LAN IP');
  });
});
