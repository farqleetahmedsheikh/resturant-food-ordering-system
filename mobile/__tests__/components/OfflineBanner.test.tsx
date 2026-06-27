import * as Network from 'expo-network';
import { act, render, waitFor } from '@testing-library/react-native';

import { OfflineBanner } from '@/src/components/feedback/OfflineBanner';

describe('OfflineBanner', () => {
  it('renders when network is unavailable', async () => {
    jest.spyOn(Network, 'getNetworkStateAsync').mockResolvedValue({
      isConnected: false,
      isInternetReachable: false,
      type: 'none' as Network.NetworkStateType,
    });

    const screen = render(<OfflineBanner />);

    await waitFor(() => {
      expect(screen.getByText(/offline/i)).toBeTruthy();
    });

    act(() => {
      screen.unmount();
    });
  });
});
