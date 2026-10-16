import * as Network from 'expo-network';
import { act, waitFor } from '@testing-library/react-native';

import { OfflineBanner } from '@/src/components/feedback/OfflineBanner';
import { renderWithTheme } from '../test-utils';

describe('OfflineBanner', () => {
  it('renders when network is unavailable', async () => {
    jest.spyOn(Network, 'getNetworkStateAsync').mockResolvedValue({
      isConnected: false,
      isInternetReachable: false,
      type: 'none' as Network.NetworkStateType,
    });

    const screen = renderWithTheme(<OfflineBanner />);

    await waitFor(() => {
      expect(screen.getByText(/offline/i)).toBeTruthy();
    });

    act(() => {
      screen.unmount();
    });
  });
});
