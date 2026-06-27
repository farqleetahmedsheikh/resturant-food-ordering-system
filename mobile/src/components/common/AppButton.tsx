import { ActivityIndicator, Pressable, PressableProps, StyleSheet, ViewStyle } from 'react-native';

import { AppText } from './AppText';
import { colors, radius, shadows, spacing } from '@/src/theme';

type AppButtonProps = PressableProps & {
  label: string;
  variant?: 'primary' | 'secondary' | 'outline' | 'danger';
  loading?: boolean;
  fullWidth?: boolean;
};

export function AppButton({
  label,
  variant = 'primary',
  loading = false,
  disabled,
  fullWidth = false,
  style,
  ...props
}: AppButtonProps) {
  const isDisabled = disabled || loading;

  return (
    <Pressable
      accessibilityRole="button"
      accessibilityState={{ disabled: isDisabled, busy: loading }}
      disabled={isDisabled}
      style={({ pressed }) => [
        styles.base,
        styles[variant],
        fullWidth && styles.fullWidth,
        pressed && !isDisabled && styles.pressed,
        isDisabled && styles.disabled,
        style as ViewStyle,
      ]}
      {...props}
    >
      {loading ? (
        <ActivityIndicator color={variant === 'outline' || variant === 'secondary' ? colors.brand.primary : '#fff'} />
      ) : (
        <AppText
          variant="caption"
          color={variant === 'outline' || variant === 'secondary' ? colors.brand.primary : colors.text.inverse}
        >
          {label}
        </AppText>
      )}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  base: {
    minHeight: 48,
    minWidth: 48,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.lg,
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.md,
  },
  fullWidth: {
    width: '100%',
  },
  primary: {
    backgroundColor: colors.brand.primary,
    ...shadows.button,
  },
  secondary: {
    backgroundColor: colors.brand.soft,
    borderWidth: 1,
    borderColor: colors.brand.border,
  },
  outline: {
    backgroundColor: colors.surface.card,
    borderWidth: 1,
    borderColor: colors.border.strong,
  },
  danger: {
    backgroundColor: colors.semantic.danger,
  },
  pressed: {
    transform: [{ scale: 0.98 }],
    opacity: 0.9,
  },
  disabled: {
    opacity: 0.55,
  },
});
