import { zodResolver } from '@hookform/resolvers/zod';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useEffect, useState } from 'react';
import { Controller, useForm } from 'react-hook-form';
import type { FieldValues, Path, UseFormSetError } from 'react-hook-form';
import { Pressable, StyleSheet, View } from 'react-native';
import { z } from 'zod';

import {
  createCustomerAddress,
  deleteCustomerAddress,
  getCustomerAddresses,
  updateCustomerAddress,
} from '@/src/api/addresses.api';
import { normalizeApiError } from '@/src/api/api-error';
import { getRegisteredDevices, registerDevice, revokeDevice } from '@/src/api/devices.api';
import { updateCustomerProfile } from '@/src/api/profile.api';
import { useAuthStore } from '@/src/auth/auth.store';
import { AppIcon, appIcons, type AppIconName } from '@/src/components/common/AppIcon';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { ConfirmModal } from '@/src/components/common/ConfirmModal';
import { SectionTitle } from '@/src/components/common/SectionTitle';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { AppCheckbox } from '@/src/components/forms/AppCheckbox';
import { AppInput } from '@/src/components/forms/AppInput';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { useLocationPermission } from '@/src/hooks/useLocationPermission';
import { createPushRegistrationPayload, getPushRegistrationSupport } from '@/src/notifications/push';
import { colors, radius, shadows, spacing } from '@/src/theme';
import type { CustomerAddress } from '@/src/types/address';
import type { UserDevice } from '@/src/types/device';
import { formatDateTime } from '@/src/utils/date';

const profileSchema = z.object({
  name: z.string().trim().min(2, 'Enter your full name.'),
  phone: z.string().trim().max(30, 'Phone is too long.').optional(),
});

const addressSchema = z.object({
  label: z.string().trim().max(80, 'Label is too long.').optional(),
  recipient_name: z.string().trim().max(191, 'Name is too long.').optional(),
  phone: z.string().trim().max(40, 'Phone is too long.').optional(),
  address: z.string().trim().min(8, 'Enter a delivery address.').max(1000, 'Address is too long.'),
  delivery_notes: z.string().trim().max(700, 'Notes are too long.').optional(),
  is_default: z.boolean(),
});

type ProfileForm = z.infer<typeof profileSchema>;
type AddressForm = z.infer<typeof addressSchema>;
type ProfileSection = 'account' | 'addresses' | 'notifications';
type PendingAction =
  | { type: 'logout' }
  | { type: 'delete-address'; address: CustomerAddress }
  | { type: 'revoke-device'; device: UserDevice };

export default function CustomerProfileScreen() {
  const queryClient = useQueryClient();
  const user = useAuthStore((state) => state.session?.user);
  const logout = useAuthStore((state) => state.logout);
  const updateUser = useAuthStore((state) => state.updateUser);
  const location = useLocationPermission();
  const [activeSection, setActiveSection] = useState<ProfileSection>('account');
  const [addressFormOpen, setAddressFormOpen] = useState(false);
  const [pendingAction, setPendingAction] = useState<PendingAction | null>(null);
  const [editingAddress, setEditingAddress] = useState<CustomerAddress | null>(null);
  const [message, setMessage] = useState<{ tone: 'success' | 'error' | 'info'; text: string } | null>(null);

  const addressesQuery = useQuery({
    queryKey: queryKeys.customerAddresses,
    queryFn: getCustomerAddresses,
  });
  const devicesQuery = useQuery({
    queryKey: queryKeys.devices,
    queryFn: getRegisteredDevices,
  });

  const profileForm = useForm<ProfileForm>({
    resolver: zodResolver(profileSchema),
    defaultValues: {
      name: user?.name ?? '',
      phone: user?.phone ?? '',
    },
  });

  const addressForm = useForm<AddressForm>({
    resolver: zodResolver(addressSchema),
    defaultValues: emptyAddressDefaults(user),
  });

  useEffect(() => {
    profileForm.reset({
      name: user?.name ?? '',
      phone: user?.phone ?? '',
    });
  }, [profileForm, user?.name, user?.phone]);

  const profileMutation = useMutation({
    mutationFn: updateCustomerProfile,
    onSuccess: (updatedUser) => {
      updateUser(updatedUser);
      setMessage({ tone: 'success', text: 'Profile updated.' });
    },
    onError: (error) => applyFormError(error, profileForm.setError),
  });

  const saveAddressMutation = useMutation({
    mutationFn: (values: AddressForm) => {
      const payload = {
        ...values,
        label: values.label || null,
        recipient_name: values.recipient_name || null,
        phone: values.phone || null,
        delivery_notes: values.delivery_notes || null,
        latitude: location.coords?.latitude ?? editingAddress?.latitude ?? null,
        longitude: location.coords?.longitude ?? editingAddress?.longitude ?? null,
      };

      return editingAddress ? updateCustomerAddress(editingAddress.id, payload) : createCustomerAddress(payload);
    },
    onSuccess: async () => {
      setMessage({ tone: 'success', text: editingAddress ? 'Address updated.' : 'Address saved.' });
      setEditingAddress(null);
      setAddressFormOpen(false);
      addressForm.reset(emptyAddressDefaults(user));
      await queryClient.invalidateQueries({ queryKey: queryKeys.customerAddresses });
    },
    onError: (error) => applyFormError(error, addressForm.setError),
  });

  const deleteAddressMutation = useMutation({
    mutationFn: (address: CustomerAddress) => deleteCustomerAddress(address.id),
    onSuccess: async () => {
      setMessage({ tone: 'success', text: 'Address deleted.' });
      setPendingAction(null);
      await queryClient.invalidateQueries({ queryKey: queryKeys.customerAddresses });
    },
    onError: (error) => {
      setMessage({ tone: 'error', text: normalizeApiError(error).message });
      setPendingAction(null);
    },
  });

  const registerDeviceMutation = useMutation({
    mutationFn: async () => registerDevice(await createPushRegistrationPayload()),
    onSuccess: async () => {
      setMessage({ tone: 'success', text: 'Order notifications enabled on this device.' });
      await queryClient.invalidateQueries({ queryKey: queryKeys.devices });
    },
    onError: (error) => setMessage({ tone: 'error', text: userFacingError(error) }),
  });

  const revokeDeviceMutation = useMutation({
    mutationFn: (device: UserDevice) => revokeDevice(device.id),
    onSuccess: async () => {
      setMessage({ tone: 'success', text: 'Device notifications removed.' });
      setPendingAction(null);
      await queryClient.invalidateQueries({ queryKey: queryKeys.devices });
    },
    onError: (error) => {
      setMessage({ tone: 'error', text: normalizeApiError(error).message });
      setPendingAction(null);
    },
  });

  async function confirmLogout() {
    await logout();
  }

  function editAddress(address: CustomerAddress) {
    setEditingAddress(address);
    setAddressFormOpen(true);
    setActiveSection('addresses');
    addressForm.reset({
      label: address.label ?? '',
      recipient_name: address.recipient_name ?? user?.name ?? '',
      phone: address.phone ?? user?.phone ?? '',
      address: address.address,
      delivery_notes: address.delivery_notes ?? '',
      is_default: address.is_default,
    });
  }

  function addAddress() {
    setEditingAddress(null);
    setAddressFormOpen(true);
    setActiveSection('addresses');
    addressForm.reset(emptyAddressDefaults(user));
  }

  const addresses = addressesQuery.data ?? [];
  const devices = devicesQuery.data ?? [];
  const pushSupport = getPushRegistrationSupport();

  return (
    <AppScreen
      keyboard
      refreshing={addressesQuery.isRefetching || devicesQuery.isRefetching}
      onRefresh={() => {
        void addressesQuery.refetch();
        void devicesQuery.refetch();
      }}
      contentStyle={styles.screen}
    >
      <View style={styles.hero}>
        <View style={styles.topRow}>
          <View style={styles.avatar}>
            <AppText variant="title" color={colors.brand.primary}>
              {(user?.name ?? 'Customer').slice(0, 1).toUpperCase()}
            </AppText>
          </View>
          <View style={styles.flex}>
            <AppText variant="caption" color={colors.brand.primary}>CUSTOMER PROFILE</AppText>
            <AppText variant="h2" numberOfLines={1}>{user?.name ?? 'Customer'}</AppText>
            <AppText color={colors.text.secondary} numberOfLines={1}>{user?.email}</AppText>
          </View>
          <StatusBadge status={user?.role ?? 'customer'} />
        </View>
        <View style={styles.profileStats}>
          <ProfileStat icon={appIcons.contact} label="Phone" value={user?.phone || 'Add'} />
          <ProfileStat icon={appIcons.home} label="Address" value={String(addresses.length)} />
          <ProfileStat icon={appIcons.profile} label="Alerts" value={String(devices.length)} />
        </View>
      </View>

      {message ? <FeedbackMessage tone={message.tone} message={message.text} /> : null}

      <View style={styles.segmented}>
        <SectionTab label="Account" active={activeSection === 'account'} onPress={() => setActiveSection('account')} />
        <SectionTab label="Addresses" active={activeSection === 'addresses'} onPress={() => setActiveSection('addresses')} />
        <SectionTab label="Alerts" active={activeSection === 'notifications'} onPress={() => setActiveSection('notifications')} />
      </View>

      {activeSection === 'account' ? (
        <>
          <AppCard style={styles.card}>
            <SectionTitle title="Account details" subtitle="Used for checkout receipts and order updates." />
            {profileForm.formState.errors.root ? (
              <FeedbackMessage tone="error" message={profileForm.formState.errors.root.message ?? 'Unable to update profile.'} />
            ) : null}
            <Controller
              control={profileForm.control}
              name="name"
              render={({ field, fieldState }) => (
                <AppInput label="Name" value={field.value} onChangeText={field.onChange} error={fieldState.error?.message} />
              )}
            />
            <View style={styles.lockedEmailRow}>
              <View style={styles.flex}>
                <AppText variant="caption" color={colors.text.secondary}>
                  Email
                </AppText>
                <AppText variant="body" numberOfLines={1}>
                  {user?.email ?? 'No email available'}
                </AppText>
              </View>
              <View style={styles.lockedBadge}>
                <AppText variant="caption" color={colors.text.secondary}>
                  Locked
                </AppText>
              </View>
            </View>
            <AppText variant="caption" color={colors.text.secondary}>
              Email is used for login and cannot be changed from your profile.
            </AppText>
            <Controller
              control={profileForm.control}
              name="phone"
              render={({ field, fieldState }) => (
                <AppInput
                  label="Phone"
                  value={field.value ?? ''}
                  onChangeText={field.onChange}
                  keyboardType="phone-pad"
                  error={fieldState.error?.message}
                />
              )}
            />
            <AppButton
              label="Save profile"
              loading={profileMutation.isPending}
              fullWidth
              onPress={profileForm.handleSubmit((values) => profileMutation.mutate(values))}
            />
          </AppCard>

          <Pressable
            accessibilityRole="button"
            onPress={() => setPendingAction({ type: 'logout' })}
            style={({ pressed }) => [styles.logoutRow, pressed && styles.pressed]}
          >
            <View style={styles.logoutIcon}>
              <AppIcon name={appIcons.logout} size={18} color={colors.semantic.danger} />
            </View>
            <View style={styles.flex}>
              <AppText variant="title">Logout</AppText>
              <AppText color={colors.text.secondary}>Remove secure access from this device.</AppText>
            </View>
            <AppIcon name={appIcons.chevronRight} size={18} color={colors.text.secondary} />
          </Pressable>
        </>
      ) : null}

      {activeSection === 'addresses' ? (
        <AppCard style={styles.card}>
          <View style={styles.sectionHeaderRow}>
            <View style={styles.flex}>
              <SectionTitle title="Delivery addresses" subtitle={`${addresses.length} saved address${addresses.length === 1 ? '' : 'es'}`} />
            </View>
            {!addressFormOpen ? <AppButton label="Add" variant="secondary" onPress={addAddress} /> : null}
          </View>

          {addressFormOpen ? (
            <View style={styles.formPanel}>
              <SectionTitle title={editingAddress ? 'Edit address' : 'New address'} subtitle="Save once, checkout faster next time." />
              {addressForm.formState.errors.root ? (
                <FeedbackMessage tone="error" message={addressForm.formState.errors.root.message ?? 'Unable to save address.'} />
              ) : null}
              <Controller
                control={addressForm.control}
                name="label"
                render={({ field, fieldState }) => (
                  <AppInput label="Label" placeholder="Home, Work, Reception" value={field.value ?? ''} onChangeText={field.onChange} error={fieldState.error?.message} />
                )}
              />
              <Controller
                control={addressForm.control}
                name="recipient_name"
                render={({ field, fieldState }) => (
                  <AppInput label="Recipient" value={field.value ?? ''} onChangeText={field.onChange} error={fieldState.error?.message} />
                )}
              />
              <Controller
                control={addressForm.control}
                name="phone"
                render={({ field, fieldState }) => (
                  <AppInput label="Phone" value={field.value ?? ''} onChangeText={field.onChange} keyboardType="phone-pad" error={fieldState.error?.message} />
                )}
              />
              <Controller
                control={addressForm.control}
                name="address"
                render={({ field, fieldState }) => (
                  <AppInput
                    label="Address"
                    value={field.value}
                    onChangeText={field.onChange}
                    multiline
                    numberOfLines={2}
                    textAlignVertical="top"
                    style={styles.addressInput}
                    error={fieldState.error?.message}
                  />
                )}
              />
              <Controller
                control={addressForm.control}
                name="delivery_notes"
                render={({ field, fieldState }) => (
                  <AppInput
                    label="Notes"
                    value={field.value ?? ''}
                    onChangeText={field.onChange}
                    multiline
                    numberOfLines={2}
                    textAlignVertical="top"
                    style={styles.notesInput}
                    error={fieldState.error?.message}
                  />
                )}
              />
              <Controller
                control={addressForm.control}
                name="is_default"
                render={({ field }) => (
                  <AppCheckbox checked={field.value} label="Use as default address" onChange={field.onChange} />
                )}
              />
              <View style={styles.actionRow}>
                <AppButton
                  label={location.coords ? 'Location ready' : 'Use location'}
                  variant="outline"
                  loading={location.loading}
                  onPress={() => void location.requestCurrentLocation()}
                />
                <AppButton
                  label="Cancel"
                  variant="outline"
                  onPress={() => {
                    setEditingAddress(null);
                    setAddressFormOpen(false);
                    addressForm.reset(emptyAddressDefaults(user));
                  }}
                />
              </View>
              {location.error ? <FeedbackMessage tone="warning" message={location.error} /> : null}
              <AppButton
                label={editingAddress ? 'Update address' : 'Save address'}
                loading={saveAddressMutation.isPending}
                fullWidth
                onPress={addressForm.handleSubmit((values) => saveAddressMutation.mutate(values))}
              />
            </View>
          ) : null}

          {addresses.length === 0 && !addressFormOpen ? (
            <CompactEmpty title="No saved addresses" message="Add a delivery address to make checkout faster." actionLabel="Add address" onAction={addAddress} />
          ) : null}

          {addresses.map((address) => (
            <AddressRow
              key={address.id}
              address={address}
              onEdit={() => editAddress(address)}
              onDelete={() => setPendingAction({ type: 'delete-address', address })}
            />
          ))}
        </AppCard>
      ) : null}

      {activeSection === 'notifications' ? (
        <AppCard style={styles.card}>
          <SectionTitle title="Order notifications" subtitle={`${devices.length} active device${devices.length === 1 ? '' : 's'}`} />
          <View style={styles.notificationHero}>
            <View style={styles.notificationIcon}>
              <AppIcon name={appIcons.orders} size={20} color={colors.brand.primary} />
            </View>
            <View style={styles.flex}>
              <AppText variant="title">Stay updated</AppText>
              <AppText color={colors.text.secondary}>Get order status and delivery updates on this device.</AppText>
            </View>
          </View>
          {pushSupport.supported ? (
            <AppButton
              label="Enable notifications"
              loading={registerDeviceMutation.isPending}
              fullWidth
              onPress={() => registerDeviceMutation.mutate()}
            />
          ) : (
            <FeedbackMessage tone="info" message={pushSupport.reason ?? 'Push notifications are not available on this device.'} />
          )}
          {devices.length === 0 ? (
            <CompactEmpty
              title="No active devices"
              message={pushSupport.supported ? 'Enable notifications to receive order updates.' : 'This preview can still manage profile and addresses safely.'}
            />
          ) : null}
          {devices.map((device) => (
            <DeviceRow
              key={device.id}
              device={device}
              onRemove={() => setPendingAction({ type: 'revoke-device', device })}
            />
          ))}
        </AppCard>
      ) : null}

      <ConfirmModal
        visible={Boolean(pendingAction)}
        title={confirmTitle(pendingAction)}
        message={confirmMessage(pendingAction)}
        confirmLabel="Confirm"
        destructive
        loading={deleteAddressMutation.isPending || revokeDeviceMutation.isPending}
        onConfirm={() => {
          if (!pendingAction) {
            return;
          }

          if (pendingAction.type === 'logout') {
            void confirmLogout();
          }

          if (pendingAction.type === 'delete-address') {
            deleteAddressMutation.mutate(pendingAction.address);
          }

          if (pendingAction.type === 'revoke-device') {
            revokeDeviceMutation.mutate(pendingAction.device);
          }
        }}
        onCancel={() => setPendingAction(null)}
      />
    </AppScreen>
  );
}

function emptyAddressDefaults(user: { name?: string | null; phone?: string | null } | null | undefined): AddressForm {
  return {
    label: '',
    recipient_name: user?.name ?? '',
    phone: user?.phone ?? '',
    address: '',
    delivery_notes: '',
    is_default: true,
  };
}

function applyFormError<T extends FieldValues>(
  error: unknown,
  setError: UseFormSetError<T>,
) {
  const normalized = normalizeApiError(error);
  Object.entries(normalized.validationErrors).forEach(([field, messages]) => {
    setError(field as Path<T>, { message: messages[0] });
  });
  setError('root', { message: normalized.message });
}

function userFacingError(error: unknown): string {
  const normalized = normalizeApiError(error);

  if (normalized.status !== null || normalized.isNetworkError) {
    return normalized.message;
  }

  if (error instanceof Error) {
    return error.message;
  }

  return normalized.message;
}

function confirmTitle(action: PendingAction | null): string {
  if (action?.type === 'delete-address') {
    return 'Delete address?';
  }

  if (action?.type === 'revoke-device') {
    return 'Remove device?';
  }

  return 'Logout?';
}

function confirmMessage(action: PendingAction | null): string {
  if (action?.type === 'delete-address') {
    return 'This saved address will be removed from your account.';
  }

  if (action?.type === 'revoke-device') {
    return 'This device will stop receiving order notifications.';
  }

  return 'Your secure session token will be removed from this device.';
}

function SectionTab({ label, active, onPress }: { label: string; active: boolean; onPress: () => void }) {
  return (
    <Pressable
      accessibilityRole="button"
      accessibilityState={{ selected: active }}
      onPress={onPress}
      style={({ pressed }) => [styles.sectionTab, active && styles.sectionTabActive, pressed && styles.pressed]}
    >
      <AppText variant="caption" color={active ? colors.text.inverse : colors.text.secondary}>
        {label}
      </AppText>
    </Pressable>
  );
}

function CompactEmpty({
  title,
  message,
  actionLabel,
  onAction,
}: {
  title: string;
  message: string;
  actionLabel?: string;
  onAction?: () => void;
}) {
  return (
    <View style={styles.compactEmpty}>
      <View style={styles.emptyIcon}>
        <AppIcon name={appIcons.home} size={18} color={colors.brand.primary} />
      </View>
      <View style={styles.flex}>
        <AppText variant="title">{title}</AppText>
        <AppText color={colors.text.secondary}>{message}</AppText>
      </View>
      {actionLabel && onAction ? (
        <AppButton label={actionLabel} variant="secondary" onPress={onAction} />
      ) : null}
    </View>
  );
}

function AddressRow({
  address,
  onEdit,
  onDelete,
}: {
  address: CustomerAddress;
  onEdit: () => void;
  onDelete: () => void;
}) {
  return (
    <View style={styles.addressRow}>
      <View style={styles.rowIcon}>
        <AppIcon name={appIcons.home} size={18} color={colors.brand.primary} />
      </View>
      <View style={styles.flex}>
        <View style={styles.inlineTitle}>
          <AppText variant="title" numberOfLines={1}>{address.label ?? 'Saved address'}</AppText>
          {address.is_default ? <StatusBadge status="default" /> : null}
        </View>
        <AppText color={colors.text.secondary} numberOfLines={2}>{address.address}</AppText>
        {address.delivery_notes ? (
          <AppText variant="caption" color={colors.text.secondary} numberOfLines={1}>{address.delivery_notes}</AppText>
        ) : null}
        <View style={styles.inlineActions}>
          <Pressable accessibilityRole="button" onPress={onEdit} style={styles.textAction}>
            <AppText variant="caption" color={colors.brand.primary}>Edit</AppText>
          </Pressable>
          <Pressable accessibilityRole="button" onPress={onDelete} style={styles.textActionDanger}>
            <AppText variant="caption" color={colors.semantic.danger}>Delete</AppText>
          </Pressable>
        </View>
      </View>
    </View>
  );
}

function DeviceRow({ device, onRemove }: { device: UserDevice; onRemove: () => void }) {
  return (
    <View style={styles.deviceRow}>
      <View style={styles.rowIcon}>
        <AppIcon name={appIcons.profile} size={18} color={colors.brand.primary} />
      </View>
      <View style={styles.flex}>
        <AppText variant="title" numberOfLines={1}>{device.device_name ?? 'Registered device'}</AppText>
        <AppText color={colors.text.secondary} numberOfLines={1}>
          {device.platform} · last seen {formatDateTime(device.last_seen_at)}
        </AppText>
      </View>
      <Pressable accessibilityRole="button" onPress={onRemove} style={styles.removePill}>
        <AppText variant="caption" color={colors.brand.primary}>Remove</AppText>
      </Pressable>
    </View>
  );
}

const styles = StyleSheet.create({
  screen: {
    gap: spacing.sm,
  },
  hero: {
    gap: spacing.md,
    borderRadius: radius.xl,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.card,
    padding: spacing.md,
    ...shadows.card,
  },
  card: {
    gap: spacing.md,
    padding: spacing.md,
  },
  topRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
  },
  avatar: {
    height: 48,
    width: 48,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.lg,
    backgroundColor: colors.brand.soft,
  },
  profileStats: {
    flexDirection: 'row',
    gap: spacing.xs,
  },
  profileStat: {
    flex: 1,
    gap: spacing.xs,
    minHeight: 58,
    borderRadius: radius.md,
    backgroundColor: colors.surface.muted,
    padding: spacing.sm,
  },
  statIcon: {
    width: 26,
    height: 26,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.sm,
    backgroundColor: colors.brand.soft,
  },
  accountPreview: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    borderRadius: radius.xl,
    backgroundColor: colors.surface.muted,
    padding: spacing.md,
  },
  rowBetween: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: spacing.md,
  },
  segmented: {
    flexDirection: 'row',
    borderRadius: radius.pill,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.card,
    padding: spacing.xs,
  },
  sectionTab: {
    flex: 1,
    minHeight: 40,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.pill,
  },
  sectionTabActive: {
    backgroundColor: colors.brand.primary,
  },
  sectionHeaderRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: spacing.sm,
  },
  formPanel: {
    gap: spacing.md,
    borderRadius: radius.xl,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.muted,
    padding: spacing.md,
  },
  addressCard: {
    gap: spacing.md,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.muted,
    padding: spacing.md,
  },
  deviceRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.muted,
    padding: spacing.md,
  },
  addressRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: spacing.sm,
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.muted,
    padding: spacing.md,
  },
  rowIcon: {
    width: 36,
    height: 36,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.md,
    backgroundColor: colors.brand.soft,
  },
  inlineTitle: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  inlineActions: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  textAction: {
    minHeight: 32,
    justifyContent: 'center',
    borderRadius: radius.pill,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.md,
  },
  textActionDanger: {
    minHeight: 32,
    justifyContent: 'center',
    borderRadius: radius.pill,
    backgroundColor: colors.brand.soft,
    paddingHorizontal: spacing.md,
  },
  removePill: {
    minHeight: 34,
    justifyContent: 'center',
    borderRadius: radius.pill,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.md,
  },
  compactEmpty: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    borderRadius: radius.lg,
    backgroundColor: colors.surface.muted,
    padding: spacing.md,
  },
  emptyIcon: {
    width: 38,
    height: 38,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.pill,
    backgroundColor: colors.brand.soft,
  },
  notificationHero: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    borderRadius: radius.lg,
    backgroundColor: colors.gold.pale,
    padding: spacing.md,
  },
  notificationIcon: {
    width: 40,
    height: 40,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.md,
    backgroundColor: colors.surface.card,
  },
  logoutRow: {
    minHeight: 72,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    borderRadius: radius.xl,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.card,
    padding: spacing.md,
    ...shadows.card,
  },
  logoutIcon: {
    width: 38,
    height: 38,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.md,
    backgroundColor: colors.brand.soft,
  },
  actionRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  flex: {
    flex: 1,
    minWidth: 0,
    gap: spacing.xs,
  },
  addressInput: {
    minHeight: 74,
  },
  notesInput: {
    minHeight: 64,
  },
  lockedEmailRow: {
    minHeight: 56,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.muted,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.sm,
  },
  lockedBadge: {
    minHeight: 30,
    justifyContent: 'center',
    borderRadius: radius.pill,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.md,
  },
  pressed: {
    transform: [{ scale: 0.98 }],
    opacity: 0.9,
  },
});

function ProfileStat({ icon, label, value }: { icon: AppIconName; label: string; value: string }) {
  return (
    <View style={styles.profileStat}>
      <View style={styles.statIcon}>
        <AppIcon name={icon} size={20} />
      </View>
      <AppText variant="caption" color={colors.text.secondary}>{label}</AppText>
      <AppText variant="title" numberOfLines={1}>{value}</AppText>
    </View>
  );
}
