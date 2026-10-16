import { RoleUnavailable } from '@/src/components/feedback/RoleUnavailable';
import { renderWithTheme } from '../test-utils';

describe('RoleUnavailable', () => {
  it('renders access message', () => {
    const { getByText } = renderWithTheme(<RoleUnavailable title="No access" message="Use the web dashboard." />);

    expect(getByText('No access')).toBeTruthy();
    expect(getByText('Use the web dashboard.')).toBeTruthy();
  });
});
