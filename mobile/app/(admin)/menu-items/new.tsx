import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { router } from 'expo-router';
import { StyleSheet } from 'react-native';

import { createAdminMenuItem, getAdminCategories } from '@/src/api/admin.api';
import { normalizeApiError } from '@/src/api/api-error';
import { MenuItemEditor } from '@/src/components/admin/MenuItemEditor';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { spacing } from '@/src/theme';
import type { AdminMenuItemPayload } from '@/src/types/admin';

export default function NewAdminMenuItemScreen() {
  const queryClient = useQueryClient();
  const categoriesQuery = useQuery({
    queryKey: queryKeys.adminCategories,
    queryFn: getAdminCategories,
  });
  const mutation = useMutation({
    mutationFn: createAdminMenuItem,
    onSuccess: async (item) => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: ['admin', 'menu-items'] }),
        queryClient.invalidateQueries({ queryKey: queryKeys.adminDashboard }),
      ]);
      router.replace({ pathname: '/(admin)/menu-items/[id]', params: { id: String(item.id) } });
    },
  });

  if (categoriesQuery.isLoading) {
    return <LoadingScreen label="Preparing item editor..." />;
  }

  if (categoriesQuery.isError) {
    return <ErrorState message="Unable to load categories for this item." onRetry={() => void categoriesQuery.refetch()} />;
  }

  const serverError = mutation.isError ? normalizeApiError(mutation.error).message : null;

  function createItem(payload: AdminMenuItemPayload) {
    mutation.mutate(payload);
  }

  return (
    <AppScreen keyboard contentStyle={styles.screen}>
      <AppHeader title="New item" subtitle="Create a customer-ready menu item." eyebrow="Admin" />
      <MenuItemEditor
        categories={categoriesQuery.data ?? []}
        submitLabel="Create item"
        submitting={mutation.isPending}
        serverError={serverError}
        onSubmit={createItem}
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
