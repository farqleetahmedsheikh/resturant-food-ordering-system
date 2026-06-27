import { render } from '@testing-library/react-native';

import { AppButton } from '@/src/components/common/AppButton';

describe('AppButton', () => {
  it('shows busy accessibility state while loading', () => {
    const { getByRole } = render(<AppButton label="Login" loading />);

    expect(getByRole('button').props.accessibilityState).toMatchObject({
      busy: true,
      disabled: true,
    });
  });
});
