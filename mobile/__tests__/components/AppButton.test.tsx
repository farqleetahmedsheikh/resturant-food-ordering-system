import { AppButton } from '@/src/components/common/AppButton';
import { renderWithTheme } from '../test-utils';

describe('AppButton', () => {
  it('shows busy accessibility state while loading', () => {
    const { getByRole } = renderWithTheme(<AppButton label="Login" loading />);

    expect(getByRole('button').props.accessibilityState).toMatchObject({
      busy: true,
      disabled: true,
    });
  });
});
