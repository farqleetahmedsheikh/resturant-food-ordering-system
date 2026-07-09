import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { router, useLocalSearchParams } from 'expo-router';
import { StyleSheet } from 'react-native';

import { getAdminCategories, getAdminMenuItem, updateAdminMenuItem } from '@/src/api/admin.api';
import { normalizeApiError } from '@/src/api/api-error';
import { MenuItemEditor } from '@/src/components/admin/MenuItemEditor';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { spacing } from '@/src/theme';
import type { AdminMenuItemPayload } from '@/src/types/admin';

export default function EditAdminMenuItemScreen() {
  const queryClient = useQueryClient();
  const params = useLocalSearchParams<{ id?: string | string[] }>();
  const id = Array.isArray(params.id) ? params.id[0] : params.id;
  const itemQuery = useQuery({
    queryKey: queryKeys.adminMenuItem(id),
    queryFn: () => getAdminMenuItem(id ?? ''),
    enabled: Boolean(id),
  });
  const categoriesQuery = useQuery({
    queryKey: queryKeys.adminCategories,
    queryFn: getAdminCategories,
  });
  const mutation = useMutation({
    mutationFn: (payload: AdminMenuItemPayload) => updateAdminMenuItem(id ?? '', payload),
    onSuccess: async (item) => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: ['admin', 'menu-items'] }),
        queryClient.invalidateQueries({ queryKey: queryKeys.adminMenuItem(item.id) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.adminDashboard }),
      ]);
    },
  });

  if (itemQuery.isLoading || categoriesQuery.isLoading) {
    return <LoadingScreen label="Loading item editor..." />;
  }

  if (itemQuery.isError || !itemQuery.data) {
    return <ErrorState message="Unable to load this menu item." onRetry={() => void itemQuery.refetch()} />;
  }

  if (categoriesQuery.isError) {
    return <ErrorState message="Unable to load menu categories." onRetry={() => void categoriesQuery.refetch()} />;
  }

  const serverError = mutation.isError ? normalizeApiError(mutation.error).message : null;

  return (
    <AppScreen
      keyboard
      refreshing={itemQuery.isRefetching || categoriesQuery.isRefetching}
      onRefresh={() => {
        void itemQuery.refetch();
        void categoriesQuery.refetch();
      }}
      contentStyle={styles.screen}
    >
      <AppHeader title="Edit item" subtitle="Update the live customer menu safely." eyebrow="Admin" />
      {mutation.isSuccess ? <FeedbackMessage tone="success" message="Menu item updated." /> : null}
      <MenuItemEditor
        item={mutation.data ?? itemQuery.data}
        categories={categoriesQuery.data ?? []}
        submitLabel="Save item"
        submitting={mutation.isPending}
        serverError={serverError}
        onSubmit={(payload) => mutation.mutate(payload)}
        onCancel={() => router.back()}
      />
    </AppScreen>
  );
}

const styles = StyleSheet.create({
  screen: {
    gap: spacing.sm,
  },
});
