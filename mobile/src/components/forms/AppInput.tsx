import { forwardRef, useState } from 'react';
import { Pressable, StyleSheet, TextInput, TextInputProps, View } from 'react-native';

import { AppText } from '@/src/components/common/AppText';
import { radius, spacing, useTheme } from '@/src/theme';

type AppInputProps = TextInputProps & {
  label: string;
  error?: string;
  secureToggle?: boolean;
};

export const AppInput = forwardRef<TextInput, AppInputProps>(
  ({ label, error, secureTextEntry, secureToggle = false, style, ...props }, ref) => {
    const { colors } = useTheme();
    const [hidden, setHidden] = useState(Boolean(secureTextEntry));
    const secure = secureToggle ? hidden : secureTextEntry;

    return (
      <View style={styles.wrapper}>
        <AppText variant="caption" color={colors.text.secondary}>
          {label}
        </AppText>
        <View style={[styles.inputShell, { borderColor: error ? colors.semantic.danger : colors.border.strong, backgroundColor: colors.surface.card }]}>
          <TextInput
            ref={ref}
            placeholderTextColor={colors.text.muted}
            secureTextEntry={secure}
            autoCapitalize="none"
            style={[styles.input, { color: colors.text.primary }, style]}
            {...props}
          />
          {secureToggle ? (
            <Pressable
              accessibilityRole="button"
              accessibilityLabel={hidden ? 'Show password' : 'Hide password'}
              onPress={() => setHidden((current) => !current)}
              style={styles.toggle}
            >
              <AppText variant="caption" color={colors.brand.primary}>
                {hidden ? 'Show' : 'Hide'}
              </AppText>
            </Pressable>
          ) : null}
        </View>
        {error ? (
          <AppText variant="caption" color={colors.semantic.danger}>
            {error}
          </AppText>
        ) : null}
      </View>
    );
  },
);

AppInput.displayName = 'AppInput';

const styles = StyleSheet.create({
  wrapper: {
    gap: spacing.sm,
  },
  inputShell: {
    minHeight: 52,
    flexDirection: 'row',
    alignItems: 'center',
    borderRadius: radius.lg,
    borderWidth: 1,
  },
  input: {
    flex: 1,
    minHeight: 52,
    paddingHorizontal: spacing.lg,
    fontSize: 15,
    fontWeight: '600',
  },
  toggle: {
    minHeight: 44,
    minWidth: 54,
    alignItems: 'center',
    justifyContent: 'center',
  },
});
