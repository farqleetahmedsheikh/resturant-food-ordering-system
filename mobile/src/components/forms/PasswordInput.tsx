import type { ComponentProps } from 'react';

import { AppInput } from './AppInput';

type PasswordInputProps = Omit<ComponentProps<typeof AppInput>, 'secureTextEntry' | 'secureToggle'>;

export function PasswordInput(props: PasswordInputProps) {
  return <AppInput {...props} secureTextEntry secureToggle />;
}
