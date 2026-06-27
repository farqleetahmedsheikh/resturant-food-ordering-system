import { Modal, Pressable, StyleSheet, View } from 'react-native';

import { AppButton } from './AppButton';
import { AppText } from './AppText';
import { colors, radius, shadows, spacing } from '@/src/theme';

type ConfirmModalProps = {
  visible: boolean;
  title: string;
  message: string;
  confirmLabel?: string;
  cancelLabel?: string;
  destructive?: boolean;
  loading?: boolean;
  onConfirm: () => void;
  onCancel: () => void;
};

export function ConfirmModal({
  visible,
  title,
  message,
  confirmLabel = 'Confirm',
  cancelLabel = 'Cancel',
  destructive = false,
  loading = false,
  onConfirm,
  onCancel,
}: ConfirmModalProps) {
  return (
    <Modal visible={visible} transparent animationType="fade" onRequestClose={onCancel}>
      <Pressable style={styles.backdrop} onPress={onCancel}>
        <Pressable style={styles.dialog}>
          <AppText variant="title">{title}</AppText>
          <AppText color={colors.text.secondary}>{message}</AppText>
          <View style={styles.actions}>
            <AppButton label={cancelLabel} variant="outline" fullWidth onPress={onCancel} />
            <AppButton
              label={confirmLabel}
              variant={destructive ? 'danger' : 'primary'}
              loading={loading}
              fullWidth
              onPress={onConfirm}
            />
          </View>
        </Pressable>
      </Pressable>
    </Modal>
  );
}

const styles = StyleSheet.create({
  backdrop: {
    flex: 1,
    justifyContent: 'center',
    backgroundColor: 'rgba(15, 13, 12, 0.52)',
    padding: spacing.xl,
  },
  dialog: {
    gap: spacing.lg,
    borderRadius: radius.xl,
    backgroundColor: colors.surface.card,
    padding: spacing.xl,
    ...shadows.card,
  },
  actions: {
    gap: spacing.md,
  },
});
