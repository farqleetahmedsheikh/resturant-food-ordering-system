import { render } from '@testing-library/react-native';

import { RoleUnavailable } from '@/src/components/feedback/RoleUnavailable';

describe('RoleUnavailable', () => {
  it('renders access message', () => {
    const { getByText } = render(<RoleUnavailable title="No access" message="Use the web dashboard." />);

    expect(getByText('No access')).toBeTruthy();
    expect(getByText('Use the web dashboard.')).toBeTruthy();
  });
});
